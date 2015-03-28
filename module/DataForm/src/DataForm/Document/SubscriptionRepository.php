<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DataForm\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use DataForm\Document\Subscription as SubscriptionDocument;

/**
 * Subscription document repository
 * 
 * @category    DataForm
 * @package     Document
 */
class SubscriptionRepository extends DocumentRepository
                             implements DataFormRepositoryInterface
{
    /**
     * Remove all documents
     */
    public function removeAll()
    {
        $dm = $this->getDocumentManager();

        $qb = $dm->createQueryBuilder();
        $qb->remove('DataForm\Document\Subscription')
           ->getQuery()
           ->execute();
    }
}
