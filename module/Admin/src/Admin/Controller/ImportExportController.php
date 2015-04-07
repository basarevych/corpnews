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
use DynamicTable\Adapter\ArrayAdapter;
use Application\Exception\NotFoundException;
use Application\Entity\Client as ClientEntity;
use Admin\Form\Import as ImportForm;

/**
 * Import/Export controller
 *
 * @category    Admin
 * @package     Controller
 */
class ImportExportController extends AbstractActionController
{
    /**
     * Index action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $dfm = $sl->get('DataFormManager');

        $groups = $em->getRepository('Application\Entity\Group')
                     ->findBy([], [ 'name' => 'asc' ]);

        $forms = [];
        foreach ($dfm->getNames() as $name) {
            $class = $dfm->getDocumentClass($name);
            $doc = new $class();

            $fields = [];
            foreach (array_keys($doc->toArray()) as $key) {
                if (!in_array($key, [ 'id', 'client_email' ]))
                    $fields[] = $key;
            }

            $forms[$name] = [
                'title'     => $dfm->getTitle($name),
                'fields'    => $fields,
            ];
        }

        return new ViewModel([
            'groups'    => $groups,
            'forms'     => $forms,
        ]);
    }

    /**
     * Download exported file action
     */
    public function downloadAction()
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $dm = $sl->get('doctrine.documentmanager.odm_default');
        $dfm = $sl->get('DataFormManager');

        $groups = $this->params()->fromQuery('groups');
        $fields = $this->params()->fromQuery('fields');

        $result = '';
        $row = [];
        foreach (explode(',', $fields) as $field) {
            $field = str_replace('-', ' / ', $field);
            $row[] = '"' . $field . '"';
        }
        $result .= join(',', $row) . "\n";

        $clients = $em->getRepository('Application\Entity\Client')
                      ->findByGroupIds(explode(',', $groups));
        foreach ($clients as $client) {
            $row = [];
            foreach (explode(',', $fields) as $field) {
                if ($field == 'email') {
                    $row[$field] = '"' . $client->getEmail() . '"';
                    continue;
                }

                $parts = explode('-', $field);
                $class = $dfm->getDocumentClass($parts[0]);
                if (!$class)
                    continue;

                $doc = $dm->getRepository($class)
                          ->find($client->getId());
                if (!$doc)
                    continue;

                $value = @$doc->toArray()[$parts[1]];
                $value = str_replace('"', '""', $value);
                $row[$field] = '"' . $value . '"';
            }
            $result .= join(',', $row) . "\n";
        }

        $response = $this->getResponse();
        $response->getHeaders()->addHeaders([
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="export.csv"',
        ]);
        $response->setContent($result);
        return $response;
    }

    /**
     * Upload file form action
     *
     * @return ViewModel
     */
    public function uploadAction()
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $dm = $sl->get('doctrine.documentmanager.odm_default');
        $dfm = $sl->get('DataFormManager');
        $session = $sl->get('Session');
        $translate = $sl->get('viewhelpermanager')->get('translate');
        $basePath = $sl->get('viewhelpermanager')->get('basePath');

        $groups = $this->params()->fromQuery('groups');
        if (!$groups)
            $groups = $this->params()->fromPost('groups');
        $fields = $this->params()->fromQuery('fields');
        if (!$fields)
            $fields = $this->params()->fromPost('fields');

        $script = null;
        $form = new ImportForm();
        $messages = [];

        // Handle validate request
        if ($this->params()->fromQuery('query') == 'validate') {
            $field = $this->params()->fromQuery('field');
            $data = $this->params()->fromQuery('form');

            if ($field == 'file')
                return new JsonModel([ 'valid' => true ]);

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

        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($post);

            if ($form->isValid()) {
                $data = $form->getData();

                if (@is_array($data['file'])) {
                    $filename = $data['file']['tmp_name'];
                    $file = file_get_contents($filename);

                    if ($data['encoding'] != 'utf-8')
                        $file = iconv($data['encoding'], 'utf-8', $file);

                    $cnt = $session->getContainer();

                    $usedFields = explode(',', $fields);

                    $rows = [];
                    $first = true;
                    foreach (explode("\n", $file) as $line) {
                        if ($first || trim($line) == "") {
                            $first = false;
                            continue;
                        }

                        $csv = str_getcsv($line, ",", '"', '"');

                        $row = [];
                        for ($i = 0; $i < count($usedFields); $i++)
                            $row[$usedFields[$i]] = @$csv[$i];

                        $input = [];
                        foreach ($row as $field => $value) {
                            $parts = explode('-', $field);
                            if (count($parts) != 2)
                                continue;

                            $docName = $parts[0];
                            $propName = $parts[1];

                            if (!isset($input[$docName]))
                                $input[$docName] = [];

                            $input[$docName][$propName] = $value;
                        }

                        $docs = [];
                        foreach ($input as $docName => $props) {
                            $class = $dfm->getDocumentClass($docName);
                            if (!$class)
                                continue;

                            $doc = new $class();
                            $doc->fromArray($props);
                            $docs[$docName] = $doc;
                        }

                        $rows[] = [
                            'email'     => $row['email'],
                            'docs'      => $docs,
                        ];
                    }

                    $cnt->import = [
                        'groups'    => $groups,
                        'fields'    => $fields,
                        'rows'      => $rows,
                    ];

                    unlink($filename);
                }

                $location = $basePath('/admin/import-export/import-preview');
                $script = "$('#modal-form').modal('hide'); window.location = '$location'";
            }
        } else {
            $form->setData([
                'groups'    => $groups,
                'fields'    => $fields,
            ]);
        }

        $model = new ViewModel([
            'script'    => $script,
            'form'      => $form,
            'messages'  => $messages,
            'hasEmail'  => in_array('email', explode(',', $fields)),
        ]);
        $model->setTerminal(true);
        return $model;
    }

    /**
     * Preview imported data action
     *
     * @return ViewModel
     */
    public function importPreviewAction()
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $basePath = $sl->get('viewhelpermanager')->get('basePath');
        $session = $sl->get('Session');
        $cnt = $session->getContainer();

        if (!$cnt->offsetExists('import'))
            return $this->redirect()->toUrl($basePath('/admin/import-export'));

        $names = [];
        foreach (explode(',', $cnt->import['groups']) as $id) {
            if (empty($id))
                continue;

            $group = $em->getRepository('Application\Entity\Group')
                        ->find($id);
            $names[] = $group->getName();
        }

        return new ViewModel([
            'groups'    => $names,
        ]);
    }

    /**
     * Table data retrieving action
     *
     * @return JsonModel
     */
    public function previewTableAction()
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
     * Accept import action
     *
     * @return mixed
     */
    public function acceptImportAction()
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $dm = $sl->get('doctrine.documentmanager.odm_default');
        $dfm = $sl->get('DataFormManager');
        $basePath = $sl->get('viewhelpermanager')->get('basePath');
        $session = $sl->get('Session');
        $cnt = $session->getContainer();

        if ($cnt->offsetExists('import')) {
            $groups = [];
            foreach (explode(',', $cnt->import['groups']) as $id) {
                $group = $em->getRepository('Application\Entity\Group')
                            ->find($id);
                if ($group)
                    $groups[] = $group;
            }

            foreach ($cnt->import['rows'] as $row) {
                if (!isset($row['email']))
                    continue;

                $client = $em->getRepository('Application\Entity\Client')
                             ->findOneByEmail($row['email']);
                if (!$client) {
                    $client = new ClientEntity();
                    $client->setEmail($row['email']);
                }

                foreach ($groups as $group) {
                    if (!$client->getGroups()->contains($group))
                        $client->addGroup($group);
                    if (!$group->getClients()->contains($client))
                        $group->addClient($client);
                }

                $em->persist($client);
                $em->flush();

                foreach ($row['docs'] as $doc) {
                    $doc->setId($client->getId());
                    $doc->setClientEmail($row['email']);
                    $dm->persist($doc);
                }
                $dm->flush();
            }
        }

        return $this->redirect()->toUrl($basePath('/admin/client'));
    }

    /**
     * Cancel import action
     *
     * @return mixed
     */
    public function cancelImportAction()
    {
        $sl = $this->getServiceLocator();
        $basePath = $sl->get('viewhelpermanager')->get('basePath');
        $session = $sl->get('Session');
        $cnt = $session->getContainer();

        if ($cnt->offsetExists('import'))
            unset($cnt->import);

        return $this->redirect()->toUrl($basePath('/admin/import-export'));
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
        $session = $sl->get('Session');
        $cnt = $session->getContainer();

        $columns = [];
        foreach (explode(',', $cnt->import['fields']) as $field) {
            if ($field == 'email') {
                $columns[$field] = [
                    'title'     => $field,
                    'type'      => Table::TYPE_STRING,
                    'filters'   => [ Table::FILTER_LIKE ],
                    'sortable'  => true,
                    'visible'   => true,
                ];
            } else {
                $columns[$field] = [
                    'title'     => str_replace('-', ' / ', $field),
                    'type'      => Table::TYPE_STRING,
                    'filters'   => [ ],
                    'sortable'  => false,
                    'visible'   => true,
                ];
            }
        }

        $table = new Table();
        $table->setColumns($columns);

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
        $session = $sl->get('Session');
        $cnt = $session->getContainer();
        $escapeHtml = $sl->get('viewhelpermanager')->get('escapeHtml');
        $translate = $sl->get('viewhelpermanager')->get('translate');

        $data = [];
        foreach ($cnt->import['rows'] as $row) {
            $dataRow = [];
            foreach (explode(',', $cnt->import['fields']) as $field) {
                $parts = explode('-', $field);
                if ($parts[0] == 'email') {
                    $dataRow['email'] = @$row['email'];
                    continue;
                }
                $doc = $row['docs'][$parts[0]];
                $property = $parts[1];
                $method = str_replace("_", " ", $property);
                $method = ucfirst($method);
                $method = 'get' . str_replace(" ", "", $method);
                $dataRow[$field] = $doc->$method();
            }
            $data[] = $dataRow;
        }

        $adapter = new ArrayAdapter();
        $adapter->setData($data);

        $mapper = function ($row) use ($escapeHtml, $translate) {
            if (!$row)
                return null;

            $result = [];
            foreach ($row as $key => $value) {
                if ($value instanceof \DateTime)
                    $result[$key] = $value->format($translate('GENERIC_DATETIME_FORMAT'));
                else if (is_array($value))
                    $result[$key] = join(', ', $value);
                else
                    $result[$key] = $escapeHtml($value);
            }

            return $result;
        };

        $table->setAdapter($adapter);
        $table->setMapper($mapper);
    }
}
