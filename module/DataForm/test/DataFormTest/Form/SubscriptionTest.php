<?php

namespace DataFormTest\Form;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Application\Entity\Tag as TagEntity;
use DataForm\Form\Subscription as SubscriptionForm;

class SubscriptionTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getRepository' ])
                         ->getMock();

        $this->repoTags = $this->getMockBuilder('Application\Entity\TagRepository')
                               ->disableOriginalConstructor()
                               ->setMethods([ 'findBy' ])
                               ->getMock();

        $this->tag = new TagEntity();
        $this->tag->setName('tag1');
        $this->tag->setDescr('Tag description');

        $reflection = new \ReflectionClass(get_class($this->tag));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->tag, 42);

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Tag', $this->repoTags ],
                 ]));

        $this->sl = $this->getApplicationServiceLocator();
        $this->sl->setAllowOverride(true);
        $this->sl->setService('Doctrine\ORM\EntityManager', $this->em);
    }

    public function testInvalidForm()
    {
        $this->repoTags->expects($this->any())
                       ->method('findBy')
                       ->will($this->returnValue([ $this->tag ]));

        $form = new SubscriptionForm($this->sl);

        $input = [
        ];

        $form->setData($input);
        $valid = $form->isValid();

        $this->assertEquals(false, $valid, "Form should not be reported as valid");
        $this->assertGreaterThan(0, count($form->get('security')->getMessages()), "Security should have errors");
        $this->assertGreaterThan(0, count($form->get('list')->getMessages()), "List should have errors");
    }

    public function testValidFormNoTags()
    {
        $this->repoTags->expects($this->any())
                       ->method('findBy')
                       ->will($this->returnValue([]));

        $form = new SubscriptionForm($this->sl);

        $input = [
            'security' => $form->get('security')->getValue(),
            'subscribe' => [ 'all' ],
        ];

        $form->setData($input);
        $valid = $form->isValid();
        $output = $form->getData();

        $this->assertEquals(true, $valid, "Form should be reported as valid");
        $this->assertEquals([ 'all' ], $output['subscribe'], "Subscribe is wrong");
    }

    public function testValidFormWithTags()
    {
        $this->repoTags->expects($this->any())
                       ->method('findBy')
                       ->will($this->returnValue([ $this->tag ]));

        $form = new SubscriptionForm($this->sl);

        $input = [
            'security' => $form->get('security')->getValue(),
            'subscribe' => [ 'all' ],
            'list' => '42',
            'tags' => [ 42 ],
        ];

        $form->setData($input);
        $valid = $form->isValid();
        $output = $form->getData();

        $this->assertEquals(true, $valid, "Form should be reported as valid");
        $this->assertEquals([ 'all' ], $output['subscribe'], "Subscribe is wrong");
        $this->assertEquals('42', $output['list'], "List is wrong");
        $this->assertEquals([ 42 ], $output['tags'], "Tags are wrong");
    }
}
