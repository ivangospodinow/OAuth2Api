<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 *
 * @method Task|null find($id, $lockMode = null, $lockVersion = null)
 * @method Task|null findOneBy(array $criteria, array $orderBy = null)
 * @method Task[]    findAll()
 * @method Task[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function save(Task $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Task $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * For data example @see TaskList.json
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
            ->join('t.project', 'project')
            ->andWhere('project.deletedAt IS NULL')
            ->orderBy('t.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset);

        if (isset($filter['id'])) {
            $query->andWhere('t.id = :id')
                ->setParameter(':id', $filter['id']);
        }

        if (isset($filter['project'])) {
            $query->andWhere('t.project = :project')
                ->setParameter('project', $filter['project']);
        }

        return $query->getQuery()
            ->getResult() ?: [];
    }
}
