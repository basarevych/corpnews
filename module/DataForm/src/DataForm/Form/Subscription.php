<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DataForm\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Filter;
use Zend\Validator;
use Zend\ServiceManager\ServiceLocatorInterface;
use Doctrine\ORM\EntityManager;

/**
 * Subscription data form
 *
 * @category    Admin
 * @package     Form
 */
class Subscription extends Form
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
     * @param ServiceLocatorInterface $em        Service locator
     * @param null|int|string         $name      Optional name
     * @param array                   $options   Optional options
     */
    public function __construct(ServiceLocatorInterface $sl, $name = 'subscription', $options = array())
    {
        $this->em = $sl->get('Doctrine\ORM\EntityManager');

        $entities = $this->em->getRepository('Application\Entity\Tag')
                             ->findBy([], [ 'name' => 'ASC' ]);
        $options = [];
        foreach ($entities as $entity)
            $options[$entity->getId()] = $entity->getDescr();

        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');

        $csrf = new Element\Csrf('security');
        $this->add($csrf);

        $subscribe = new Element\MultiCheckbox('subscribe');
        $subscribe->setLabel('Subscribe');
        $subscribe->setValueOptions([ 'all' => 'UNSUBSCRIBE_WANT_TO_RECEIVE' ]);
        $subscribe->setValue([ 'all' ]);
        $this->add($subscribe);

        if (count($options)) {
            $list = new Element\Hidden('list');
            $list->setValue(join(',', array_keys($options)));
            $this->add($list);

            $tags = new Element\MultiCheckbox('tags');
            $tags->setLabel('Themes');
            $tags->setValueOptions($options);
            $tags->setValue(array_keys($options));
            $this->add($tags);
        }
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

        $subscribe = new Input('subscribe');
        $subscribe->setRequired(false)
                  ->setBreakOnFailure(false);
        $filter->add($subscribe);

        if ($this->has('list')) {
            $list = new Input('list');
            $list->setRequired(true)
                 ->setBreakOnFailure(false);
            $filter->add($list);

            $tags = new Input('tags');
            $tags->setRequired(false)
                 ->setBreakOnFailure(false);
            $filter->add($tags);
        }

        $this->inputFilter = $filter;
        return $filter;
    }
}
