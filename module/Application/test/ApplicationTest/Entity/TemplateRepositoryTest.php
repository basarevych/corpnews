<?php

namespace ApplicationTest\Entity;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Application\Entity\Template as TemplateEntity;

class TemplateRepositoryTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->infrastructure = new ORMInfrastructure([
            '\Application\Entity\Template',
        ]);

        $this->em = $this->infrastructure->getEntityManager();
        $this->repo = $this->em->getRepository('Application\Entity\Template');

        $template = new TemplateEntity();
        $template->setMessageId('mid');
        $template->setSubject('subject');
        $template->setHeaders('headers');
        $template->setBody('body');

        $this->infrastructure->import([ $template ]);

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
