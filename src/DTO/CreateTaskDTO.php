<?php
namespace App\DTO;

use App\Enum\TaskStatus;

class CreateTaskDTO
{
    public function __construct(
        public string $title,
        public ?string $description = null,
        public TaskStatus $status = TaskStatus::Pending,
    ) {}
}
