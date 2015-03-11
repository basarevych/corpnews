<?php

namespace ApplicationTest\Service;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Application\Service\MailParser;

class MailParserTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);

        $config = $sl->get('Config');
        $config['corpnews']['parser_commands'] = [
            'FirstName' => [
                'descr'     => 'PARSER_FIRST_NAME_DESCR',
                'usage'     => 'PARSER_FIRST_NAME_USAGE',
            ],
        ];

        $sl->setService('Config', $config);
    }

    public function testServiceLocatorMethods()
    {
        $service = new MailParser();
        $sl = $this->getApplicationServiceLocator();
        $service->setServiceLocator($sl);

        $this->assertEquals($sl, $service->getServiceLocator(), "Service Locator is wrong");
    }

    public function testSimpleMethods()
    {
        $service = new MailParser();
        $sl = $this->getApplicationServiceLocator();
        $service->setServiceLocator($sl);

        $this->assertEquals([ 'FirstName' ], $service->getCommands(), "Returned commands are wrong");
        $this->assertEquals('PARSER_FIRST_NAME_DESCR', $service->getDescr('FirstName'), "Returned description is wrong");
        $this->assertEquals('PARSER_FIRST_NAME_USAGE', $service->getUsage('FirstName'), "Returned usage info is wrong");
    }
}
