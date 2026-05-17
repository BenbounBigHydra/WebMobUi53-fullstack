<template>
    <div class="poll-form">
        <div class="form-group">
            <label>Titre</label>
            <input v-model="formData.title" type="text" placeholder="Enter title" />
        </div>

        <div class="form-group">
            <label>Question <span class="required">*</span></label>
            <textarea v-model="formData.question" placeholder="Enter question"></textarea>
        </div>

        <div class="form-group">
            <label>Durée (jours)</label>
            <input v-model.number="durationInDays" type="number" min="1" placeholder="Leave empty for no limit" />
        </div>

        <div class="form-group toggles">
            <label class="toggle-label">
                <input type="checkbox" v-model="formData.allow_multiple_choices" />
                Choix multiples
            </label>
            <label class="toggle-label">
                <input type="checkbox" v-model="formData.allow_vote_change" />
                Autoriser changement de vote
            </label>
            <label class="toggle-label">
                <input type="checkbox" v-model="formData.results_public" />
                Résultats publics
            </label>
        </div>

        <div class="form-group">
            <label>Options <span class="required">*</span></label>
            <div v-for="(option, index) in formData.options" :key="option.id ?? `new-${index}`" class="option-row">
                <input v-model="option.label" type="text" :placeholder="`Option ${index + 1}`" />
                <button type="button" class="btn-remove" :disabled="formData.options.length <= 2"
                    @click="removeOption(index)">
                    ✕
                </button>
            </div>
            <button type="button" class="btn-add-option" @click="addOption">
                + Option supplémentaire
            </button>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-cancel" @click="$emit('close')">
                Annuler
            </button>
            <button type="button" class="btn-save" @click="handleSubmit">
                Sauvegarder
            </button>
        </div>
    </div>
</template>

<script setup>
import { reactive, computed } from 'vue';

const durationInDays = computed({
    get: () => formData.duration ? Math.round(formData.duration / 86400) : null,
    set: (days) => formData.duration = days ? days * 86400 : null
});

const props = defineProps({
    poll: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['submit', 'close']);

const formData = reactive({
    ...props.poll,
    allow_multiple_choices: !!props.poll.allow_multiple_choices,
    allow_vote_change: !!props.poll.allow_vote_change,
    results_public: !!props.poll.results_public,
    options: props.poll.options ? props.poll.options.map(o => ({ ...o })) : [{ label: '' }, { label: '' }]
});

console.dir(props.poll);

const addOption = () => {
    formData.options.push({ label: '' });
};

const removeOption = (index) => {
    if (formData.options.length > 2) {
        formData.options.splice(index, 1);
    }
};

const handleSubmit = () => {
    if (!formData.question) {
        alert("Question is required.");
        return;
    }

    if (formData.options.some(o => !o.label.trim())) {
        alert("All options must have a label.");
        return;
    }

    emit('submit', formData);
};
</script>

<style scoped>
.poll-form {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.form-group input[type="text"],
.form-group input[type="number"],
.form-group textarea {
    padding: 0.5rem;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.required {
    color: red;
}

.toggles {
    gap: 0.75rem;
}

.toggle-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
}

.option-row {
    display: flex;
    gap: 0.5rem;
    align-items: center;
}

.option-row input {
    flex: 1;
    padding: 0.5rem;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.btn-remove {
    background: none;
    border: 1px solid #ccc;
    border-radius: 4px;
    padding: 0.25rem 0.5rem;
    cursor: pointer;
    color: #e53e3e;
}

.btn-remove:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

.btn-add-option {
    align-self: flex-start;
    background: none;
    border: 1px dashed #3490dc;
    color: #3490dc;
    padding: 0.4rem 0.75rem;
    border-radius: 4px;
    cursor: pointer;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.5rem;
    margin-top: 1rem;
}

.btn-cancel {
    background: #eee;
    border: none;
    padding: 0.5rem 1rem;
    cursor: pointer;
    border-radius: 4px;
}

.btn-save {
    background: #3490dc;
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    cursor: pointer;
    border-radius: 4px;
}
</style>
