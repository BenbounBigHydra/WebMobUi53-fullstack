<script setup>
import { ref, computed, reactive, onUnmounted } from 'vue'

import { useFetchApi } from '@/composables/useFetchApi.js'
const { fetchApi } = useFetchApi()

import { usePollStore } from '@/stores/usePollStore.js';
const { submitVote } = usePollStore()
const voteError = ref(null)
const voteLoading = ref(false)

const props = defineProps({
    poll: { type: Object, required: true },
    userId: { type: Number, default: null },
    userVote: { type: Array, default: null },
    loginUrl: { type: String, default: null },
})
const options = reactive(props.poll.options.map(o => ({ ...o })))

const isDraft = computed(() => props.poll.is_draft === 1)
const isMultiple = computed(() => props.poll.allow_multiple_choices === 1)
const canChange = computed(() => props.poll.allow_vote_change === 1)
const isPublic = computed(() => props.poll.results_public === 1)
const isLoggedIn = computed(() => props.userId !== null)
const isOwner = computed(() => props.userId === props.poll.user_id)
const now = new Date()
const voted = ref(!!props.userVote)
const currentVoteIds = ref(props.userVote ? [...props.userVote] : [])

const hasStarted = computed(() =>
    props.poll.started_at !== null && new Date(props.poll.started_at) <= now
)

const hasEnded = computed(() =>
    props.poll.ends_at !== null && new Date(props.poll.ends_at) < now
)

const isActive = computed(() => hasStarted.value && !hasEnded.value)

// Pré-sélection de l'ancien vote
const selectedId = ref(
    !isMultiple.value && props.userVote ? props.userVote[0] : null
)
const selectedIds = ref(
    isMultiple.value && props.userVote ? new Set(props.userVote) : new Set()
)

const canVote = computed(() =>
    isLoggedIn.value &&
    !isOwner.value &&
    !isDraft.value &&
    isActive.value &&
    (!voted.value || canChange.value)
)

const showResults = computed(() => isOwner.value || isPublic.value || hasEnded.value)

const hasSelection = computed(() =>
    isMultiple.value ? selectedIds.value.size > 0 : selectedId.value !== null
)

function toggle(id) {
    if (!canVote.value) return
    if (isMultiple.value) {
        const s = new Set(selectedIds.value)
        s.has(id) ? s.delete(id) : s.add(id)
        selectedIds.value = s
    } else {
        selectedId.value = id
    }
}

function isSelected(id) {
    return isMultiple.value
        ? selectedIds.value.has(id)
        : selectedId.value === id
}

const totalVotes = computed(() =>
    options.reduce((sum, o) => sum + (o.votes_count ?? 0), 0)
)

function getPercent(option) {
    if (totalVotes.value === 0) return 0
    return Math.round(((option.votes_count ?? 0) / totalVotes.value) * 100)
}

async function handleSubmit() {
    if (!hasSelection.value || !canVote.value) return

    const payload = isMultiple.value ? [...selectedIds.value] : [selectedId.value]
    voteError.value = null
    voteLoading.value = true

    try {
        await submitVote(props.poll.id, payload)

        // Mise à jour locale des compteurs (code déjà en place)
        if (voted.value && canChange.value) {
            currentVoteIds.value.forEach(oldId => {
                const old = options.find(o => o.id === oldId)
                if (old && old.votes_count > 0) old.votes_count--
            })
        }
        payload.forEach(id => {
            const opt = options.find(o => o.id === id)
            if (opt) opt.votes_count = (opt.votes_count ?? 0) + 1
        })
        currentVoteIds.value = payload
        voted.value = true

    } catch (e) {
        voteError.value = e.data?.message ?? e.statusText ?? 'Erreur lors du vote.'
    } finally {
        voteLoading.value = false
    }
}

// Polling — mise à jour des résultats toutes les 5 secondes
const POLL_INTERVAL = 5000

async function refreshResults() {
    try {
        const data = await fetchApi({ url: `polls/${props.poll.secret_token}` })
        data.options.forEach(updated => {
            const local = options.find(o => o.id === updated.id)
            if (local) local.votes_count = updated.votes_count
        })
    } catch {
        // silencieux
    }
}

const pollingTimer = showResults.value
    ? setInterval(refreshResults, POLL_INTERVAL)
    : null

onUnmounted(() => {
    if (pollingTimer) clearInterval(pollingTimer)
})
</script>

<template>
    <div class="poll-card">
        <h2 class="poll-title">{{ poll.title }}</h2>
        <p class="poll-question">{{ poll.question }}</p>

        <div v-if="isDraft" class="info-banner">
            Ce sondage est en brouillon et n'accepte pas encore de votes.
        </div>
        <div v-else-if="!hasStarted" class="info-banner">
            Ce sondage n'a pas encore commencé.
        </div>

        <template v-else>
            <div v-if="hasEnded" class="info-banner">Ce sondage est terminé.</div>

            <template v-else>
                <div v-if="isOwner" class="info-banner">
                    Vous êtes le créateur de ce sondage — résultats en temps réel.
                </div>
                <div v-else-if="!isLoggedIn" class="info-banner">
                    <a :href="loginUrl">Connectez-vous</a> pour voter.
                </div>
                <div v-else-if="voted && !canChange" class="info-banner">
                    Vous avez déjà voté.
                </div>

                <p v-if="isMultiple && canVote" class="poll-hint">Plusieurs réponses possibles</p>

                <div class="poll-options">
                    <button v-for="option in options" :key="option.id" class="poll-option"
                        :class="{ selected: isSelected(option.id), passive: !canVote }" :disabled="!canVote"
                        @click="toggle(option.id)">
                        <span v-if="canVote" class="indicator" :class="{ checkbox: isMultiple }">
                            <span v-if="isSelected(option.id)" class="indicator-inner" />
                        </span>
                        <span class="option-label">{{ option.label }}</span>
                    </button>
                </div>

                <div v-if="canVote" class="submit-row">
                    <button class="submit-btn" :disabled="!hasSelection || voteLoading" @click="handleSubmit">
                        {{ voteLoading ? 'Envoi…' : (voted && canChange ? 'Modifier mon vote' : 'Voter') }}
                    </button>
                    <span v-if="voteError" class="error-msg">{{ voteError }}</span>
                </div>
            </template>

            <!-- Graphique : affiché pour le sondage terminé ET actif si showResults -->
            <div v-if="showResults" class="chart">
                <p class="total-votes">{{ totalVotes }} vote{{ totalVotes !== 1 ? 's' : '' }} au total</p>
                <div v-for="option in options" :key="option.id" class="chart-row">
                    <span class="chart-label">{{ option.label }}</span>
                    <div class="chart-bar-track">
                        <div class="chart-bar-fill" :class="{ 'chart-bar-voted': currentVoteIds.includes(option.id) }"
                            :style="{ width: getPercent(option) + '%' }" />
                    </div>
                    <span class="chart-percent">{{ getPercent(option) }}%</span>
                </div>
            </div>
        </template>
    </div>
</template>

<style scoped>
.draft-banner {
    padding: 12px 16px;
    border: 0.5px solid rgba(255, 255, 255, 0.25);
    border-radius: 8px;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.5);
}

.poll-card {
    background: transparent;
    border: 0.5px solid rgba(255, 255, 255, 0.3);
    border-radius: 12px;
    padding: 2rem;
    max-width: 560px;
    margin: 2rem auto;
}

.poll-title {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.5);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin: 0 0 8px;
}

.poll-question {
    font-size: 20px;
    font-weight: 500;
    margin: 0 0 6px;
    color: white;
}

/* .poll-label {
    font-size: 15px;
    color: rgba(255, 255, 255, 0.7);
    margin: 0 0 1.5rem;
    line-height: 1.5;
} */

.poll-hint {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.4);
    margin: -1rem 0 1rem;
}

.poll-options {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.poll-option {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border: 0.5px solid rgba(255, 255, 255, 0.25);
    border-radius: 8px;
    background: transparent;
    cursor: pointer;
    font-size: 15px;
    text-align: left;
    color: white;
    transition: border-color 0.15s, background 0.15s;
    width: 100%;
}

.poll-option:hover:not(:disabled) {
    border-color: rgba(255, 255, 255, 0.6);
    background: rgba(255, 255, 255, 0.05);
}

.poll-option.selected {
    border-color: white;
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

.poll-option:disabled {
    cursor: default;
}

.indicator {
    width: 18px;
    height: 18px;
    border-radius: 50%;
    border: 0.5px solid rgba(255, 255, 255, 0.4);
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s, border-color 0.15s;
}

.indicator.checkbox {
    border-radius: 4px;
}

.poll-option.selected .indicator {
    background: white;
    border-color: white;
}

.indicator-inner {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #1e293b;
}

.indicator.checkbox .indicator-inner {
    width: 9px;
    height: 5px;
    border-radius: 0;
    background: transparent;
    border-left: 2px solid #1e293b;
    border-bottom: 2px solid #1e293b;
    transform: rotate(-45deg) translateY(-1px);
}

.submit-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 1.5rem;
}

.submit-btn {
    padding: 10px 20px;
    border-radius: 8px;
    border: 0.5px solid rgba(255, 255, 255, 0.4);
    background: transparent;
    font-size: 14px;
    color: white;
    cursor: pointer;
    transition: background 0.15s;
}

.submit-btn:hover:not(:disabled) {
    background: rgba(255, 255, 255, 0.08);
}

.submit-btn:disabled {
    opacity: 0.4;
    cursor: default;
}

.success-msg {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.6);
}

.error-msg {
    font-size: 13px;
    color: rgba(255, 100, 100, 0.8);
}

.chart {
    margin-top: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.total-votes {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.35);
    margin: 0 0 4px;
}

.chart-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.chart-label {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.7);
    width: 120px;
    flex-shrink: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.chart-bar-track {
    flex: 1;
    height: 8px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
    overflow: hidden;
}

.chart-bar-fill {
    height: 100%;
    background: rgba(255, 255, 255, 0.4);
    border-radius: 4px;
    transition: width 0.4s ease;
}

.chart-bar-voted {
    background: rgba(255, 255, 255, 0.85);
}

.chart-percent {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.5);
    width: 36px;
    text-align: right;
    flex-shrink: 0;
}
</style>
