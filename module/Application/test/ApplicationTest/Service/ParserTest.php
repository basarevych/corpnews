<?php

namespace ApplicationTest\Service;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Application\Service\Parser;
use Application\Entity\Template as TemplateEntity;
use Application\Entity\Client as ClientEntity;

class ParserTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();
    }

    public function testServiceLocatorMethods()
    {
        $service = new Parser();
        $sl = $this->getApplicationServiceLocator();
        $service->setServiceLocator($sl);

        $this->assertEquals($sl, $service->getServiceLocator(), "Service Locator is wrong");
    }

    public function testSimpleMethods()
    {
        $service = new Parser();
        $sl = $this->getApplicationServiceLocator();
        $service->setServiceLocator($sl);

        $config = $sl->get('Config');
        $keys = array_keys($config['corpnews']['parser']['functions']);

        $this->assertEquals($keys, $service->getFunctions(), "Returned functions are wrong");
        $this->assertEquals('PARSER_FIRST_NAME_DESCR', $service->getFunctionDescr('first_name'), "Returned description is wrong");
    }

    public function testSyntaxValid()
    {
        $msg = 'Hello {{ echo "Sir"; $last_name("default") }}';

        $service = new Parser();
        $sl = $this->getApplicationServiceLocator();
        $service->setServiceLocator($sl);
        $service->setTemplate(new TemplateEntity());
        $service->setClient(new ClientEntity());

        $valid = $service->checkSyntax($msg, $output, false);
        $this->assertEquals(true, $valid, "Valid syntax reported as invalid");
    }

    public function testSyntaxInvalid()
    {
        $msg = '{{ echo 1; }} Hello  <<{{ echo %"Sir" }}>> {{ echo "Sir" }}  {{ xxx';

        $service = new Parser();
        $sl = $this->getApplicationServiceLocator();
        $service->setServiceLocator($sl);
        $service->setTemplate(new TemplateEntity());
        $service->setClient(new ClientEntity());

        $valid = $service->checkSyntax($msg, $output, false);
        $this->assertEquals(false, $valid, "Invalid syntax reported as valid");
    }

    public function testParseWorks()
    {
        $msg = 'Hello {{ echo "Sir-"; $first_name("foobar") }}';

        $service = $this->getMockBuilder('Application\Service\Parser')
                        ->setMethods([ 'getVariableValue' ])
                        ->getMock();

        $sl = $this->getApplicationServiceLocator();
        $service->setServiceLocator($sl);
        $service->setTemplate(new TemplateEntity());
        $service->setClient(new ClientEntity());

        $valid = $service->parse($msg, $output, false, false);
        $this->assertEquals('Hello Sir-foobar', $output, "Substitution error");
    }
}
