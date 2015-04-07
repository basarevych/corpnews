<?php

namespace AdminTest\Form;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Admin\Form\Import as ImportForm;

class ImportTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();
    }

    public function testInvalidForm()
    {
        $form = new ImportForm();

        $input = [
        ];

        $form->setData($input);
        $valid = $form->isValid();

        $this->assertEquals(false, $valid, "Form should not be reported as valid");
        $this->assertGreaterThan(0, count($form->get('security')->getMessages()), "Security should have errors");
        $this->assertGreaterThan(0, count($form->get('fields')->getMessages()), "Fields should have errors");
        $this->assertGreaterThan(0, count($form->get('encoding')->getMessages()), "Encoding should have errors");
        $this->assertGreaterThan(0, count($form->get('file')->getMessages()), "File should have errors");
    }

    public function testValidForm()
    {
        $form = new ImportForm();

        $input = [
            'security' => $form->get('security')->getValue(),
            'groups' => '1,2',
            'fields' => 'foo,bar',
            'encoding' => 'utf-8',
        ];

        $form->setData($input);
        $valid = $form->isValid();
        $output = $form->getData();

        $this->assertEquals(0, count($form->get('security')->getMessages()), "Security should have no errors");
        $this->assertEquals(0, count($form->get('fields')->getMessages()), "Fields should have no errors");
        $this->assertEquals(0, count($form->get('encoding')->getMessages()), "Encoding should have no errors");
    }
}
