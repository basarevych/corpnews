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
     * Find all documents with level higher than or equal to $level
     *
     * @param string $level
     * @param integer $limit
     * @return array
     */
    public function findAllByLevel($level, $limit = null)
    {
        $dm = $this->getDocumentManager();
        $levels = SyslogDocument::getLevels($level);

        $qb = $dm->createQueryBuilder();
        $qb->find('Application\Document\Syslog')
           ->field('level')->in($levels)
           ->sort('when_happened', 'desc');

        if ($limit)
            $qb->limit($limit);

        return $qb->getQuery()->execute();
    }

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
