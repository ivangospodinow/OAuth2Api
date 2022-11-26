<?php

namespace App\Service;

use DateInterval;
use JsonSchema\Validator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use \stdClass;

class JsonSchemaValidator extends Validator
{
    private $schemaDir;

    public function __construct()
    {
        parent::__construct();
        $this->schemaDir = realpath(__DIR__ . '/../../schema');
    }

    public function validateSchemaWithErrorReponse(Request $request, string $schemaName)
    {
        $file = $this->schemaDir . '/' . $schemaName;
        if (!file_exists($file)) {
            throw new \Exception('Scheme could not be found ' . $schemaName);
        }
        $objectToValidate = $this->getData($request);
        $this->validate(
            $objectToValidate,
            (object) [
                '$ref' => 'file://' . $file,
            ]
        );

        if ($this->isValid()) {
            return false;
        }

        return $this->createErrorsResponse($this->getErrors());
    }

    public function validateSchemaWithDurationErrorReponse(Request $request, string $schemaName)
    {
        $result = $this->validateSchemaWithErrorReponse($request, $schemaName);
        $data = $this->getData($request);
        if (isset($data->duration) && !$this->validateDateInterval($data->duration)) {
            $result = $this->createErrorsResponse(array_merge(
                $this->getErrors(),
                [
                    [
                        'property' => 'duration',
                        'pointer' => '/duration',
                        'message' => 'Invalid dateinterval format',
                    ],
                ]
            ));
        }
        return $result;
    }

    /**
     * It is ok to suppress the fail, the only way to pass
     * is to have the correct data.
     *
     * @param Request $request
     * @return object
     */
    public function getData(Request $request)
    {
        if ($request->isMethod('GET')) {
            return !empty($request->query->all()) ? json_decode(json_encode($request->query->all())) : new stdClass;
        }
        return @json_decode($request->getContent()) ?: new stdClass;
    }

    /**
     * @param Request $request
     * @return array
     */
    public function getArrayData(Request $request)
    {
        if ($request->isMethod('GET')) {
            return $request->query->all();
        }
        return @json_decode($request->getContent(), true) ?: [];
    }

    private function createErrorsResponse(array $errors): JsonResponse
    {
        return new JsonResponse(
            [
                'success' => false,
                'errors' => $errors,
                'code' => 422,
            ],
            200
        );
    }

    private function validateDateInterval(string $input)
    {
        // @TODO, check for something baked into symfony for this type.
        try {
            $dateInterval = new DateInterval($input);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
