<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Http\Request;

class PollShowController extends Controller
{
    public function __invoke(Request $request, string $secret_token)
    {
        $poll = Poll::where('secret_token', $secret_token)
                    ->with(['options' => function ($query) {
                        $query->withCount('votes');
                    }])
                    ->firstOrFail();

        $userId   = null;
        $userVote = null;

        if ($request->user()) {
            $userId = $request->user()->id;

            $userVote = PollVote::where('poll_id', $poll->id)
                ->where('user_id', $userId)
                ->pluck('poll_option_id')
                ->values()
                ->all();

            // null si pas encore voté, tableau d'ids sinon
            if (empty($userVote)) $userVote = null;
        }

        return view('polls.show', [
            'poll'     => $poll,
            'userId'   => $userId,
            'userVote' => $userVote,
        ]);
    }
}
