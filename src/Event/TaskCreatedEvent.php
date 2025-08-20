<?php
namespace App\Event;

use App\Entity\Task;

class TaskCreatedEvent
{
    public function __construct(public Task $task) {}
}
