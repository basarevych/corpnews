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
use Application\Entity\Group;
use Application\Entity\Letter;
use Application\Entity\Secret;

/**
 * Client entity
 * 
 * @category    Application
 * @package     Entity
 * 
 * @ORM\Entity(repositoryClass="Application\Entity\ClientRepository")
 * @ORM\Table(name="clients")
 */
class Client
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
     * Email
     *
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * Is bounced
     *
     * @var \DateTime
     * 
     * @ORM\Column(type="boolean")
     */
    protected $bounced;

    /**
     * Group entities
     *
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Group", inversedBy="clients")
     * @ORM\JoinTable(name="client_groups")
     */
    protected $groups;

    /**
     * Letter entities
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Letter", mappedBy="client")
     */
    protected $letters;

    /**
     * Secret entities
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Secret", mappedBy="client")
     */
    protected $secrets;

    /**
     * Ignored tag entities
     *
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Tag", inversedBy="clients")
     * @ORM\JoinTable(name="client_ignored_tags")
     */
    protected $ignored_tags;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->bounced = false;
        $this->groups = new ArrayCollection();
        $this->letters = new ArrayCollection();
        $this->secrets = new ArrayCollection();
        $this->ignored_tags = new ArrayCollection();
    }

    /**
     * Converts this object to array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id'        => $this->getId(),
            'email'     => $this->getEmail(),
            'bounced'   => $this->getBounced(),
            'groups'    => $this->getGroups()->toArray(),
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
     * Set email
     *
     * @param string $email
     * @return Client
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set bounced
     *
     * @param boolean $bounced
     * @return Client
     */
    public function setBounced($bounced)
    {
        $this->bounced = $bounced;

        return $this;
    }

    /**
     * Get bounced
     *
     * @return boolean
     */
    public function getBounced()
    {
        return $this->bounced;
    }

    /**
     * Add group
     *
     * @param Group $group
     * @return Client
     */
    public function addGroup(Group $group)
    {
        $this->groups[] = $group;

        return $this;
    }

    /**
     * Remove group
     *
     * @param Group $group
     * @return Client
     */
    public function removeGroup(Group $group)
    {
        $this->groups->removeElement($group);

        return $this;
    }

    /**
     * Get groups
     *
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Add letter
     *
     * @param Letter $letter
     * @return Client
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
     * @return Client
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

    /**
     * Add secret
     *
     * @param Secret $secret
     * @return Client
     */
    public function addSecret(Secret $secret)
    {
        $this->secrets[] = $secret;

        return $this;
    }

    /**
     * Remove secret
     *
     * @param Secret $secret
     * @return Client
     */
    public function removeSecret(Secret $secret)
    {
        $this->secrets->removeElement($secret);

        return $this;
    }

    /**
     * Get secrets
     *
     * @return ArrayCollection
     */
    public function getSecrets()
    {
        return $this->secrets;
    }

    /**
     * Add ignored tag
     *
     * @param Tag $ignoredTag
     *
     * @return Client
     */
    public function addIgnoredTag(Tag $ignoredTag)
    {
        $this->ignored_tags[] = $ignoredTag;

        return $this;
    }

    /**
     * Remove ignored tag
     *
     * @param Tag $ignoredTag
     * @return Client
     */
    public function removeIgnoredTag(Tag $ignoredTag)
    {
        $this->ignored_tags->removeElement($ignoredTag);

        return this;
    }

    /**
     * Get ignored tags
     *
     * @return ArrayCollection
     */
    public function getIgnoredTags()
    {
        return $this->ignored_tags;
    }
}
