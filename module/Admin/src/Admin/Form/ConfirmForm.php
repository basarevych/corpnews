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
use Zend\Validator;

/**
 * Confirmation dialog form
 *
 * @category    Admin
 * @package     Form
 */
class ConfirmForm extends Form
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
     * @param null|int|string  $name        Optional name
     * @param array            $options     Optional options
     */
    public function __construct($name = 'notice', $options = array())
    {
        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');

        $csrf = new Element\Csrf('security');
        $this->add($csrf);

        $box = new Element\Hidden('box');
        $this->add($box);

        $uid = new Element\Hidden('uid');
        $this->add($uid);
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
        $csrf->setRequired(true)
             ->setBreakOnFailure(false);
        $filter->add($csrf);

        $box = new Input('box');
        $box->setRequired(true)
            ->setBreakOnFailure(false);
        $filter->add($box);

        $uid = new Input('uid');
        $uid->setRequired(true)
            ->setBreakOnFailure(false);
        $filter->add($uid);

        $this->inputFilter = $filter;
        return $filter;
    }
}
