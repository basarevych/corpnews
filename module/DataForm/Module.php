<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DataForm;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

/**
 * Admin module boostrap class
 * 
 * @category    DataForm
 * @package     Bootstrap
 */
class Module
{
    /**
     * Bootstrap code
     *
     * @param MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $sharedEventManager = $eventManager->getSharedManager();
        $sharedEventManager->attach(
            "Zend\\Mvc\\Controller\\AbstractActionController",
            MvcEvent::EVENT_DISPATCH,
            [ $this, 'setLayout' ]
        );
    }

    /**
     * Set this module default layout
     *
     * @param MvcEvent $e
     */
    public function setLayout(MvcEvent $e)
    {
        $controller = $e->getTarget();      
        $class = get_class($controller);
        $module = substr($class, 0, strpos($class, "\\"));

        if ($module == 'DataForm')
            $controller->layout('layout/form');
    }

    /**
     * Returns configuration to merge with application configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Return an array for passing to Zend\Loader\AutoloaderFactory.
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return [
            'Zend\Loader\StandardAutoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ],
            ],
        ];
    }
}
