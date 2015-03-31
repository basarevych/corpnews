<?php

namespace ApplicationTest\TaskDaemon;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Application\Entity\Campaign as CampaignEntity;
use Application\Entity\Template as TemplateEntity;
use Application\Entity\Letter as LetterEntity;
use Application\Entity\Client as ClientEntity;
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

        $this->template = new TemplateEntity();
        $this->template->setMessageId('mid');
        $this->template->setSubject('subject');
        $this->template->setHeaders('header: foo');
        $this->template->setBody('body');
        $this->setProp($this->template, 'id', 42);

        $this->campaign = new CampaignEntity();
        $this->campaign->setName('foobar');
        $this->campaign->setStatus(CampaignEntity::STATUS_STARTED);
        $this->setProp($this->campaign, 'id', 42);

        $this->campaign->addTemplate($this->template);
        $this->template->setCampaign($this->campaign);

        $this->repoCampaign->expects($this->any())
                           ->method('findByStatus')
                           ->will($this->returnValue([ $this->campaign ]));

        $this->repoLetter = $this->getMockBuilder('Application\Entity\LetterRepository')
                                 ->disableOriginalConstructor()
                                 ->setMethods([ 'findPending' ])
                                 ->getMock();

        $this->letter = new LetterEntity();
        $this->letter->setWhenCreated(new \DateTime());
        $this->letter->setFromAddress('foo');
        $this->letter->setToAddress('bar');
        $this->letter->setSubject('subject');
        $this->letter->setHeaders('headers');
        $this->letter->setBody('body');

        $this->client = new ClientEntity();
        $this->client->setEmail('foo@bar');

        $this->letter->setClient($this->client);
        $this->client->addLetter($this->letter);

        $this->repoSetting = $this->getMockBuilder('Application\Entity\SettingRepository')
                                  ->disableOriginalConstructor()
                                  ->setMethods([ 'findOneByName', 'getValue' ])
                                  ->getMock();

        $this->mailInterval = 1;
        $this->repoSetting->expects($this->any())
                          ->method('getValue')
                          ->will($this->returnValue($this->mailInterval));

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Campaign', $this->repoCampaign ],
                    [ 'Application\Entity\Letter', $this->repoLetter ],
                    [ 'Application\Entity\Setting', $this->repoSetting ],
                ]));

        $this->logger = $this->getMockBuilder('Application\Service\Logger')
                             ->disableOriginalConstructor()
                             ->setMethods([ 'log' ])
                             ->getMock();

        $this->mail = $this->getMockBuilder('Application\Service\Mail')
                           ->disableOriginalConstructor()
                           ->setMethods([ 'sendLetter' ])
                           ->getMock();

        $this->sl = $this->getApplicationServiceLocator();
        $this->sl->setAllowOverride(true);
        $this->sl->setService('Doctrine\ORM\EntityManager', $this->em);
        $this->sl->setService('Logger', $this->logger);
        $this->sl->setService('Mail', $this->mail);
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

    public function testSendingFails()
    {
        $this->repoLetter->expects($this->any())
                         ->method('findPending')
                         ->will($this->returnValue([ $this->letter ]));

        $this->mail->expects($this->any())
                   ->method('sendLetter')
                   ->will($this->returnValue(false));

        $task = new SendEmailTask();
        $task->setServiceLocator($this->sl);
        $task->run($exit);

        $this->assertEquals(CampaignEntity::STATUS_PAUSED, $this->campaign->getStatus());
    }

    public function testSendingFinishes()
    {
        $first = true;
        $this->repoLetter->expects($this->any())
                         ->method('findPending')
                         ->will($this->returnCallback(function () use (&$first) {
                            if ($first) {
                                $first = false;
                                return [ $this->letter ];
                            }
                            return [];
                         }));

        $letterSent = null;
        $this->mail->expects($this->any())
                   ->method('sendLetter')
                   ->will($this->returnCallback(function ($letter) use (&$letterSent) {
                        $letterSent = $letter;
                        return true;
                   }));

        $task = new SendEmailTask();
        $task->setServiceLocator($this->sl);
        $task->run($exit);

        $this->assertEquals($this->letter, $letterSent, "The letter was not sent");
        $this->assertEquals(CampaignEntity::STATUS_FINISHED, $this->campaign->getStatus(), "Campaign was not finishe");
    }

    public function testSendChecksClientBounced()
    {
        $this->client->setWhenBounced(new \DateTime());

        $first = true;
        $this->repoLetter->expects($this->any())
                         ->method('findPending')
                         ->will($this->returnCallback(function () use (&$first) {
                            if ($first) {
                                $first = false;
                                return [ $this->letter ];
                            }
                            return [];
                         }));

        $letterSent = null;
        $this->mail->expects($this->any())
                   ->method('sendLetter')
                   ->will($this->returnCallback(function ($letter) use (&$letterSent) {
                        $letterSent = $letter;
                        return true;
                   }));

        $task = new SendEmailTask();
        $task->setServiceLocator($this->sl);
        $task->run($exit);

        $this->assertEquals(null, $letterSent, "The letter should not be sent");
        $this->assertEquals(CampaignEntity::STATUS_FINISHED, $this->campaign->getStatus(), "Campaign was not finishe");
    }

    protected function setProp($object, $property, $value)
    {
        $class = new \ReflectionClass(get_class($object));

        $property = $class->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
