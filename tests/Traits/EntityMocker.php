<?php
namespace App\Tests\Traits;

use App\Entity\Project;

trait EntityMocker
{
    public function getProjectDataMock()
    {
        return [
            'id' => '1ed6d61a-d2c4-61fe-' . rand(1000, 9999) . '-eb9a2e06a110',
            'title' => 'Project #' . rand(1, 99999),
            'content' => str_repeat('Content', rand(1, 5)),
            'duration' => 'P' . rand(1, 99) . 'D',
            'client' => uniqid('My company'),
            'status' => 'not_started',
        ];
    }
    public function createProjectMock()
    {
        return new Project($this->getProjectDataMock());
    }
}
