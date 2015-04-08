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
     * @const STATUS_CREATED
     * @const STATUS_SENT
     * @const STATUS_SKIPPED
     * @const STATUS_FAILED
     */
    const STATUS_CREATED = 'created';
    const STATUS_SENT = 'sent';
    const STATUS_SKIPPED = 'skipped';
    const STATUS_FAILED = 'failed';

    /**
     * @const MESSAGE_ID_LENGTH
     */
    const MESSAGE_ID_LENGTH = 48;

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
     * Letter status
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $status;

    /**
     * When created
     *
     * @var DateTime
     * 
     * @ORM\Column(type="utcdatetime")
     */
    protected $when_created;

    /**
     * When processed
     *
     * @var DateTime
     * 
     * @ORM\Column(type="utcdatetime", nullable=true)
     */
    protected $when_processed;

    /**
     * Message ID
     *
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    protected $message_id;

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
     * Constructor
     */
    public function __construct()
    {
        $this->status = self::STATUS_CREATED;
        $this->when_created = new \DateTime();
    }

    /**
     * Converts this object to array
     *
     * @return array
     */
    public function toArray()
    {
        $whenCreated = $this->getWhenCreated();
        $whenProcessed = $this->getWhenProcessed();

        return [
            'id'                => $this->getId(),
            'status'            => $this->getStatus(),
            'when_created'      => $whenCreated ? $whenCreated->getTimestamp() : null,
            'when_processed'    => $whenProcessed ? $whenProcessed->getTimestamp() : null,
            'message_id'        => $this->getMessageId(),
            'from_address'      => $this->getFromAddress(),
            'to_address'        => $this->getToAddresss(),
            'subject'           => $this->getSubject(),
            'headers'           => $this->getHeaders(),
            'body'              => $this->getBody(),
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
     * Set status
     *
     * @param string $status
     * @return Letter
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set when_created
     *
     * @param \DateTime $whenCreated
     * @return Letter
     */
    public function setWhenCreated($whenCreated)
    {
        $this->when_created = $whenCreated;

        return $this;
    }

    /**
     * Get when_created
     *
     * @return \DateTime
     */
    public function getWhenCreated()
    {
        return $this->when_created;
    }

    /**
     * Set when_processed
     *
     * @param \DateTime $whenProcessed
     * @return Letter
     */
    public function setWhenProcessed($whenProcessed)
    {
        $this->when_processed = $whenProcessed;

        return $this;
    }

    /**
     * Get when_processed
     *
     * @return \DateTime
     */
    public function getWhenProcessed()
    {
        return $this->when_processed;
    }

    /**
     * Set message_id
     *
     * @param string $messageId
     * @return Letter
     */
    public function setMessageId($messageId)
    {
        $this->message_id = $messageId;

        return $this;
    }

    /**
     * Get message_id
     *
     * @return string 
     */
    public function getMessageId()
    {
        return $this->message_id;
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

    /**
     * Get all statuses
     *
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_CREATED,
            self::STATUS_SENT,
            self::STATUS_SKIPPED,
            self::STATUS_FAILED,
        ];
    }

    /**
     * Generate unique Message-ID
     *
     * @return string
     */
    public static function generateMessageId()
    {
        $randomData = openssl_random_pseudo_bytes(1024);
        if ($randomData === false)
            throw new \Exception('Could not generate random string');

        $host = '@corpnews';
        $token = substr(hash('sha512', $randomData), 0, self::MESSAGE_ID_LENGTH - strlen($host));

        return $token . $host;
    }
}
