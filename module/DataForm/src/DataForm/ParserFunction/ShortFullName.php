<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DataForm\ParserFunction;

use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\Entity\Template as TemplateEntity;
use Application\Entity\Client as ClientEntity;
use DataForm\ParserFunction\ParserFunctionInterface;
use DataForm\Document\Profile as ProfileDocument;

/**
 * $short_full_name variable
 *
 * @category    DataForm
 * @package     ParserFunction
 */
class ShortFullName implements ServiceLocatorAwareInterface,
                               ParserFunctionInterface
{
    /**
     * Service Locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Current template
     *
     * @var TemplateEntity
     */
    protected $template = null;

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
     * Set current template
     *
     * @param TemplateEntity $template
     * @return ShortFullName
     */
    public function setTemplate(TemplateEntity $template)
    {
        $this->template = $template;
        return $this;
    }

    /**
     * Get current template
     *
     * @return TemplateEntity
     */
    public function getTemplate()
    {
        return $this->template;
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
     * Execute the function
     *
     * @param string $default
     */
    public function execute($default = '')
    {
        $sl = $this->getServiceLocator();
        $dm = $sl->get('doctrine.documentmanager.odm_default');
        $dfm = $sl->get('DataFormManager');

        $class = $dfm->getDocumentClass('profile');
        if (!$class) {
            echo $default;
            return null;
        }

        $client = $this->getClient();
        if (!$client) {
            echo $default;
            return null;
        }

        $doc = $dm->getRepository($class)
                  ->find($client->getId());
        if (!$doc) {
            echo $default;
            return null;
        }

        $value = trim($doc->getFirstName() . ' ' . $doc->getLastName());
        echo strlen($value) == 0 ? $default : $value;
    }
}
