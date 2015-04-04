<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Exception\NotFoundException;
use Admin\Form\Login as LoginForm;

/**
 * Auth controller
 *
 * @category    Admin
 * @package     Controller
 */
class AuthController extends AbstractActionController
{
    /**
     * Index action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $session = $sl->get('Session');
        $translate = $sl->get('viewhelpermanager')->get('translate');

        // Handle validate request
        if ($this->params()->fromQuery('query') == 'validate') {
            $field = $this->params()->fromQuery('field');
            $data = $this->params()->fromQuery('form');

            $form = new LoginForm();
            $form->setData($data);
            $form->isValid();

            $control = $form->get($field);
            $messages = [];
            foreach ($control->getMessages() as $msg)
                $messages[] = $translate($msg);

            return new JsonModel([
                'valid'     => (count($messages) == 0),
                'messages'  => $messages,
            ]);
        }

        $cnt = $session->getContainer();
        if ($cnt->offsetExists('is_admin'))
            return $this->redirect()->toRoute('admin');

        $request = $this->getRequest();
        $form = new LoginForm();
        $messages = [];

        $prg = $this->prg($request->getRequestUri(), true);
        if ($prg instanceof \Zend\Http\PhpEnvironment\Response)
            return $prg;

        if ($prg !== false) {
            $form->setData($prg);

            if ($form->isValid()) {
                $data = $form->getData();
                $config = $sl->get('Config');

                if ($data['login'] == @$config['corpnews']['admin']['account']
                        && $data['password'] == @$config['corpnews']['admin']['password']) {
                    $cnt->is_admin = true;
                    return $this->redirect()->refresh();
                }

                $messages[] = 'Invalid login or password';
            }
        }

        $this->getResponse()->setStatusCode(401);

        return new ViewModel([
            'form'      => $form,
            'messages'  => $messages,
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
