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
