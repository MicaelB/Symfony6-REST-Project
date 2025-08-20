<?php
namespace App\EventListener;

use App\Event\TaskCreatedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class TaskCreatedListener
{
    public function __construct(private LoggerInterface $logger, private CacheInterface $cache) {}

    public function onTaskCreated(TaskCreatedEvent $event): void
    {
        $task = $event->task;
        $this->logger->info('Task created', ['id' => $task->getId(), 'title' => $task->getTitle()]);

        // Invalidate cached task list
        $this->cache->delete('tasks_all');
    }
}
