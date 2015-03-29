<?php

namespace ApplicationTest\Entity;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Application\Entity\Client as ClientEntity;
use Application\Entity\Group as GroupEntity;
use Application\Entity\Campaign as CampaignEntity;
use Application\Entity\Template as TemplateEntity;
use Application\Entity\Letter as LetterEntity;

class ClientRepositoryTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->infrastructure = new ORMInfrastructure([
            '\Application\Entity\Client',
            '\Application\Entity\Group',
            '\Application\Entity\Campaign',
            '\Application\Entity\Template',
            '\Application\Entity\Letter',
        ]);

        $this->em = $this->infrastructure->getEntityManager();
        $this->repo = $this->em->getRepository('Application\Entity\Client');

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
    }

    public function testFindByGroupName()
    {
        $group = new GroupEntity();
        $group->setName('the group');

        $a = new ClientEntity();
        $a->setEmail('foobar');

        $a->addGroup($group);
        $group->addClient($a);

        $b = new ClientEntity();
        $b->setEmail('wrong');

        $this->infrastructure->import([ $group, $a, $b ]);

        $result = $this->repo->findByGroupName('the group');
        $this->assertEquals(1, count($result), "Only one item should be found");
        $this->assertEquals($a->getId(), $result[0]->getId(), "Wrong entity found");
    }

    public function testFindWithoutLetters()
    {
        $campaign = new CampaignEntity();
        $campaign->setName('campaign');
        $campaign->setStatus(CampaignEntity::STATUS_CREATED);

        $group = new GroupEntity();
        $group->setName('group');

        $campaign->addGroup($group);
        $group->addCampaign($campaign);

        $template = new TemplateEntity();
        $template->setMessageId('mid');
        $template->setSubject('subject');
        $template->setHeaders('headers');
        $template->setBody('body');

        $campaign->addTemplate($template);
        $template->setCampaign($campaign);

        $correctClient = new ClientEntity();
        $correctClient->setEmail('correct');

        $correctClient->addGroup($group);
        $group->addClient($correctClient);

        $wrongClient = new ClientEntity();
        $wrongClient->setEmail('wrong');

        $wrongClient->addGroup($group);
        $group->addClient($wrongClient);

        $letter = new LetterEntity();
        $letter->setWhenCreated(new \DateTime());
        $letter->setFromAddress('foo');
        $letter->setToAddress('bar');
        $letter->setSubject('subject');
        $letter->setHeaders('headers');
        $letter->setBody('body');

        $letter->setTemplate($template);
        $template->addLetter($letter);
        $letter->setClient($wrongClient);
        $wrongClient->addLetter($letter);

        $this->infrastructure->import([ $campaign, $group, $template, $correctClient, $wrongClient, $letter ]);

        $result = $this->repo->findWithoutLetters($template);
        $this->assertEquals(1, count($result), "Only one item should be found");
        $this->assertEquals($correctClient->getId(), $result[0]->getId(), "Wrong entity found");
    }

    public function testFindWithFailedLetters()
    {
        $campaign = new CampaignEntity();
        $campaign->setName('campaign');
        $campaign->setStatus(CampaignEntity::STATUS_CREATED);

        $group = new GroupEntity();
        $group->setName('group');

        $campaign->addGroup($group);
        $group->addCampaign($campaign);

        $template = new TemplateEntity();
        $template->setMessageId('mid');
        $template->setSubject('subject');
        $template->setHeaders('headers');
        $template->setBody('body');

        $campaign->addTemplate($template);
        $template->setCampaign($campaign);

        $correctClient = new ClientEntity();
        $correctClient->setEmail('correct');

        $correctClient->addGroup($group);
        $group->addClient($correctClient);

        $wrongClient1 = new ClientEntity();
        $wrongClient1->setEmail('wrong 1');

        $wrongClient1->addGroup($group);
        $group->addClient($wrongClient1);

        $wrongClient2 = new ClientEntity();
        $wrongClient2->setEmail('wrong 2');

        $wrongClient2->addGroup($group);
        $group->addClient($wrongClient2);

        $letterWrong1 = new LetterEntity();
        $letterWrong1->setWhenCreated(new \DateTime('@100'));
        $letterWrong1->setError('error');
        $letterWrong1->setFromAddress('foo');
        $letterWrong1->setToAddress('bar');
        $letterWrong1->setSubject('subject');
        $letterWrong1->setHeaders('headers');
        $letterWrong1->setBody('body');

        $letterWrong1->setTemplate($template);
        $template->addLetter($letterWrong1);
        $letterWrong1->setClient($wrongClient1);
        $wrongClient1->addLetter($letterWrong1);

        $letterWrong2 = new LetterEntity();
        $letterWrong2->setWhenCreated(new \DateTime('@200'));
        $letterWrong2->setFromAddress('foo');
        $letterWrong2->setToAddress('bar');
        $letterWrong2->setSubject('subject');
        $letterWrong2->setHeaders('headers');
        $letterWrong2->setBody('body');

        $letterWrong2->setTemplate($template);
        $template->addLetter($letterWrong2);
        $letterWrong2->setClient($wrongClient1);
        $wrongClient1->addLetter($letterWrong2);

        $letterCorrect1 = new LetterEntity();
        $letterCorrect1->setWhenCreated(new \DateTime('@100'));
        $letterCorrect1->setFromAddress('foo');
        $letterCorrect1->setToAddress('bar');
        $letterCorrect1->setSubject('subject');
        $letterCorrect1->setHeaders('headers');
        $letterCorrect1->setBody('body');

        $letterCorrect1->setTemplate($template);
        $template->addLetter($letterCorrect1);
        $letterCorrect1->setClient($correctClient);
        $correctClient->addLetter($letterCorrect1);

        $letterCorrect2 = new LetterEntity();
        $letterCorrect2->setWhenCreated(new \DateTime('@200'));
        $letterCorrect2->setError('error');
        $letterCorrect2->setFromAddress('foo');
        $letterCorrect2->setToAddress('bar');
        $letterCorrect2->setSubject('subject');
        $letterCorrect2->setHeaders('headers');
        $letterCorrect2->setBody('body');

        $letterCorrect2->setTemplate($template);
        $template->addLetter($letterCorrect2);
        $letterCorrect2->setClient($correctClient);
        $correctClient->addLetter($letterCorrect2);

        $this->infrastructure->import([
            $campaign, $group, $template, $correctClient, $wrongClient1, $wrongClient2,
            $letterWrong1, $letterWrong2, $letterCorrect1, $letterCorrect2
        ]);

        $result = $this->repo->findWithFailedLetters($template);
        $this->assertEquals(1, count($result), "Only one item should be found");
        $this->assertEquals($correctClient->getId(), $result[0]->getId(), "Wrong entity found");
    }

    public function testRemoveAll()
    {
        $client = new ClientEntity();
        $client->setEmail('foobar');

        $this->infrastructure->import([ $client ]);
        $data = $this->repo->findAll();
        $this->assertEquals(1, count($data), "One item should have been returned");

        $this->repo->removeAll();
        $data = $this->repo->findAll();
        $this->assertEquals(0, count($data), "No items should have been returned");
    }
}
