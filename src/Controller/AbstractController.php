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
    protected function createResponse(array $data): JsonResponse
    {
        if (isset($data['errors'])) {
            $data['code'] = 422;
        }

        return new JsonResponse($data, 200);
    }

    protected function normalizeEntityList($entities): array
    {
        $dateIntervalNormalizer = new DateIntervalNormalizer();
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, [new JsonEncoder()]);

        $list = [];
        foreach ($entities as $entity) {
            $list[] = $serializer->normalize(
                $entity,
                null,
                [
                    'callbacks' => [
                        'duration' => function ($value) use ($dateIntervalNormalizer) {
                            return $dateIntervalNormalizer->normalize($value);
                        },
                    ],
                ]
            );
        }
        return $list;
    }
}
