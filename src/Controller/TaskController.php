<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Service\JsonSchemaValidator;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;

class TaskController extends AbstractController
{
    /**
     * @var TaskRepository
     */
    private $repo;

    /**
     * @var ProjectRepository
     */
    private $projectRepo;

    /**
     * @var JsonSchemaValidator
     */
    private $validator;

    public function __construct(TaskRepository $repo, ProjectRepository $projectRepo, JsonSchemaValidator $schemaValidator)
    {
        $this->repo = $repo;
        $this->projectRepo = $projectRepo;
        $this->validator = $schemaValidator;
    }

    public function index(Request $request)
    {
        if ($errors = $this->validator->validateSchemaWithDurationErrorReponse($request, 'TaskList.json')) {
            return $errors;
        }

        $data = $this->validator->getArrayData($request);

        return $this->createResponse([
            'data' => [
                'list' => $this->normalizeEntityList($this->repo->findForList($data)),
            ],
        ]);

    }

    public function create(Request $request)
    {
        if ($errors = $this->validator->validateSchemaWithDurationErrorReponse($request, 'TaskCreate.json')) {
            return $errors;
        }

        $data = $this->validator->getArrayData($request);

        $project = $this->projectRepo->find($data['project']);
        if (!$project) {
            return $this->createResponse([
                'errors' => [
                    [
                        'property' => 'project',
                        'pointer' => '/project',
                        'message' => 'Project does not exists',
                    ],
                ],
            ]);
        }
        $data['project'] = $project;

        $task = new Task();
        $task->exchangeArray($data);
        $this->repo->save($task, true);

        return $this->createResponse([
            'data' => [
                'id' => $task->getId(),
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        if ($errors = $this->validator->validateSchemaWithDurationErrorReponse($request, 'TaskUpdate.json')) {
            return $errors;
        }

        $task = $this->repo->find($id);
        if (!$task) {
            return $this->createResponse([
                'errors' => [
                    [
                        'property' => 'id',
                        'pointer' => '/id',
                        'message' => 'Task does not exists',
                    ],
                ],
            ]);
        }

        $data = $this->validator->getArrayData($request);

        $project = $this->projectRepo->find($data['project']);
        if (!$project) {
            return $this->createResponse([
                'errors' => [
                    [
                        'property' => 'project',
                        'pointer' => '/project',
                        'message' => 'Project does not exists',
                    ],
                ],
            ]);
        }
        $data['project'] = $project;

        $task->exchangeArray($data);
        $this->repo->save($task, true);

        return $this->createResponse([
            'data' => [
                'id' => $task->getId(),
            ],
        ]);
    }

    public function delete($id)
    {
        $task = $this->repo->find($id);
        if (!$task) {
            return $this->createResponse([
                'errors' => [
                    [
                        'property' => 'id',
                        'pointer' => '/id',
                        'message' => 'Task does not exists',
                    ],
                ],
            ]);
        }

        $task->setDeletedAt(new DateTimeImmutable());
        $this->repo->save($task, true);

        return $this->createResponse([
            'success' => true,
        ]);
    }

    protected function getNormalizeRules()
    {
        return [
            'project' => function ($value) {
                return [
                    'id' => $value->getId(),
                    'title' => $value->getTitle(),
                ];
            },
        ];
    }
}
