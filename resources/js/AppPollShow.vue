<script setup>
import PollDetail from './components/PollDetail.vue'

defineProps({
    poll:     { type: Object, default: () => ({}) },
    userId:   { type: Number, default: null },
    userVote: { type: Array,  default: null },
    loginUrl: { type: String, default: null },
})
</script>

<template>
    <PollDetail
        :poll="poll"
        :user-id="userId"
        :user-vote="userVote"
        :login-url="loginUrl"
    />
</template>
