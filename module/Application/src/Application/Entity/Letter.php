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
use Application\Entity\Template;
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
     * When sent
     *
     * @var DateTime
     * 
     * @ORM\Column(type="utcdatetime", nullable=true)
     */
    protected $when_sent;

    /**
     * Error message or NULL if success
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $error;

    /**
     * From address
     *
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    protected $from_address;

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
     * Template entity
     *
     * @var ArrayCollection
     *
     * @ORM\ManyToOne(targetEntity="Template", inversedBy="letters")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id")
     */
    protected $template;

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
            'when_sent'     => $whenSent ? $whenSent->getTimestamp() : null,
            'error'         => $this->getError(),
            'from_address'  => $this->getFromAddress(),
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
     * Set error
     *
     * @param string $error
     * @return Letter
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get error
     *
     * @return string 
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set from_address
     *
     * @param string $fromAddress
     * @return Letter
     */
    public function setFromAddress($fromAddress)
    {
        $this->from_address = $fromAddress;

        return $this;
    }

    /**
     * Get from_address
     *
     * @return string 
     */
    public function getFromAddress()
    {
        return $this->from_address;
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
     * Set template
     *
     * @param Template $template
     * @return Letter
     */
    public function setTemplate(Template $template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return Template 
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set client
     *
     * @param Client $client
     * @return Letter
     */
    public function setClient(Client $client)
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
