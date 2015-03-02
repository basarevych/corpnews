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
use Application\Entity\Setting as SettingEntity;
use Admin\Form\MailboxSettingsForm;

/**
 * Settings controller
 *
 * @category    Admin
 * @package     Controller
 */
class SettingController extends AbstractActionController
{
    /**
     * Mailbox form action
     */
    public function mailboxFormAction()
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');

        // Handle validate request
        if ($this->params()->fromQuery('query') == 'validate') {
            $field = $this->params()->fromQuery('field');
            $data = $this->params()->fromQuery('form');

            $form = new MailboxSettingsForm();
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

        $setting = $em->getRepository('Application\Entity\Setting')
                      ->findOneByName('MailboxAutodelete');
        if (!$setting)
            throw new \Exception('Setting MailboxAutodelete does not exist');

        $script = null;
        $form = new MailboxSettingsForm();
        $messages = [];

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();
                $setting->setValueInteger($data['autodelete']);

                $em->persist($setting);
                $em->flush();

                $script = "$('#modal-form').modal('hide')";
            }
        } else {
            $form->setData([
                'autodelete' => $setting->getValueInteger(),
            ]);
        }

        $model = new ViewModel([
            'script'    => $script,
            'form'      => $form,
            'messages'  => $messages,
        ]);
        $model->setTerminal(true);
        return $model;
    }

    /**
     * This action is called when requested action is not found
     */
    public function notFoundAction()
    {
        throw new NotFoundException('Action is not found');
    }
}
