<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Entity;

use Exception;
use Doctrine\ORM\EntityRepository;
use Application\Entity\Setting as SettingEntity;

/**
 * Repository for Setting entity
 *
 * @category    Application
 * @package     Entity
 */
class SettingRepository extends EntityRepository
{
    /**
     * Get the value of setting
     *
     * @param string $name
     * @return mixed
     * @throws Exception    For unknown type
     */
    public function getValue($name)
    {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();
        $qb->select('s')
           ->from('Application\Entity\Setting', 's')
           ->where('s.name = :name')
           ->setParameter('name', $name);

        $settings = $qb->getQuery()->getResult();
        if (count($settings) == 0)
            return null;

        $setting = $settings[0];
        switch ($setting->getType()) {
            case SettingEntity::TYPE_STRING:   return $setting->getValueString();
            case SettingEntity::TYPE_INTEGER:  return $setting->getValueInteger();
            case SettingEntity::TYPE_FLOAT:    return $setting->getValueFloat();
            case SettingEntity::TYPE_BOOLEAN:  return $setting->getValueBoolean();
            case SettingEntity::TYPE_DATETIME: return $setting->getValueDatetime();
        }

        throw new Exception('Unknown variable type');
    }
}
