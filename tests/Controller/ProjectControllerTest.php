<?php

namespace App\Tests\Controller;

use App\Controller\ProjectController;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Service\JsonSchemaValidator;
use App\Tests\Traits\EntityMocker;
use App\Tests\Traits\InvokeObject;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;

final class ProjectControllerTest extends TestCase
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
    private $controller;

    public function setUp(): void
    {
        parent::setUp();

        $this->request = $this->createMock(Request::class);
        $this->repo = $this->createMock(ProjectRepository::class);
        $this->controller = new ProjectController($this->repo, new JsonSchemaValidator);
    }

    public function testIndexActionNoParameters()
    {
        $entities = [
            $this->createProjectMock(),
            $this->createProjectMock(),
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
            $this->createProjectMock(),
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
        $post = $this->getProjectDataMock();
        unset($post['id']);
        $project = new Project($post);

        $this->request->method('getContent')->willReturn(json_encode($post));
        $this->repo->method('save')->with($project, true);

        $response = $this->controller->create($this->request);
        $arrayReponse = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $arrayReponse['data']);
    }

    public function testUpdateAction()
    {
        $post = $this->getProjectDataMock();
        $project = new Project($post);

        $this->request->method('getContent')->willReturn(json_encode($post));
        $this->repo->method('find')->with($post['id'])->willReturn($project);
        $this->repo->method('save')->with($project, true);

        $response = $this->controller->update($this->request, $post['id']);
        $arrayReponse = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $arrayReponse['data']);
    }

    public function testDeleteAction()
    {
        $post = $this->getProjectDataMock();
        $project = new Project($post);

        $this->request->method('getContent')->willReturn(json_encode($post));
        $this->repo->method('find')->with($post['id'])->willReturn($project);
        $this->repo->method('remove')->with($project, true);

        $response = $this->controller->delete($post['id']);
        $arrayReponse = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('id', $arrayReponse['data']);
        $this->assertInstanceOf(DateTimeImmutable::class, $project->getDeletedAt());
    }
}
