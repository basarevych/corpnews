<?php

namespace AdminTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class IndexControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();
    }

    public function testIndexActionIsProtected()
    {
        $this->dispatch('/admin');
        $this->assertResponseStatusCode(401);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\auth');
        $this->assertControllerClass('AuthController');
        $this->assertMatchedRouteName('admin');
    }

    public function testIndexActionCanBeAccessed()
    {
        $sl = $this->getApplicationServiceLocator();
        $session = $sl->get('Session');
        $cnt = $session->getContainer();
        $cnt->is_admin = true;

        $this->dispatch('/admin');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\index');
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('admin');
    }
}
