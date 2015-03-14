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
use Application\Entity\Client;
use Application\Entity\Campaign;

/**
 * Client group entity
 * 
 * @category    Application
 * @package     Entity
 * 
 * @ORM\Entity(repositoryClass="Application\Entity\GroupRepository")
 * @ORM\Table(name="groups")
 */
class Group 
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
     * Client entities
     *
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Client", mappedBy="groups")
     */
    protected $clients;

    /**
     * Campaign entities
     *
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Campaign", mappedBy="groups")
     */
    protected $campaigns;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->clients = new ArrayCollection();
        $this->campaigns = new ArrayCollection();
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
     * @return Group
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
     * Add client
     *
     * @param Client $client
     * @return Group
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
     * @return Group
     */
    public function removeClient(Client $client)
    {
        $this->clients->removeElement($client);

        return $this;
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

    /**
     * Add campaign
     *
     * @param Campaign $campaign
     * @return Group
     */
    public function addCampaign(Campaign $campaign)
    {
        $this->campaigns[] = $campaign;

        return $this;
    }

    /**
     * Remove campaign
     *
     * @param Campaign $campaign
     * @return Group
     */
    public function removeCampaign(Campaign $campaign)
    {
        $this->campaigns->removeElement($campaign);

        return $this;
    }

    /**
     * Get campaigns
     *
     * @return ArrayCollection
     */
    public function getCampaigns()
    {
        return $this->campaigns;
    }
}
