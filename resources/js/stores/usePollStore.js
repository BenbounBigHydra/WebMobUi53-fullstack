import { ref } from "vue";
import { useFetchApi } from "@/composables/useFetchApi";

const polls = ref([]);

export function usePollStore() {
    const { fetchApi } = useFetchApi();

    function setPolls(data) {
        polls.value = data;
    }

    async function deletePoll(id) {
        const result = await fetchApi({ url: "polls/" + id, method: "DELETE" });
        if (result) {
            polls.value = polls.value.filter((p) => p.id !== id);
        }
    }

    async function editPoll(id, data) {
        console.log(data);
        const result = await fetchApi({
            url: "polls/" + id,
            method: "PUT",
            data: data,
        });
        if (result) {
            const index = polls.value.findIndex((p) => p.id === id);
            if (index !== -1) {
                polls.value[index] = { ...polls.value[index], ...data };
            }
        }
        return result;
    }

    async function publishPoll(id) {
        const result = await fetchApi({
            url: "polls/" + id + "/publish",
            method: "POST",
        });
        if (result) {
            const index = polls.value.findIndex((p) => p.id === id);
            if (index !== -1) {
                console.log(result);
                console.log(polls.value[index]);
                polls.value[index].is_draft = result.poll.is_draft;
                polls.value[index].started_at = new Date(result.poll.started_at)
                    .toLocaleString("sv-SE", {
                        year: "numeric",
                        month: "2-digit",
                        day: "2-digit",
                        hour: "2-digit",
                        minute: "2-digit",
                        second: "2-digit",
                    })
                    .replace("T", " ");
                polls.value[index].ends_at = new Date(result.poll.ends_at)
                    .toLocaleString("sv-SE", {
                        year: "numeric",
                        month: "2-digit",
                        day: "2-digit",
                        hour: "2-digit",
                        minute: "2-digit",
                        second: "2-digit",
                    })
                    .replace("T", " ");
            }
        }
        return result;
    }

    async function createPoll(data) {
        const result = await fetchApi({
            url: "polls",
            method: "POST",
            data: data,
        });
        const newPoll = result.poll;
        polls.value.unshift(newPoll);
        return newPoll;
    }

    return { polls, setPolls, deletePoll, editPoll, publishPoll, createPoll };
}
