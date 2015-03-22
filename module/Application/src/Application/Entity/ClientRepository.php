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
use Application\Entity\Client as ClientEntity;

/**
 * Repository for Client entity
 *
 * @category    Application
 * @package     Entity
 */
class ClientRepository extends EntityRepository
{
    /**
     * Find all clients by their group name
     *
     * @param string $name
     * @return array
     */
    public function findByGroupName($name)
    {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();
        $qb->select('c')
           ->from('Application\Entity\Client', 'c')
           ->join('c.groups', 'g')
           ->where('g.name = :name')
           ->setParameter('name', $name)
           ->orderBy('c.email');

        return $qb->getQuery()->getResult();
    }

    /**
     * Find all clients that does not have a letter
     *
     * @param TemplateEntity $template
     * @return array
     */
    public function findWithoutLetters($template)
    {
        $campaign = $template->getCampaign();
        $groups = [];
        foreach ($campaign->getGroups() as $group)
            $groups[] = $group->getId();

        $em = $this->getEntityManager();

        $qbLetters = $em->createQueryBuilder();
        $qbLetters->select('l')
                  ->from('Application\Entity\Letter', 'l')
                  ->join('l.client', 'c2')
                  ->join('l.template', 't')
                  ->andWhere('c2.id = c1.id')
                  ->andWhere('t.id = :template_id');

        $qbClients = $em->createQueryBuilder();
        $qbClients->select('c1')
                  ->from('Application\Entity\Client', 'c1')
                  ->join('c1.groups', 'g')
                  ->andWhere('g.id IN (:group_ids)')
                  ->andWhere($qbClients->expr()->not($qbClients->expr()->exists($qbLetters->getDql())))
                  ->setParameter('group_ids', $groups)
                  ->setParameter('template_id', $template->getId());

        return $qbClients->getQuery()->getResult();
    }

    /**
     * Find all clients that have a failed letter
     *
     * @param TemplateEntity $template
     * @return array
     */
    public function findWithFailedLetters($template)
    {
        $campaign = $template->getCampaign();
        $groups = [];
        foreach ($campaign->getGroups() as $group)
            $groups[] = $group->getId();

        $em = $this->getEntityManager();

        $qbMaxDate = $em->createQueryBuilder();
        $qbMaxDate->select('MAX(l2.when_created)')
                  ->from('Application\Entity\Letter', 'l2')
                  ->join('l2.template', 't2')
                  ->join('l2.client', 'c2')
                  ->andWhere('t2.id = t1.id')
                  ->andWhere('c2.id = c1.id');

        $qbClients = $em->createQueryBuilder();
        $qbClients->select('c1')
                  ->from('Application\Entity\Client', 'c1')
                  ->join('c1.groups', 'g')
                  ->join('c1.letters', 'l1')
                  ->join('l1.template', 't1')
                  ->andWhere('g.id IN (:group_ids)')
                  ->andWhere('l1.error IS NOT NULL')
                  ->andWhere('t1.id = :template_id')
                  ->andWhere($qbClients->expr()->eq('l1.when_created', '(' . $qbMaxDate->getDql() . ')'))
                  ->setParameter('group_ids', $groups)
                  ->setParameter('template_id', $template->getId());

        return $qbClients->getQuery()->getResult();
    }

    /**
     * Remove all the table content
     */
    public function removeAll()
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'DELETE Application\Entity\Client c'
        );
        $query->getResult();
    }
}
