<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DataForm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use DataForm\Document\AbstractDataFormDocument;

/**
 * User unsubscribe document
 * 
 * @category    DataForm
 * @package     Document
 * 
 * @ODM\Document(repositoryClass="DataForm\Document\UnsubscribeRepository")
 */
class Unsubscribe extends AbstractDataFormDocument
{
    /**
     * Ignored tags
     *
     * @var array
     * 
     * @ODM\Collection
     */
    protected $ignored_tags;

    /**
     * Converts this object to array
     *
     * @return array
     */
    public function toArray()
    {
        $whenUpdated = $this->getWhenUpdated();

        return array(
            'id'                => $this->getId(),
            'client_email'      => $this->getClientEmail(),
            'when_updated'      => $whenUpdated ? $whenUpdated->getTimestamp() : null,
            'ignored_tags'      => $this->getIgnoredTags(),
        );
    }

    /**
     * Set ignored_tags
     *
     * @param array $ignoredTags
     * @return Unsubscribe
     */
    public function setIgnoredTags($ignoredTags)
    {
        $this->ignored_tags = $ignoredTags;
        return $this;
    }

    /**
     * Get ignored_tags
     *
     * @return array
     */
    public function getIgnoredTags()
    {
        return $this->ignored_tags;
    }
}
