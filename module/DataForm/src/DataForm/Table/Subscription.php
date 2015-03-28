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
 * Subscription table
 *
 * @category    Admin
 * @package     Controller
 */
class Subscription extends Table
{
    /**
     * @const NAME      DataForm name
     */
    const NAME = 'subscription';

    /**
     * Constructor
     *
     * @param ServiceLocatorInterface $sl
     */
    public function __construct(ServiceLocatorInterface $sl)
    {
        $dfm = $sl->get('DataFormManager');
        $em = $sl->get('Doctrine\ORM\EntityManager');
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
            'ignored_tags' => [
                'title'         => $translate('Ignored tags'),
                'field_name'    => 'ignored_tags',
                'type'          => Table::TYPE_STRING,
                'filters'       => [ Table::FILTER_LIKE, Table::FILTER_NULL ],
                'sortable'      => false,
                'visible'       => true,
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

        $entities = $em->getRepository('Application\Entity\Tag')
                       ->findBy([], [ 'name' => 'ASC' ]);
        $tags = [];
        foreach ($entities as $entity)
            $tags[$entity->getId()] = $entity->getName();

        $mapper = function ($row) use ($url, $tags, $escapeHtml, $translate) {
            $email = $escapeHtml($row->getClientEmail());
            $email = '<a href="' . $url . '?email=' . urlencode($email) . '" target="_blank">' . $email . '</a>';

            $whenUpdated = $row->getWhenUpdated();
            if ($whenUpdated !== null)
                $whenUpdated = $whenUpdated->getTimestamp();

            $ignoredTags = [];
            if ($row->getIgnoredTags()) {
                foreach ($row->getIgnoredTags() as $tag)
                    $ignoredTags[] = $tags[$tag];
            }

            return [
                'id'            => $row->getId(),
                'client_email'  => $email,
                'when_updated'  => $whenUpdated,
                'ignored_tags'  => $escapeHtml(join(', ', $ignoredTags)),
            ];
        };

        $this->setAdapter($adapter);
        $this->setMapper($mapper);
    }
}
