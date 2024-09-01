<?php

namespace App\Controller;

use App\Document\TeamTree;
use App\Service\TeamReaderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends AbstractController
{
    #[Route('/api/teams/upload', name: 'upload_team', methods: ['POST'])]
    public function upload(Request $request, TeamReaderService $teamReaderService): JsonResponse
    {
        try {
            $file = $request->files->get('csv_file');
            if ($file == null) {
                return new JsonResponse(
                    ['error' => ['message' => 'Please upload csv file.']]
                    , JsonResponse::HTTP_BAD_REQUEST);
            }

            $data = $teamReaderService->processCsvFile($file);
            if (!empty($data['errors']))
                return new JsonResponse(['error' => $data['errors']], JsonResponse::HTTP_BAD_REQUEST);

            $tree = new TeamTree($data['teams']);
            $newTree = $tree->createBranchUpToTeamName($request->get('_q'));

            return new JsonResponse ($newTree->printTree());
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => ['message' => 'Error processing tree: ' . $e->getMessage()]],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }
    }
}