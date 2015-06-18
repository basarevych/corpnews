<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DataForm\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Application\Exception\NotFoundException;
use Application\Exception\AccessDeniedException;
use Application\Entity\Campaign as CampaignEntity;
use DataForm\Form\Profile as ProfileForm;

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
        $translate = $sl->get('viewhelpermanager')->get('translate');

        $cnt = $session->getContainer();
        $admin = $cnt->offsetExists('is_admin') && $cnt->is_admin;

        $email = $this->params()->fromQuery('email');
        if ($email && !$admin)
            throw new AccessDeniedException('Admin access for not an admin denied');

        $secret = null;
        $key = $this->params()->fromQuery('key');
        if ($key) {
            $secret = $em->getRepository('Application\Entity\Secret')
                         ->findOneBy([ 'secret_key' => $key ]);
            if (!$secret)
                throw new NotFoundException("[" . self::DATA_FORM_NAME . "] Secret '$key' not found");
            if ($secret->getDataForm() != self::DATA_FORM_NAME)
                throw new NotFoundException("[" . self::DATA_FORM_NAME . "] Secret '$key' is for " . $secret->getDataForm());
            if ($secret->getCampaign()->getStatus() == CampaignEntity::STATUS_ARCHIVED)
                throw new NotFoundException("Campaign is archived");
            $client = $secret->getClient();
            if (!$client)
                throw new NotFoundException("[" . self::DATA_FORM_NAME . "] Secret '$key' has no client");
        } else if ($email) {
            $client = $em->getRepository('Application\Entity\Client')
                         ->findOneByEmail($email);
            if (!$client)
                throw new NotFoundException("[" . self::DATA_FORM_NAME . "] Client '$email' not found");
        } else {
            throw new \Exception('Need key or email');
        }

        $docClass = $dfm->getDocumentClass(self::DATA_FORM_NAME);
        $formClass = $dfm->getFormClass(self::DATA_FORM_NAME);

        $doc = $dm->getRepository($docClass)
                  ->find($client->getId());
        if (!$doc) {
            $dfm->createClientDocuments($client);
            $doc = $dm->getRepository($docClass)
                      ->find($client->getId());
            if (!$doc)
                throw new \Exception("[" . self::DATA_FORM_NAME . "] Documents for client '$email' could not be created");
        }

        $form = new $formClass();
        $messages = [];
        $success = false;

        // Handle validate request
        if ($this->params()->fromPost('query') == 'validate') {
            $field = $this->params()->fromPost('field');
            $data = $this->params()->fromPost('form');

            $form->setData($data);
            $form->isValid();

            $control = $form->get($field);
            foreach ($control->getMessages() as $msg)
                $messages[] = $translate($msg);

            return new JsonModel([
                'valid'     => (count($messages) == 0),
                'messages'  => $messages,
            ]);
        }

        $request = $this->getRequest();
        $prg = $this->prg($request->getRequestUri(), true);
        if ($prg instanceof \Zend\Http\PhpEnvironment\Response)
            return $prg;

        if ($prg !== false) {
            $form->setData($prg);

            if ($form->isValid()) {
                $data = $form->getData();

                if ($secret) {
                    $secret->setWhenSaved(new \DateTime());
                    $em->persist($secret);
                    $em->flush();
                }

                $gender = null;
                if (in_array($data['gender'], [ 'male', 'female' ]))
                    $gender = $data['gender'];

                $doc->setWhenUpdated(new \DateTime());
                $doc->setFirstName(empty($data['first_name']) ? null : $data['first_name']);
                $doc->setMiddleName(empty($data['middle_name']) ? null : $data['middle_name']);
                $doc->setLastName(empty($data['last_name']) ? null : $data['last_name']);
                $doc->setGender($gender);
                $doc->setCompany(empty($data['company']) ? null : $data['company']);
                $doc->setPosition(empty($data['position']) ? null : $data['position']);
                $dm->persist($doc);
                $dm->flush();

                $success = true;
            }
        } else {
            if ($secret) {
                $secret->setWhenOpened(new \DateTime());
                $em->persist($secret);
                $em->flush();
            }
        }

        $form->setData([
            'first_name'    => $doc->getFirstName(),
            'middle_name'   => $doc->getMiddleName(),
            'last_name'     => $doc->getLastName(),
            'gender'        => $doc->getGender(),
            'company'       => $doc->getCompany(),
            'position'      => $doc->getPosition(),
        ]);

        return new ViewModel([
            'title'     => $dfm->getTitle(self::DATA_FORM_NAME),
            'email'     => $client->getEmail(),
            'form'      => $form,
            'messages'  => $messages,
            'success'   => $success,
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
