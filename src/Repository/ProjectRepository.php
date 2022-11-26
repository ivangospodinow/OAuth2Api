<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 *
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function save(Project $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Project $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * For data example @see ProjectList.json
     *
     * @param array $params
     * @return void
     */
    public function findForList(array $params = [])
    {
        $limit = $params['list']['limit'] ?? 10;
        $offset = (($params['list']['page'] ?? 1) - 1) * $limit;
        $filter = $params['filter'] ?? [];

        $query = $this->createQueryBuilder('t')
            ->leftJoin('t.tasks', 'tasks')
            ->orderBy('t.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if (isset($filter['id'])) {
            $query->andWhere('t.id = :id')
                ->setParameter(':id', $filter['id']);
        }

        if (isset($filter['status'])) {
            $query->andWhere('t.status = :status')
                ->setParameter('status', $filter['status']);
        }

        return $query->getQuery()
            ->getResult() ?: [];
    }
}
