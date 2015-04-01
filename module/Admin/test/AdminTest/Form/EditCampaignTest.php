<?php

namespace AdminTest\Form;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Admin\Form\EditCampaign as EditCampaignForm;
use Application\Entity\Campaign as CampaignEntity;
use Application\Entity\Tag as TagEntity;
use Application\Entity\Group as GroupEntity;

class EditCampaignTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
                         ->disableOriginalConstructor()
                         ->setMethods([ 'getRepository', 'createQueryBuilder' ])
                         ->getMock();

        $this->repoGroups = $this->getMockBuilder('Application\Entity\GroupRepository')
                                 ->disableOriginalConstructor()
                                 ->setMethods([ 'findBy' ])
                                 ->getMock();

        $this->group = new GroupEntity();
        $this->group->setName('group');

        $reflection = new \ReflectionClass(get_class($this->group));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->group, 9000);

        $this->repoGroups->expects($this->any())
                         ->method('findBy')
                         ->will($this->returnValue([ $this->group ]));

        $this->repoCampaigns = $this->getMockBuilder('Application\Entity\CampaignRepository')
                                    ->disableOriginalConstructor()
                                    ->setMethods([ 'find' ])
                                    ->getMock();

        $this->campaign = new CampaignEntity();
        $this->campaign->setName('campaign');
        $this->campaign->setStatus(CampaignEntity::STATUS_CREATED);

        $this->repoCampaigns->expects($this->any())
                            ->method('find')
                            ->will($this->returnValue($this->campaign));

        $this->repoTags = $this->getMockBuilder('Application\Entity\TagRepository')
                               ->disableOriginalConstructor()
                               ->setMethods([ 'findBy' ])
                               ->getMock();

        $this->tag = new TagEntity();
        $this->tag->setName('tag');

        $reflection = new \ReflectionClass(get_class($this->tag));
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->tag, 123);

        $this->repoTags->expects($this->any())
                       ->method('findBy')
                       ->will($this->returnValue([ $this->tag ]));

        $this->em->expects($this->any())
                 ->method('getRepository')
                 ->will($this->returnValueMap([
                    [ 'Application\Entity\Group', $this->repoGroups ],
                    [ 'Application\Entity\Campaign', $this->repoCampaigns ],
                    [ 'Application\Entity\Tag', $this->repoTags ],
                 ]));

        $this->sl = $this->getApplicationServiceLocator();
        $this->sl->setAllowOverride(true);
        $this->sl->setService('Doctrine\ORM\EntityManager', $this->em);
    }

    public function testInvalidForm()
    {
        $form = new EditCampaignForm($this->sl, 42);

        $input = [
        ];

        $form->setData($input);
        $valid = $form->isValid();

        $this->assertEquals(false, $valid, "Form should not be reported as valid");
        $this->assertGreaterThan(0, count($form->get('security')->getMessages()), "Security should have errors");
        $this->assertGreaterThan(0, count($form->get('id')->getMessages()), "ID should have errors");
        $this->assertGreaterThan(0, count($form->get('name')->getMessages()), "Name should have errors");
        $this->assertGreaterThan(0, count($form->get('groups')->getMessages()), "Groups should have errors");
    }

    public function testValidForm()
    {
        $form = new EditCampaignForm($this->sl, 42);
        $dt = new \DateTime();
        $format = $form->get('when_deadline')->getFormat();

        $input = [
            'security' => $form->get('security')->getValue(),
            'id' => 42,
            'name' => ' example ',
            'when_deadline' => " " . $dt->format($format),
            'groups' => [ 9000 ],
            'tags' => [ 123 ],
        ];

        $form->setData($input);
        $valid = $form->isValid();
        $output = $form->getData();

        $this->assertEquals(true, $valid, "Form should be reported as valid");
        $this->assertEquals(42, $output['id'], "Incorrect ID");
        $this->assertEquals('example', $output['name'], "Name should be trimmed");
        $this->assertEquals($dt->format($format), $output['when_deadline'], "Deadline should be trimmed and converted");
        $this->assertEquals([ 9000 ], $output['groups'], "Wrong groups");
        $this->assertEquals([ 123 ], $output['tags'], "Wrong tags");
    }
}
