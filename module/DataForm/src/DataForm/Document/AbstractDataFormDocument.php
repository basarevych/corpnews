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

/**
 * Base class for all data form documents
 * 
 * @category    DataForm
 * @package     Document
 * 
 * @ODM\MappedSuperclass
 */
abstract class AbstractDataFormDocument
{
    /**
     * Client ID
     *
     * @var integer 
     *
     * @ODM\Id(strategy="NONE")
     */
    private $id;

    /**
     * Client email (duplicate of Client entity email)
     *
     * @var string
     *
     * @ODM\String
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
     * Converts this object to array
     *
     * @return array
     */
    abstract public function toArray();

    /**
     * Sets properties from array
     *
     * @param array $data
     * @return AbstractDataFormDocument
     */
    abstract public function fromArray($data);
 
    /**
     * Set id
     *
     * @param mixed $id
     * @return AbstractDataFormDocument
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set client_email
     *
     * @param string $clientEmail
     * @return AbstractDataFormDocument
     */
    public function setClientEmail($clientEmail)
    {
        $this->client_email = $clientEmail;
        return $this;
    }

    /**
     * Get client_email
     *
     * @return string
     */
    public function getClientEmail()
    {
        return $this->client_email;
    }

    /**
     * Set when_updated
     *
     * @param \DateTime $whenUpdated
     * @return AbstractDataFormDocument
     */
    public function setWhenUpdated($whenUpdated)
    {
        $this->when_updated = $whenUpdated;
        return $this;
    }

    /**
     * Get when_updated
     *
     * @return \DateTime
     */
    public function getWhenUpdated()
    {
        return $this->when_updated;
    }
}
