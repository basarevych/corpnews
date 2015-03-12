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
        $config['corpnews']['parser'] = [
            'variables' => [
                'first_name' => [
                    'descr'     => 'PARSER_FIRST_NAME_DESCR',
                ],
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

        $this->assertEquals([ 'first_name' ], $service->getVariables(), "Returned variables are wrong");
        $this->assertEquals('PARSER_FIRST_NAME_DESCR', $service->getVariableDescr('first_name'), "Returned description is wrong");
    }
}
