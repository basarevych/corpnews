<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2014 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Admin\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use DynamicTable\Table;
use DynamicTable\Adapter\DoctrineORMAdapter;
use Application\Exception\NotFoundException;
use Application\Entity\Client as ClientEntity;
use Application\Form\Confirm as ConfirmForm;
use Admin\Form\EditClient as EditClientForm;

/**
 * Clients controller
 *
 * @category    Admin
 * @package     Controller
 */
class ClientController extends AbstractActionController
{
    /**
     * Index action
     */
    public function indexAction()
    {
        return new ViewModel();
    }

    /**
     * Table data retrieving action
     */
    public function clientTableAction()
    {
        $table = $this->createTable();
        $this->connectTableData($table);

        $query = $this->params()->fromQuery('query');
        switch ($query) {
        case 'describe':
            $data = $table->describe();
            break;
        case 'data':
            $data = $table->setPageParams($_GET)->fetch();
            break;
        default:
            throw new \Exception('Unknown query type: ' . $query);
        }

        $data['success'] = true;
        return new JsonModel($data);
    }

    /**
     * Create/edit client entity form action
     */
    public function editClientAction()
    {
        $sl = $this->getServiceLocator();
        $dfm = $sl->get('DataFormManager');
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $translate = $sl->get('viewhelpermanager')->get('translate');

        // Handle validate request
        if ($this->params()->fromQuery('query') == 'validate') {
            $field = $this->params()->fromQuery('field');
            $data = $this->params()->fromQuery('form');

            $form = new EditClientForm($em, @$data['id']);
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

        $entity = null;
        $id = $this->params()->fromQuery('id');
        if (!$id)
            $id = $this->params()->fromPost('id');
        if ($id) {
            $entity = $em->getRepository('Application\Entity\Client')
                         ->find($id);
            if (!$entity)
                throw new NotFoundException('Wrong ID');
        }

        $script = null;
        $form = new EditClientForm($em, $id);
        $messages = [];

        $request = $this->getRequest();
        if ($request->isPost()) {  // Handle form submission
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();

                $date = null;
                if (!empty($data['when_bounced'])) {
                    $format = $form->get('when_bounced')->getFormat();
                    $date = \DateTime::createFromFormat($format, $data['when_bounced']);
                }

                if (!$entity)
                    $entity = new ClientEntity();

                try {
                    $oldEmail = $entity->getEmail();
                    $entity->setEmail($data['email']);
                    $entity->setWhenBounced($date);

                    $em->persist($entity);
                    $em->flush();

                    if (!$id)
                        $dfm->createClientDocuments($entity);
                    else if ($oldEmail != $entity->getEmail())
                        $dfm->updateClientDocuments($entity);
                } catch (\Exception $e) {
                    if (!$id)
                        $dfm->deleteClientDocuments($entity);
                    throw $e;
                }

                $script = "$('#modal-form').modal('hide'); reloadTable()";
            }
        } else if ($entity) {       // Load initial form values
            $date = "";
            if (($dt = $entity->getWhenBounced()) !== null) {
                $format = $form->get('when_bounced')->getFormat();
                $date = $dt->format($format);
            }

            $form->setData([
                'id'            => $entity->getId(),
                'email'         => $entity->getEmail(),
                'when_bounced'  => $date
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
     * Delete client form action
     */
    public function deleteClientAction()
    {
        $id = $this->params()->fromQuery('id');
        if (!$id)
            $id = $this->params()->fromPost('id');
        if (!$id)
            throw new \Exception('No "id" parameter');

        $sl = $this->getServiceLocator();
        $dfm = $sl->get('DataFormManager');
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $repo = $em->getRepository('Application\Entity\Client');

        $script = null;
        $form = new ConfirmForm();
        $messages = [];

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                if ($id == '_all') {
                    $repo->removeAll();
                    $dfm->deleteAllDocuments();
                } else {
                    foreach (explode(',', $id) as $item) {
                        $entity = $repo->find($item);
                        if (!$entity)
                            continue;

                        $dfm->deleteClientDocuments($entity);
                        $em->remove($entity);
                        $em->flush();
                    }
                }

                $script = "$('#modal-form').modal('hide'); reloadTable()";
            }
        } else {
            $form->setData([
                'id' => $id
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

    /**
     * Create Table object
     *
     * @return Table
     */
    protected function createTable()
    {
        $sl = $this->getServiceLocator();
        $translate = $sl->get('viewhelpermanager')->get('translate');

        $table = new Table();

        $table->setColumns([
            'id' => [
                'title'     => $translate('ID'),
                'sql_id'    => 'c.id',
                'type'      => Table::TYPE_INTEGER,
                'filters'   => [],
                'sortable'  => false,
                'visible'   => false,
            ],
            'email' => [
                'title'     => $translate('Email address'),
                'sql_id'    => 'c.email',
                'type'      => Table::TYPE_STRING,
                'filters'   => [ Table::FILTER_LIKE ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'when_bounced' => [
                'title'     => $translate('Email bounced'),
                'sql_id'    => 'c.when_bounced',
                'type'      => Table::TYPE_DATETIME,
                'filters'   => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'forms' => [
                'title'     => $translate('Filled forms'),
                'sql_id'    => 'none',
                'type'      => Table::TYPE_STRING,
                'filters'   => [],
                'sortable'  => false,
                'visible'   => true,
            ],
        ]);

        return $table;
    }

    /**
     * Create adapter and mapper
     *
     * @param Table $table
     */
    protected function connectTableData($table)
    {
        $sl = $this->getServiceLocator();
        $dfm = $sl->get('DataFormManager');
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $dm = $sl->get('doctrine.documentmanager.odm_default');
        $escapeHtml = $sl->get('viewhelpermanager')->get('escapeHtml');
        $translate = $sl->get('viewhelpermanager')->get('translate');
        $basePath = $sl->get('viewhelpermanager')->get('basePath');

        $qb = $em->createQueryBuilder();
        $qb->select('c')
           ->from('Application\Entity\Client', 'c');

        $adapter = new DoctrineORMAdapter();
        $adapter->setQueryBuilder($qb);

        $mapper = function ($row) use ($dfm, $dm, $escapeHtml, $basePath, $translate) {
            $email = '<a href="javascript:void(0)" onclick="editClient('
                . $row->getId() . ')">' . $escapeHtml($row->getEmail()) . '</a>';

            $whenBounced = $row->getWhenBounced();
            if ($whenBounced !== null)
                $whenBounced = $whenBounced->getTimestamp();

            $filledForms = [];
            foreach ($dfm->getNames() as $name) {
                $class = $dfm->getDocumentClass($name);
                $repo = $dm->getRepository($class);
                $doc = $repo->find($row->getId());
                if ($doc && $doc->getWhenUpdated()) {
                    $url = $basePath($dfm->getUrl($name));
                    $title = $escapeHtml($translate($dfm->getTitle($name)));
                    $filledForms[] = '<a href="' . $url . '?email=' . urlencode($row->getEmail()) . '" target="_blank">' . $title . '</a>';
                } 
            }

            return [
                'id'            => $row->getId(),
                'email'         => $email,
                'when_bounced'  => $whenBounced,
                'forms'         => join(', ', $filledForms),
            ];
        };

        $table->setAdapter($adapter);
        $table->setMapper($mapper);
    }
}
