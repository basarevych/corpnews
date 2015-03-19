<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Application\Document\Syslog as SyslogDocument;

/**
 * Syslog document repository
 * 
 * @category    Application
 * @package     Document
 */
class SyslogRepository extends DocumentRepository
{
    /**
     * Remove all documents
     */
    public function removeAll()
    {
        $dm = $this->getDocumentManager();

        $qb = $dm->createQueryBuilder();
        $qb->remove('Application\Document\Syslog')
           ->getQuery()
           ->execute();
    }
}
