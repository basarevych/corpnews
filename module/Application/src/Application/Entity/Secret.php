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
 * Secret entity
 * 
 * @category    Application
 * @package     Entity
 * 
 * @ORM\Entity(repositoryClass="Application\Entity\SecretRepository")
 * @ORM\Table(name="secrets")
 */
class Secret
{
    /**
     * @const KEY_LENGTH
     */
    const KEY_LENGTH = 32;

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
    protected $secret_key;

    /**
     * When link opened
     *
     * @var DateTime
     * 
     * @ORM\Column(type="utcdatetime", nullable=true)
     */
    protected $when_opened;

    /**
     * When data form saved
     *
     * @var DateTime
     * 
     * @ORM\Column(type="utcdatetime", nullable=true)
     */
    protected $when_saved;

    /**
     * Data form name
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $data_form;

    /**
     * Campaign entity
     *
     * @var ArrayCollection
     *
     * @ORM\ManyToOne(targetEntity="Campaign", inversedBy="secrets")
     * @ORM\JoinColumn(name="campaign_id", referencedColumnName="id")
     */
    protected $campaign;

    /**
     * Client entity
     *
     * @var ArrayCollection
     *
     * @ORM\ManyToOne(targetEntity="Client", inversedBy="secrets")
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
        $whenOpened = $this->getWhenOpened();
        $whenSaved = $this->getWhenSaved();

        return [
            'id'            => $this->getId(),
            'secret_key'    => $this->getSecretKey(),
            'when_opened'   => $whenOpened ? $whenOpened->getTimestamp() : null,
            'when_saved'    => $whenSaved ? $whenSaved->getTimestamp() : null,
            'data_form'     => $this->getError(),
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
     * Set secret_key
     *
     * @param string $secretKey
     * @return Secret
     */
    public function setSecretKey($secretKey)
    {
        $this->secret_key = $secretKey;

        return $this;
    }

    /**
     * Get secret_key
     *
     * @return string 
     */
    public function getSecretKey()
    {
        return $this->secret_key;
    }

    /**
     * Set when_opened
     *
     * @param utcdatetime $whenOpened
     * @return Secret
     */
    public function setWhenOpened($whenOpened)
    {
        $this->when_opened = $whenOpened;

        return $this;
    }

    /**
     * Get when_opened
     *
     * @return utcdatetime 
     */
    public function getWhenOpened()
    {
        return $this->when_opened;
    }

    /**
     * Set when_saved
     *
     * @param utcdatetime $whenSaved
     * @return Secret
     */
    public function setWhenSaved($whenSaved)
    {
        $this->when_saved = $whenSaved;

        return $this;
    }

    /**
     * Get when_saved
     *
     * @return utcdatetime 
     */
    public function getWhenSaved()
    {
        return $this->when_saved;
    }

    /**
     * Set data_form
     *
     * @param string $dataForm
     * @return Secret
     */
    public function setDataForm($dataForm)
    {
        $this->data_form = $dataForm;

        return $this;
    }

    /**
     * Get data_form
     *
     * @return string 
     */
    public function getDataForm()
    {
        return $this->data_form;
    }

    /**
     * Set campaign
     *
     * @param Campaign $campaign
     * @return Secret
     */
    public function setCampaign(Campaign $campaign)
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
     * @return Secret
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
     * Generate unique secret key
     *
     * @return string
     */
    public static function generateSecretKey()
    {
        $randomData = openssl_random_pseudo_bytes(1024);
        if ($randomData === false)
            throw new \Exception('Could not generate random string');

        return substr(hash('sha512', $randomData), 0, self::KEY_LENGTH);
    }
}
