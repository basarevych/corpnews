<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Entity;

use Exception;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Campaign as CampaignEntity;

/**
 * Repository for Campaign entity
 *
 * @category    Application
 * @package     Entity
 */
class CampaignRepository extends EntityRepository
{
    /**
     * Count entities by status
     *
     * @param string $status
     * @return integer
     */
    public function getStatusCount($status)
    {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();
        $qb->select('COUNT(c)')
           ->from('Application\Entity\Campaign', 'c')
           ->where('c.status = :status')
           ->setParameter('status', $status);

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Remove all the table content
     */
    public function removeAll()
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'DELETE Application\Entity\Campaign c'
        );
        $query->getResult();
    }
}
