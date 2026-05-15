<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ApiPollController extends Controller
{
    /**
     * Display a listing of the authenticated user's polls.
     */
    public function index(Request $request)
    {
        $polls = $request->user()->polls()
            ->with('options')
            ->orderBy('created_at', 'desc')
            ->get();

        return $polls;
    }

    /**
     * Display the specified poll by its secret token.
     */
    public function show(string $token)
    {
        $poll = Poll::with(['options' => function ($query) {
            $query->withCount('votes');
        }])->where('secret_token', $token)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        return $poll;
    }

    /**
     * Remove the specified poll.
     */
    public function remove(Request $request, int $id)
    {
        $poll = Poll::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        $poll->delete();

        return response()->json(['message' => 'success'], 200);
    }

    public function update(Request $request, int $id)
    {
        $poll = Poll::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        if (!$poll->is_draft) {
            return response()->json(['message' => 'Published polls cannot be edited.'], 403);
        }

        $validated = $request->validate([
            'title'                  => 'sometimes|nullable|string|max:255',
            'question'               => 'sometimes|required|string|max:255',
            'allow_multiple_choices' => 'sometimes|boolean',
            'allow_vote_change'      => 'sometimes|boolean',
            'results_public'         => 'sometimes|boolean',
            'duration'               => 'sometimes|nullable|integer|min:1',
            'options'                => 'sometimes|array|min:2',
            'options.*.id'           => 'sometimes|integer|exists:poll_options,id',
            'options.*.label'        => 'required_with:options|string|max:255',
        ]);

        DB::transaction(function () use ($poll, $validated) {
            $poll->update(collect($validated)->except('options')->toArray());

            if (isset($validated['options'])) {
                $poll->options()->delete();

                foreach ($validated['options'] as $optionData) {
                    $poll->options()->create(['label' => $optionData['label']]);
                }
            }
        });

        return response()->json(['message' => 'success', 'poll' => $poll->load('options')], 200);
    }

    public function publish(Request $request, int $id)
    {
        $poll = Poll::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (!$poll) {
            return response()->json(['message' => 'Poll not found.'], 404);
        }

        if (!$poll->is_draft) {
            return response()->json(['message' => 'Poll is already published.'], 400);
        }

        if ($poll->options()->count() < 2) {
            return response()->json(['message' => 'At least 2 options are required to publish the poll.'], 400);
        }

        $now = now();

        $poll->started_at = $now;
        $poll->ends_at = $now->copy()->addSeconds($poll->duration);
        $poll->is_draft = 0;
        $poll->save();

        return response()->json(['message' => 'success', 'poll' => $poll], 200);
    }

    public function store(Request $request)
    {
        // return $request->user()->id;

        $validated = $request->validate([
            'title'                  => 'nullable|string|max:255',
            'question'               => 'required|string|max:255',
            'allow_multiple_choices' => 'boolean',
            'allow_vote_change'      => 'boolean',
            'results_public'         => 'boolean',
            'duration'               => 'nullable|integer|min:1',
            'options'                => 'required|array|min:2',
            'options.*.label'        => 'required|string|max:255',
        ]);

        $poll = DB::transaction(function () use ($request, $validated) {
            $poll = Poll::create([
                ...collect($validated)->except('options')->toArray(),
                'user_id'      => $request->user()->id,
                'is_draft'     => 1,
                'secret_token' => \Illuminate\Support\Str::random(32),
            ]);

            foreach ($validated['options'] as $optionData) {
                $poll->options()->create(['label' => $optionData['label']]);
            }

            return $poll;
        });

        return response()->json([
            'message' => 'success',
            'poll'    => $poll->load('options')
        ], 201);
    }
}
