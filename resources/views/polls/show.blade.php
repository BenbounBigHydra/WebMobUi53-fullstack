<x-default-layout>
    <x-vue-app-layout>
        <x-slot:scripts>
            @vite(['resources/js/poll-show.js'])
        </x-slot>

        <x-slot:title>
            Sondage
        </x-slot>

        <div
            id="app"
            data-props="{{ json_encode([
                'poll'     => $poll,
                'userId'   => $userId,
                'userVote' => $userVote,
                'loginUrl' => route('login'),
            ]) }}"
        ></div>
    </x-vue-app-layout>
</x-default-layout>
