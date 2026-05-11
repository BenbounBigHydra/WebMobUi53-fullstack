<script setup>
import { ref } from 'vue';
import { usePollStore } from '@/stores/usePollStore.js';

const { polls, deletePoll } = usePollStore();

async function delPoll(id) {
    console.log('delete Poll ID:', id);
    await deletePoll(id);
}

async function copyLink(secret_token) {
    const link = `${window.location.origin}/poll/${secret_token}`;
    try {
        await navigator.clipboard.writeText(link);
        // alert('Lien copié dans le presse-papiers !');
    } catch (err) {
        console.error('Erreur lors de la copie :', err);
    }
}

import Modal from './Modal.vue';
import EditPollForm from './EditPollForm.vue';

const showModal = ref(false);
const editingPoll = ref(null);

const openEditModal = (poll) => {
  editingPoll.value = { ...poll }; // Clone to avoid direct store mutation
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingPoll.value = null;
};

const handleUpdate = async (updatedData) => {
  // Call the store action
  const success = await pollStore.editPoll(updatedData.id, updatedData);

  if (success) {
    closeModal(); // Close modal on success
  } else {
    // Handle error (e.g., keep modal open so user can try again)
    console.error("Update failed");
  }
};
</script>

<template>
    <p v-if="polls.length === 0">Aucun sondage.</p>

    <table v-else class="w-full border-collapse text-left">
        <thead>
            <tr>
                <th class="border px-3 py-2">Actions</th>
                <th class="border px-3 py-2">ID</th>
                <th class="border px-3 py-2">Titre</th>
                <th class="border px-3 py-2">Question</th>
                <th class="border px-3 py-2">Brouillon</th>
                <th class="border px-3 py-2">Debut</th>
                <th class="border px-3 py-2">Fin</th>
                <th class="border px-3 py-2">Partager</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="poll in polls" :key="poll.id">
                <td class="border px-3 py-2"><button @click="delPoll(poll.id)">Supp.</button><button
                        v-if="poll.is_draft" @click="openEditModal(poll)">Edit.</button></td>
                <td class="border px-3 py-2">{{ poll.id }}</td>
                <td class="border px-3 py-2">{{ poll.title || '-' }}</td>
                <td class="border px-3 py-2">{{ poll.question }}</td>
                <td class="border px-3 py-2">{{ poll.is_draft ? 'Oui' : 'Non' }}</td>
                <td class="border px-3 py-2">{{ poll.started_at || '-' }}</td>
                <td class="border px-3 py-2">{{ poll.ends_at || '-' }}</td>
                <td class="border px-3 py-2"><button @click="copyLink(poll.secret_token)">Copier lien</button></td>
            </tr>
        </tbody>
        <Modal :isOpen="showModal" @close="closeModal">
            <template #header>
                <h3>Editing: {{ editingPoll?.title }}</h3>
            </template>

            <EditPollForm v-if="editingPoll" :poll="editingPoll" @submit="handleUpdate" @close="closeModal" />
        </Modal>
    </table>
</template>

<style scoped>
button {
    background-color: #e3342f;
    color: white;
    padding: 0.25rem 0.5rem;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
    margin-bottom: 2px;
}
</style>
