<?php

namespace ApplicationTest\Entity;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Application\Entity\Secret as SecretEntity;
use Application\Entity\Campaign as CampaignEntity;

class SecretRepositoryTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->infrastructure = new ORMInfrastructure([
            '\Application\Entity\Secret',
            '\Application\Entity\Campaign',
        ]);

        $this->em = $this->infrastructure->getEntityManager();
        $this->repo = $this->em->getRepository('Application\Entity\Secret');

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
    }

    public function testGetCampaignForms()
    {
        $campaign = new CampaignEntity();
        $campaign->setName('foobar');
        $campaign->setStatus(CampaignEntity::STATUS_CREATED);

        $a = new SecretEntity();
        $a->setWhenOpened(new \DateTime());
        $a->setSecretKey('foo');
        $a->setDataForm('bar');

        $a->setCampaign($campaign);
        $campaign->addSecret($a);

        $b = new SecretEntity();
        $b->setWhenOpened(new \DateTime());
        $b->setWhenSaved(new \DateTime());
        $b->setSecretKey('foo');
        $b->setDataForm('bar');

        $b->setCampaign($campaign);
        $campaign->addSecret($b);

        $c = new SecretEntity();
        $c->setWhenSaved(new \DateTime());
        $c->setSecretKey('foo');
        $c->setDataForm('baz');

        $c->setCampaign($campaign);
        $campaign->addSecret($c);

        $this->infrastructure->import([ $campaign, $a, $b, $c ]);

        $result = $this->repo->getCampaignForms($campaign);
        $this->assertEquals(2, count($result), "Two rows should have been returned");
        $this->assertEquals('bar', $result[0], "First data form name is wrong");
        $this->assertEquals('baz', $result[1], "Second data form name is wrong");
    }

    public function testCountOpened()
    {
        $campaign = new CampaignEntity();
        $campaign->setName('foobar');
        $campaign->setStatus(CampaignEntity::STATUS_CREATED);

        $a = new SecretEntity();
        $a->setWhenOpened(new \DateTime());
        $a->setSecretKey('foo');
        $a->setDataForm('bar');

        $a->setCampaign($campaign);
        $campaign->addSecret($a);

        $b = new SecretEntity();
        $b->setWhenOpened(new \DateTime());
        $b->setWhenSaved(new \DateTime());
        $b->setSecretKey('foo');
        $b->setDataForm('bar');

        $b->setCampaign($campaign);
        $campaign->addSecret($b);

        $c = new SecretEntity();
        $c->setWhenSaved(new \DateTime());
        $c->setSecretKey('foo');
        $c->setDataForm('bar');

        $c->setCampaign($campaign);
        $campaign->addSecret($c);

        $this->infrastructure->import([ $campaign, $a, $b, $c ]);

        $count = $this->repo->countOpened($campaign, 'bar');
        $this->assertEquals(2, $count);
    }

    public function testCountSaved()
    {
        $campaign = new CampaignEntity();
        $campaign->setName('foobar');
        $campaign->setStatus(CampaignEntity::STATUS_CREATED);

        $a = new SecretEntity();
        $a->setWhenOpened(new \DateTime());
        $a->setSecretKey('foo');
        $a->setDataForm('bar');

        $a->setCampaign($campaign);
        $campaign->addSecret($a);

        $b = new SecretEntity();
        $b->setWhenOpened(new \DateTime());
        $b->setWhenSaved(new \DateTime());
        $b->setSecretKey('foo');
        $b->setDataForm('bar');

        $b->setCampaign($campaign);
        $campaign->addSecret($b);

        $c = new SecretEntity();
        $c->setWhenSaved(new \DateTime());
        $c->setSecretKey('foo');
        $c->setDataForm('bar');

        $c->setCampaign($campaign);
        $campaign->addSecret($c);

        $this->infrastructure->import([ $campaign, $a, $b, $c ]);

        $count = $this->repo->countSaved($campaign, 'bar');
        $this->assertEquals(2, $count);
    }

    public function testRemoveAll()
    {
        $secret = new SecretEntity();
        $secret->setSecretKey('foo');
        $secret->setDataForm('bar');

        $this->infrastructure->import([ $secret ]);

        $data = $this->repo->findAll();
        $this->assertEquals(1, count($data), "One item should have been returned");

        $this->repo->removeAll();
        $data = $this->repo->findAll();
        $this->assertEquals(0, count($data), "No items should have been returned");
    }
}
