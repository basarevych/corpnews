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
 * User subscription document
 * 
 * @category    DataForm
 * @package     Document
 * 
 * @ODM\Document(repositoryClass="DataForm\Document\SubscriptionRepository")
 */
class Subscription extends AbstractDataFormDocument
{
    /**
     * Unsubscribed flag
     *
     * @var boolean
     *
     * @ODM\Boolean
     */
    protected $unsubscribed;

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
            'unsubscribed'      => $this->getUnsubscribed(),
            'ignored_tags'      => $this->getIgnoredTags(),
        );
    }

    /**
     * Set unsubscribed
     *
     * @param boolean $unsubscribed
     * @return Subscription
     */
    public function setUnsubscribed($unsubscribed)
    {
        $this->unsubscribed = $unsubscribed;
        return $this;
    }

    /**
     * Get unsubscribed
     *
     * @return boolean
     */
    public function getUnsubscribed()
    {
        return $this->unsubscribed;
    }

    /**
     * Set ignored_tags
     *
     * @param array $ignoredTags
     * @return Subscription
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
