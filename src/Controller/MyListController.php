<?php

namespace App\Controller;

use App\Service\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyListController extends AbstractController
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    #[Route('/my-list', name: 'app_my_list')]
    public function index(GameService $gameService): Response
    {
        $session = $this->requestStack->getSession();
        $myListIds = $session->get('my_list', []);
        $games = $gameService->getGamesByIds($myListIds);

        $request = $this->requestStack->getCurrentRequest();
        $genre = $request->get('genre', '');
        $sort = $request->get('sort', 'date_asc');

        if (!empty($genre) && $genre !== 'all') {
            $games = array_filter($games, function ($game) use ($genre) {
                return stripos($game['genre'], $genre) !== false;
            });
        }

        switch ($sort) {
            case 'date_desc':
                usort($games, fn($a, $b) => new \DateTime($b['releaseDate']) <=> new \DateTime($a['releaseDate']));
                break;
            case 'title_asc':
                usort($games, fn($a, $b) => strcasecmp($a['title'], $b['title']));
                break;
            case 'title_desc':
                usort($games, fn($a, $b) => strcasecmp($b['title'], $a['title']));
                break;
            case 'price_asc':
                usort($games, fn($a, $b) => $a['price'] <=> $b['price']);
                break;
            case 'price_desc':
                usort($games, fn($a, $b) => $b['price'] <=> $a['price']);
                break;
            default:
                usort($games, fn($a, $b) => new \DateTime($a['releaseDate']) <=> new \DateTime($b['releaseDate']));
        }

        $allGames = $gameService->getGamesByIds($myListIds);
        $genres = [];
        foreach ($allGames as $game) {
            $gameGenres = array_map('trim', explode(',', $game['genre']));
            foreach ($gameGenres as $g) {
                if (!in_array($g, $genres)) {
                    $genres[] = $g;
                }
            }
        }
        sort($genres);

        return $this->render('my_list/index.html.twig', [
            'games' => $games,
            'games_json' => json_encode($games),
            'availableGenres' => $genres,
            'currentGenre' => $genre,
            'currentSort' => $sort,
            'totalGames' => count($allGames)
        ]);
    }

    #[Route('/my-list/add/{id}', name: 'my_list_add', methods: ['GET'])]
    public function add(int $id, GameService $gameService): Response
    {
        $game = $gameService->getGameById($id);
        if (!$game) {
            $this->addFlash('error', 'Game not found.');
            return $this->redirectToRoute('app_upcoming');
        }

        $session = $this->requestStack->getSession();
        $myList = $session->get('my_list', []);

        if (in_array($id, $myList)) {
            $this->addFlash('info', $game['title'] . ' is already in your list!');
        } else {
            $myList[] = $id;
            $session->set('my_list', $myList);
            $this->addFlash('success', $game['title'] . ' added to your list!');
        }

        return $this->redirectToRoute('app_upcoming');
    }

    #[Route('/my-list/remove/{id}', name: 'my_list_remove', methods: ['GET'])]
    public function remove(int $id, GameService $gameService): Response
    {
        $session = $this->requestStack->getSession();
        $myList = $session->get('my_list', []);

        $game = $gameService->getGameById($id);
        $gameTitle = $game ? $game['title'] : 'Unknown Game';

        if (in_array($id, $myList)) {
            $myList = array_filter($myList, fn($gameId) => $gameId != $id);
            $myList = array_values($myList);
            $session->set('my_list', $myList);
            $this->addFlash('info', $gameTitle . ' removed from your list.');
        } else {
            $this->addFlash('warning', $gameTitle . ' was not in your list.');
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request->get('from') === 'upcoming') {
            return $this->redirectToRoute('app_upcoming');
        }

        return $this->redirectToRoute('app_my_list');
    }
}
