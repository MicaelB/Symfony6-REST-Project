<?php
namespace App\Controller;

use App\DTO\CreateTaskDTO;
use App\Enum\TaskStatus;
use App\Service\TaskService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/tasks')]
class TaskController extends AbstractController
{
    public function __construct(private TaskService $service) {}

    #[Route('/', name: 'task_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json($this->service->listTasks());
    }

    #[Route('/{id}', name: 'task_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        try {
            return $this->json($this->service->getTask($id));
        } catch (\RuntimeException $e) {
            return $this->json(['error' => $e->getMessage()], 404);
        }
    }

    #[Route('/', name: 'task_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $title = (string)($data['title'] ?? '');
        $description = $data['description'] ?? null;
        $statusRaw = (string)($data['status'] ?? 'pending');

        if ($title === '') {
            return $this->json(['error' => 'title is required'], 400);
        }

        try {
            $status = TaskStatus::from($statusRaw);
        } catch (\ValueError) {
            return $this->json(['error' => 'invalid status'], 400);
        }

        $dto = new CreateTaskDTO($title, $description, $status);
        $result = $this->service->createTask($dto);
        return $this->json($result, 201);
    }
}
