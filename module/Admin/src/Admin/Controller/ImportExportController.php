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
use PHPExcel;
use PHPExcel_Cell;
use PHPExcel_Writer_Excel2007;
use PHPExcel_IOFactory;
use PHPExcel_Shared_Date;
use Application\Exception\NotFoundException;
use Application\Entity\Client as ClientEntity;
use Admin\Form\Export as ExportForm;
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
        $dfm = $sl->get('DataFormManager');

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

        $encodings = [
            'utf-8' => 'utf-8',
            'ibm866' => 'ibm866',
            'iso-8859-2' => 'iso-8859-2',
            'iso-8859-3' => 'iso-8859-3',
            'iso-8859-4' => 'iso-8859-4',
            'iso-8859-5' => 'iso-8859-5',
            'iso-8859-6' => 'iso-8859-6',
            'iso-8859-7' => 'iso-8859-7',
            'iso-8859-8' => 'iso-8859-8',
            'iso-8859-10' => 'iso-8859-10',
            'iso-8859-13' => 'iso-8859-13',
            'iso-8859-14' => 'iso-8859-14',
            'iso-8859-15' => 'iso-8859-15',
            'iso-8859-16' => 'iso-8859-16',
            'koi8-r' => 'koi8-r',
            'koi8-u' => 'koi8-u',
            'macintosh' => 'macintosh',
            'windows-874' => 'windows-874',
            'windows-1250' => 'windows-1250',
            'windows-1251' => 'windows-1251',
            'windows-1252' => 'windows-1252',
            'windows-1253' => 'windows-1253',
            'windows-1254' => 'windows-1254',
            'windows-1255' => 'windows-1255',
            'windows-1256' => 'windows-1256',
            'windows-1257' => 'windows-1257',
            'windows-1258' => 'windows-1258',
            'x-mac-cyrillic' => 'x-mac-cyrillic',
            'big5' => 'big5',
            'euc-jp' => 'euc-jp',
            'iso-2022-jp' => 'iso-2022-jp',
            'shift_jis' => 'shift_jis',
            'euc-kr' => 'euc-kr',
        ];

        return new ViewModel([
            'forms'     => $forms,
            'encodings' => $encodings,
        ]);
    }

    /**
     * Download exported file action
     */
    public function downloadAction()
    {
        $sl = $this->getServiceLocator();
        $basePath = $sl->get('viewhelpermanager')->get('basePath');

        $fields = $this->params()->fromQuery('fields');
        if (!$fields)
            $fields = $this->params()->fromPost('fields');
        $format = $this->params()->fromQuery('format');
        if (!$format)
            $format = $this->params()->fromPost('format');
        $separatorParam = $this->params()->fromQuery('separator');
        if (!$separatorParam)
            $separatorParam = $this->params()->fromPost('separator');
        $endingParam = $this->params()->fromQuery('ending');
        if (!$endingParam)
            $endingParam = $this->params()->fromPost('ending');
        $encoding = $this->params()->fromQuery('encoding');
        if (!$encoding)
            $encoding = $this->params()->fromPost('encoding');

        switch ($separatorParam) {
            default:
            case 'comma':       $separator = ','; break;
            case 'semicolon':   $separator = ';'; break;
            case 'tab':         $separator = "\t"; break;
        }

        switch ($endingParam) {
            default:
            case 'windows': $ending = "\r\n"; break;
            case 'unix':    $ending = "\n"; break;
        }

        $script = null;
        $form = new ExportForm($sl);
        $messages = [];

        // Handle validate request
        if ($this->params()->fromPost('query') == 'validate') {
            $field = $this->params()->fromPost('field');
            $data = $this->params()->fromPost('form');

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
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $data = $form->getData();

                $url = $basePath('/admin/import-export/generate-' . $data['format'])
                    . '?fields=' . urlencode($data['fields'])
                    . '&separator=' . urlencode($data['separator'])
                    . '&ending=' . urlencode($data['ending'])
                    . '&encoding=' . urlencode($data['encoding'])
                    . '&groups=' . urlencode(join(',', $data['groups']));

                $script = "$('#modal-form').modal('hide'); window.location = '" . $url . "';";
            }
        } else {
            $form->setData([
                'fields'    => $fields,
                'format'    => $format,
                'separator' => $separatorParam,
                'ending'    => $endingParam,
                'encoding'  => $encoding,
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
     * Generate CSV file to download
     *
     * @return mixed
     */
    protected function generateCsvAction()
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $dm = $sl->get('doctrine.documentmanager.odm_default');
        $dfm = $sl->get('DataFormManager');

        $fields = $this->params()->fromQuery('fields');
        $separatorParam = $this->params()->fromQuery('separator');
        $endingParam = $this->params()->fromQuery('ending');
        $encoding = $this->params()->fromQuery('encoding');
        $groups = $this->params()->fromQuery('groups');

        switch ($separatorParam) {
            default:
            case 'comma':       $separator = ','; break;
            case 'semicolon':   $separator = ';'; break;
            case 'tab':         $separator = "\t"; break;
        }

        switch ($endingParam) {
            default:
            case 'windows': $ending = "\r\n"; break;
            case 'unix':    $ending = "\n"; break;
        }

        $result = '';
        $row = [];
        foreach (explode(',', $fields) as $field) {
            $field = str_replace('-', ' / ', $field);
            $row[] = '"' . $field . '"';
        }
        $result .= join($separator, $row) . $ending;

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
                if (count($parts) != 2)
                    continue;

                $class = $dfm->getDocumentClass($parts[0]);
                $prop = $parts[1];
                if (!$class || !$prop)
                    continue;

                $doc = $dm->getRepository($class)
                          ->find($client->getId());
                if ($doc)
                    $value = @$doc->toArray()[$prop];
                else
                    $value = "";

                $value = str_replace('"', '""', $value);
                $row[$field] = '"' . $value . '"';
            }
            $result .= join($separator, $row) . $ending;
        }

        if ($encoding != 'utf-8')
            $result = iconv('utf-8', $encoding,  $result);

        $response = $this->getResponse();
        $response->getHeaders()->addHeaders([
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="export.csv"',
        ]);
        $response->setContent($result);
        return $response;
    }

    /**
     * Generate Excel file to download
     *
     * @return mixed
     */
    protected function generateExcelAction()
    {
        $sl = $this->getServiceLocator();
        $em = $sl->get('Doctrine\ORM\EntityManager');
        $dm = $sl->get('doctrine.documentmanager.odm_default');
        $dfm = $sl->get('DataFormManager');

        $fields = $this->params()->fromQuery('fields');
        $groups = $this->params()->fromQuery('groups');

        $spreadsheet = new PHPExcel();
        $spreadsheet->setActiveSheetIndex(0);
        $worksheet = $spreadsheet->getActiveSheet();

        $column = 0;
        foreach (explode(',', $fields) as $field) {
            $field = str_replace('-', ' / ', $field);
            $row[] = '"' . $field . '"';
            $worksheet->SetCellValueByColumnAndRow($column, 1, $field);
            $worksheet->getColumnDimension(PHPExcel_Cell::stringFromColumnIndex($column++))->setWidth(30);
        }

        $row = 2;
        $clients = $em->getRepository('Application\Entity\Client')
                      ->findByGroupIds(explode(',', $groups));
        foreach ($clients as $client) {
            $column = 0;
            foreach (explode(',', $fields) as $field) {
                if ($field == 'email') {
                    $worksheet->SetCellValueByColumnAndRow($column++, $row, $client->getEmail());
                    continue;
                }

                $parts = explode('-', $field);
                if (count($parts) != 2)
                    continue;

                $class = $dfm->getDocumentClass($parts[0]);
                $prop = $parts[1];
                if (!$class || !$prop)
                    continue;

                $method = 'get' . \Application\Tool\Text::toCamelCase($prop);
                $doc = $dm->getRepository($class)
                          ->find($client->getId());
                if ($doc)
                    $value = $doc->$method();
                else
                    $value = "";

                if ($value instanceof \DateTime) {
                    $worksheet->SetCellValueByColumnAndRow(
                        $column,
                        $row,
                        PHPExcel_Shared_Date::FormattedPHPToExcel(
                            $value->format('Y'),
                            $value->format('n'),
                            $value->format('j'),
                            $value->format('G'),
                            $value->format('i'),
                            $value->format('s')
                        )
                    );
                    $worksheet->getStyleByColumnAndRow($column++, $row)->getNumberFormat()->setFormatCode('yyyy-mm-dd hh:mm:ss');
                } else {
                    $worksheet->SetCellValueByColumnAndRow($column++, $row, $value);
                }
            }
            $row++;
        }

        $writer = new PHPExcel_Writer_Excel2007($spreadsheet);
        $writer->setIncludeCharts(true);
        $tmpFilename = tempnam('/tmp', 'corpnews-xlsx');
        $writer->save($tmpFilename);
        $file = file_get_contents($tmpFilename);
        unlink($tmpFilename);

        $response = $this->getResponse();

        $response->getHeaders()->addHeaders(array(
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Transfer-Encoding' => 'binary',
            'Content-disposition' => 'attachment; filename="export.xlsx"'
        ));
        $response->setContent($file);

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

        $fields = $this->params()->fromQuery('fields');
        if (!$fields)
            $fields = $this->params()->fromPost('fields');
        $format = $this->params()->fromQuery('format');
        if (!$format)
            $format = $this->params()->fromPost('format');
        $separatorParam = $this->params()->fromQuery('separator');
        if (!$separatorParam)
            $separatorParam = $this->params()->fromPost('separator');
        $endingParam = $this->params()->fromQuery('ending');
        if (!$endingParam)
            $endingParam = $this->params()->fromPost('ending');
        $encoding = $this->params()->fromQuery('encoding');
        if (!$encoding)
            $encoding = $this->params()->fromPost('encoding');
        $groups = $this->params()->fromQuery('groups');
        if (!$groups)
            $groups = $this->params()->fromPost('groups');

        $script = null;
        $form = new ImportForm($sl);
        $messages = [];

        // Handle validate request
        if ($this->params()->fromPost('query') == 'validate') {
            $field = $this->params()->fromPost('field');
            $data = $this->params()->fromPost('form');

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
            $upload = array_merge_recursive(
                $request->getPost()->toArray(),
                $request->getFiles()->toArray()
            );
            $form->setData($upload);

            if ($form->isValid()) {
                $data = $form->getData();

                if (@is_array($data['file'])) {
                    if ($format == 'csv')
                        $this->importCsv($data);
                    else if ($format == 'excel')
                        $this->importExcel($data);

                    $filename = $data['file']['tmp_name'];
                    unlink($filename);
                }

                $location = $basePath('/admin/import-export/import-preview');
                $script = "$('#modal-form').modal('hide'); window.location = '$location'";
            }
        } else {
            $form->setData([
                'fields'    => $fields,
                'format'    => $format,
                'separator' => $separatorParam,
                'ending'    => $endingParam,
                'encoding'  => $encoding,
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
        if (is_array($cnt->import['groups'])) {
            foreach ($cnt->import['groups'] as $id) {
                if (empty($id))
                    continue;

                $group = $em->getRepository('Application\Entity\Group')
                            ->find($id);
                $names[] = $group->getName();
            }
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
            foreach ($cnt->import['groups'] as $id) {
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
     * Import CSV
     *
     * @param array $data
     */
    protected function importCsv($data)
    {
        $sl = $this->getServiceLocator();
        $dfm = $sl->get('DataFormManager');
        $session = $sl->get('Session');
        $cnt = $session->getContainer();

        switch ($data['separator']) {
            default:
            case 'comma':       $separator = ','; break;
            case 'semicolon':   $separator = ';'; break;
            case 'tab':         $separator = "\t"; break;
        }

        switch ($data['ending']) {
            default:
            case 'windows': $ending = "\r\n"; break;
            case 'unix':    $ending = "\n"; break;
        }

        $filename = $data['file']['tmp_name'];
        $file = file_get_contents($filename);

        if ($data['encoding'] != 'utf-8')
            $file = iconv($data['encoding'], 'utf-8', $file);

        $usedFields = explode(',', $data['fields']);

        $rows = [];
        $first = true;
        foreach (explode($ending, $file) as $line) {
            if ($first || trim($line) == "") {
                $first = false;
                continue;
            }

            $csv = str_getcsv(trim($line), $separator, '"', '"');

            $row = [];
            for ($i = 0; $i < count($usedFields); $i++)
                $row[$usedFields[$i]] = @$csv[$i];

            if (strlen(@$row['email']) == 0)
                continue;

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
            'groups'    => $data['groups'],
            'fields'    => $data['fields'],
            'rows'      => $rows,
        ];
    }

    /**
     * Import Excel
     *
     * @param array $data
     */
    protected function importExcel($data)
    {
        $sl = $this->getServiceLocator();
        $dfm = $sl->get('DataFormManager');
        $session = $sl->get('Session');
        $cnt = $session->getContainer();

        $filename = $data['file']['tmp_name'];
        $usedFields = explode(',', $data['fields']);

        try {
            $type = PHPExcel_IOFactory::identify($filename);
            $reader = PHPExcel_IOFactory::createReader($type);
            $excel = $reader->load($filename);
        } catch(Exception $e) {
            throw new Exception('Error loading file: ' . $e->getMessage());
        }

        $worksheet = $excel->getSheet(0); 
        $highestRow = $worksheet->getHighestRow(); 
        $highestColumn = $worksheet->getHighestColumn();

        $rows = [];
        for ($i = 2; $i <= $highestRow; $i++) {
            $row = [];
            for ($j = 0; $j < count($usedFields); $j++) {
                $coords = chr(ord('A') + $j) . $i;
                $cell = $worksheet->getCell($coords);
                $value = $cell->getValue();
                if (PHPExcel_Shared_Date::isDateTime($cell)) {
                    $date = PHPExcel_Shared_Date::ExcelToPHPObject($value);
                    $value = $date->format('Y-m-d H:i:s P');
                }
                $row[$usedFields[$j]] = $value;
            }

            if (strlen(@$row['email']) == 0)
                continue;

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
            'groups'    => $data['groups'],
            'fields'    => $data['fields'],
            'rows'      => $rows,
        ];
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
