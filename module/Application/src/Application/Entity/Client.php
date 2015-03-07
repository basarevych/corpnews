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
     * When bounced
     *
     * @var DateTime
     * 
     * @ORM\Column(type="utcdatetime", nullable=true)
     */
    protected $when_bounced;

    /**
     * Converts this object to array
     *
     * @return array
     */
    public function toArray()
    {
        $whenBounced = $this->getWhenBounced();

        return [
            'id'                => $this->getId(),
            'email'             => $this->getEmail(),
            'when_bounced'      => $whenBounced ? $whenBounced->getTimestamp() : null
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
     * Set when_bounced
     *
     * @param utcdatetime $whenBounced
     * @return Client
     */
    public function setWhenBounced($whenBounced)
    {
        $this->when_bounced = $whenBounced;

        return $this;
    }

    /**
     * Get when_bounced
     *
     * @return utcdatetime 
     */
    public function getWhenBounced()
    {
        return $this->when_bounced;
    }
}
