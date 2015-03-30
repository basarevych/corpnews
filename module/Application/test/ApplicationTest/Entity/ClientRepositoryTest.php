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

    public function setUpLetters()
    {
        $this->campaign = new CampaignEntity();
        $this->campaign->setName('campaign');
        $this->campaign->setStatus(CampaignEntity::STATUS_STARTED);

        $this->template = new TemplateEntity();
        $this->template->setMessageId('mid');
        $this->template->setSubject('subject');
        $this->template->setHeaders('headers');
        $this->template->setBody('body');

        $this->campaign->addTemplate($this->template);
        $this->template->setCampaign($this->campaign);

        $this->clientA = new ClientEntity();
        $this->clientA->setEmail('foo');

        $this->clientB = new ClientEntity();
        $this->clientB->setEmail('bar');

        $this->clientC = new ClientEntity();
        $this->clientC->setEmail('new');

        $this->group = new GroupEntity();
        $this->group->setName('group');

        $this->group->addClient($this->clientA);
        $this->clientA->addGroup($this->group);
        $this->group->addClient($this->clientB);
        $this->clientB->addGroup($this->group);
        $this->group->addClient($this->clientC);
        $this->clientC->addGroup($this->group);

        $this->group->addCampaign($this->campaign);
        $this->campaign->addGroup($this->group);

        $this->a1 = new LetterEntity();
        $this->a1->setWhenCreated(new \DateTime());
        $this->a1->setFromAddress('foo');
        $this->a1->setToAddress('bar');
        $this->a1->setSubject('subject');
        $this->a1->setHeaders('headers');
        $this->a1->setBody('body');

        $this->a1->setTemplate($this->template);
        $this->template->addLetter($this->a1);
        $this->a1->setClient($this->clientA);
        $this->clientA->addLetter($this->a1);

        $this->a2 = new LetterEntity();
        $this->a2->setWhenCreated(new \DateTime());
        $this->a2->setWhenSent(new \DateTime());
        $this->a2->setError('error');
        $this->a2->setFromAddress('foo');
        $this->a2->setToAddress('bar');
        $this->a2->setSubject('subject');
        $this->a2->setHeaders('headers');
        $this->a2->setBody('body');

        $this->a2->setTemplate($this->template);
        $this->template->addLetter($this->a2);
        $this->a2->setClient($this->clientA);
        $this->clientA->addLetter($this->a2);

        $this->b1 = new LetterEntity();
        $this->b1->setWhenCreated(new \DateTime());
        $this->b1->setWhenSent(new \DateTime());
        $this->b1->setError('error');
        $this->b1->setFromAddress('foo');
        $this->b1->setToAddress('bar');
        $this->b1->setSubject('subject');
        $this->b1->setHeaders('headers');
        $this->b1->setBody('body');

        $this->b1->setTemplate($this->template);
        $this->template->addLetter($this->b1);
        $this->b1->setClient($this->clientB);
        $this->clientB->addLetter($this->b1);

        $this->b2 = new LetterEntity();
        $this->b2->setWhenCreated(new \DateTime());
        $this->b2->setFromAddress('foo');
        $this->b2->setToAddress('bar');
        $this->b2->setSubject('subject');
        $this->b2->setHeaders('headers');
        $this->b2->setBody('body');

        $this->b2->setTemplate($this->template);
        $this->template->addLetter($this->b2);
        $this->b2->setClient($this->clientB);
        $this->clientB->addLetter($this->b2);

        $this->infrastructure->import([
            $this->campaign, $this->group, $this->template,
            $this->clientA, $this->clientB, $this->clientC,
            $this->a1, $this->a2, $this->b1, $this->b2
        ]);
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
        $this->setUpLetters();

        $result = $this->repo->findWithoutLetters($this->template);
        $this->assertEquals(1, count($result), "Only one item should be found");
        $this->assertEquals($this->clientC->getId(), $result[0]->getId(), "Wrong entity found");
    }

    public function testFindWithFailedLetters()
    {
        $this->setUpLetters();

        $result = $this->repo->findWithFailedLetters($this->template);
        $this->assertEquals(1, count($result), "Only one item should be found");
        $this->assertEquals($this->clientA->getId(), $result[0]->getId(), "Wrong entity found");
    }

    public function testCountWithExistingLetters()
    {
        $this->setUpLetters();

        $count = $this->repo->countWithExistingLetters($this->template);

        $this->assertEquals(2, $count);
    }

    public function testCountWithPendingLetters()
    {
        $this->setUpLetters();

        $count = $this->repo->countWithPendingLetters($this->template);

        $this->assertEquals(1, $count);
    }

    public function testFindWithPendingLetters()
    {
        $this->setUpLetters();

        $result = $this->repo->findWithPendingLetters($this->template);

        $this->assertEquals(1, count($result), "Only one entity should be found");
        $this->assertEquals($this->clientB->getId(), $result[0]->getId(), "Client B should be returned");
    }

    public function testRemoveAll()
    {
        $this->setUpLetters();

        $data = $this->repo->findAll();
        $this->assertEquals(3, count($data), "One item should have been returned");

        $this->repo->removeAll();
        $data = $this->repo->findAll();
        $this->assertEquals(0, count($data), "No items should have been returned");
    }
}
