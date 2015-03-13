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
use Application\Entity\Template;

/**
 * Campaign entity
 * 
 * @category    Application
 * @package     Entity
 * 
 * @ORM\Entity(repositoryClass="Application\Entity\CampaignRepository")
 * @ORM\Table(name="campaigns")
 */
class Campaign
{
    /**
     * Campaign statuses
     *
     * @const STATUS_CREATED
     * @const STATUS_TESTED
     * @const STATUS_QUEUED
     * @const STATUS_STARTED
     * @const STATUS_DONE
     */
    const STATUS_CREATED = 'created';
    const STATUS_TESTED = 'tested';
    const STATUS_QUEUED = 'queued';
    const STATUS_STARTED = 'started';
    const STATUS_DONE = 'done';

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
     * Name
     *
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * Status
     *
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    protected $status;

    /**
     * When started to send emails
     *
     * @var DateTime
     * 
     * @ORM\Column(type="utcdatetime", nullable=true)
     */
    protected $when_started;

    /**
     * Templates entities
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Template", mappedBy="campaign")
     */
    protected $templates;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->templates = new ArrayCollection();
    }

    /**
     * Converts this object to array
     *
     * @return array
     */
    public function toArray()
    {
        $whenStarted = $this->getWhenStarted();
        $whenFinished = $this->getWhenFinished();

        return [
            'id'            => $this->getId(),
            'name'          => $this->getName(),
            'status'        => $this->getStatus(),
            'when_started'  => $whenStarted ? $whenStarted->getTimestamp() : null,
            'when_finished' => $whenFinished ? $whenFinished->getTimestamp() : null,
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
     * Set name
     *
     * @param string $name
     * @return Campaign
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Campaign
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
     * Set when_started
     *
     * @param DateTime $whenStarted
     * @return Campaign
     */
    public function setWhenStarted($whenStarted)
    {
        $this->when_started = $whenStarted;

        return $this;
    }

    /**
     * Get when_started
     *
     * @return DateTime 
     */
    public function getWhenStarted()
    {
        return $this->when_started;
    }

    /**
     * Add template
     *
     * @param Template $template
     * @return Campaign
     */
    public function addTemplate(Template $template)
    {
        $this->templates[] = $template;

        return $this;
    }

    /**
     * Remove templates
     *
     * @param Template $template
     * @return Campaign
     */
    public function removeTemplate(Template $template)
    {
        $this->templates->removeElement($template);

        return $this;
    }

    /**
     * Get templates
     *
     * @return ArrayCollection
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * Returns array of all the statuses
     *
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_CREATED,
            self::STATUS_TESTED,
            self::STATUS_QUEUED,
            self::STATUS_STARTED,
            self::STATUS_DONE,
        ];
    }
}
