<?php

namespace App\Controller;

use App\Service\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
// Removed: use Parsedown; // No longer needed for this approach

class UpcomingController extends AbstractController
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    #[Route('/', name: 'app_upcoming')]
    public function index(GameService $gameService): Response
    {
        $games = $gameService->getUpcomingGames();
        $session = $this->requestStack->getSession();
        $myList = $session->get('my_list', []);
        
        $gamesByDate = [];
        foreach ($games as $game) {
            $date = $game['releaseDate'];
            if (!isset($gamesByDate[$date])) {
                $gamesByDate[$date] = [];
            }
            $game['isInList'] = in_array($game['id'], $myList);
            $gamesByDate[$date][] = $game;
        }
        
        ksort($gamesByDate);
        
        return $this->render('upcoming/index.html.twig', [
            'gamesByDate' => $gamesByDate,
            'totalGames' => count($games),
            'myListCount' => count($myList)
        ]);
    }

    #[Route('/api/readme', name: 'api_readme', methods: ['GET'])]
    public function getReadme(): JsonResponse
    {
        $readmePath = $this->getParameter('kernel.project_dir') . '/README.md';
        
        if (!file_exists($readmePath)) {
            return new JsonResponse(['error' => 'README.md not found'], 404);
        }
        
        $content = file_get_contents($readmePath);
        $html = $this->convertMarkdown($content);
        
        return new JsonResponse(['content' => $html]);
    }
    
    private function convertMarkdown($markdown)
    {
        // Remove the "Screenshots" section and everything after it
        $markdown = preg_replace('/## Screenshots.*$/s', '', $markdown);

        $html = $markdown;
        
        $html = preg_replace('/^# (.+)$/m', '<h2 style="color: var(--gog-purple-light); margin-bottom: 1rem;">$1</h2>', $html);
        $html = preg_replace('/^## (.+)$/m', '<h4 style="color: var(--gog-purple-light); margin-bottom: 0.75rem;">$1</h4>', $html);
        $html = preg_replace('/^### (.+)$/m', '<h5 style="color: var(--gog-purple-light); margin-bottom: 0.5rem;">$1</h5>', $html);
        $html = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/^- (.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/(<li>.*<\/li>\s*)+/s', '<ul style="margin-bottom: 1rem;">$0</ul>', $html);
        $html = preg_replace('/^(?!<[h|u|l]).+$/m', '<p style="margin-bottom: 1rem;">$0</p>', $html);
        
        return $html;
    }
}