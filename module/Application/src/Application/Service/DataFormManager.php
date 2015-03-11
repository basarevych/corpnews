<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Service;

use Exception;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Entity\Client as ClientEntity;

/**
 * DataFormManager service
 * 
 * @category    Application
 * @package     Service
 */
class DataFormManager implements ServiceLocatorAwareInterface
{
    /**
     * Service Locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Data forms
     *
     * @var mixed
     */
    protected $dataForms = null;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Mail
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        if (!$this->dataForms) {
            $options = $serviceLocator->get('Config');
            if (!isset($options['corpnews']['data_forms']))
                throw new \Exception("No 'data_forms' section in the config");

            $this->dataForms = $options['corpnews']['data_forms'];

            $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');
            foreach ($this->dataForms as $name => $props) {
                $class = $props['document'];
                $doc = new $class();
                if (! $doc instanceOf \DataForm\Document\AbstractDataFormDocument)
                    throw new \Exception('All the documents must inherit from AbstractDataFormDocument');
                $repo = $dm->getRepository($class);
                if (! $repo instanceOf \DataForm\Document\DataFormRepositoryInterface)
                    throw new \Exception('All the document repositories must implement DataFormRepositoryInterface');
            }
        }

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
     * Get data form names
     *
     * @return array
     */
    public function getNames()
    {
        return array_keys($this->dataForms);
    }

    /**
     * Get title
     *
     * @param string $name
     * @return string
     */
    public function getTitle($name)
    {
        if (!isset($this->dataForms[$name]) || !isset($this->dataForms[$name]['title']))
            return $name;

        return $this->dataForms[$name]['title'];
    }

    /**
     * Get URL
     *
     * @param string $name
     * @return string
     */
    public function getUrl($name)
    {
        if (!isset($this->dataForms[$name]) || !isset($this->dataForms[$name]['url']))
            return null;

        return $this->dataForms[$name]['url'];
    }

    /**
     * Get class of data form document
     *
     * @param string $name
     * @return string
     */
    public function getDocumentClass($name)
    {
        if (!isset($this->dataForms[$name]) || !isset($this->dataForms[$name]['document']))
            return null;

        return $this->dataForms[$name]['document'];
    }

    /**
     * Get class of data form form 
     *
     * @param string $name
     * @return string
     */
    public function getFormClass($name)
    {
        if (!isset($this->dataForms[$name]) || !isset($this->dataForms[$name]['form']))
            return null;

        return $this->dataForms[$name]['form'];
    }

    /**
     * Get class of data form form table
     *
     * @param string $name
     * @return string
     */
    public function getTableClass($name)
    {
        if (!isset($this->dataForms[$name]) || !isset($this->dataForms[$name]['table']))
            return null;

        return $this->dataForms[$name]['table'];
    }

    /**
     * Create all documents for a client
     *
     * @param ClientEntity $client
     */
    public function createClientDocuments($client)
    {
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');

        foreach ($this->dataForms as $name => $config) {
            $class = $this->getDocumentClass($name);
            if (!$class)
                continue;

            $doc = $dm->getRepository($class)
                      ->find($client->getId());
            if (!$doc) {
                $doc = new $class();
                $doc->setId($client->getId());
                $doc->setClientEmail($client->getEmail());
                $dm->persist($doc);
            }
        }
        $dm->flush();
    }

    /**
     * Update all documents of a client
     *
     * @param ClientEntity $client
     */
    public function updateClientDocuments($client)
    {
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');

        foreach ($this->dataForms as $name => $config) {
            $class = $this->getDocumentClass($name);
            if (!$class)
                continue;

            $doc = $dm->getRepository($class)
                      ->find($client->getId());
            if ($doc) {
                $doc->setClientEmail($client->getEmail());
                $dm->persist($doc);
            }
        }
        $dm->flush();
    }

    /**
     * Delete all documents of a client
     *
     * @param ClientEntity $client
     */
    public function deleteClientDocuments($client)
    {
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');

        foreach ($this->dataForms as $name => $config) {
            $class = $this->getDocumentClass($name);
            if (!$class)
                continue;

            $doc = $dm->getRepository($class)
                      ->find($client->getId());
            if ($doc)
                $dm->remove($doc);
        }
        $dm->flush();
    }

    /**
     * Delete all documents of all clients
     *
     * @param ClientEntity $client
     */
    public function deleteAllDocuments()
    {
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');

        foreach ($this->dataForms as $name => $config) {
            $class = $this->getDocumentClass($name);
            if (!$class)
                continue;

            $dm->getRepository($class)
               ->removeAll();
        }
    }
}
