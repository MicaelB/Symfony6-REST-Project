<?php
namespace App\Service;

use App\DTO\CreateTaskDTO;
use App\Entity\Task;
use App\Enum\TaskStatus;
use App\Event\TaskCreatedEvent;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class TaskService
{
    public function __construct(
        private EntityManagerInterface $em,
        private TaskRepository $repo,
        private CacheInterface $cache,
        private EventDispatcherInterface $dispatcher,
        private int $defaultPageSize = 20,
    ) {}

    /** @return array<int, array<string, mixed>> */
    public function listTasks(): array
    {
        return $this->cache->get('tasks_all', function () {
            $tasks = $this->repo->findBy([], ['id' => 'DESC'], $this->defaultPageSize);
            return array_map(fn(Task $t) => $this->toArray($t), $tasks);
        });
    }

    /** @return array<string, mixed> */
    public function getTask(int $id): array
    {
        $t = $this->repo->find($id);
        if (!$t) {
            throw new \RuntimeException('Task not found');
        }
        return $this->toArray($t);
    }

    /** @return array<string, mixed> */
    public function createTask(CreateTaskDTO $dto): array
    {
        $task = new Task($dto->title, $dto->description, $dto->status);
        $this->em->persist($task);
        $this->em->flush();

        // Dispatch domain event
        $this->dispatcher->dispatch(new TaskCreatedEvent($task));

        return $this->toArray($task);
    }

    /** @return array<string, mixed> */
    private function toArray(Task $t): array
    {
        return [
            'id' => $t->getId(),
            'title' => $t->getTitle(),
            'description' => $t->getDescription(),
            'status' => $t->getStatus()->value,
            'createdAt' => $t->getCreatedAt()->format(DATE_ATOM),
            'updatedAt' => $t->getUpdatedAt()->format(DATE_ATOM),
        ];
    }
}
