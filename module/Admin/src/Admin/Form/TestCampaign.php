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
use Application\Entity\Group as GroupEntity;

/**
 * Test campaign form
 *
 * @category    Admin
 * @package     Form
 */
class TestCampaign extends Form
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
    public function __construct(ServiceLocatorInterface $sl, $name = 'test-campaign', $options = array())
    {
        $this->em = $sl->get('Doctrine\ORM\EntityManager');

        parent::__construct($name, $options);
        $this->setAttribute('method', 'post');

        $csrf = new Element\Csrf('security');
        $this->add($csrf);

        $id = new Element\Hidden('id');
        $this->add($id);

        $entities = $this->em->getRepository('Application\Entity\Client')
                             ->findByGroupName(GroupEntity::NAME_TESTERS);

        $options = [];
        foreach ($entities as $entity)
            $options[$entity->getEmail()] = $entity->getEmail();

        $tester = new Element\Radio('tester');
        $tester->setLabel('Tester');
        $tester->setValueOptions($options);
        $this->add($tester);

        $sendTo = new Element\Text('send_to');
        $sendTo->setLabel('Send to');
        $this->add($sendTo);

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

        $id = new Input('id');
        $id->setRequired(true)
           ->setBreakOnFailure(false);
        $filter->add($id);

        $tester = new Input('tester');
        $tester->setRequired(true)
               ->setBreakOnFailure(false);
        $filter->add($tester);

        $emailParams = [
            'allow' => Validator\Hostname::ALLOW_DNS | Validator\Hostname::ALLOW_LOCAL
        ];

        $sendTo = new Input('send_to');
        $sendTo->setRequired(true)
               ->setBreakOnFailure(false)
               ->getFilterChain()
               ->attach(new Filter\StringTrim());
        $sendTo->getValidatorChain()
               ->attach(new Validator\EmailAddress($emailParams));
        $filter->add($sendTo);

        $this->inputFilter = $filter;
        return $filter;
    }
}
