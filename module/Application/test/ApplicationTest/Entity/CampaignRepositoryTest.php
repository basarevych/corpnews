<?php

namespace ApplicationTest\Entity;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Application\Entity\Campaign as CampaignEntity;

class CampaignRepositoryTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->infrastructure = new ORMInfrastructure([
            '\Application\Entity\Campaign',
        ]);

        $this->em = $this->infrastructure->getEntityManager();
        $this->repo = $this->em->getRepository('Application\Entity\Campaign');

        $campaign = new CampaignEntity();
        $campaign->setName('foobar');
        $campaign->setStatus(CampaignEntity::STATUS_CREATED);

        $this->infrastructure->import([ $campaign ]);

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
    }

    public function testGetStatusCountWorks()
    {
        $count = $this->repo->getStatusCount(CampaignEntity::STATUS_CREATED);
        $this->assertEquals(1, $count);
    }

    public function testRemoveAll()
    {
        $data = $this->repo->findAll();
        $this->assertEquals(1, count($data), "One item should have been returned");

        $this->repo->removeAll();
        $data = $this->repo->findAll();
        $this->assertEquals(0, count($data), "No items should have been returned");
    }
}
