<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // ── Users ────────────────────────────────────────────────────────
            DB::table('users')->insert([
                [
                    'id'         => 1,
                    'first_name' => 'John',
                    'last_name'  => 'Doe',
                    'username'   => 'johndoe',
                    'email'      => 'john.doe@example.com',
                    'password'   => Hash::make('password'),
                    'created_at' => new \DateTime('2026-02-09 10:00:00'),
                    'updated_at' => new \DateTime('2026-02-09 10:00:00'),
                ],
                [
                    'id'         => 2,
                    'first_name' => 'Jane',
                    'last_name'  => 'Doe',
                    'username'   => 'janedoe',
                    'email'      => 'jane.doe@example.com',
                    'password'   => Hash::make('password'),
                    'created_at' => new \DateTime('2026-02-09 11:00:00'),
                    'updated_at' => new \DateTime('2026-02-09 11:00:00'),
                ],
            ]);

            // ── Posts ────────────────────────────────────────────────────────
            DB::table('posts')->insert([
                ['id' => 1, 'user_id' => 1, 'title' => "John's First Post",  'content' => "Content of John's first post.",  'created_at' => new \DateTime('2026-02-09 12:00:00'), 'updated_at' => new \DateTime('2026-02-09 12:00:00')],
                ['id' => 2, 'user_id' => 1, 'title' => null,                  'content' => "Content of John's second post.", 'created_at' => new \DateTime('2026-02-09 12:05:00'), 'updated_at' => new \DateTime('2026-02-09 12:05:00')],
                ['id' => 3, 'user_id' => 1, 'title' => null,                  'content' => "Content of John's third post.",  'created_at' => new \DateTime('2026-02-09 12:10:00'), 'updated_at' => new \DateTime('2026-02-09 12:10:00')],
                ['id' => 4, 'user_id' => 2, 'title' => null,                  'content' => "Content of Jane's first post.",  'created_at' => new \DateTime('2026-02-09 12:05:00'), 'updated_at' => new \DateTime('2026-02-09 12:05:00')],
                ['id' => 5, 'user_id' => 2, 'title' => "Jane's Second Post",  'content' => "Content of Jane's second post.", 'created_at' => new \DateTime('2026-02-09 12:10:00'), 'updated_at' => new \DateTime('2026-02-09 12:10:00')],
                ['id' => 6, 'user_id' => 2, 'title' => "Jane's Third Post",   'content' => "Content of Jane's third post.",  'created_at' => new \DateTime('2026-02-09 12:15:00'), 'updated_at' => new \DateTime('2026-02-09 12:15:00')],
            ]);

            // ── Likes ────────────────────────────────────────────────────────
            DB::table('likes')->insert([
                ['user_id' => 2, 'post_id' => 1, 'reaction' => 'like', 'created_at' => new \DateTime('2026-02-09 12:20:00'), 'updated_at' => new \DateTime('2026-02-09 12:20:00')],
                ['user_id' => 1, 'post_id' => 2, 'reaction' => 'love', 'created_at' => new \DateTime('2026-02-09 12:25:00'), 'updated_at' => new \DateTime('2026-02-09 12:25:00')],
                ['user_id' => 1, 'post_id' => 4, 'reaction' => 'like', 'created_at' => new \DateTime('2026-02-09 12:30:00'), 'updated_at' => new \DateTime('2026-02-09 12:30:00')],
                ['user_id' => 1, 'post_id' => 5, 'reaction' => 'love', 'created_at' => new \DateTime('2026-02-09 12:35:00'), 'updated_at' => new \DateTime('2026-02-09 12:35:00')],
                ['user_id' => 2, 'post_id' => 5, 'reaction' => 'wow',  'created_at' => new \DateTime('2026-02-09 12:40:00'), 'updated_at' => new \DateTime('2026-02-09 12:40:00')],
            ]);

            // ── Polls ────────────────────────────────────────────────────────
            // Légende colonnes : id, user_id, title, is_draft, allow_multiple, allow_change, results_public, started_at, ends_at
            $now  = new \DateTime();
            $past = new \DateTime('-2 hours');
            $later = (clone $now)->modify('+2000 hours');
            $yesterday = new \DateTime('-1 day');
            $tomorrow  = new \DateTime('+1 day');
            $ts = ['created_at' => $now, 'updated_at' => $now];

            $polls = [
                // 1 — Brouillon (John) → personne ne peut voter
                [
                    'id' => 1, 'user_id' => 1,
                    'title'    => '[Draft] Sondage non publié',
                    'question' => 'Ceci est un brouillon, personne ne peut voter.',
                    'is_draft' => true, 'allow_multiple_choices' => false,
                    'allow_vote_change' => false, 'results_public' => false,
                    'started_at' => null, 'ends_at' => null,
                ],

                // 2 — Pas encore commencé (Jane) → started_at dans le futur
                [
                    'id' => 2, 'user_id' => 2,
                    'title'    => '[Pas commencé] Sondage futur',
                    'question' => 'Ce sondage commence dans longtemps.',
                    'is_draft' => false, 'allow_multiple_choices' => false,
                    'allow_vote_change' => false, 'results_public' => false,
                    'started_at' => $later, 'ends_at' => $tomorrow,
                ],

                // 3 — Terminé (John) → ends_at dans le passé
                [
                    'id' => 3, 'user_id' => 1,
                    'title'    => '[Terminé] Sondage expiré',
                    'question' => 'Ce sondage est terminé depuis hier.',
                    'is_draft' => false, 'allow_multiple_choices' => false,
                    'allow_vote_change' => false, 'results_public' => false,
                    'started_at' => $yesterday, 'ends_at' => $past,
                ],

                // 4 — Actif, vote unique, résultats privés (Jane) → cas standard
                [
                    'id' => 4, 'user_id' => 2,
                    'title'    => '[Actif] Vote unique, résultats privés',
                    'question' => 'Quelle est votre couleur préférée ?',
                    'is_draft' => false, 'allow_multiple_choices' => false,
                    'allow_vote_change' => false, 'results_public' => false,
                    'started_at' => $past, 'ends_at' => $tomorrow,
                ],

                // 5 — Actif, vote unique, résultats publics (John)
                [
                    'id' => 5, 'user_id' => 1,
                    'title'    => '[Actif] Vote unique, résultats publics',
                    'question' => 'Quel framework préférez-vous ?',
                    'is_draft' => false, 'allow_multiple_choices' => false,
                    'allow_vote_change' => false, 'results_public' => true,
                    'started_at' => $past, 'ends_at' => $tomorrow,
                ],

                // 6 — Actif, choix multiples, résultats publics (Jane)
                [
                    'id' => 6, 'user_id' => 2,
                    'title'    => '[Actif] Choix multiples, résultats publics',
                    'question' => 'Quels langages utilisez-vous ?',
                    'is_draft' => false, 'allow_multiple_choices' => true,
                    'allow_vote_change' => false, 'results_public' => true,
                    'started_at' => $past, 'ends_at' => $tomorrow,
                ],

                // 7 — Actif, vote modifiable (John)
                [
                    'id' => 7, 'user_id' => 1,
                    'title'    => '[Actif] Vote modifiable',
                    'question' => 'Quel OS utilisez-vous ?',
                    'is_draft' => false, 'allow_multiple_choices' => false,
                    'allow_vote_change' => true, 'results_public' => false,
                    'started_at' => $past, 'ends_at' => $tomorrow,
                ],

                // 8 — Actif, choix multiples + vote modifiable + résultats publics (Jane)
                [
                    'id' => 8, 'user_id' => 2,
                    'title'    => '[Actif] Multi + modifiable + résultats publics',
                    'question' => 'Quels éditeurs de code utilisez-vous ?',
                    'is_draft' => false, 'allow_multiple_choices' => true,
                    'allow_vote_change' => true, 'results_public' => true,
                    'started_at' => $past, 'ends_at' => $tomorrow,
                ],
            ];

            foreach ($polls as $poll) {
                DB::table('polls')->insert(array_merge($poll, [
                    'secret_token' => \Illuminate\Support\Str::random(32),
                    'duration'     => 86400,
                ], $ts));
            }

            // ── Options ──────────────────────────────────────────────────────
            $options = [
                1 => ['Oui', 'Non', 'Peut-être'],
                2 => ['Lundi', 'Mardi', 'Mercredi', 'Jeudi'],
                3 => ['Option A', 'Option B', 'Option C'],
                4 => ['Rouge', 'Bleu', 'Vert', 'Jaune'],
                5 => ['Laravel', 'Symfony', 'Django', 'Rails'],
                6 => ['PHP', 'JavaScript', 'Python', 'TypeScript', 'Rust'],
                7 => ['Windows', 'macOS', 'Linux'],
                8 => ['VS Code', 'PhpStorm', 'Vim/Neovim', 'Sublime Text'],
            ];

            $optionId = 1;
            $optionIdMap = []; // poll_id => [label => option_id]

            foreach ($options as $pollId => $labels) {
                $optionIdMap[$pollId] = [];
                foreach ($labels as $label) {
                    DB::table('poll_options')->insert([
                        'id'         => $optionId,
                        'poll_id'    => $pollId,
                        'label'      => $label,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $optionIdMap[$pollId][$label] = $optionId;
                    $optionId++;
                }
            }

            // ── Votes pré-existants ──────────────────────────────────────────
            // Poll 5 (résultats publics) — Jane a voté Laravel
            DB::table('poll_votes')->insert([
                'poll_id' => 5, 'user_id' => 2,
                'poll_option_id' => $optionIdMap[5]['Laravel'],
                'created_at' => $now, 'updated_at' => $now,
            ]);

            // Poll 6 (multi) — John a voté PHP + JavaScript
            DB::table('poll_votes')->insert([
                ['poll_id' => 6, 'user_id' => 1, 'poll_option_id' => $optionIdMap[6]['PHP'],        'created_at' => $now, 'updated_at' => $now],
                ['poll_id' => 6, 'user_id' => 1, 'poll_option_id' => $optionIdMap[6]['JavaScript'], 'created_at' => $now, 'updated_at' => $now],
            ]);

            // Poll 7 (vote modifiable) — Jane a voté macOS (pourra changer)
            DB::table('poll_votes')->insert([
                'poll_id' => 7, 'user_id' => 2,
                'poll_option_id' => $optionIdMap[7]['macOS'],
                'created_at' => $now, 'updated_at' => $now,
            ]);
        });
    }
}
