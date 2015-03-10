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
use DataForm\Document\Profile as ProfileDocument;

/**
 * Profile document repository
 * 
 * @category    Application
 * @package     Document
 */
class ProfileRepository extends DocumentRepository
{
    /**
     * Remove all documents
     */
    public function removeAll()
    {
        $dm = $this->getDocumentManager();

        $qb = $dm->createQueryBuilder();
        $qb->remove('DataForm\Document\Profile')
           ->getQuery()
           ->execute();
    }
}
