<?php

namespace AdminTest\Form;

use Zend\Test\PHPUnit\Controller\AbstractControllerTestCase;
use Admin\Form\LoginForm;

class LoginFormTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(require 'config/application.config.php');

        parent::setUp();
    }

    public function testLoginFormValidation()
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
