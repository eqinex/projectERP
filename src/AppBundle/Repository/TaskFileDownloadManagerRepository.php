<?php

namespace AppBundle\Repository;

use AppBundle\Entity\TaskFile;

/**
 * TaskFileDownloadManagerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TaskFileDownloadManagerRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param TaskFile $taskFile
     * @return mixed
     */
    public function getDownloadsFile(TaskFile $taskFile)
    {
        $qb = $this->createQueryBuilder('tf');

        $qb
            ->select('tf')
            ->where($qb->expr()->eq('tf.taskFile', ':taskFile'))
            ->setParameter('taskFile', $taskFile->getId())
        ;

        return $qb->getQuery()->getResult();
    }
}
