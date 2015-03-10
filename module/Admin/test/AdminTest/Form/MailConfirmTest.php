<?php

namespace AdminTest\Form;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Admin\Form\MailConfirm as MailConfirmForm;

class MailConfirmTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();
    }

    public function testInvalidForm()
    {
        $form = new MailConfirmForm();

        $input = [
        ];

        $form->setData($input);
        $valid = $form->isValid();

        $this->assertEquals(false, $valid, "Form should not be reported as valid");
        $this->assertGreaterThan(0, count($form->get('security')->getMessages()), "Security should have errors");
        $this->assertGreaterThan(0, count($form->get('box')->getMessages()), "Box should have errors");
        $this->assertGreaterThan(0, count($form->get('uid')->getMessages()), "UID should have errors");
    }

    public function testValidForm()
    {
        $form = new MailConfirmForm();

        $input = [
            'security' => $form->get('security')->getValue(),
            'box' => 'foobar',
            'uid' => 42,
        ];

        $form->setData($input);
        $valid = $form->isValid();
        $output = $form->getData();

        $this->assertEquals(true, $valid, "Form should be reported as valid");
    }
}
