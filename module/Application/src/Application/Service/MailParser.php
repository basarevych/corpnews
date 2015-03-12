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
 * MailParser service
 * 
 * @category    Application
 * @package     Service
 */
class MailParser implements ServiceLocatorAwareInterface
{
    /**
     * Service Locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Variables
     *
     * @var mixed
     */
    protected $variables = null;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return MailParser
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        if (!$this->variables) {
            $options = $serviceLocator->get('Config');
            if (!isset($options['corpnews']['parser']['variables']))
                throw new \Exception("No 'parser/variables' section in the config");

            $this->variables = $options['corpnews']['parser']['variables'];
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
     * Get parser variables
     *
     * @return array
     */
    public function getVariables()
    {
        return array_keys($this->variables);
    }

    /**
     * Get variable description
     *
     * @param string $variable
     * @return string
     */
    public function getVariableDescr($variable)
    {
        if (!isset($this->variables[$variable]) || !isset($this->variables[$variable]['descr']))
            return null;

        return $this->variables[$variable]['descr'];
    }
}
