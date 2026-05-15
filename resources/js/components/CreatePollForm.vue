<template>
    <div class="poll-form">
        <div class="form-group">
            <label>Poll Title</label>
            <input v-model="formData.title" type="text" placeholder="Enter title" />
        </div>

        <div class="form-group">
            <label>Question <span class="required">*</span></label>
            <textarea v-model="formData.question" placeholder="Enter question"></textarea>
        </div>

        <div class="form-group">
            <label>Duration (days)</label>
            <input v-model.number="durationInDays" type="number" min="1" />
        </div>

        <div class="form-group toggles">
            <label class="toggle-label">
                <input type="checkbox" v-model="formData.allow_multiple_choices" />
                Allow multiple choices
            </label>
            <label class="toggle-label">
                <input type="checkbox" v-model="formData.allow_vote_change" />
                Allow vote change
            </label>
            <label class="toggle-label">
                <input type="checkbox" v-model="formData.results_public" />
                Public results
            </label>
        </div>

        <div class="form-group">
            <label>Options <span class="required">*</span></label>
            <div v-for="(option, index) in formData.options" :key="`new-${index}`" class="option-row">
                <input v-model="option.label" type="text" :placeholder="`Option ${index + 1}`" />
                <button type="button" class="btn-remove" :disabled="formData.options.length <= 2"
                    @click="removeOption(index)">
                    ✕
                </button>
            </div>
            <button type="button" class="btn-add-option" @click="addOption">
                + Add option
            </button>
        </div>

        <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>

        <div class="form-actions">
            <button type="button" class="btn-cancel" @click="$emit('close')">
                Cancel
            </button>
            <button type="button" class="btn-save" @click="handleSubmit" :disabled="isSubmitting">
                {{ isSubmitting ? 'Creating...' : 'Create Poll' }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { reactive, computed, ref } from 'vue';
import { usePollStore } from '@/stores/usePollStore.js';

const { createPoll } = usePollStore();

const emit = defineEmits(['created', 'close']);

const isSubmitting = ref(false);
const errorMessage = ref(null);

const defaultFormData = () => ({
    title: '',
    question: '',
    duration: null,
    allow_multiple_choices: false,
    allow_vote_change: false,
    results_public: false,
    options: [{ label: '' }, { label: '' }]
});

const formData = reactive(defaultFormData());

const durationInDays = computed({
    get: () => formData.duration ? Math.round(formData.duration / 86400) : null,
    set: (days) => formData.duration = days ? days * 86400 : null
});

const addOption = () => {
    formData.options.push({ label: '' });
};

const removeOption = (index) => {
    if (formData.options.length > 2) {
        formData.options.splice(index, 1);
    }
};

const handleSubmit = async () => {
    errorMessage.value = null;

    if (!formData.question) {
        errorMessage.value = "Question is required.";
        return;
    }

    if (formData.options.some(o => !o.label.trim())) {
        errorMessage.value = "All options must have a label.";
        return;
    }

    isSubmitting.value = true;

    try {
        // createPoll doit retourner le poll créé en cas de succès, ou null/false en cas d'échec
        const newPoll = await createPoll({ ...formData });

        if (newPoll) {
            emit('created', newPoll); // On passe le nouveau poll au parent
        } else {
            errorMessage.value = "Failed to create poll. Please try again.";
            // formData est conservé tel quel pour que l'utilisateur puisse réessayer
        }
    } catch (err) {
        errorMessage.value = "An unexpected error occurred.";
    } finally {
        isSubmitting.value = false;
    }
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
