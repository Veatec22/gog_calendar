<?php

namespace App\Service;

class GameService
{
    private $games = [];

    public function __construct()
    {
        $this->games = [
            1 => [
                'id' => 1,
                'title' => 'Baldur\'s Gate 4',
                'genre' => 'RPG, Turn-based',
                'price' => 279.99,
                'releaseDate' => '2025-08-30',
                'cover' => 'https://images.gog-statics.com/8f7e6d5c4b3a2f1e0d9c8b7a6f5e4d3c2b1a9f8e7d6c5b4a3f2e1d0c9b8a7f6.jpg',
                'description' => 'The next epic adventure in the legendary D&D universe.',
                'developer' => 'Larian Studios',
                'platforms' => ['PC', 'Mac', 'PS5', 'Xbox Series X/S']
            ],
            2 => [
                'id' => 2,
                'title' => 'Minecraft 2',
                'genre' => 'Sandbox, Survival',
                'price' => 149.99,
                'releaseDate' => '2025-10-20',
                'cover' => 'https://images.gog-statics.com/minecraft_2_cover.jpg',
                'description' => 'The next generation of block-building adventures.',
                'developer' => 'Mojang Studios',
                'platforms' => ['PC', 'Xbox', 'PS5', 'Switch', 'Mobile']
            ],
            3 => [
                'id' => 3,
                'title' => 'The Witcher 4',
                'genre' => 'RPG',
                'price' => 299.00,
                'releaseDate' => '2025-10-20',
                'cover' => 'https://cdn.mos.cms.futurecdn.net/ySozGbn4f2aDsFpxEk6k6Z.jpg',
                'description' => 'The next chapter in The Witcher saga begins a new trilogy.',
                'developer' => 'CD Projekt RED',
                'platforms' => ['PC', 'PS5', 'Xbox Series X/S']
            ],
            4 => [
                'id' => 4,
                'title' => 'GTA VI',
                'genre' => 'Action, Open World',
                'price' => 349.99,
                'releaseDate' => '2025-11-15',
                'cover' => 'https://images.gog-statics.com/gta_vi_cover.jpg',
                'description' => 'The most anticipated game of the decade.',
                'developer' => 'Rockstar Games',
                'platforms' => ['PC', 'PS5', 'Xbox Series X/S']
            ],
            5 => [
                'id' => 5,
                'title' => 'Halo: Infinite 2',
                'genre' => 'FPS, Sci-Fi',
                'price' => 229.99,
                'releaseDate' => '2025-12-03',
                'cover' => 'https://images.gog-statics.com/halo_infinite_evolution_cover.jpg',
                'description' => 'Master Chief returns in the ultimate Halo experience.',
                'developer' => '343 Industries',
                'platforms' => ['PC', 'Xbox Series X/S']
            ],
            6 => [
                'id' => 6,
                'title' => 'Portal 3',
                'genre' => 'Puzzle, Sci-Fi',
                'price' => 189.99,
                'releaseDate' => '2025-12-21',
                'cover' => 'https://images.gog-statics.com/portal_3_cover.jpg',
                'description' => 'Aperture Science returns with mind-bending puzzles.',
                'developer' => 'Valve Corporation',
                'platforms' => ['PC', 'Steam Deck']
            ]
        ];
    }

    public function getUpcomingGames()
    {
        $futureGames = array_filter($this->games, function ($game) {
            return new \DateTime($game['releaseDate']) > new \DateTime();
        });

        uasort($futureGames, function ($a, $b) {
            return new \DateTime($a['releaseDate']) <=> new \DateTime($b['releaseDate']);
        });

        return $futureGames;
    }

    public function getGamesByIds($ids)
    {
        $result = [];
        foreach ($ids as $id) {
            if (isset($this->games[$id])) {
                $result[] = $this->games[$id];
            }
        }
        return $result;
    }

    public function getGameById($id)
    {
        return $this->games[$id] ?? null;
    }
}