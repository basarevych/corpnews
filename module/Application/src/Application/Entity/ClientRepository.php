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
use Application\Entity\Template as TemplateEntity;
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
     * @param integer $limit
     * @return array
     */
    public function findWithoutLetters(TemplateEntity $template, $limit = null)
    {
        $em = $this->getEntityManager();

        $qbLetters = $em->createQueryBuilder();
        $qbLetters->select('l')
                  ->from('Application\Entity\Letter', 'l')
                  ->join('l.client', 'c2')
                  ->join('l.template', 't2')
                  ->andWhere('c2.id = c1.id')
                  ->andWhere('t2.id = t1.id');

        $qbClients = $em->createQueryBuilder();
        $qbClients->select('c1')
                  ->from('Application\Entity\Client', 'c1')
                  ->join('c1.groups', 'g')
                  ->join('g.campaigns', 'ca')
                  ->join('ca.templates', 't1')
                  ->andWhere('t1.id = :template_id')
                  ->andWhere($qbClients->expr()->not($qbClients->expr()->exists($qbLetters->getDql())))
                  ->setParameter('template_id', $template->getId());

        if ($limit)
            $qbClients->setMaxResults($limit);

        return $qbClients->getQuery()->getResult();
    }

    /**
     * Find all clients that have a failed letter
     *
     * @param TemplateEntity $template
     * @param integer $limit
     * @return array
     */
    public function findWithFailedLetters(TemplateEntity $template, $limit = null)
    {
        $campaign = $template->getCampaign();
        $groups = [];
        foreach ($campaign->getGroups() as $group)
            $groups[] = $group->getId();

        $em = $this->getEntityManager();

        $qbMaxDate = $em->createQueryBuilder();
        $qbMaxDate->select('MAX(l2.id)')
                  ->from('Application\Entity\Letter', 'l2')
                  ->join('l2.client', 'c2')
                  ->join('l2.template', 't2')
                  ->andWhere('c2.id = c1.id')
                  ->andWhere('t2.id = t1.id');

        $qbClients = $em->createQueryBuilder();
        $qbClients->select('c1')
                  ->from('Application\Entity\Client', 'c1')
                  ->join('c1.groups', 'g')
                  ->join('g.campaigns', 'ca')
                  ->join('ca.templates', 't1')
                  ->join('c1.letters', 'l1')
                  ->andWhere('l1.when_sent IS NOT NULL')
                  ->andWhere('l1.error IS NOT NULL')
                  ->andWhere('t1.id = :template_id')
                  ->andWhere($qbClients->expr()->eq('l1.id', '(' . $qbMaxDate->getDql() . ')'))
                  ->setParameter('template_id', $template->getId());

        if ($limit)
            $qbClients->setMaxResults($limit);

        return $qbClients->getQuery()->getResult();
    }

    /**
     * Count clients which have letters
     *
     * @param TemplateEntity $template
     * @return integer
     */
    public function countWithExistingLetters(TemplateEntity $template)
    {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();
        $qb->select('COUNT(DISTINCT cl)')
           ->from('Application\Entity\Client', 'cl')
           ->leftJoin('cl.letters', 'l')
           ->join('l.template', 't')
           ->andWhere('t.id = :template_id')
           ->setParameter('template_id', $template->getId());

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * Count clients awaiting their letters
     *
     * @param TemplateEntity $template
     * @return integer
     */
    public function countWithPendingLetters(TemplateEntity $template)
    {
        $em = $this->getEntityManager();

        $qbMaxDate = $em->createQueryBuilder();
        $qbMaxDate->select('MAX(l2.id)')
                  ->from('Application\Entity\Letter', 'l2')
                  ->join('l2.client', 'c2')
                  ->join('l2.template', 't2')
                  ->andWhere('c2.id = c1.id')
                  ->andWhere('t2.id = t1.id');

        $qbClients = $em->createQueryBuilder();
        $qbClients->select('COUNT(c1)')
                  ->from('Application\Entity\Client', 'c1')
                  ->join('c1.letters', 'l1')
                  ->join('l1.template', 't1')
                  ->andWhere('l1.when_sent IS NULL')
                  ->andWhere('t1.id = :template_id')
                  ->andWhere($qbClients->expr()->eq('l1.id', '(' . $qbMaxDate->getDql() . ')'))
                  ->setParameter('template_id', $template->getId());

        return $qbClients->getQuery()->getSingleScalarResult();
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
