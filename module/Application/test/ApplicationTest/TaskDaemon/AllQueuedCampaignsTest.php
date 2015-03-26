<?php

namespace ApplicationTest\TaskDaemon;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Application\Entity\Campaign as CampaignEntity;
use Application\TaskDaemon\AllQueuedCampaigns as AllQueuedCampaignsTask;

class AllQueuedCampaignsConnectionMock
{
    public function close()
    {
    }

    public function connect()
    {
    }
}

class AllQueuedCampaignsTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->getApplication()->bootstrap();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getConnection', 'getRepository' ])
                         ->getMock();

        $this->em->expects($this->any())
                 ->method('getConnection')
                 ->will($this->returnValue(new AllQueuedCampaignsConnectionMock()));

        $this->repoCampaign = $this->getMockBuilder('Application\Entity\CampaignRepository')
                                   ->disableOriginalConstructor()
                                   ->setMethods([ 'findBy' ])
                                   ->getMock();

        $campaign = new CampaignEntity();
        $campaign->setName('foobar');
        $campaign->setStatus(CampaignEntity::STATUS_CREATED);
        $this->setProp($campaign, 'id', 42);

        $this->repoCampaign->expects($this->any())
                           ->method('findBy')
                           ->will($this->returnValue([ $campaign ]));

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Campaign', $this->repoCampaign ],
                ]));

        $this->daemon = $this->getMockBuilder('Application\Service\TaskDaemon')
                             ->setMethods([ 'runTask' ])
                             ->getMock();

        $this->logger = $this->getMockBuilder('Application\Service\Logger')
                             ->disableOriginalConstructor()
                             ->setMethods([ 'log' ])
                             ->getMock();

        $this->sl = $this->getApplicationServiceLocator();
        $this->sl->setAllowOverride(true);
        $this->sl->setService('Doctrine\ORM\EntityManager', $this->em);
        $this->sl->setService('Logger', $this->logger);
    }

    public function testTaskFindsNewLetters()
    {
        $taskName = null;
        $taskData = null;
        $this->daemon->expects($this->any())
                     ->method('runTask')
                     ->will($this->returnCallback(function ($name, $data) use (&$taskName, &$taskData) {
                        $taskName = $name;
                        $taskData = $data;
                      }));

        $task = new AllQueuedCampaignsTask();
        $task->setDaemon($this->daemon);
        $task->setServiceLocator($this->sl);
        $task->run();

        $this->assertEquals('queued_campaign', $taskName, "Task name is wrong");
        $this->assertEquals(42, $taskData, "Task data is wrong");
    }

    protected function setProp($object, $property, $value)
    {
        $class = new \ReflectionClass(get_class($object));

        $property = $class->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
