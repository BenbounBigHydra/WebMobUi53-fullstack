<script setup>
import { ref, toRaw } from 'vue';
import { usePollStore } from '@/stores/usePollStore.js';

const { polls, deletePoll, editPoll, publishPoll } = usePollStore();

async function delPoll(id) {
    console.log('delete Poll ID:', id);
    await deletePoll(id);
}

async function copyLink(secret_token) {
    const link = `${window.location.origin}/poll/${secret_token}`;
    try {
        await navigator.clipboard.writeText(link);
        alert('Lien copié dans le presse-papiers !');
    } catch (err) {
        console.error('Erreur lors de la copie :', err);
    }
}

import Modal from './Modal.vue';
import EditPollForm from './EditPollForm.vue';
import CreatePollForm from './CreatePollForm.vue';

const showModal = ref(false);
const editingPoll = ref(null);
// const creatingPoll = ref(null);

const openEditModal = (poll) => {
    editingPoll.value = { ...poll }; // Clone to avoid direct store mutation
    showModal.value = true;
};

const openCreateModal = () => {
    showModal.value = true;
};

const onPollCreated = () => {
    // polls.push(newPoll); // ou via une action du store
    closeModal();
};

const closeModal = () => {
    showModal.value = false;
    editingPoll.value = null;
};

const handleUpdate = async (updatedData) => {
    // Call the store action
    console.log(toRaw(updatedData));
    const success = await editPoll(updatedData.id, toRaw(updatedData));

    if (success) {
        closeModal(); // Close modal on success
    } else {
        // Handle error (e.g., keep modal open so user can try again)
        console.error("Update failed");
    }
};
</script>

<template>
    <button class="mainButton" @click="openCreateModal()">Nouveau sondage</button>

    <p v-if="polls.length === 0">Aucun sondage actuellement.</p>

    <table v-else class="w-full border-collapse text-left">
        <thead>
            <tr>
                <th class="border px-3 py-2">Actions</th>
                <th class="border px-3 py-2">ID</th>
                <th class="border px-3 py-2">Titre</th>
                <th class="border px-3 py-2">Question</th>
                <!-- <th class="border px-3 py-2">Draft</th> -->
                <th class="border px-3 py-2">Commence</th>
                <th class="border px-3 py-2">Fini</th>
                <th class="border px-3 py-2">Publier</th>
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
                <!-- <td class="border px-3 py-2">{{ poll.is_draft ? 'Oui' : 'Non' }}</td> -->
                <td class="border px-3 py-2">{{ poll.started_at || '-' }}</td>
                <td class="border px-3 py-2">{{ poll.ends_at || '-' }}</td>
                <td class="border px-3 py-2"><button v-if="poll.is_draft" @click="publishPoll(poll.id)">Démarrer</button></td>
                <td class="border px-3 py-2"><button @click="copyLink(poll.secret_token)">Lien du sondage</button></td>
            </tr>
        </tbody>
    </table>
    <Modal :isOpen="showModal" @close="closeModal">
        <template #header>
            <h3 v-if="editingPoll">Edit.: {{ editingPoll?.title }}</h3>
            <h3 v-else>Creating new poll</h3>
        </template>

        <template #default>
            <EditPollForm v-if="editingPoll" :poll="editingPoll" @submit="(evt) => handleUpdate(evt)"
                @close="closeModal" />
            <CreatePollForm v-else @created="onPollCreated" @close="closeModal" />
        </template>
    </Modal>
</template>

<style scoped>
button {
    background-color: #e3342f;
    color: white;
    padding: 0.25rem 0.5rem;
    border: none;
    border-radius: 0.25rem;
    cursor: pointer;
    margin: 2px;
}

.mainButton {
    background-color: oklch(38.1% 0.176 304.987);
    padding: 0.5rem 1rem;
    margin-bottom: 1rem;
    width: 100%;
}
</style>
