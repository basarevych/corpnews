<?php

namespace AdminTest\Controller;

use Zend\Http\Request as HttpRequest;
use Zend\Dom\Query;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class AuthControllerTest extends AbstractHttpControllerTestCase
{
    use \ApplicationTest\Controller\PostRedirectGetTrait;

    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/admin/auth');
        $this->assertResponseStatusCode(401);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\auth');
        $this->assertControllerClass('AuthController');
        $this->assertMatchedRouteName('admin');
    }

    public function testAcceptsCorrectCredentials()
    {
        $sl = $this->getApplicationServiceLocator();
        $config = $sl->get('Config');
        $login = $config['corpnews']['admin']['account'];
        $password = $config['corpnews']['admin']['password'];

        $this->dispatch('/admin/auth');

        $response = $this->getResponse();
        $dom = new Query($response->getContent());
        $result = $dom->execute('input[name="security"]');
        $security = count($result) ? $result[0]->getAttribute('value') : null;

        $this->reset();
        $this->prg('/admin/auth', [
            'security'  => $security,
            'login'     => $login,
            'password'  => $password,
        ]);

        $session = $sl->get('Session');
        $cnt = $session->getContainer();
        $this->assertEquals(
            true,
            $cnt->offsetExists('is_admin') && $cnt->is_admin,
            "Admin flag is not set for correct credentials"
        );
    }
}
