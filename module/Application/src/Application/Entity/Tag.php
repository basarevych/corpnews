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

/**
 * Campaign tag entity
 * 
 * @category    Application
 * @package     Entity
 * 
 * @ORM\Entity(repositoryClass="Application\Entity\TagRepository")
 * @ORM\Table(name="tags")
 */
class Tag
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
     * Description
     *
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    protected $descr;

    /**
     * Client entity
     *
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Client", mappedBy="ignored_tags")
     */
    protected $clients;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->clients = new ArrayCollection();
    }

    /**
     * Converts this object to array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id'    => $this->getId(),
            'name'  => $this->getName(),
            'descr' => $this->getDescr(),
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
     *
     * @return Tag
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
     * Set descr
     *
     * @param string $descr
     *
     * @return Tag
     */
    public function setDescr($descr)
    {
        $this->descr = $descr;

        return $this;
    }

    /**
     * Get descr
     *
     * @return string
     */
    public function getDescr()
    {
        return $this->descr;
    }

    /**
     * Add client
     *
     * @param Client $client
     *
     * @return Tag
     */
    public function addClient(Client $client)
    {
        $this->clients[] = $client;

        return $this;
    }

    /**
     * Remove client
     *
     * @param Client $client
     */
    public function removeClient(Client $client)
    {
        $this->clients->removeElement($client);
    }

    /**
     * Get clients
     *
     * @return ArrayCollection
     */
    public function getClients()
    {
        return $this->clients;
    }
}
