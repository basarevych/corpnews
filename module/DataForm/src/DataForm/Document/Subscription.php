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
        $dtFormat = 'Y-m-d H:i:s P';
        $ignored = $this->getIgnoredTags();

        return array(
            'id'                => $this->getId(),
            'client_email'      => $this->getClientEmail(),
            'when_updated'      => $whenUpdated ? $whenUpdated->format($dtFormat) : null,
            'unsubscribed'      => $this->getUnsubscribed(),
            'ignored_tags'      => $ignored ? join(', ', $ignored) : null,
        );
    }

    /**
     * Sets properties from array
     *
     * @param array $data
     * @return AbstractDataFormDocument
     */
    public function fromArray($data)
    {
        $keys = array_keys($data);

        if (in_array('id', $keys))
            $this->setId(empty($data['id']) ? null : $data['id']);

        if (in_array('client_email', $keys))
            $this->setClientEmail(empty($data['client_email']) ? null : $data['client_email']);

        if (in_array('when_updated', $keys)) {
            $whenUpdated = null;
            if (!empty($data['when_updated'])) {
                $dtFormat = 'Y-m-d H:i:s P';
                $whenUpdated = \DateTime::createFromFormat($dtFormat, $data['when_updated']);
            }
            $this->setWhenUpdated($whenUpdated);
        }

        if (in_array('unsubscribed', $keys))
            $this->setUnsubscribed(empty($data['unsubscribed']) ? null : $data['unsubscribed']);

        if (in_array('ignored_tags', $keys)) {
            $tags = null;
            if (!empty($data['ignored_tags'])) {
                $tags = [];
                foreach (explode(',', $data['ignored_tags']) as $tag)
                    $tags[] = (int)trim($tag);
            }
            $this->setIgnoredTags($tags);
        }
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
