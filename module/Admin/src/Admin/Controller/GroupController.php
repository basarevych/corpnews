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
use Application\Entity\Group as GroupEntity;
use Application\Form\Confirm as ConfirmForm;
use Admin\Form\EditGroup as EditGroupForm;

/**
 * Groups controller
 *
 * @category    Admin
 * @package     Controller
 */
class GroupController extends AbstractActionController
{
    /**
     * Index action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        return new ViewModel();
    }

    /**
     * Table data retrieving action
     *
     * @return JsonModel
     */
    public function groupTableAction()
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
     * Create/edit group entity form action
     *
     * @return ViewModel
     */
    public function editGroupAction()
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $translate = $sl->get('viewhelpermanager')->get('translate');

        // Handle validate request
        if ($this->params()->fromQuery('query') == 'validate') {
            $field = $this->params()->fromQuery('field');
            $data = $this->params()->fromQuery('form');

            $form = new EditGroupForm($em, @$data['id']);
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
            $entity = $em->getRepository('Application\Entity\Group')
                         ->find($id);
            if (!$entity)
                throw new NotFoundException('Wrong ID');
        }

        $script = null;
        $messages = [];

        if ($entity && in_array($entity->getName(), GroupEntity::getSystemNames()))
            $form = new ConfirmForm();
        else
            $form = new EditGroupForm($em, $id);

        $request = $this->getRequest();
        if ($request->isPost()) {  // Handle form submission
            $form->setData($request->getPost());

            if ($form->isValid()) {
                if ($form instanceof EditGroupForm) {
                    $data = $form->getData();

                    if (!$entity)
                        $entity = new GroupEntity();

                    $entity->setName($data['name']);

                    $em->persist($entity);
                    $em->flush();
                }

                $script = "$('#modal-form').modal('hide'); reloadTable()";
            }
        } else if ($entity) {       // Load initial form values
            $form->setData([
                'id'    => $entity->getId(),
                'name'  => $entity->getName(),
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
     * Empty group form action
     *
     * @return ViewModel
     */
    public function emptyGroupAction()
    {
        $id = $this->params()->fromQuery('id');
        if (!$id)
            $id = $this->params()->fromPost('id');
        if (!$id)
            throw new \Exception('No "id" parameter');

        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $repo = $em->getRepository('Application\Entity\Group');

        $script = null;
        $form = new ConfirmForm();
        $messages = [];

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $groups = [];
                if ($id == '_all') {
                    $groups = $repo->findAll();
                } else {
                    foreach (explode(',', $id) as $item) {
                        $group = $repo->find($item);
                        if (!$group)
                            continue;
                        $groups[] = $group;
                    }
                }

                foreach ($groups as $group) {
                    $clients = $group->getClients();
                    foreach ($clients as $client) {
                        $group->removeClient($client);
                        $client->removeGroup($group);
                    }
                }
                $em->flush();

                $script = "$('#modal-form').modal('hide'); reloadTable()";
            }
        } else {
            $form->setData([
                'id' => $id
            ]);
        }

        $model = new ViewModel([
            'script'            => $script,
            'form'              => $form,
            'messages'          => $messages,
        ]);
        $model->setTerminal(true);
        return $model;
    }

    /**
     * Delete group form action
     *
     * @return ViewModel
     */
    public function deleteGroupAction()
    {
        $id = $this->params()->fromQuery('id');
        if (!$id)
            $id = $this->params()->fromPost('id');
        if (!$id)
            throw new \Exception('No "id" parameter');

        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $repo = $em->getRepository('Application\Entity\Group');

        $script = null;
        $form = new ConfirmForm();
        $messages = [];

        $entities = [];
        $keepSystemGroup = ($id == '_all');
        if ($id != '_all') {
            foreach (explode(',', $id) as $item) {
                $entity = $repo->find($item);
                if (!$entity)
                    continue;

                if (in_array($entity->getName(), GroupEntity::getSystemNames()))
                    $keepSystemGroup = true;
                else
                    $entities[] = $entity;
            }
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());

            if ($form->isValid()) {
                if ($id == '_all') {
                    $repo->removeAll();
                } else {
                    foreach ($entities as $entity) {
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
            'script'            => $script,
            'form'              => $form,
            'messages'          => $messages,
            'systemGroups'      => GroupEntity::getSystemNames(),
            'keepSystemGroup'   => $keepSystemGroup,
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
                'sql_id'    => 'g.id',
                'type'      => Table::TYPE_INTEGER,
                'filters'   => [ Table::FILTER_BETWEEN ],
                'sortable'  => true,
                'visible'   => false,
            ],
            'name' => [
                'title'     => $translate('Name'),
                'sql_id'    => 'g.name',
                'type'      => Table::TYPE_STRING,
                'filters'   => [ Table::FILTER_LIKE ],
                'sortable'  => true,
                'visible'   => true,
            ],
            'clients' => [
                'title'     => $translate('Number of clients'),
                'sql_id'    => 'none',
                'type'      => Table::TYPE_INTEGER,
                'filters'   => [  ],
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
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $escapeHtml = $sl->get('viewhelpermanager')->get('escapeHtml');

        $qb = $em->createQueryBuilder();
        $qb->select('g')
           ->from('Application\Entity\Group', 'g')
           ->leftJoin('g.clients', 'c');

        $adapter = new DoctrineORMAdapter();
        $adapter->setQueryBuilder($qb);

        $mapper = function ($row) use ($escapeHtml) {
            $name = '<a href="javascript:void(0)" onclick="editGroup('
                . $row->getId() . ')">' . $escapeHtml($row->getName()) . '</a>';

            return [
                'id'        => $row->getId(),
                'name'      => $name,
                'clients'   => count($row->getClients()),
            ];
        };

        $table->setAdapter($adapter);
        $table->setMapper($mapper);
    }
}
