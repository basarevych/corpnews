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
use TaskDaemon\TaskDaemon as Daemon;

/**
 * TaskDaemon service
 * 
 * @category    Application
 * @package     Service
 */
class TaskDaemon implements ServiceLocatorAwareInterface
{
    /**
     * Service Locator
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator = null;

    /**
     * Daemon instance
     *
     * @var Daemon
     */
    protected $daemon = null;

    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Mail
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
     * Get daemon instance
     *
     * @return Daemon
     */
    public function getDaemon()
    {
        if (!$this->daemon) {
            $config = $this->getServiceLocator()->get('Config');
            if (!isset($config['gearman']['namespace']))
                throw new \Exception(' No gearman namespace in the config');
            if (!isset($config['gearman']['host']))
                throw new \Exception(' No gearman host in the config');
            if (!isset($config['gearman']['port']))
                throw new \Exception(' No gearman port in the config');

            Daemon::setOptions([
                'namespace' => $config['gearman']['namespace'],
                'num_workers' => 10,
                'pid_file'  => '/var/tmp/corpnews-daemon.pid',
                'debug' => false,
                'gearman' => [
                    'host' => $config['gearman']['host'],
                    'port' => $config['gearman']['port'],
                ],
            ]);
            $this->daemon = Daemon::getInstance();

            foreach ($config['corpnews']['task_daemon']['tasks'] as $name => $class) {
                $object = new $class();
                $object->setServiceLocator($this->getServiceLocator());
                $this->daemon->defineTask($name, $object);
            }
        }

        return $this->daemon;
    }
}
