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

        $config = $sl->get('Config');
        $config['corpnews'] = [
            'parser' => [
                'variables' => [
                    'first_name' => [
                        'descr'     => 'PARSER_FIRST_NAME_DESCR',
                    ],
                ],
            ],
        ];
     
        $sl->setAllowOverride(true);
        $sl->setService('Config', $config);
    }


    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/admin/parser');
        $this->assertResponseStatusCode(200);

        $this->assertModuleName('admin');
        $this->assertControllerName('admin\controller\parser');
        $this->assertControllerClass('ParserController');
        $this->assertMatchedRouteName('admin');
    }

    public function testIndexActionDisplaysVariables()
    {
        $this->dispatch('/admin/parser');
        $this->assertQueryContentRegexAtLeastOnce('p.parser-variable', '/^.*\$first_name.*$/m');
    }
}
