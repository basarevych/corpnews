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
use Application\Entity\Letter;

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
     * Letter entities
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Letter", mappedBy="campaign")
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
     * Add letter
     *
     * @param Letter $letter
     * @return Campaign
     */
    public function addLetter(Letter $letter)
    {
        $this->letters[] = $letter;

        return $this;
    }

    /**
     * Remove letter
     *
     * @param Letter $letter
     * @return Campaign
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
