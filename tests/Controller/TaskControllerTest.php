<?php

namespace App\Tests\Controller;

use App\Controller\TaskController;
use App\Entity\Task;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Service\JsonSchemaValidator;
use App\Tests\Traits\EntityMocker;
use App\Tests\Traits\InvokeObject;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;

final class TaskControllerTest extends TestCase
{
    use InvokeObject, EntityMocker;

    /**
     * @var MockObject
     */
    private $request;

    /**
     * @var MockObject
     */
    private $repo;

    /**
     * @var MockObject
     */
    private $projectRepo;

    private $controller;

    public function setUp(): void
    {
        parent::setUp();

        $this->request = $this->createMock(Request::class);
        $this->repo = $this->createMock(TaskRepository::class);
        $this->projectRepo = $this->createMock(ProjectRepository::class);
        $this->controller = new TaskController($this->repo, $this->projectRepo, new JsonSchemaValidator);
    }

    public function testIndexActionNoParameters()
    {
        $entities = [
            $this->createTaskMock(),
            $this->createTaskMock(),
        ];
        $this->repo->method('findForList')->with([])->willReturn($entities);
        $response = $this->controller->index($this->request);

        $this->assertSame(
            json_encode([
                'data' => [
                    'list' => $this->invokeMethod($this->controller, 'normalizeEntityList', [$entities]),
                ],
            ]),
            $response->getContent()
        );
    }

    public function testIndexActionSomeParameters()
    {
        $entities = [
            $this->createTaskMock(),
        ];
        $this->request->query = new InputBag([
            'filter' => [
                'id' => $entities[0]->getId(),
            ],
        ]);

        $this->request->method('isMethod')->with('GET')->willReturn(true);
        $this->repo->method('findForList')->with($this->request->query->all())->willReturn($entities);
        $response = $this->controller->index($this->request);

        $this->assertSame(
            json_encode([
                'data' => [
                    'list' => $this->invokeMethod($this->controller, 'normalizeEntityList', [$entities]),
                ],
            ]),
            $response->getContent()
        );
    }

    public function testCreateAction()
    {
        $post = $this->getTaskDataMock();
        unset($post['id']);
        $task = new Task(array_merge($post, ['project' => $this->createProjectMock($post['project'])]));

        $this->request->method('getContent')->willReturn(json_encode($post));
        $this->repo->method('save')->with($task, true);
        $this->projectRepo->method('find')->with($post['project'])->willReturn($task->getProject());

        $response = $this->controller->create($this->request);
        $arrayReponse = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $arrayReponse['data']);
    }

    public function testUpdateAction()
    {
        $post = $this->getTaskDataMock();
        $task = new Task(array_merge($post, ['project' => $this->createProjectMock($post['project'])]));

        $this->request->method('getContent')->willReturn(json_encode($post));
        $this->repo->method('find')->with($post['id'])->willReturn($task);
        $this->repo->method('save')->with($task, true);
        $this->projectRepo->method('find')->with($post['project'])->willReturn($task->getProject());

        $response = $this->controller->update($this->request, $post['id']);
        $arrayReponse = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('id', $arrayReponse['data']);
    }

    public function testDeleteAction()
    {
        $post = $this->getTaskDataMock();
        $task = new Task(array_merge($post, ['project' => $this->createProjectMock($post['project'])]));

        $this->request->method('getContent')->willReturn(json_encode($post));
        $this->repo->method('find')->with($post['id'])->willReturn($task);
        $this->repo->method('save')->with($task, true);

        $response = $this->controller->delete($post['id']);
        $arrayReponse = json_decode($response->getContent(), true);
        $this->assertTrue($arrayReponse['success']);
        $this->assertInstanceOf(DateTimeImmutable::class, $task->getDeletedAt());
    }
}
