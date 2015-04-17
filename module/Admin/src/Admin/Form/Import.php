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
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\EntityManager;

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
     * Doctrine EntityManager
     *
     * @var EntityManager
     */
    protected $em = null;

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface   $em        Service locator
     * @param null|int|string           $name    Optional name
     * @param array                     $options Optional options
     */
    public function __construct(ServiceLocatorInterface $sl, $name = null, $options = array())
    {
        $this->em = $sl->get('Doctrine\ORM\EntityManager');
        $translate = $sl->get('viewhelpermanager')->get('translate');

        parent::__construct($name ? $name : 'import', $options);
        $this->setAttribute('method', 'post');

        $csrf = new Element\Csrf('security');
        $this->add($csrf);

        $fields = new Element\Hidden('fields');
        $this->add($fields);

        $format = new Element\Hidden('format');
        $this->add($format);

        $separator = new Element\Hidden('separator');
        $this->add($separator);

        $ending = new Element\Hidden('ending');
        $this->add($ending);

        $encoding = new Element\Hidden('encoding');
        $this->add($encoding);

        $entities = $this->em->getRepository('Application\Entity\Group')
                             ->findBy([], [ 'name' => 'ASC' ]);
        $options = [];
        foreach ($entities as $entity)
            $options[$entity->getId()] = $entity->getName();

        $groups = new Element\MultiCheckbox('groups');
        $groups->setLabel('Groups');
        $groups->setValueOptions($options);
        $groups->setValue(array_keys($options));
        $this->add($groups);

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

        $fields = new Input('fields');
        $fields->setRequired(true);
        $filter->add($fields);

        $format = new Input('format');
        $format->setRequired(true);
        $filter->add($format);

        $separator = new Input('separator');
        $separator->setRequired(true);
        $filter->add($separator);

        $ending = new Input('ending');
        $ending->setRequired(true);
        $filter->add($ending);

        $encoding = new Input('encoding');
        $encoding->setRequired(true);
        $filter->add($encoding);

        $groups = new Input('groups');
        $groups->setRequired(false);
        $filter->add($groups);

        $file = new Input('file');
        $file->setRequired(true);

        global $__UPLOAD_MOCK;
        if ($__UPLOAD_MOCK !== true) {
            $file->getValidatorChain()
                 ->attach(new Validator\File\UploadFile());
        }

        $filter->add($file);

        $this->inputFilter = $filter;
        return $filter;
    }
}
