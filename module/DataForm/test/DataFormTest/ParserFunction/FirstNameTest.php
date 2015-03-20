<?php

namespace DataFormTest\ParserFunction;

use Zend\Json\Json;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Application\Entity\Template as TemplateEntity;
use Application\Entity\Client as ClientEntity;
use DataForm\Document\Profile as ProfileDocument;
use DataForm\ParserFunction\FirstName as FirstNameParserFunction;

class FirstNameTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->doc = new ProfileDocument();

        $this->dm = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getRepository' ])
                         ->getMock();

        $this->repo = $this->getMockBuilder('DataForm\Document\ProfileRepository')
                           ->disableOriginalConstructor()
                           ->setMethods([ 'find' ])
                           ->getMock();

        $this->repo->expects($this->any())
                   ->method('find')
                   ->will($this->returnValue($this->doc));

        $this->dm->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValue($this->repo));

        $this->dfm = $this->getMockBuilder('Application\Service\DataFormManager')
                          ->disableOriginalConstructor()
                          ->setMethods([ 'getDocumentClass' ])
                          ->getMock();

        $this->dfm->expects($this->any())
                  ->method('getDocumentClass')
                  ->will($this->returnValue('DocumentClass'));

        $this->sl = $this->getApplicationServiceLocator();
        $this->sl->setAllowOverride(true);
        $this->sl->setService('doctrine.documentmanager.odm_default', $this->dm);
        $this->sl->setService('DataFormManager', $this->dfm);
    }

    public function testExecute()
    {
        $var = new FirstNameParserFunction();
        $var->setServiceLocator($this->sl);
        $var->setTemplate(new TemplateEntity());
        $var->setClient(new ClientEntity());

        ob_start();
        $var->execute('foobar');
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('foobar', $output);

        $this->doc->setFirstName('first');

        ob_start();
        $var->execute();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('first', $output);
    }
}
