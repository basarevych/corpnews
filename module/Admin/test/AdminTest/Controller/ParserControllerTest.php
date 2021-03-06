<?php

namespace AdminTest\Controller;

use Zend\Http\Request as HttpRequest;
use Zend\Dom\Query;
use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class ParserControllerTest extends AbstractHttpControllerTestCase
{
    use \ApplicationTest\Controller\RegexAtLeastOnceTrait;

    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $sl = $this->getApplicationServiceLocator();
        $session = $sl->get('Session');
        $cnt = $session->getContainer();
        $cnt->is_admin = true;
    }


    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/admin/parser');

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\parser');
        $this->assertControllerClass('ParserController');
        $this->assertMatchedRouteName('admin');
    }

    public function testIndexActionDisplaysFunctions()
    {
        $sl = $this->getApplicationServiceLocator();
        $config = $sl->get('Config');
        $keys = array_keys($config['corpnews']['parser']['functions']);

        $this->dispatch('/admin/parser');
        $this->assertResponseStatusCode(200);

        foreach ($keys as $key)
            $this->assertQueryContentRegexAtLeastOnce('p.parser-function', '/^.*' . $key . '.*$/m');
    }
}
