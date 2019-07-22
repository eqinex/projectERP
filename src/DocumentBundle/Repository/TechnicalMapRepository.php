<?php

namespace DocumentBundle\Repository;

use AppBundle\Entity\User;
use AppBundle\Traits\RepositoryPaginatorTrait;
use DocumentBundle\Entity\TechnicalMap;

/**
 * TechnicalMapRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TechnicalMapRepository extends \Doctrine\ORM\EntityRepository
{
    use RepositoryPaginatorTrait;

    public function getAvailableTechnicalMaps($filters, $currentPage = 1, $perPage = 20)
    {
        $qb = $this->createQueryBuilder('tm');

        $qb->select('tm');

        if (!empty($filters['project']))  {
            $qb
                ->andWhere('tm.project = :project')
                ->setParameter('project', $filters['project']);
        }

        if (!empty($filters['owner']))  {
            $qb
                ->andWhere('tm.owner = :owner')
                ->setParameter('owner', $filters['owner']);
        }

        $qb
            -> orderby('tm.id','DESC');

        $paginator = $this->paginate($qb, $currentPage, $perPage);

        return $paginator;
    }

    /**
     * @param User $user
     * @param $filters
     * @param int $currentPage
     * @param int $perPage
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getNeedsApproveTechnicalMaps(User $user, $filters, $currentPage = 1, $perPage = 20)
    {
        $qb = $this->createQueryBuilder('tm');

        $qb
            ->select('tm')
            ->leftJoin('tm.signatories','tms')
            ->andWhere(
                $qb->expr()->andX(
                    $qb->expr()->eq('tms.signatory', ':signatory'),
                    $qb->expr()->eq('tms.approved', 0)))
            ->setParameter('signatory', $user);

        if (!empty($filters['project']))  {
            $qb
                ->andWhere('tm.project = :project')
                ->setParameter('project', $filters['project']);
        }

        if (!empty($filters['owner']))  {
            $qb
                ->andWhere('tm.owner = :owner')
                ->setParameter('owner', $filters['owner']);
        }

        $paginator = $this->paginate($qb, $currentPage, $perPage);

        return $paginator;
    }

    /**
     * @param $status
     * @param User $user
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTechnicalMapsCounter($status, User $user)
    {
        $qb = $this->createQueryBuilder('tm');

        $qb->select('COUNT(tm.id)');

        $qb
            ->leftJoin('tm.signatories','tms')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('tms.signatory', ':signatory'),
                    $qb->expr()->eq('tms.approved', ':notApproved'),
                    $qb->expr()->eq('tm.status', ':status')
                )
            )
            ->setParameter('signatory', $user)
            ->setParameter('notApproved', false)
            ->setParameter('status', $status);

        return $qb->getQuery()->getSingleScalarResult();
    }
}