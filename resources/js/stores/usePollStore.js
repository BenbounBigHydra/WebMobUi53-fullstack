import { ref } from 'vue';
import { useFetchApi } from '@/composables/useFetchApi';

const polls = ref([]);

export function usePollStore() {
  const { fetchApi } = useFetchApi();

  function setPolls(data) {
    polls.value = data;
  }

  async function deletePoll(id) {
    const result = await fetchApi({ url: 'polls/' + id, method: 'DELETE' });
    if (result) {
      polls.value = polls.value.filter(p => p.id !== id);
    }
  }

  async function editPoll(id, data) {
    const result = await fetchApi({ url: 'polls/' + id, method: 'PUT', body: data });
    if (result) {
      const index = polls.value.findIndex(p => p.id === id);
      if (index !== -1) {
        polls.value[index] = { ...polls.value[index], ...data };
      }
    }
    // return result;
  }

  return { polls, setPolls, deletePoll, editPoll };
}
