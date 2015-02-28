<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Filter;

/**
 * Login form
 *
 * @category    Admin
 * @package     Form
 */
class LoginForm extends Form
{
    /**
     * The input filter
     *
     * @var InputFilter
     */
    protected $inputFilter = null;

    /**
     * Constructor
     *
     * @param  null|int|string  $name    Optional name
     * @param  array            $options Optional options
     */
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name ? $name : 'login', $options);
        $this->setAttribute('method', 'post');

        $csrf = new Element\Csrf('security');
        $this->add($csrf);

        $login = new Element\Text('login');
        $login->setLabel('Login');
        $this->add($login);

        $password = new Element\Password('password');
        $password->setLabel('Password');
        $this->add($password);

        $submit = new Element\Submit('submit');
        $submit->setLabel('Sign in');
        $this->add($submit);
    }

    /**
     * Retrieve input filter used by this form
     *
     * @return null|InputFilterInterface
     */
    public function getInputFilter()
    {
        if ($this->inputFilter)
            return $this->inputFilter;

        $filter = new InputFilter();

        $csrf = new Input('security');
        $csrf->setRequired(true);
        $filter->add($csrf);

        $login = new Input('login');
        $login->setRequired(true)
              ->getFilterChain()
              ->attach(new Filter\StringTrim());
        $filter->add($login);

        $password = new Input('password');
        $password->setRequired(true)
                 ->getFilterChain()
                 ->attach(new Filter\StringTrim());
        $filter->add($password);

        $this->inputFilter = $filter;
        return $filter;
    }
}
