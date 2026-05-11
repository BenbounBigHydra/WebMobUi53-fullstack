<template>
  <Teleport to="body">
    <div v-if="isOpen" class="modal-overlay" @click.self="$emit('close')">
      <div class="modal-content">
        <header class="modal-header">
          <slot name="header"><h3>Edit Item</h3></slot>
          <button class="close-btn" @click="$emit('close')">&times;</button>
        </header>
        <main class="modal-body">
          <slot></slot>
        </main>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
defineProps({
  isOpen: Boolean
});
defineEmits(['close']);
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0; left: 0; width: 100%; height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex; justify-content: center; align-items: center;
  z-index: 1000;
}
.modal-content {
  background: white;
  padding: 20px;
  border-radius: 8px;
  width: 90%; max-width: 500px;
}
.modal-header {
  display: flex; justify-content: space-between; align-items: center;
  border-bottom: 1px solid #eee; margin-bottom: 15px;
}
.close-btn { background: none; border: none; font-size: 1.5rem; cursor: pointer; }
</style>
