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
use Admin\Form\LoginForm;

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
     */
    public function indexAction()
    {
        $sl = $this->getServiceLocator();
        $config = $sl->get('Config');
        $session = $sl->get('Session');
        $translate = $sl->get('viewhelpermanager')->get('translate');

        $cnt = $session->getContainer();
        if ($cnt->offsetExists('admin_password') && $cnt->admin_password == $config['corpnews']['admin']['password'])
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
                    $cnt->admin_password = $data['password'];
                    return $this->redirect()->refresh();
                }

                $messages[] = $translate('Invalid login or password');
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
