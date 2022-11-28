<?php
namespace App\Tests\Traits;

use App\Entity\Project;
use App\Entity\Task;

trait EntityMocker
{
    public function getProjectDataMock($id = null)
    {
        return [
            'id' => $id ?: $this->createUuid(),
            'title' => 'Project #' . rand(1, 99999),
            'description' => str_repeat('Description', rand(1, 5)),
            'duration' => 'P' . rand(1, 99) . 'D',
            'client' => uniqid('My company'),
            'status' => 'not_started',
        ];
    }

    public function createProjectMock($id = null)
    {
        return new Project($this->getProjectDataMock($id));
    }

    public function getTaskDataMock($id = null)
    {
        return [
            'id' => $id ?: $this->createUuid(),
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

    private function createUuid($data = null)
    {
        // Generate 16 bytes (128 bits) of random data or use the data passed into the function.
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);

        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

}
