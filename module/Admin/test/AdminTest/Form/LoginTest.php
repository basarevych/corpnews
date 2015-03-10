<?php

namespace AdminTest\Form;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Admin\Form\Login as LoginForm;

class LoginTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();
    }

    public function testInvalidLoginForm()
    {
        $form = new LoginForm();

        $input = [
        ];

        $form->setData($input);
        $valid = $form->isValid();

        $this->assertEquals(false, $valid, "Form should not be reported as valid");
        $this->assertGreaterThan(0, count($form->get('security')->getMessages()), "Security should have errors");
        $this->assertGreaterThan(0, count($form->get('login')->getMessages()), "Login should have errors");
        $this->assertGreaterThan(0, count($form->get('password')->getMessages()), "Password should have errors");
    }

    public function testValidLoginForm()
    {
        $form = new LoginForm();

        $input = [
            'security' => $form->get('security')->getValue(),
            'login' => ' login ',
            'password' => ' password '
        ];

        $form->setData($input);
        $valid = $form->isValid();
        $output = $form->getData();

        $this->assertEquals(true, $valid, "Form should be reported as valid");
        $this->assertEquals('login', $output['login'], "Login should be trimmed");
        $this->assertEquals('password', $output['password'], "Password should be trimmed");
    }
}
