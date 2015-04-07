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
 * Upload form
 *
 * @category    Admin
 * @package     Form
 */
class Import extends Form
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
        parent::__construct($name ? $name : 'import', $options);
        $this->setAttribute('method', 'post');

        $csrf = new Element\Csrf('security');
        $this->add($csrf);

        $groups = new Element\Hidden('groups');
        $this->add($groups);

        $fields = new Element\Hidden('fields');
        $this->add($fields);

        $encoding = new Element\Select('encoding');
        $encoding->setLabel('Encoding');
        $encoding->setValueOptions([
            'utf-8' => 'utf-8',
            'ibm866' => 'ibm866',
            'iso-8859-2' => 'iso-8859-2',
            'iso-8859-3' => 'iso-8859-3',
            'iso-8859-4' => 'iso-8859-4',
            'iso-8859-5' => 'iso-8859-5',
            'iso-8859-6' => 'iso-8859-6',
            'iso-8859-7' => 'iso-8859-7',
            'iso-8859-8' => 'iso-8859-8',
            'iso-8859-10' => 'iso-8859-10',
            'iso-8859-13' => 'iso-8859-13',
            'iso-8859-14' => 'iso-8859-14',
            'iso-8859-15' => 'iso-8859-15',
            'iso-8859-16' => 'iso-8859-16',
            'koi8-r' => 'koi8-r',
            'koi8-u' => 'koi8-u',
            'macintosh' => 'macintosh',
            'windows-874' => 'windows-874',
            'windows-1250' => 'windows-1250',
            'windows-1251' => 'windows-1251',
            'windows-1252' => 'windows-1252',
            'windows-1253' => 'windows-1253',
            'windows-1254' => 'windows-1254',
            'windows-1255' => 'windows-1255',
            'windows-1256' => 'windows-1256',
            'windows-1257' => 'windows-1257',
            'windows-1258' => 'windows-1258',
            'x-mac-cyrillic' => 'x-mac-cyrillic',
            'big5' => 'big5',
            'euc-jp' => 'euc-jp',
            'iso-2022-jp' => 'iso-2022-jp',
            'shift_jis' => 'shift_jis',
            'euc-kr' => 'euc-kr',
        ]);
        $this->add($encoding);

        $file = new Element\File('file');
        $file->setLabel('File');
        $this->add($file);
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

        $groups = new Input('groups');
        $groups->setRequired(false);
        $filter->add($groups);

        $fields = new Input('fields');
        $fields->setRequired(true);
        $filter->add($fields);

        $encoding = new Input('encoding');
        $encoding->setRequired(true);
        $filter->add($encoding);

        $file = new Input('file');
        $file->setRequired(true)
             ->getValidatorChain()
             ->attach(new Validator\File\UploadFile());
        $filter->add($file);

        $this->inputFilter = $filter;
        return $filter;
    }
}
