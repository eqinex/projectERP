<?php

namespace InfrastructureBundle\Repository;

use AppBundle\Traits\RepositoryPaginatorTrait;
use Doctrine\ORM\QueryBuilder;
use InfrastructureBundle\Entity\Computer;
use InfrastructureBundle\Entity\ComputerPart;

/**
 * ComputerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ComputerRepository extends \Doctrine\ORM\EntityRepository
{
    use RepositoryPaginatorTrait;


    /**
     * @param $type
     * @param $filters
     * @param $orderBy
     * @param $order
     * @param int $currentPage
     * @param int $perPage
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getComputers($type, $filters, $orderBy, $order, $currentPage = 1, $perPage = 20)
    {
        $qb = $this->createQueryBuilder('pc');

        $qb = $this->applyComputers($qb, $type, $filters, $orderBy, $order);

        $paginator = $this->paginate($qb, $currentPage, $perPage);

        return $paginator;
    }

    /**
     * @param $type
     * @param $filters
     * @param $orderBy
     * @param $order
     * @return mixed
     */
    public function getAvailableComputers($type, $filters, $orderBy, $order)
    {
        $qb = $this->createQueryBuilder('pc');

        $qb = $this->applyComputers($qb, $type, $filters, $orderBy, $order);

        return $qb->getQuery()->getResult();
    }


    /**
     * @param QueryBuilder $qb
     * @param $type
     * @param $filters
     * @param $orderBy
     * @param $order
     * @return QueryBuilder
     */
    protected function applyComputers(QueryBuilder $qb, $type, $filters, $orderBy, $order)
    {
        $qb
            ->select('pc')
            ->leftJoin('pc.employee', 'u')
            ->andWhere($qb->expr()->eq('pc.deleted',':deleted'))
            ->setParameter('deleted', false);

        if (is_array($type)) {
            $qb->andWhere($qb->expr()->in('pc.type', $type));
        } else {
            $qb
                ->andWhere($qb->expr()->eq('pc.type', ':type'))
                ->setParameter('type', $type);
        }

        $qb = $this->applyFilters($qb, $filters, $orderBy, $order);

        return $qb;

    }

    /**
     * @param QueryBuilder $qb
     * @param $filters
     * @param $orderBy
     * @param $order
     * @return QueryBuilder
     */
    protected function applyFilters(QueryBuilder $qb, $filters, $orderBy, $order)
    {
        if (!empty($filters['type'])) {
            $qb
                ->andWhere($qb->expr()->eq('pc.type',':type'))
                ->setParameter('type', $filters['type'])
            ;
        }

        if (!empty($filters['user'])) {
            $qb
                ->andWhere($qb->expr()->eq('pc.employee', ':userId'))
                ->setParameter('userId', $filters['user']);
        }

        if (!empty($filters['room'])) {
            $qb
                ->andWhere($qb->expr()->eq('u.room', ':room'))
                ->setParameter('room', $filters['room']);
        }

        if (!empty($filters['ipAddress'])) {
            $qb
                ->andWhere($qb->expr()->like('pc.ipAddress', ':ipAddress'))
                ->setParameter('ipAddress', '%' . $filters['ipAddress'] . '%');
        }

        if (!empty($filters['macAddress'])) {
            $qb
                ->andWhere($qb->expr()->like('pc.macAddress', ':macAddress'))
                ->setParameter('macAddress', '%' . $filters['macAddress'] . '%');
        }

        if (!empty($filters['processor'])) {
            $qb
                ->andWhere($qb->expr()->eq('pc.processor', ':processor'))
                ->setParameter('processor', $filters['processor']);
        }

        if (!empty($filters['ram'])) {
            $qb
                ->andWhere($qb->expr()->eq('pc.ram', ':ram'))
                ->setParameter('ram', $filters['ram']);
        }

        if (!empty($filters['motherboard'])) {
            $qb
                ->andWhere($qb->expr()->eq('pc.motherboard', ':motherboard'))
                ->setParameter('motherboard', $filters['motherboard']);
        }

        if (!empty($filters['videoCard'])) {
            $qb
                ->andWhere($qb->expr()->eq('pc.videoCard', ':videoCard'))
                ->setParameter('videoCard', $filters['videoCard']);
        }

        if (!empty($filters['hdd'])) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('pc.hddFirst' , ':hdd'),
                        $qb->expr()->eq('pc.hddSecond', ':hdd'))
                )
                ->setParameter('hdd', $filters['hdd']);
        }

        if (!empty($filters['monitor'])) {
            $qb
                ->leftJoin('pc.computerParts', 'cp')
                ->andWhere($qb->expr()->in('cp.part', ':monitor'))
                ->setParameter('monitor', $filters['monitor']);
        }

        if (!empty($filters['keyboard'])) {
            $qb
                ->andWhere($qb->expr()->eq('pc.keyboard', ':keyboard'))
                ->setParameter('keyboard', $filters['keyboard']);
        }

        if (!empty($filters['mouse'])) {
            $qb
                ->andWhere($qb->expr()->eq('pc.mouse', ':mouse'))
                ->setParameter('mouse', $filters['mouse']);
        }

        if (!empty($filters['operationSystem'])) {
            $qb
                ->andWhere($qb->expr()->eq('pc.operationSystem', ':operationSystem'))
                ->setParameter('operationSystem', $filters['operationSystem']);
        }

        if (!empty($filters['keyOS'])) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('pc.keyInSystem' , ':keyOS'),
                        $qb->expr()->like('pc.keyOnSticker', ':keyOS'))
                )
                ->setParameter('keyOS', '%' . $filters['keyOS'] . '%');
        }

        if (!empty($filters['legal'])) {
            $qb->andWhere($qb->expr()->eq('pc.legal', true));
        }

        if (!empty($orderBy)) {
            if ($orderBy == 'lastname') {
                $qb->orderBy('u.lastname', $order);
            } elseif ($orderBy == 'ipAddress') {
                $qb->orderBy('pc.ipAddress', $order);
            } elseif ($orderBy == 'room') {
                $qb->orderBy('u.room', $order);
            } elseif ($orderBy == 'type') {
                $qb->orderBy('pc.type', $order);
            }
        }

        return $qb;
    }

    /**
     * @param ComputerPart $computerPart
     * @return array
     */
    public function findComputersPartTied(ComputerPart $computerPart)
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->select('c')
            ->leftJoin('c.computerParts', 'cp')
            ->where(
                $qb->expr()->orX(
                    $qb->expr()->eq('c.operationSystem', ':computerPartId'),
                    $qb->expr()->eq('c.processor', ':computerPartId'),
                    $qb->expr()->eq('c.ram', ':computerPartId'),
                    $qb->expr()->eq('c.motherboard', ':computerPartId'),
                    $qb->expr()->eq('c.videoCard', ':computerPartId'),
                    $qb->expr()->eq('c.hddFirst', ':computerPartId'),
                    $qb->expr()->eq('c.hddSecond', ':computerPartId'),
                    $qb->expr()->eq('c.keyboard', ':computerPartId'),
                    $qb->expr()->eq('c.mouse', ':computerPartId'),
                    $qb->expr()->in('cp.part', ':computerPartId')
                )
            )
            ->setParameter('computerPartId', $computerPart->getId());

        $computerNames = [];

        /** @var Computer $computerName */
        foreach ($qb->getQuery()->getScalarResult() as $computerName) {
            $computerNames[] = $computerName['c_name'];
        }

        return $computerNames;
    }

    /**
     * @param Computer $server
     * @return array
     */
    public function findServerTied(Computer $server)
    {
        $qb = $this->createQueryBuilder('c');

        $qb
            ->select('c')
            ->where(
                $qb->expr()->andX(
                    $qb->expr()->eq('c.host', ':serverIpAddress'),
                    $qb->expr()->eq('c.deleted', 0),
                    $qb->expr()->in('c.type', Computer::getServerTypesList())
                )
            )
            ->setParameter('serverIpAddress', $server->getHost());

        $serverIpAddresses = [];

        foreach ($qb->getQuery()->getScalarResult() as $serverIpAddress) {
            $serverIpAddresses[] = $serverIpAddress['c_ipAddress'];
        }

        return $serverIpAddresses;
    }
}