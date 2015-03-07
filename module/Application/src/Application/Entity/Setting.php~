<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Setting entity
 * 
 * @category    Application
 * @package     Entity
 * 
 * @ORM\Entity(repositoryClass="Application\Entity\SettingRepository")
 * @ORM\Table(name="settings")
 */
class Setting
{
    /**
     * Stored variable type
     *
     * @const TYPE_STRING
     * @const TYPE_INTEGER
     * @const TYPE_FLOAT
     * @const TYPE_BOOLEAN
     * @const TYPE_DATETIME
     */
    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_FLOAT = 'float';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DATETIME = 'datetime';

    /**
     * Row ID
     *
     * @var integer
     * 
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * Variable name
     *
     * @var string
     * 
     * @ORM\Column(type="string", unique=true)
     */
    protected $name;

    /**
     * Variable type
     *
     * @var string
     * 
     * @ORM\Column(type="string")
     */
    protected $type;

    /**
     * String value
     *
     * @var string
     * 
     * @ORM\Column(type="string", nullable=true)
     */
    protected $value_string;

    /**
     * Integer value
     *
     * @var integer
     * 
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $value_integer;

    /**
     * Float value
     *
     * @var float
     * 
     * @ORM\Column(type="float", nullable=true)
     */
    protected $value_float;

    /**
     * Boolean value
     *
     * @var boolean
     * 
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $value_boolean;

    /**
     * DateTime value (stored in UTC tz always)
     *
     * @var DateTime
     * 
     * @ORM\Column(type="utcdatetime", nullable=true)
     */
    protected $value_datetime;

    /**
     * Converts this object to array
     *
     * @return array
     */
    public function toArray()
    {
        $dt = $this->getValueDatetime();

        return [
            'id'                => $this->getId(),
            'name'              => $this->getName(),
            'type'              => $this->getType(),
            'value_string'      => $this->getValueString(),
            'value_integer'     => $this->getValueInteger(),
            'value_float'       => $this->getValueFloat(),
            'value_boolean'     => $this->getValueBoolean(),
            'value_datetime'    => $dt ? $dt->getTimestamp() : null
        ];
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Setting
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Setting
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set value_string
     *
     * @param string $valueString
     * @return Setting
     */
    public function setValueString($valueString)
    {
        $this->value_string = $valueString;

        return $this;
    }

    /**
     * Get value_string
     *
     * @return string 
     */
    public function getValueString()
    {
        return $this->value_string;
    }

    /**
     * Set value_integer
     *
     * @param integer $valueInteger
     * @return Setting
     */
    public function setValueInteger($valueInteger)
    {
        $this->value_integer = $valueInteger;

        return $this;
    }

    /**
     * Get value_integer
     *
     * @return integer 
     */
    public function getValueInteger()
    {
        return $this->value_integer;
    }

    /**
     * Set value_float
     *
     * @param float $valueFloat
     * @return Setting
     */
    public function setValueFloat($valueFloat)
    {
        $this->value_float = $valueFloat;

        return $this;
    }

    /**
     * Get value_float
     *
     * @return float 
     */
    public function getValueFloat()
    {
        return $this->value_float;
    }

    /**
     * Set value_boolean
     *
     * @param boolean $valueBoolean
     * @return Setting
     */
    public function setValueBoolean($valueBoolean)
    {
        $this->value_boolean = $valueBoolean;

        return $this;
    }

    /**
     * Get value_boolean
     *
     * @return boolean 
     */
    public function getValueBoolean()
    {
        return $this->value_boolean;
    }

    /**
     * Set value_datetime
     *
     * @param utcdatetime $valueDatetime
     * @return Setting
     */
    public function setValueDatetime($valueDatetime)
    {
        $this->value_datetime = $valueDatetime;

        return $this;
    }

    /**
     * Get value_datetime
     *
     * @return utcdatetime 
     */
    public function getValueDatetime()
    {
        return $this->value_datetime;
    }

    /**
     * Lists all available types
     *
     * @return array
     */
    public static function getTypes()
    {
        return [
            self::TYPE_STRING,
            self::TYPE_INTEGER,
            self::TYPE_FLOAT,
            self::TYPE_BOOLEAN,
            self::TYPE_DATETIME
        ];
    }
}
