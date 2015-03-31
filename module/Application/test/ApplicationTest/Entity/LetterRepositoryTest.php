<?php

namespace ApplicationTest\Entity;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Webfactory\Doctrine\ORMTestInfrastructure\ORMInfrastructure;
use Application\Entity\Template as TemplateEntity;
use Application\Entity\Letter as LetterEntity;

class LetterRepositoryTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->infrastructure = new ORMInfrastructure([
            '\Application\Entity\Template',
            '\Application\Entity\Letter',
        ]);

        $this->em = $this->infrastructure->getEntityManager();
        $this->repo = $this->em->getRepository('Application\Entity\Letter');

        $sl = $this->getApplicationServiceLocator();
        $sl->setAllowOverride(true);
        $sl->setService('Doctrine\ORM\EntityManager', $this->em);
    }

    public function testFindPending()
    {
        $template = new TemplateEntity();
        $template->setMessageId('mid');
        $template->setSubject('subject');
        $template->setHeaders('headers');
        $template->setBody('body');

        $letter1 = new LetterEntity();
        $letter1->setStatus(LetterEntity::STATUS_SENT);
        $letter1->setWhenCreated(new \DateTime());
        $letter1->setWhenProcessed(new \DateTime());
        $letter1->setFromAddress('foo');
        $letter1->setToAddress('bar');
        $letter1->setSubject('subject');
        $letter1->setHeaders('headers');
        $letter1->setBody('body');

        $letter1->setTemplate($template);
        $template->addLetter($letter1);

        $letter2 = new LetterEntity();
        $letter2->setWhenCreated(new \DateTime());
        $letter2->setFromAddress('foo');
        $letter2->setToAddress('bar');
        $letter2->setSubject('subject');
        $letter2->setHeaders('headers');
        $letter2->setBody('body');

        $letter2->setTemplate($template);
        $template->addLetter($letter2);

        $this->infrastructure->import([ $template, $letter1, $letter2 ]);

        $data = $this->repo->findPending($template);
        $this->assertEquals(1, count($data), "One item should have been returned");
        $this->assertEquals($letter2->getId(), $data[0]->getId(), "Wrong entity found");
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
