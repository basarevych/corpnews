<?php

namespace ApplicationTest\TaskDaemon;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Application\Entity\Campaign as CampaignEntity;
use Application\TaskDaemon\SendEmail as SendEmailTask;

class SendEmailConnectionMock
{
    public function close()
    {
    }

    public function connect()
    {
    }
}

class SendEmailTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->getApplication()->bootstrap();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getConnection', 'getRepository', 'persist', 'flush' ])
                         ->getMock();

        $this->em->expects($this->any())
                 ->method('getConnection')
                 ->will($this->returnValue(new SendEmailConnectionMock()));

        $this->repoCampaign = $this->getMockBuilder('Application\Entity\CampaignRepository')
                                   ->disableOriginalConstructor()
                                   ->setMethods([ 'findByStatus' ])
                                   ->getMock();

        $this->campaign = new CampaignEntity();
        $this->campaign->setName('foobar');
        $this->campaign->setStatus(CampaignEntity::STATUS_STARTED);
        $this->setProp($this->campaign, 'id', 42);

        $this->repoCampaign->expects($this->any())
                           ->method('findByStatus')
                           ->will($this->returnValue([ $this->campaign ]));

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Campaign', $this->repoCampaign ],
                ]));

        $this->logger = $this->getMockBuilder('Application\Service\Logger')
                             ->disableOriginalConstructor()
                             ->setMethods([ 'log' ])
                             ->getMock();

        $this->sl = $this->getApplicationServiceLocator();
        $this->sl->setAllowOverride(true);
        $this->sl->setService('Doctrine\ORM\EntityManager', $this->em);
        $this->sl->setService('Logger', $this->logger);
    }

    public function testDeadline()
    {
        $dt = new \DateTime();
        $dt->sub(new \DateInterval('P1D'));
        $this->campaign->setWhenDeadline($dt);

        $task = new SendEmailTask();
        $task->setServiceLocator($this->sl);
        $task->run($exit);

        $this->assertEquals(CampaignEntity::STATUS_FINISHED, $this->campaign->getStatus());
    }

    protected function setProp($object, $property, $value)
    {
        $class = new \ReflectionClass(get_class($object));

        $property = $class->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
