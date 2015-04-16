<?php

namespace AdminTest\Form;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Admin\Form\Export as ExportForm;

class ExportTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();

        $this->sl = $this->getApplicationServiceLocator();
    }

    public function testInvalidForm()
    {
        $form = new ExportForm($this->sl);

        $input = [
        ];

        $form->setData($input);
        $valid = $form->isValid();

        $this->assertEquals(false, $valid, "Form should not be reported as valid");
        $this->assertGreaterThan(0, count($form->get('security')->getMessages()), "Security should have errors");
        $this->assertGreaterThan(0, count($form->get('fields')->getMessages()), "Fields should have errors");
        $this->assertGreaterThan(0, count($form->get('separator')->getMessages()), "Separator should have errors");
        $this->assertGreaterThan(0, count($form->get('ending')->getMessages()), "Ending should have errors");
        $this->assertGreaterThan(0, count($form->get('encoding')->getMessages()), "Encoding should have errors");
        $this->assertGreaterThan(0, count($form->get('groups')->getMessages()), "Group should have errors");
    }

    public function testValidForm()
    {
        $form = new ExportForm($this->sl);

        $input = [
            'security' => $form->get('security')->getValue(),
            'fields' => 'foo,bar',
            'separator' => 'comma',
            'ending' => 'windows',
            'encoding' => 'utf-8',
            'groups' => '1,2',
        ];

        $form->setData($input);
        $valid = $form->isValid();
        $output = $form->getData();

        $this->assertEquals(0, count($form->get('security')->getMessages()), "Security should have no errors");
        $this->assertEquals(0, count($form->get('fields')->getMessages()), "Fields should have no errors");
        $this->assertEquals(0, count($form->get('separator')->getMessages()), "Separator should have no errors");
        $this->assertEquals(0, count($form->get('ending')->getMessages()), "Ending should have no errors");
        $this->assertEquals(0, count($form->get('encoding')->getMessages()), "Encoding should have no errors");
        $this->assertEquals(0, count($form->get('groups')->getMessages()), "Group should have no errors");
    }
}
