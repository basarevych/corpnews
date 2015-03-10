<?php

namespace AdminTest\Form;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Admin\Form\MailboxSettings as MailboxSettingsForm;

class MailboxSettingsTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();
    }

    public function testInvalidForm()
    {
        $form = new MailboxSettingsForm();

        $input = [
        ];

        $form->setData($input);
        $valid = $form->isValid();

        $this->assertEquals(false, $valid, "Form should not be reported as valid");
        $this->assertGreaterThan(0, count($form->get('security')->getMessages()), "Security should have errors");
        $this->assertGreaterThan(0, count($form->get('autodelete')->getMessages()), "Autodelete should have errors");
    }

    public function testValidForm()
    {
        $form = new MailboxSettingsForm();

        $input = [
            'security' => $form->get('security')->getValue(),
            'autodelete' => ' 9000 '
        ];

        $form->setData($input);
        $valid = $form->isValid();
        $output = $form->getData();

        $this->assertEquals(true, $valid, "Form should be reported as valid");
        $this->assertEquals('9000', $output['autodelete'], "Autodelete should be trimmed");
    }
}
