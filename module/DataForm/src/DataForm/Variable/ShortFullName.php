<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DataForm\Variable;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Entity\Client as ClientEntity;
use DataForm\Variable\VariableInterface;
use DataForm\Document\Profile as ProfileDocument;

/**
 * $short_full_name variable
 *
 * @category    DataForm
 * @package     Variable
 */
class ShortFullName implements ServiceLocatorAwareInterface,
                               VariableInterface
{
    /**
     * Service Locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Current client
     *
     * @var ClientEntity
     */
    protected $client = null;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return FirstName
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        if (!$this->serviceLocator)
            throw new \Exception('No Service Locator configured');
        return $this->serviceLocator;
    }

    /**
     * Set current client
     *
     * @param ClientEntity $client
     * @return FirstName
     */
    public function setClient(ClientEntity $client)
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Get current client
     *
     * @return ClientEntity
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Get variable value
     *
     * @return string
     */
    public function getValue()
    {
        $sl = $this->getServiceLocator();
        $dm = $sl->get('doctrine.documentmanager.odm_default');
        $dfm = $sl->get('DataFormManager');

        $class = $dfm->getDocumentClass('profile');
        if (!$class)
            return null;

        $client = $this->getClient();
        $doc = $dm->getRepository($class)
                  ->find($client->getId());
        if (!$doc)
            return null;

        $name = trim($doc->getFirstName() . ' ' . $doc->getLastName());

        return (strlen($name) > 0 ? $name : null);
    }
}
