<?php

namespace ApplicationTest\Entity;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Application\Entity\Secret as SecretEntity;

class SecretRepositoryTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->infrastructure = new ORMInfrastructure([
            '\Application\Entity\Secret',
        ]);

        $this->em = $this->infrastructure->getEntityManager();
        $this->repo = $this->em->getRepository('Application\Entity\Secret');

        $secret = new SecretEntity();
        $secret->setSecretKey('foo');
        $secret->setDataForm('bar');

        $this->infrastructure->import([ $secret ]);

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
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
