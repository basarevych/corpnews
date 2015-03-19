<?php

namespace ApplicationTest\Service;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Application\Service\Logger as LoggerService;
use Application\Document\Syslog as SyslogDocument;

class LoggerTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->getApplication()->bootstrap();

        $this->dm = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getRepository', 'persist', 'flush' ])
                         ->getMock();

        $this->repoSyslog = $this->getMockBuilder('Application\Document\SyslogRepository')
                                 ->disableOriginalConstructor()
                                 ->setMethods([ 'find', 'removeAll' ])
                                 ->getMock();

        $this->dm->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Document\Syslog', $this->repoSyslog ],
                ]));

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('doctrine.documentmanager.odm_default', $this->dm);
    }

    public function testServiceLocatorMethods()
    {
        $service = new LoggerService();
        $sl = $this->getApplicationServiceLocator();
        $service->setServiceLocator($sl);

        $this->assertEquals($sl, $service->getServiceLocator(), "Service Locator is wrong");
    }

    public function testLogWorks()
    {
        $persisted = [];
        $this->dm->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($doc) use (&$persisted) {
                    $persisted[] = $doc;
                 }));

        $sl = $this->getApplicationServiceLocator();
        $service = new LoggerService();
        $service->setServiceLocator($sl);

        $service->log(SyslogDocument::LEVEL_INFO, 'msg', [ 'source_name' => 'foo', 'source_id' => 'bar' ]);

        $this->assertEquals(true, count($persisted) == 1 && $persisted[0] instanceof SyslogDocument, "Syslog not persisted");

        $this->assertEquals(SyslogDocument::LEVEL_INFO, $persisted[0]->getLevel(), "Level is wrong");
        $this->assertEquals('msg', $persisted[0]->getMessage(), "Message is wrong");
        $this->assertEquals('foo', $persisted[0]->getSourceName(), "Source name is wrong");
        $this->assertEquals('bar', $persisted[0]->getSourceId(), "Source ID is wrong");
    }

    public function testClearWorks()
    {
        $cleared = false;
        $this->repoSyslog->expects($this->any())
                         ->method('removeAll')
                         ->will($this->returnCallback(function () use (&$cleared) {
                            $cleared = true;
                         }));

        $sl = $this->getApplicationServiceLocator();
        $service = new LoggerService();
        $service->setServiceLocator($sl);

        $service->clear();

        $this->assertEquals(true, $cleared);
    }
}
