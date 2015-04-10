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
use Application\Entity\Secret as SecretEntity;
use Application\Entity\Campaign as CampaignEntity;

/**
 * Repository for Secret entity
 *
 * @category    Application
 * @package     Entity
 */
class SecretRepository extends EntityRepository
{
    /**
     * Distinct data form names
     *
     * @param CampaignEntity $campaign
     * @return array
     */
    public function getCampaignForms(CampaignEntity $campaign)
    {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();
        $qb->select('DISTINCT s.data_form')
           ->from('Application\Entity\Secret', 's')
           ->join('s.campaign', 'c')
           ->andWhere('c.id = :campaign_id')
           ->setParameter('campaign_id', $campaign->getId());

        $rows = $qb->getQuery()->getResult();

        $result = [];
        foreach ($rows as $row)
            $result[] = $row['data_form'];

        return $result;
    }

    /**
     * Count opened data forms
     *
     * @param CampaignEntity $campaign
     * @param string $formName
     * @return integer
     */
    public function countOpened(CampaignEntity $campaign, $formName)
    {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();
        $qb->select('COUNT(s)')
           ->from('Application\Entity\Secret', 's')
           ->join('s.campaign', 'c')
           ->andWhere('s.data_form = :data_form')
           ->andWhere('s.when_opened IS NOT NULL')
           ->andWhere('c.id = :campaign_id')
           ->setParameter('data_form', $formName)
           ->setParameter('campaign_id', $campaign->getId());

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Count saved data forms
     *
     * @param CampaignEntity $campaign
     * @param string $formName
     * @return integer
     */
    public function countSaved(CampaignEntity $campaign, $formName)
    {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();
        $qb->select('COUNT(s)')
           ->from('Application\Entity\Secret', 's')
           ->join('s.campaign', 'c')
           ->andWhere('s.data_form = :data_form')
           ->andWhere('s.when_saved IS NOT NULL')
           ->andWhere('c.id = :campaign_id')
           ->setParameter('data_form', $formName)
           ->setParameter('campaign_id', $campaign->getId());

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Remove all the table content
     */
    public function removeAll()
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'DELETE Application\Entity\Secret s'
        );
        $query->getResult();
    }
}
