<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Form\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Exception\NotFoundException;
use Application\Exception\AccessDeniedException;

/**
 * Profile form controller
 *
 * @category    Form
 * @package     Controller
 */
class ProfileController extends AbstractActionController
{
    /**
     * @const DATA_FORM_NAME
     */
    const DATA_FORM_NAME = 'profile';

    /**
     * Index action
     */
    public function indexAction()
    {
        $sl = $this->getServiceLocator();
        $session = $sl->get('Session');
        $dfm = $sl->get('DataFormManager');
        $dm = $sl->get('doctrine.documentmanager.odm_default');
        $em = $sl->get('Doctrine\ORM\EntityManager');

        $cnt = $session->getContainer();
        $admin = $cnt->offsetExists('is_admin') && $cnt->is_admin;

        $email = $this->params()->fromQuery('email');
        if ($email && !$admin)
            throw new AccessDeniedException('Admin access for not an admin denied');

        $class = $dfm->getDocumentClass(self::DATA_FORM_NAME);

        $doc = $dm->getRepository($class)
                  ->find($email);
        $client = $em->getRepository('Application\Entity\Client')
                     ->findOneByEmail($email);

        if (!$client && !$doc)
            throw new NotFoundException('No data found');
        if ($client && !$doc)
            throw new \Exception("Data form '" . self::DATA_FORM_NAME . "' requested for '$email', but the document does not exist");
        if (!$client && $doc)
            throw new \Exception("Data form '" . self::DATA_FORM_NAME . "' requested for '$email', but the client does not exist");

        return new ViewModel([
            'title' => $dfm->getTitle(self::DATA_FORM_NAME),
        ]);
    }

    /**
     * This action is called when requested action is not found
     */
    public function notFoundAction()
    {
        throw new NotFoundException('Action is not found');
    }
}
