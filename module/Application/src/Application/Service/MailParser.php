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
     * Commands
     *
     * @var mixed
     */
    protected $commands = null;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return MailParser
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;

        if (!$this->commands) {
            $options = $serviceLocator->get('Config');
            if (!isset($options['corpnews']['parser_commands']))
                throw new \Exception("No 'parser_commands' section in the config");

            $this->commands = $options['corpnews']['parser_commands'];
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
     * Get parser commands
     *
     * @return array
     */
    public function getCommands()
    {
        return array_keys($this->commands);
    }

    /**
     * Get command description
     *
     * @param string $command
     * @return string
     */
    public function getDescription($command)
    {
        if (!isset($this->commands[$command]) || !isset($this->commands[$command]['descr']))
            return null;

        return $this->commands[$command]['descr'];
    }

    /**
     * Get command usage info 
     *
     * @param string $command
     * @return string
     */
    public function getUsage($command)
    {
        if (!isset($this->commands[$command]) || !isset($this->commands[$command]['usage']))
            return null;

        return $this->commands[$command]['usage'];
    }
}
