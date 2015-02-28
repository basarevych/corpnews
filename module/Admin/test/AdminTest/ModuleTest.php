<?php

namespace AdminTest;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Zend\Mvc\MvcEvent;

class ModuleTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();
    }

    public function testAuthorizedCheckAttached()
    {
        $this->getApplication()->bootstrap();
        $serviceLocator = $this->getApplicationServiceLocator();
        $eventManager = $this->getApplication()->getEventManager();

        $attached = false;

        foreach ($eventManager->getListeners(MvcEvent::EVENT_ROUTE) as $listener) {
            $callback = $listener->getCallback();
            $class = get_class($callback[0]);
            $method = $callback[1];
            if ($class = "Admin\\Module" && $method = "checkAuthorized")
                $attached = true;
        }

        $this->assertEquals(true, $attached, "Authorized check in EVENT_ROUTE");
    }

    public function testSetLayoutAttached()
    {
        $this->getApplication()->bootstrap();
        $serviceLocator = $this->getApplicationServiceLocator();
        $eventManager = $this->getApplication()->getEventManager();
        $sharedManager = $eventManager->getSharedManager();

        $attached = false;

        foreach ($sharedManager->getListeners("Zend\\Mvc\\Controller\\AbstractActionController", MvcEvent::EVENT_DISPATCH) as $listener) {
            $callback = $listener->getCallback();
            $class = get_class($callback[0]);
            $method = $callback[1];
            if ($class = "Admin\\Module" && $method = "setLayout")
                $attached = true;
        }

        $this->assertEquals(true, $attached, "Set layout in EVENT_DISPATCH for AbstractActionController");
    }
}
