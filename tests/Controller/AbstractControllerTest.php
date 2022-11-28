<?php

namespace App\Tests\Controller;

use App\Controller\AbstractController;
use App\Controller\ProjectController;
use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Service\JsonSchemaValidator;
use App\Tests\Traits\InvokeObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

final class AbstractControllerTest extends TestCase
{
    use InvokeObject;

    protected $controller;

    public function setUp(): void
    {
        parent::setUp();

        $this->controller = new class extends AbstractController
        {

        };

    }

    public function testCreateResponseErrors()
    {
        $data = ['errors' => ['test']];
        $response = $this->invokeMethod($this->controller, 'createResponse', [$data]);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertSame('{"errors":["test"],"code":422}', $content);
    }

    public function testCreateResponseData()
    {
        $data = [['test']];
        $response = $this->invokeMethod($this->controller, 'createResponse', [$data]);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());

        $content = $response->getContent();
        $this->assertSame(json_encode($data), $content);
    }

    public function getNormalizeEntityListEmpty()
    {
        $items = $this->invokeMethod($this->controller, 'normalizeEntityList', []);
        $this->assertEmpty($items);
    }

    public function testGetNormalizeEntityList()
    {
        $this->controller = new class($this->createMock(ProjectRepository::class), new JsonSchemaValidator()) extends ProjectController
        {

        };

        $expected = array(
            0 => array(
                'id' => 'bb27ac1e-f042-471c-9f2a-0d5bcc4eafe3',
                'title' => 'Project #1',
                'description' => 'Description',
                'status' => null,
                'duration' => 'P0Y1M0DT0H0M0S',
                'client' => 'My company',
                'company' => null,
                'tasks' => array(
                ),
                'deletedAt' => null,
            ),
            1 => array(
                'id' => 'fb27ac1e-f042-471c-9f1a-0d5bcc3eafe3',
                'title' => 'Project #2',
                'description' => 'Description of project 2',
                'status' => null,
                'duration' => 'P1Y0M0DT0H0M0S',
                'client' => 'Unknown',
                'company' => null,
                'tasks' => array(
                ),
                'deletedAt' => null,
            ),
        );

        $entities = [
            new Project([
                'id' => 'bb27ac1e-f042-471c-9f2a-0d5bcc4eafe3',
                'title' => 'Project #1',
                'description' => 'Description',
                'duration' => 'P1M',
                'client' => 'My company',
            ]),
            new Project([
                'id' => 'fb27ac1e-f042-471c-9f1a-0d5bcc3eafe3',
                'title' => 'Project #2',
                'description' => 'Description of project 2',
                'duration' => 'P1Y',
                'client' => 'Unknown',
            ]),
        ];

        $items = $this->invokeMethod($this->controller, 'normalizeEntityList', [$entities]);

        $this->assertSame($expected, $items);
    }
}
