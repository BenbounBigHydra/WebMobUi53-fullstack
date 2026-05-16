<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Http\Request;

class ApiPollVoteController extends Controller
{
    public function store(Request $request, Poll $poll)
    {
        $user = $request->user();

        // Brouillon
        if ($poll->is_draft) {
            return response()->json(['message' => 'Ce sondage est en brouillon.'], 403);
        }

        // Pas encore commencé
        if (!$poll->started_at || now()->lt($poll->started_at)) {
            return response()->json(['message' => 'Ce sondage n\'a pas encore commencé.'], 403);
        }

        // Terminé
        if ($poll->ends_at && now()->gt($poll->ends_at)) {
            return response()->json(['message' => 'Ce sondage est terminé.'], 403);
        }

        // Propriétaire ne peut pas voter
        if ($poll->user_id === $user->id) {
            return response()->json(['message' => 'Vous ne pouvez pas voter sur votre propre sondage.'], 403);
        }

        $existingVotes = PollVote::where('poll_id', $poll->id)
            ->where('user_id', $user->id)
            ->exists();

        // Déjà voté sans droit de modification
        if ($existingVotes && !$poll->allow_vote_change) {
            return response()->json(['message' => 'Vous avez déjà voté.'], 403);
        }

        $request->validate([
            'option_ids'   => ['required', 'array', 'min:1'],
            'option_ids.*' => ['integer', 'exists:poll_options,id'],
        ]);

        // Vérifier que toutes les options appartiennent bien à ce poll
        $validIds = $poll->options->pluck('id');
        foreach ($request->option_ids as $id) {
            if (!$validIds->contains($id)) {
                return response()->json(['message' => 'Option invalide.'], 422);
            }
        }

        // Choix multiple non autorisé
        if (!$poll->allow_multiple_choices && count($request->option_ids) > 1) {
            return response()->json(['message' => 'Ce sondage n\'autorise qu\'un seul choix.'], 422);
        }

        // Supprimer l'ancien vote si modification autorisée
        if ($existingVotes && $poll->allow_vote_change) {
            PollVote::where('poll_id', $poll->id)
                ->where('user_id', $user->id)
                ->delete();
        }

        // Enregistrer le/les nouveaux votes
        foreach ($request->option_ids as $optionId) {
            PollVote::create([
                'poll_id'        => $poll->id,
                'user_id'        => $user->id,
                'poll_option_id' => $optionId,
            ]);
        }

        return response()->json(['message' => 'Vote enregistré.'], 201);
    }

}
