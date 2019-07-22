<?php

namespace PurchaseBundle\Repository;
use PurchaseBundle\Entity\PurchaseRequest;
use PurchaseBundle\PurchaseConstants;

/**
 * PurchaseRequestDiffRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PurchaseRequestDiffRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param PurchaseRequest $purchaseRequest
     * @return array
     */
    public function getPurchaseRequestChanges(PurchaseRequest $purchaseRequest)
    {
        $qb = $this->createQueryBuilder('prd');

        $qb
            ->select('prd')
            ->where($qb->expr()->eq('prd.purchaseRequest', ':requestId'))
            ->setParameter('requestId', $purchaseRequest->getId());

        $qb->orderBy('prd.id', 'DESC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array
     */
    public function getRequestStatesChangesReport()
    {
        $qb = $this->createQueryBuilder('prd');
        $dayStart = (new \DateTime())->setTime(0, 0, 0);
        $dayEnd = (new \DateTime())->setTime(23, 59, 59);

        $states = [
            PurchaseConstants::STATUS_MANAGER_ASSIGNED,
            PurchaseConstants::STATUS_MANAGER_STARTED_WORK,
            PurchaseConstants::STATUS_MANAGER_FINISHED_WORK,
        ];

        $qb->select('prd.newValue, COUNT(DISTINCT prd.id) as cnt');
        $qb
            ->where($qb->expr()->eq('prd.field', ':field'))
            ->andWhere($qb->expr()->in('prd.newValue', ':states'))
            ->andWhere($qb->expr()->between('prd.updatedAt', ':dayStart', ':dayEnd'))

            ->setParameter('field', 'status')
            ->setParameter('dayStart', $dayStart->format('Y-m-d H:i:s'))
            ->setParameter('dayEnd', $dayEnd->format('Y-m-d H:i:s'))
            ->setParameter('states', $states)
        ;

        $qb->groupBy('prd.newValue');

        $report = [];

        foreach ($states as $state) {
            $report[$state] = 0;
        }

        foreach ($qb->getQuery()->getScalarResult() as $state) {
            $report[$state['newValue']] = $state['cnt'];
        }

        return $report;
    }

    /**
     * @return array
     */
    public function getRequestFinancStatesChangesReport()
    {
        $qb = $this->createQueryBuilder('prd');
        $dayStart = (new \DateTime())->setTime(0, 0, 0);
        $dayEnd = (new \DateTime())->setTime(23, 59, 59);

        $states = [
            PurchaseConstants::PAYMENT_STATUS_NEEDS_PAYMENT,
            PurchaseConstants::PAYMENT_STATUS_PAYMENT_PROCESSING,
            PurchaseConstants::PAYMENT_STATUS_PAID,
        ];

        $qb->select('prd.newValue, COUNT(DISTINCT prd.id) as cnt');
        $qb
            ->where($qb->expr()->eq('prd.field', ':field'))
            ->andWhere($qb->expr()->in('prd.newValue', ':states'))
            ->andWhere($qb->expr()->between('prd.updatedAt', ':dayStart', ':dayEnd'))

            ->setParameter('field', 'paymentstatus')
            ->setParameter('dayStart', $dayStart->format('Y-m-d H:i:s'))
            ->setParameter('dayEnd', $dayEnd->format('Y-m-d H:i:s'))
            ->setParameter('states', $states)
        ;

        $qb->groupBy('prd.newValue');

        $report = [];

        foreach ($states as $state) {
            $report[$state] = 0;
        }

        foreach ($qb->getQuery()->getScalarResult() as $state) {
            $report[$state['newValue']] = $state['cnt'];
        }

        return $report;
    }

    /**
     * @param $manager
     * @param $date
     * @return mixed
     */
    public function getFinishedRequestsCount($manager, $date)
    {
        $qb = $this->createQueryBuilder('prd');

        $dateStart = clone $date;
        $dateEnd = clone $date;

        $qb
            ->select('prd')
            ->where($qb->expr()->eq('prd.changedBy', ':managerId'))
            ->andWhere($qb->expr()->between('prd.updatedAt', ':dateStart', ':dateEnd'))
            ->setParameter('managerId', $manager->getId())
            ->andWhere($qb->expr()->eq('prd.oldValue', ':oldStatus'))
            ->andWhere($qb->expr()->eq('prd.newValue', ':newStatus'))
            ->setParameter('dateStart', $dateStart->setTime(0,0,0))
            ->setParameter('dateEnd', $dateEnd->setTime(23,59,59))
            ->setParameter('oldStatus', [
                PurchaseConstants::STATUS_MANAGER_FINISHED_WORK
            ])
            ->setParameter('newStatus', [
                PurchaseConstants::STATUS_DONE
            ]);;


        return count($qb->getQuery()->getResult());
    }

    /**
     * @param $manager
     * @param $date
     * @return mixed
     */
    public function getProcessedRequestsStats($manager, $date)
    {
        $qb = $this->createQueryBuilder('prd');

        $dateStart = clone $date;
        $dateEnd = clone $date;

        $qb
            ->select('prd')
            ->where($qb->expr()->eq('prd.changedBy', ':managerId'))
            ->andWhere($qb->expr()->between('prd.updatedAt', ':dateStart', ':dateEnd'))
            ->andWhere($qb->expr()->in('prd.oldValue', ':oldStatus'))
            ->andWhere($qb->expr()->in('prd.newValue', ':newStatus'))
            ->setParameter('managerId', $manager->getId())
            ->setParameter('dateStart', $dateStart->setTime(0,0,0))
            ->setParameter('dateEnd', $dateEnd->setTime(23,59,59))
            ->setParameter('oldStatus', [
                PurchaseConstants::STATUS_ON_PRELIMINARY_ESTIMATE,
                PurchaseConstants::STATUS_MANAGER_STARTED_WORK
            ])
            ->setParameter('newStatus', [
                PurchaseConstants::STATUS_NEEDS_PRELIMINARY_ESTIMATE_APPROVE,
                PurchaseConstants::STATUS_MANAGER_FINISHED_WORK
            ]);

        $requestsDiff = $qb->getQuery()->getResult();

        $requestStatsDirty = [
            'processedRequests' => [],
            'processedItems' => [],
        ];

        $requestStats = [
            'processedRequests' => 0,
            'processedItems' => 0,
            'processedMoneyAmount' => 0
        ];

        foreach ($requestsDiff as $diff) {
            $requestStatsDirty['processedRequests'][$diff->getPurchaseRequest()->getId()] = $diff->getPurchaseRequest()->getId();
            $requestStatsDirty['processedItems'][$diff->getPurchaseRequest()->getId()] = count($diff->getPurchaseRequest()->getItems());

            foreach ($diff->getPurchaseRequest()->getInvoices() as $invoice) {
                $requestStats['processedMoneyAmount'] += $invoice->getAmount();
            }
        }

        $requestStats['processedRequests'] = count($requestStatsDirty['processedRequests']);
        foreach ($requestStatsDirty['processedItems'] as $requestItems) {
            $requestStats['processedItems'] += $requestItems;
        }

        return $requestStats;
    }
}