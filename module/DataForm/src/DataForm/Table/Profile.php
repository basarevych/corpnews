<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2014 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DataForm\Table;

use Zend\ServiceManager\ServiceLocatorInterface;
use DynamicTable\Table;
use DynamicTable\Adapter\DoctrineMongoODMAdapter;

/**
 * Profile table
 *
 * @category    Admin
 * @package     Controller
 */
class Profile extends Table
{
    /**
     * @const NAME      DataForm name
     */
    const NAME = 'profile';

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface $sl
     */
    public function __construct(ServiceLocatorInterface $sl)
    {
        $dfm = $sl->get('DataFormManager');
        $dm = $sl->get('doctrine.documentmanager.odm_default');
        $escapeHtml = $sl->get('viewhelpermanager')->get('escapeHtml');
        $translate = $sl->get('viewhelpermanager')->get('translate');
        $basePath = $sl->get('viewhelpermanager')->get('basePath');

        $this->setColumns([
            'id' => [
                'title'         => $translate('ID'),
                'field_name'    => 'id',
                'type'          => Table::TYPE_INTEGER,
                'filters'       => [ Table::FILTER_EQUAL ],
                'sortable'      => true,
                'visible'       => false,
            ],
            'client_email' => [
                'title'         => $translate('Email address'),
                'field_name'    => 'client_email',
                'type'          => Table::TYPE_STRING,
                'filters'       => [ Table::FILTER_LIKE ],
                'sortable'      => true,
                'visible'       => true,
            ],
            'when_updated' => [
                'title'         => $translate('When updated'),
                'field_name'    => 'when_bounced',
                'type'          => Table::TYPE_DATETIME,
                'filters'       => [ Table::FILTER_BETWEEN, Table::FILTER_NULL ],
                'sortable'      => true,
                'visible'       => true,
            ],
            'first_name' => [
                'title'         => $translate('First name'),
                'field_name'    => 'first_name',
                'type'          => Table::TYPE_STRING,
                'filters'       => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
                'sortable'      => true,
                'visible'       => false,
            ],
            'middle_name' => [
                'title'         => $translate('Middle name'),
                'field_name'    => 'middle_name',
                'type'          => Table::TYPE_STRING,
                'filters'       => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
                'sortable'      => true,
                'visible'       => false,
            ],
            'last_name' => [
                'title'         => $translate('Last name'),
                'field_name'    => 'last_name',
                'type'          => Table::TYPE_STRING,
                'filters'       => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
                'sortable'      => true,
                'visible'       => true,
            ],
            'gender' => [
                'title'         => $translate('Gender'),
                'field_name'    => 'gender',
                'type'          => Table::TYPE_STRING,
                'filters'       => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
                'sortable'      => true,
                'visible'       => false,
            ],
            'company' => [
                'title'         => $translate('Company'),
                'field_name'    => 'company',
                'type'          => Table::TYPE_STRING,
                'filters'       => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
                'sortable'      => true,
                'visible'       => true,
            ],
            'position' => [
                'title'         => $translate('Position'),
                'field_name'    => 'position',
                'type'          => Table::TYPE_STRING,
                'filters'       => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
                'sortable'      => true,
                'visible'       => false,
            ],
        ]);

        $url = $basePath($dfm->getUrl(self::NAME));
        $class = $dfm->getDocumentClass(self::NAME);
        if (!$class)
            throw new NotFoundException("Document for " . self::NAME . " not found");

        $qb = $dm->createQueryBuilder();
        $qb->find($class);

        $adapter = new DoctrineMongoODMAdapter();
        $adapter->setQueryBuilder($qb);

        $mapper = function ($row) use ($url, $escapeHtml, $translate) {
            $email = $escapeHtml($row->getClientEmail());
            $email = '<a href="' . $url . '?email=' . urlencode($email) . '" target="_blank">' . $email . '</a>';

            $whenUpdated = $row->getWhenUpdated();
            if ($whenUpdated !== null)
                $whenUpdated = $whenUpdated->getTimestamp();

            return [
                'id'            => $row->getId(),
                'client_email'  => $email,
                'when_updated'  => $whenUpdated,
                'first_name'    => $escapeHtml($row->getFirstName()),
                'middle_name'   => $escapeHtml($row->getMiddleName()),
                'last_name'     => $escapeHtml($row->getLastName()),
                'gender'        => $translate(($value = $row->getGender()) ? $value : ''),
                'company'       => $escapeHtml($row->getCompany()),
                'position'      => $escapeHtml($row->getPosition()),
            ];
        };

        $this->setAdapter($adapter);
        $this->setMapper($mapper);
    }
}
