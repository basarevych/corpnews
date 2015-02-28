<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Admin;

use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

/**
 * Admin module boostrap class
 * 
 * @category    Admin
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

        $eventManager->attach(
            MvcEvent::EVENT_ROUTE,
            [ $this, 'checkAuthorized' ]
        );

        $sharedEventManager = $eventManager->getSharedManager();
        $sharedEventManager->attach(
            "Zend\\Mvc\\Controller\\AbstractActionController",
            MvcEvent::EVENT_DISPATCH,
            [ $this, 'setLayout' ]
        );
    }

    /**
     * Forward to auth controller if not admin
     *
     * @param MvcEvent $e
     */
    public function checkAuthorized(MvcEvent $e)
    {
        $rm = $e->getRouteMatch();
        $controller = $rm->getParam('controller');
        $module = substr($controller, 0, strpos($controller, "\\"));

        if ($module != 'Admin' || $controller == 'Admin\Controller\Auth')
            return;

        if (!$this->isAdmin($e)) {
            $rm->setParam('controller', 'Admin\Controller\Auth')
               ->setParam('action', 'index');
        }
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

        if ($module == 'Admin')
            $controller->layout('layout/admin');
    }

    /**
     * Is the user allowed to use the module?
     *
     * @param MvcEvent $e
     * @return boolean
     */
    public function isAdmin(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();
        $config = $sm->get('Config');
        $session = $sm->get('Session');

        $cnt = $session->getContainer();
        if ($cnt->offsetExists('is_admin'))
            return $cnt->is_admin;

        return false;
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
