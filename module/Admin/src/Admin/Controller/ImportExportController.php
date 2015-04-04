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
use Application\Exception\NotFoundException;

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
                      ->findByGroupName(explode(',', $groups));
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
                $row[$parts[1]] = '"' . $value . '"';
            }
            $result .= join(',', $row) . "\n";
        }

        $response = $this->getResponse();
        $response->getHeaders()->addHeaders([
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="export.csv"',
        ]);
        $response->setContent($result);
        return $response;
    }

    /**
     * This action is called when requested action is not found
     */
    public function notFoundAction()
    {
        throw new NotFoundException('Action is not found');
    }
}
