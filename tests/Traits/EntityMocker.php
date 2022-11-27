<?php
namespace App\Tests\Traits;

use App\Entity\Project;
use App\Entity\Task;

trait EntityMocker
{
    public function getProjectDataMock($id = null)
    {
        return [
            'id' => $id ?: '1ed6d61a-d2c4-61fe-' . rand(1000, 9999) . '-eb9a2e06a110',
            'title' => 'Project #' . rand(1, 99999),
            'content' => str_repeat('Content', rand(1, 5)),
            'duration' => 'P' . rand(1, 99) . 'D',
            'client' => uniqid('My company'),
            'status' => 'not_started',
        ];
    }

    public function createProjectMock($id = null)
    {
        return new Project($this->getProjectDataMock($id));
    }

    public function getTaskDataMock()
    {
        return [
            'id' => '1ed6d61a-d2c4-61fe-' . rand(1000, 9999) . '-eb9a2e06a110',
            'name' => 'Task name #' . rand(1, 99999),
            'project' => $this->getProjectDataMock()['id'],
        ];
    }

    public function createTaskMock()
    {
        $data = $this->getTaskDataMock();
        return new Task(array_merge(
            $data,
            [
                'project' => $this->createProjectMock($data['project']),
            ],
        ));
    }
}
