<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\Campaign;
use Application\Entity\Client;

/**
 * Letter entity
 * 
 * @category    Application
 * @package     Entity
 * 
 * @ORM\Entity(repositoryClass="Application\Entity\LetterRepository")
 * @ORM\Table(name="letters")
 */
class Letter
{
    /**
     * Row ID
     *
     * @var integer
     * 
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Secret key
     *
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    protected $key;

    /**
     * When sent
     *
     * @var DateTime
     * 
     * @ORM\Column(type="utcdatetime", nullable=true)
     */
    protected $when_sent;

    /**
     * To address
     *
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    protected $to_address;

    /**
     * Subject
     *
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    protected $subject;

    /**
     * Raw headers
     *
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    protected $headers;

    /**
     * Raw body
     *
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    protected $body;

    /**
     * Campaign entity
     *
     * @var ArrayCollection
     *
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="letters")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id")
     */
    protected $campaign;

    /**
     * Client entity
     *
     * @var ArrayCollection
     *
     * @ORM\ManyToOne(targetEntity="Client", inversedBy="letters")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     */
    protected $client;

    /**
     * Converts this object to array
     *
     * @return array
     */
    public function toArray()
    {
        $whenSent = $this->getWhenSent();

        return [
            'id'            => $this->getId(),
            'key'           => $this->getKey(),
            'when_sent'     => $whenSent ? $whenSent->getTimestamp() : null,
            'to_address'    => $this->getToAddresss(),
            'subject'       => $this->getSubject(),
            'headers'       => $this->getHeaders(),
            'body'          => $this->getBody(),
        ];
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set key
     *
     * @param string $key
     * @return Letter
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Get key
     *
     * @return string 
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Set when_sent
     *
     * @param utcdatetime $whenSent
     * @return Letter
     */
    public function setWhenSent($whenSent)
    {
        $this->when_sent = $whenSent;

        return $this;
    }

    /**
     * Get when_sent
     *
     * @return utcdatetime 
     */
    public function getWhenSent()
    {
        return $this->when_sent;
    }

    /**
     * Set to_address
     *
     * @param string $toAddress
     * @return Letter
     */
    public function setToAddress($toAddress)
    {
        $this->to_address = $toAddress;

        return $this;
    }

    /**
     * Get to_address
     *
     * @return string 
     */
    public function getToAddress()
    {
        return $this->to_address;
    }

    /**
     * Set subject
     *
     * @param string $subject
     * @return Letter
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set headers
     *
     * @param string $headers
     * @return Letter
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Get headers
     *
     * @return string 
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set body
     *
     * @param string $body
     * @return Letter
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string 
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set campaign
     *
     * @param Campaign $campaign
     * @return Letter
     */
    public function setCampaign(Campaign $campaign = null)
    {
        $this->campaign = $campaign;

        return $this;
    }

    /**
     * Get campaign
     *
     * @return Campaign 
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * Set client
     *
     * @param Client $client
     * @return Letter
     */
    public function setClient(Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return Client 
     */
    public function getClient()
    {
        return $this->client;
    }
}
