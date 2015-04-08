<?php

namespace ApplicationTest\TaskDaemon;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Application\Entity\Campaign as CampaignEntity;
use Application\Entity\Template as TemplateEntity;
use Application\Entity\Letter as LetterEntity;
use Application\Entity\Client as ClientEntity;
use Application\Entity\Tag as TagEntity;
use DataForm\Document\Subscription as SubscriptionDocument;
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
        $this->letter->setMessageId('mid');
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

        $this->dm = $this->getMockBuilder('Doctrine\ODM\MongoDB\DocumentManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getRepository' ])
                         ->getMock();

        $this->repoSubscription = $this->getMockBuilder('DataForm\Document\ProfileRepository')
                                       ->disableOriginalConstructor()
                                       ->setMethods([ 'find' ])
                                       ->getMock();

        $this->doc = new SubscriptionDocument();

        $this->repoSubscription->expects($this->any())
                               ->method('find')
                               ->will($this->returnValue($this->doc));

        $this->dm->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'DataForm\Document\Subscription', $this->repoSubscription ],
                ]));

        $this->logger = $this->getMockBuilder('Application\Service\Logger')
                             ->disableOriginalConstructor()
                             ->setMethods([ 'log' ])
                             ->getMock();

        $this->mail = $this->getMockBuilder('Application\Service\Mail')
                           ->disableOriginalConstructor()
                           ->setMethods([ 'sendLetter' ])
                           ->getMock();

        $this->dfm = $this->getMockBuilder('Application\Service\DataFormManager')
                          ->disableOriginalConstructor()
                          ->setMethods([ 'getDocumentClass' ])
                          ->getMock();

        $this->dfm->expects($this->any())
                  ->method('getDocumentClass')
                  ->will($this->returnValue('DataForm\Document\Subscription'));

        $this->sl = $this->getApplicationServiceLocator();
        $this->sl->setAllowOverride(true);
        $this->sl->setService('Doctrine\ORM\EntityManager', $this->em);
        $this->sl->setService('doctrine.documentmanager.odm_default', $this->dm);
        $this->sl->setService('Logger', $this->logger);
        $this->sl->setService('Mail', $this->mail);
        $this->sl->setService('DataFormManager', $this->dfm);
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
                        $letter->setStatus(LetterEntity::STATUS_SENT);
                        $letter->setWhenProcessed(new \DateTime());
                        $letterSent = $letter;
                        return true;
                   }));

        $task = new SendEmailTask();
        $task->setServiceLocator($this->sl);
        $task->run($exit);

        $this->assertEquals($this->letter, $letterSent, "Wrong letter was sent");
        $this->assertEquals(LetterEntity::STATUS_SENT, $this->letter->getStatus(), "Letter was not marked sent");
        $this->assertEquals(CampaignEntity::STATUS_FINISHED, $this->campaign->getStatus(), "Campaign was not finished");
    }

    public function testSendChecksClientBounced()
    {
        $this->client->setBounced(true);

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
                        $letter->setStatus(LetterEntity::STATUS_SENT);
                        $letter->setWhenProcessed(new \DateTime());
                        $letterSent = $letter;
                        return true;
                   }));

        $task = new SendEmailTask();
        $task->setServiceLocator($this->sl);
        $task->run($exit);

        $this->assertEquals(null, $letterSent, "Letter should not get send");
        $this->assertEquals(LetterEntity::STATUS_SKIPPED, $this->letter->getStatus(), "Letter was not marked skipped");
        $this->assertEquals(CampaignEntity::STATUS_FINISHED, $this->campaign->getStatus(), "Campaign was not finished");
    }

    public function testSendChecksClientUnsubscribed()
    {
        $this->doc->setUnsubscribed(true);

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
                        $letter->setStatus(LetterEntity::STATUS_SENT);
                        $letter->setWhenProcessed(new \DateTime());
                        $letterSent = $letter;
                        return true;
                   }));

        $task = new SendEmailTask();
        $task->setServiceLocator($this->sl);
        $task->run($exit);

        $this->assertEquals(null, $letterSent, "Letter should not get send");
        $this->assertEquals(LetterEntity::STATUS_SKIPPED, $this->letter->getStatus(), "Letter was not marked skipped");
        $this->assertEquals(CampaignEntity::STATUS_FINISHED, $this->campaign->getStatus(), "Campaign was not finished");
    }

    public function testSendChecksClientIgnoredTags()
    {
        $tag1 = new TagEntity();
        $this->setProp($tag1, 'id', 123);

        $this->campaign->addTag($tag1);
        $tag1->addCampaign($this->campaign);

        $tag2 = new TagEntity();
        $this->setProp($tag2, 'id', 456);

        $this->campaign->addTag($tag2);
        $tag2->addCampaign($this->campaign);

        $this->doc->setIgnoredTags([ $tag1->getId(), $tag2->getId() ]);

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
                        $letter->setStatus(LetterEntity::STATUS_SENT);
                        $letter->setWhenProcessed(new \DateTime());
                        $letterSent = $letter;
                        return true;
                   }));

        $task = new SendEmailTask();
        $task->setServiceLocator($this->sl);
        $task->run($exit);

        $this->assertEquals(null, $letterSent, "Letter should not get send");
        $this->assertEquals(LetterEntity::STATUS_SKIPPED, $this->letter->getStatus(), "Letter was not marked skipped");
        $this->assertEquals(CampaignEntity::STATUS_FINISHED, $this->campaign->getStatus(), "Campaign was not finished");
    }

    protected function setProp($object, $property, $value)
    {
        $class = new \ReflectionClass(get_class($object));

        $property = $class->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
