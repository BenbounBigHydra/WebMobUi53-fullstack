<template>
  <div class="poll-form">
    <div class="form-group">
      <label>Poll Title</label>
      <input v-model="formData.title" type="text" placeholder="Enter title" />
    </div>

    <div class="form-group">
      <label>Question</label>
      <textarea v-model="formData.question" placeholder="Enter question"></textarea>
    </div>

    <div class="form-actions">
      <button type="button" class="btn-cancel" @click="$emit('close')">
        Cancel
      </button>
      <!-- Click handler calls the submit logic -->
      <button type="button" class="btn-save" @click="handleSubmit">
        Save Changes
      </button>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue';

const props = defineProps({
  poll: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['submit', 'close']);

// Local state to hold form data
const formData = reactive({ ...props.poll });

const handleSubmit = () => {
  // Basic validation
  if (!formData.title || !formData.question) {
    alert("Please fill in all fields.");
    return;
  }

  // Emit the data back to the PollTable
  emit('submit', formData);
};
</script>

<style scoped>
.poll-form { display: flex; flex-direction: column; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.5rem; }
.form-group input, .form-group textarea {
  padding: 0.5rem;
  border: 1px solid #ccc;
  border-radius: 4px;
}
.form-actions { display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1rem; }
.btn-cancel { background: #eee; border: none; padding: 0.5rem 1rem; cursor: pointer; }
.btn-save { background: #3490dc; color: white; border: none; padding: 0.5rem 1rem; cursor: pointer; border-radius: 4px; }
</style>
