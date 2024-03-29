<?php

namespace PurchaseBundle\Repository;
use AppBundle\Entity\User;
use AppBundle\Traits\RepositoryPaginatorTrait;
use Doctrine\ORM\QueryBuilder;
use ProductionBundle\Entity\Ware;
use PurchaseBundle\Entity\PurchaseRequest;
use PurchaseBundle\PurchaseConstants;

/**
 * PurchaseRequestRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PurchaseRequestRepository extends \Doctrine\ORM\EntityRepository
{
    use RepositoryPaginatorTrait;

    /**
     * @param $type
     * @param $currentUser
     * @param $filters
     * @param int $currentPage
     * @param int $perPage
     * @param string $order
     * @param string $orderBy
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getPurchaseRequests(
        $type,
        $currentUser,
        $filters,
        $orderBy,
        $order,
        $currentPage = 1,
        $perPage = 20)
    {
        $filters = $this->updateFiltersByPageType($type, $currentUser, $filters);

        $qb = $this->createQueryBuilder('pr');

        $qb = $this->applyFilters($qb, $filters);

        if (!empty($orderBy))
        {
            if ($orderBy == 'project') {
                $qb
                    ->leftJoin('pr.project', 'tpr')
                    ->orderBy('tpr.name', $order);
            } else {
                $qb->orderBy('pr.' . $orderBy, $order);
            }
        } else {
            $qb->orderBy('pr.id', 'DESC');
        }

        $paginator = $this->paginate($qb, $currentPage, $perPage);

        return $paginator;
    }

    /**
     * @param $filters
     *
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getRequestsListForExport($filters)
    {
        $qb = $this->createQueryBuilder('pr');

        $qb = $this->applyFilters($qb, $filters);

        $qb->addOrderBy('pr.id', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $filters
     *
     * @param $state
     * @return \Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function getRequestsCount($filters, $state)
    {
        $qb = $this->createQueryBuilder('pr');
        $filters['status'] = [$state];

        $qb = $this->applyFilters($qb, $filters);

        $qb->select('COUNT(pr.id)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param $type
     * @param $currentUser
     * @return mixed
     */
    public function getRequestsCounter($type, $currentUser)
    {
        $qb = $this->createQueryBuilder('pr');

        $filters = $this->updateFiltersByPageType($type, $currentUser, []);

        $qb = $this->applyFilters($qb, $filters);

        $qb->select('COUNT(pr.id)');

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param $filters
     * @return QueryBuilder
     */
    protected function applyFilters(QueryBuilder $qb, $filters)
    {
        if (!empty($filters['project'])) {
            $qb
                ->andWhere('pr.project = :project')
                ->setParameter('project', $filters['project'])
            ;
        }
        if (!empty($filters['code'])) {
            $qb
                ->andWhere(
                    $qb->expr()->like('pr.code', ':code')
                )
                ->setParameter('code', '%' . $filters['code'] . '%')
            ;

            $filters['status'] = !empty($filters['status']) ? $filters['status'] : PurchaseConstants::getStatesList();
        }

        if (!empty($filters['createdAt'])) {
            list($startAt, $endAt) = explode(' - ', $filters['createdAt']);

            $startAt = new \DateTime($startAt);
            $endAt = new \DateTime($endAt);

            $qb
                ->andWhere(
                    $qb->expr()->between('pr.createdAt', ':startAt', ':endAt')
                )
                ->setParameter('startAt', $startAt)
                ->setParameter('endAt', $endAt)
            ;

            $filters['status'] = !empty($filters['status']) ? $filters['status'] : PurchaseConstants::getStatesList();
        }

        if (!empty($filters['relevanceDate'])) {
            list($startAt, $endAt) = explode(' - ', $filters['relevanceDate']);

            $startAt = new \DateTime($startAt);
            $endAt = new \DateTime($endAt);

            $qb
                ->andWhere(
                    $qb->expr()->between('pr.relevanceDate', ':startAt', ':endAt')
                )
                ->setParameter('startAt', $startAt)
                ->setParameter('endAt', $endAt)
            ;

            $filters['status'] = !empty($filters['status']) ? $filters['status'] : PurchaseConstants::getStatesList();
        }

        if (!empty($filters['owner'])) {
            $qb
                ->andWhere('pr.owner = :owner')
                ->setParameter('owner', $filters['owner'])
            ;
        }
        if (!empty($filters['type'])) {
            $qb
                ->andWhere('pr.type = :type')
                ->setParameter('type', $filters['type'])
            ;
        }
        if (!empty($filters['priority'])) {
            $qb
                ->andWhere('pr.priority = :priority')
                ->setParameter('priority', $filters['priority'])
            ;
        }
        if (!empty($filters['leader'])) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('pr.leader' , ':leader')),
                        $qb->expr()->eq('pr.leaderApproved', 0)
                )
                ->orWhere($qb->expr()->eq('pr.projectLeader' , ':leader'))
                ->setParameter('leader', $filters['leader'])
            ;
        }
        if (!empty($filters['manager'])) {
            $qb
                ->andWhere('pr.purchasingManager = :manager')
                ->setParameter('manager', $filters['manager'])
            ;

            $filters['status'] = !empty($filters['status']) ? $filters['status'] : PurchaseConstants::getStatesList();
        }

        if (!empty($filters['status']) ||  (isset($filters['status']) && $filters['status'] === '0')) {
            $qb
                ->andWhere(
                    $qb->expr()->in('pr.status', ':statuses')
                )
                ->setParameter('statuses', $states = $filters['status'])
            ;
        } else {
            $qb
                ->andWhere(
                    $qb->expr()->notIn('pr.status', ':statuses')
                )
                ->setParameter('statuses', [PurchaseConstants::STATUS_REJECTED, PurchaseConstants::STATUS_DONE])
            ;
        }

        if (!empty($filters['invoicePayment'])) {
            $invoicePayments = $filters['invoicePayment'];
            $qb
                ->andWhere(
                    $qb->expr()->in('pr.invoicePayment', ':invoicePayments')
                )
                ->setParameter('invoicePayments', $invoicePayments)
            ;
        }

        if (!empty($filters['expensesType'])) {
            $expensesTypes = $filters['expensesType'];
            $qb
                ->andWhere(
                    $qb->expr()->in('pr.expensesType', ':expensesTypes')
                )
                ->setParameter('expensesTypes', $expensesTypes)
            ;
        }

        if (!empty($filters['paymentStatus'])) {

            $paymentStates = is_array($filters['paymentStatus']) ? $filters['paymentStatus'] :
                [$filters['paymentStatus']];
            $qb
                ->andWhere(
                    $qb->expr()->in('pr.paymentStatus', ':paymentStatuses')
                )
                ->setParameter('paymentStatuses', $paymentStates)
            ;
        }

        if (!empty($filters['financialUser'])) {
            $qb
                ->andWhere(
                    $qb->expr()->eq('pr.financialManager', ':financialUser')
                )
                ->setParameter('financialUser', $filters['financialUser'])
            ;
        }

        if (!empty($filters['deliveryStatus'])) {
            $qb
                ->andWhere(
                    $qb->expr()->in('pr.deliveryStatus', ':deliveryStatuses')
                )
                ->setParameter('deliveryStatuses', [$filters['deliveryStatus']])
            ;
        }

        if (!empty($filters['supplier']) || !empty($filters['itemName']) || !empty($filters['itemNumber']) || !empty($filters['invoiceNumber']) || !empty($filters['miscellaneous'])) {
            $qb
                ->leftJoin('pr.items', 'pri')
            ;
        }
        if (!empty($filters['supplier'])) {
            $qb
                ->andWhere(
                    $qb->expr()->eq('pri.supplier', ':supplier')
                )
                ->setParameter('supplier', [$filters['supplier']])
            ;
        }

        if (!empty($filters['invoiceNumber'])) {
            $qb
                ->andWhere(
                    $qb->expr()->eq('pri.invoiceNumber', ':invoiceNumber')
                )
                ->setParameter('invoiceNumber', [$filters['invoiceNumber']])
            ;
        }

        if (!empty($filters['itemName'])) {
            $qb

                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->in('pri.title', ':itemName')
                    )
                )
                ->setParameter('itemName', [$filters['itemName']])
            ;
        }

        if (!empty($filters['itemNumber'])) {
            $qb
                ->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->eq('pri.sku', ':itemNumber')
                    )
                )
                ->setParameter('itemNumber', [$filters['itemNumber']])
            ;
        }

        if (!empty($filters['ownProduction'])) {
            $qb
                ->andWhere('pr.productionStatus = :productionStatus')
                ->setParameter('productionStatus', PurchaseConstants::PRODUCTION_STATUS_IN_PRODUCTION)
            ;
        }

        if (!empty($filters['ware'])) {
            $qb
                ->andWhere($qb->expr()->eq('pr.ware', ':ware'))
                ->setParameter('ware', $filters['ware'])
            ;
        }

        if (isset($filters['productionLeader'])) {
            $qb
                ->andWhere('pr.productionLeader = :productionLeader')
                ->setParameter('productionLeader', $filters['productionLeader'])
            ;
        }

        if (!empty($filters['description'])) {
            $qb
                ->andWhere($qb->expr()->like('pr.description', ':description'))
                ->setParameter('description','%' . $filters['description'] . '%')
            ;
        }

        if (!empty($filters['miscellaneous'])) {
            $qb
                ->andWhere(
                    $qb->expr()->like('pri.invoiceNumber', ':miscM')
                )
                ->orWhere(
                    $qb->expr()->orX(
                        $qb->expr()->in('pri.title', ':misc')
                    )
                )
                ->orWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('pri.sku', ':miscM')
                    )
                )
                ->orWhere(
                    $qb->expr()->like('pr.description', ':miscM')
                )
                ->setParameter('miscM', '%' . $filters['miscellaneous'] . '%')
                ->setParameter('misc', [$filters['miscellaneous']])
            ;
        }

        return $qb;
    }

    /**
     * @return array
     */
    public function getPurchasingStatesReport()
    {
        $states = [
            PurchaseConstants::STATUS_NEEDS_PURCHASING_MANAGER,
            PurchaseConstants::STATUS_MANAGER_ASSIGNED,
            PurchaseConstants::STATUS_MANAGER_STARTED_WORK
        ];

        return $this->getStatesReport($states);
    }

    /**
     * @return array
     */
    public function getOngoingStatesReport()
    {
        $states = [
            PurchaseConstants::STATUS_NEW,
            PurchaseConstants::STATUS_NEEDS_FIXING,
            PurchaseConstants::STATUS_NEEDS_LEADER_APPROVAL,
            PurchaseConstants::STATUS_NEEDS_PRODUCTION_LEADER_APPROVAL
        ];

        return $this->getStatesReport($states);
    }

    /**
     * @param $states
     * @return array
     */
    public function getStatesReport($states)
    {
        $qb = $this->createQueryBuilder('pr');

        $qb->select('pr.status, COUNT(DISTINCT pr.id) as cnt');
        $qb->where($qb->expr()->in('pr.status', ':states'));

        $qb->setParameter('states', $states);

        $qb->groupBy('pr.status');

        $report = [];

        foreach ($states as $state) {
            $report[$state] = 0;
        }

        foreach ($qb->getQuery()->getScalarResult() as $state) {
            $report[$state['status']] = $state['cnt'];
        }

        return $report;
    }

    /**
     * @return array
     */
    public function getFinancialStatesReport()
    {
        $qb = $this->createQueryBuilder('pr');

        $qb->select('pr.paymentStatus, COUNT(DISTINCT pr.id) as cnt');
        $qb->where($qb->expr()->in('pr.paymentStatus', ':states'));

        $states = [
            PurchaseConstants::PAYMENT_STATUS_NEEDS_PAYMENT,
            PurchaseConstants::PAYMENT_STATUS_PAYMENT_PROCESSING
        ];

        $qb->setParameter('states', $states);

        $qb->groupBy('pr.paymentStatus');

        $report = [];

        foreach ($states as $state) {
            $report[$state] = 0;
        }

        foreach ($qb->getQuery()->getScalarResult() as $state) {
            $report[$state['paymentStatus']] = $state['cnt'];
        }

        return $report;
    }

    /**
     * @return array
     */
    public function getProductionStatesReport()
    {
        $qb = $this->createQueryBuilder('pr');

        $qb->select('pr.productionStatus, COUNT(DISTINCT pr.id) as cnt');
        $qb->where($qb->expr()->in('pr.productionStatus', ':states'));

        $states = [
            PurchaseConstants::PRODUCTION_STATUS_IN_PRODUCTION,
        ];
        $qb->setParameter('states', $states);

        $qb->groupBy('pr.productionStatus');

        $report = [];

        foreach ($states as $state) {
            $report[$state] = 0;
        }

        foreach ($qb->getQuery()->getScalarResult() as $state) {
            $report[$state['productionStatus']] = $state['cnt'];
        }

        return $report;
    }

    /**
     * @param string $type
     * @param User $currentUser
     * @param array $filters
     *
     * @return array
     */
    protected function updateFiltersByPageType($type, $currentUser, $filters) {
        switch ($type) {
            case 'my-requests':
                $filters['owner'] = $currentUser;

                break;
            case 'need-approve':
                $filters['leader'] = $currentUser;

                if (!isset($filters['status'])) {
                    $filters['status'] = [
                        PurchaseConstants::STATUS_NEEDS_LEADER_APPROVAL,
                        PurchaseConstants::STATUS_NEEDS_PROJECT_LEADER_APPROVE,
                        PurchaseConstants::STATUS_NEEDS_PRELIMINARY_ESTIMATE_APPROVE
                    ];
                }

                break;
            case 'production':

                if (!$currentUser->isProductionLeader()) {
                    $filters['productionLeader'] = $currentUser;
                }
                $filters['type'] = 'production';

                break;

            case 'need-manager':
                $filters['purchasingLeader'] = $currentUser;
                $filters['status'] = [PurchaseConstants::STATUS_NEEDS_PURCHASING_MANAGER];

                break;
            case 'manager-assigned':
                $filters['manager'] = $currentUser;

                if (!isset($filters['status'])) {
                    $filters['status'] = [
                        PurchaseConstants::STATUS_MANAGER_ASSIGNED,
                        PurchaseConstants::STATUS_MANAGER_STARTED_WORK,
                        PurchaseConstants::STATUS_MANAGER_FINISHED_WORK,
                        PurchaseConstants::STATUS_ON_PRELIMINARY_ESTIMATE
                    ];
                }

                break;
            case 'needs-payment':
                $filters['financialUser'] = $currentUser;

                $filters['status'] = [PurchaseConstants::STATUS_MANAGER_FINISHED_WORK];
                $filters['paymentStatus'] = [
                    PurchaseConstants::PAYMENT_STATUS_NEEDS_PAYMENT,
                    PurchaseConstants::PAYMENT_STATUS_PAYMENT_PROCESSING
                ];

                break;
        }

        return $filters;
    }

    /**
     * @param int $supplier
     * @return array
     */
    public function findBySupplier($supplier)
    {
        $qb = $this->createQueryBuilder('sl');

        $qb->select('sl');

        $qb->leftJoin('sl.items', 'spl');
        $qb
            ->andWhere(
                $qb->expr()->eq('spl.supplier', ':supplier')
            )
            ->setParameter('supplier', $supplier);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $limit
     * @return mixed
     */
    public function getLostPurchaseRequests($limit)
    {
        $qb = $this->createQueryBuilder('lpr');

        $qb->select('lpr');

        $qb
            ->where(
                $qb->expr()->in('lpr.priority', ':priorities')
            )
            ->andWhere(
                $qb->expr()->notIn('lpr.status', ':statuses')
            )
            ->andWhere("lpr.relevanceDate < DATE_SUB(CURRENT_DATE(), 30, 'day')")
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull('lpr.paymentStatus'),
                    $qb->expr()->isNull('lpr.deliveryStatus')
                )
            )
            ->setParameter('priorities', [
                PurchaseRequest::PRIORITY_NORMAL,
                PurchaseRequest::PRIORITY_LOW
            ])
            ->setParameter('statuses', [
                PurchaseConstants::STATUS_NEW,
                PurchaseConstants::STATUS_NEEDS_FIXING,
                PurchaseConstants::STATUS_REJECTED,
                PurchaseConstants::STATUS_DONE
            ])
            ->setMaxResults($limit)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Ware $ware
     * @return mixed
     */
    public function getAvailableSerialPurchaseRequests(Ware $ware)
    {
        $qb = $this->createQueryBuilder('asp');
        $qb->select('asp');

        $qb
            ->where('asp.status != :status')
            ->andWhere('asp.ware = :ware')
            ->setParameter('status', PurchaseConstants::STATUS_REJECTED)
            ->setParameter('ware', $ware->getId())
        ;

        return $qb->getQuery()->getResult();
    }
}