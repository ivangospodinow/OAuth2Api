<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DateIntervalNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

abstract class AbstractController extends SymfonyAbstractController
{
    /**
     * @var DateIntervalNormalizer
     */
    private $dateIntervalNormalizer;

    protected function createResponse(array $data): JsonResponse
    {
        if (isset($data['errors'])) {
            $data['code'] = 422;
        }

        return new JsonResponse($data, 200);
    }

    protected function normalizeEntityList($entities): array
    {
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, [new JsonEncoder()]);

        $list = [];
        foreach ($entities as $entity) {
            $list[] = $serializer->normalize(
                $entity,
                null,
                [
                    'callbacks' => $this->getNormalizeRules(),
                ]
            );
        }
        return $list;
    }

    protected function getNormalizeRules()
    {
        return [

        ];
    }

    protected function getDateIntervalNormalizer(): DateIntervalNormalizer
    {
        if (null === $this->dateIntervalNormalizer) {
            $this->dateIntervalNormalizer = new DateIntervalNormalizer();
        }
        return $this->dateIntervalNormalizer;
    }
}
