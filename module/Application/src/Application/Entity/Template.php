<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Entity\Campaign;
use Application\Entity\Letter;

/**
 * Mail template entity
 * 
 * @category    Application
 * @package     Entity
 * 
 * @ORM\Entity(repositoryClass="Application\Entity\TemplateRepository")
 * @ORM\Table(name="templates")
 */
class Template
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
     * Message ID
     *
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    protected $message_id;

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
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="templates")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id")
     */
    protected $campaign;

    /**
     * Letter entities
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Letter", mappedBy="template")
     */
    protected $letters;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->letters = new ArrayCollection();
    }

    /**
     * Converts this object to array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id'            => $this->getId(),
            'message_id'    => $this->getMessageId(),
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
     * Set message_id
     *
     * @param string $messageId
     * @return Template
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
     * Set subject
     *
     * @param string $subject
     * @return Template
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
     * @return Template
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
     * @return Template
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
     * @return Template
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
     * Add letter
     *
     * @param Letter $letter
     * @return Template
     */
    public function addLetter(Letter $letter)
    {
        $this->letters[] = $letter;

        return $this;
    }

    /**
     * Remove letters
     *
     * @param Letter $letter
     * @return Template
     */
    public function removeLetter(Letter $letter)
    {
        $this->letters->removeElement($letter);

        return $this;
    }

    /**
     * Get letters
     *
     * @return ArrayCollection
     */
    public function getLetters()
    {
        return $this->letters;
    }
}
