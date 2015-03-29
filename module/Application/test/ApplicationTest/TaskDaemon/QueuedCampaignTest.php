<?php

namespace ApplicationTest\TaskDaemon;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Application\Entity\Client as ClientEntity;
use Application\Entity\Template as TemplateEntity;
use Application\Entity\Campaign as CampaignEntity;
use Application\TaskDaemon\QueuedCampaign as QueuedCampaignTask;

class QueuedCampaignConnectionMock
{
    public function close()
    {
    }

    public function connect()
    {
    }
}

class QueuedCampaignTest extends AbstractControllerTestCase
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
                 ->will($this->returnValue(new QueuedCampaignConnectionMock()));

        $this->repoClient = $this->getMockBuilder('Application\Entity\ClientRepository')
                                 ->disableOriginalConstructor()
                                 ->setMethods([ 'findWithoutLetters', 'findWithFailedLetters' ])
                                 ->getMock();

        $a = new ClientEntity();
        $a->setEmail('foo@bar');

        $withoutLettersFirstRun = true;
        $this->repoClient->expects($this->any())
                         ->method('findWithoutLetters')
                         ->will($this->returnCallback(function () use (&$withoutLettersFirstRun, $a) {
                            if ($withoutLettersFirstRun) {
                                $withoutLettersFirstRun = false;
                                return [ $a ];
                            }
                            return [];
                         }));

        $b = new ClientEntity();
        $b->setEmail('baz@bar');

        $withFailedLettersFirstRun = true;
        $this->repoClient->expects($this->any())
                         ->method('findWithFailedLetters')
                         ->will($this->returnCallback(function () use (&$withFailedLettersFirstRun, $b) {
                            if ($withFailedLettersFirstRun) {
                                $withFailedLettersFirstRun = false;
                                return [ $b ];
                            }
                            return [];
                         }));

        $this->repoCampaign = $this->getMockBuilder('Application\Entity\CampaignRepository')
                                   ->disableOriginalConstructor()
                                   ->setMethods([ 'find' ])
                                   ->getMock();

        $template = new TemplateEntity();
        $template->setMessageId('mid');
        $template->setSubject('subject');
        $template->setHeaders('header: foo');
        $template->setBody('body');
        $this->setProp($template, 'id', 42);

        $campaign = new CampaignEntity();
        $campaign->setName('foobar');
        $campaign->setStatus(CampaignEntity::STATUS_QUEUED);
        $this->setProp($campaign, 'id', 42);

        $campaign->addTemplate($template);
        $template->setCampaign($campaign);

        $this->repoCampaign->expects($this->any())
                           ->method('find')
                           ->will($this->returnValue($campaign));

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Campaign', $this->repoCampaign ],
                    [ 'Application\Entity\Client', $this->repoClient ],
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

    public function testTaskFindsNewLettersAndStarts()
    {
        $persisted = [];
        $this->em->expects($this->any())
                 ->method('persist')
                 ->will($this->returnCallback(function ($entity) use (&$persisted) {
                    $persisted[] = $entity;
                 }));

        $task = new QueuedCampaignTask();
        $task->setServiceLocator($this->sl);
        $task->run($exit);

        $this->assertEquals(3, count($persisted), "Two entities should have been persisted");
        $this->assertEquals('foo@bar', $persisted[0]->getToAddress(), "Email is wrong");
        $this->assertEquals('baz@bar', $persisted[1]->getToAddress(), "Email is wrong");
        $this->assertEquals(CampaignEntity::STATUS_STARTED, $persisted[2]->getStatus(), "Campaign was not launched");
    }

    protected function setProp($object, $property, $value)
    {
        $class = new \ReflectionClass(get_class($object));

        $property = $class->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
