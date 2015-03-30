<?php

namespace ApplicationTest\Entity;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Application\Entity\Letter as LetterEntity;

class LetterRepositoryTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->infrastructure = new ORMInfrastructure([
            '\Application\Entity\Letter',
        ]);

        $this->em = $this->infrastructure->getEntityManager();
        $this->repo = $this->em->getRepository('Application\Entity\Letter');

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
    }

    public function testRemoveAll()
    {
        $letter = new LetterEntity();
        $letter->setWhenCreated(new \DateTime());
        $letter->setFromAddress('foo');
        $letter->setToAddress('bar');
        $letter->setSubject('subject');
        $letter->setHeaders('headers');
        $letter->setBody('body');

        $this->infrastructure->import([ $letter ]);

        $data = $this->repo->findAll();
        $this->assertEquals(1, count($data), "One item should have been returned");

        $this->repo->removeAll();
        $data = $this->repo->findAll();
        $this->assertEquals(0, count($data), "No items should have been returned");
    }
}
