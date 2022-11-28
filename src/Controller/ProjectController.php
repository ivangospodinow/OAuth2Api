<?php

namespace App\Controller;

use App\Entity\Project;
use App\Repository\ProjectRepository;
use App\Service\JsonSchemaValidator;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\Request;

class ProjectController extends AbstractController
{
    /**
     * @var ProjectRepository
     */
    private $repo;
    /**
     * @var JsonSchemaValidator
     */
    private $validator;

    public function __construct(ProjectRepository $repo, JsonSchemaValidator $schemaValidator)
    {
        $this->repo = $repo;
        $this->validator = $schemaValidator;
    }

    public function index(Request $request)
    {
        if ($errors = $this->validator->validateSchemaWithDurationErrorReponse($request, 'ProjectList.json')) {
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
        if ($errors = $this->validator->validateSchemaWithDurationErrorReponse($request, 'ProjectCreate.json')) {
            return $errors;
        }

        $data = $this->validator->getArrayData($request);
        if (!isset($data['status'])) {
            $data['status'] = Project::STATUS_NOT_STARTED;
        }

        $project = new Project($data);
        $this->repo->save($project, true);

        return $this->createResponse([
            'data' => [
                'id' => $project->getId(),
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        if ($errors = $this->validator->validateSchemaWithDurationErrorReponse($request, 'ProjectUpdate.json')) {
            return $errors;
        }

        $project = $this->repo->find($id);
        if (!$project) {
            return $this->createResponse([
                'errors' => [
                    [
                        'property' => 'id',
                        'pointer' => '/id',
                        'message' => 'Project does not exists',
                    ],
                ],
            ]);
        }

        $data = $this->validator->getArrayData($request);
        $project->exchangeArray($data);
        $this->repo->save($project, true);

        return $this->createResponse([
            'data' => [
                'id' => $project->getId(),
            ],
        ]);
    }

    public function delete($id)
    {
        $project = $this->repo->find($id);
        if (!$project) {
            return $this->createResponse([
                'errors' => [
                    [
                        'property' => 'id',
                        'pointer' => '/id',
                        'message' => 'Project does not exists',
                    ],
                ],
            ]);
        }

        $project->setDeletedAt(new DateTimeImmutable());
        $this->repo->save($project, true);

        return $this->createResponse([
            'success' => true,
        ]);
    }

    protected function getNormalizeRules()
    {
        return [
            'duration' => function ($value) {
                return $this->getDateIntervalNormalizer()->normalize($value);
            },
            'project' => function ($value) {
                return null;
            },
        ];
    }
}
