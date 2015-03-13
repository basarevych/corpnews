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
