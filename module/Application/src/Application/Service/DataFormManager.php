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
     * Get class of data form document
     *
     * @param string $name
     * @return string
     */
    public function getDocumentClass($name)
    {
        if (!isset($this->dataForms[$name]))
            return null;
        if (!isset($this->dataForms[$name]['document']))
            return null;

        return $this->dataForms[$name]['document'];
    }

    /**
     * Create all documents for a client
     *
     * @param string $email     Client's email
     */
    public function createClientDocuments($email)
    {
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');

        foreach ($this->dataForms as $name => $config) {
            $class = $this->getDocumentClass($name);
            if (!$class)
                continue;

            $doc = $dm->getRepository($class)
                      ->find($email);
            if (!$doc) {
                $doc = new $class();
                $doc->setClientEmail($email);
                $dm->persist($doc);
            }
        }
        $dm->flush();
    }

    /**
     * Delete all documents of a client
     *
     * @param string $email     Client's email
     */
    public function deleteClientDocuments($email)
    {
        $dm = $this->getServiceLocator()->get('doctrine.documentmanager.odm_default');

        foreach ($this->dataForms as $name => $config) {
            $class = $this->getDocumentClass($name);
            if (!$class)
                continue;

            $doc = $dm->getRepository($class)
                      ->find($email);
            if ($doc)
                $dm->remove($doc);
        }
        $dm->flush();
    }
}
