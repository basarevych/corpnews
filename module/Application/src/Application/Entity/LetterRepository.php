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
use Application\Entity\Template as TemplateEntity;
use Application\Entity\Letter as LetterEntity;

/**
 * Repository for Letter entity
 *
 * @category    Application
 * @package     Entity
 */
class LetterRepository extends EntityRepository
{
    /**
     * Find letters to send
     *
     * @param TemplateEntity $template
     * @param integer $limit
     * @return array
     */
    public function findPending(TemplateEntity $template, $limit = null)
    {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();
        $qb->select('l')
           ->from('Application\Entity\Letter', 'l')
           ->join('l.template', 't')
           ->andWhere('l.status = :status')
           ->andWhere('t.id = :template_id')
           ->setParameter('status', LetterEntity::STATUS_CREATED)
           ->setParameter('template_id', $template->getId());

        return $qb->getQuery()->getResult();
    }

    /**
     * Remove all the table content
     */
    public function removeAll()
    {
        $em = $this->getEntityManager();

        $query = $em->createQuery(
            'DELETE Application\Entity\Letter l'
        );
        $query->getResult();
    }
}
