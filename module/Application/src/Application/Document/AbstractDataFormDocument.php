<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Base class for all data form documents
 * 
 * @category    Application
 * @package     Document
 * 
 * @ODM\MappedSuperclass
 */
abstract class AbstractDataFormDocument
{
    /**
     * Client email
     *
     * @var string
     *
     * @ODM\Id(strategy="NONE")
     */
    private $client_email;

    /**
     * When the document was updated
     *
     * @var \DateTime
     *
     * @ODM\Date
     */
    private $when_updated;

    /**
     * Set clientEmail
     *
     * @param custom_id $clientEmail
     * @return self
     */
    public function setClientEmail($clientEmail)
    {
        $this->client_email = $clientEmail;
        return $this;
    }

    /**
     * Get clientEmail
     *
     * @return custom_id $clientEmail
     */
    public function getClientEmail()
    {
        return $this->client_email;
    }

    /**
     * Set whenUpdated
     *
     * @param date $whenUpdated
     * @return self
     */
    public function setWhenUpdated($whenUpdated)
    {
        $this->when_updated = $whenUpdated;
        return $this;
    }

    /**
     * Get whenUpdated
     *
     * @return date $whenUpdated
     */
    public function getWhenUpdated()
    {
        return $this->when_updated;
    }
}
