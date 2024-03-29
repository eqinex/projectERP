<?php

namespace WarehouseBundle\Repository;

use AppBundle\Traits\RepositoryPaginatorTrait;

/**
 * NomenclatureRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class NomenclatureRepository extends \Doctrine\ORM\EntityRepository
{
    use RepositoryPaginatorTrait;

    /**
     * @param $filters
     * @param int $currentPage
     * @param int $perPage
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getAvailableProducts($filters, $currentPage = 1, $perPage = 20)
    {
        $qb = $this->createQueryBuilder('p');

        $qb->select('p');

        if (!empty($filters['group'])) {
            $qb
                ->where('p.group = :group')
                ->setParameter('group', $filters['group'])
            ;
        }

        if (!empty($filters['code'])) {
            $qb
                ->andWhere($qb->expr()->like('p.code', ':code'))
                ->setParameter('code', '%' . $filters['code'] . '%')
            ;
        }

        if (!empty($filters['name'])) {
            $qb
                ->andWhere($qb->expr()->like('p.name', ':name'))
                ->setParameter('name', '%' . $filters['name'] . '%')
            ;
        }

        if (!empty($filters['vendorCode'])) {
            $qb
                ->andWhere($qb->expr()->like('p.vendorCode', ':vendorCode'))
                ->setParameter('vendorCode', '%' . $filters['vendorCode'] . '%')
            ;
        }

        if (!empty($filters['barcode'])) {
            $qb
                ->andWhere($qb->expr()->like('p.barcode', ':barcode'))
                ->setParameter('barcode', '%' . $filters['barcode'] . '%')
            ;
        }

        $paginator = $this->paginate($qb, $currentPage, $perPage);

        return $paginator;
    }
}
