<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace Application\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use DataForm\Document\AbstractDataFormDocument;

/**
 * Syslog document
 * 
 * @category    Application
 * @package     Document
 * 
 * @ODM\Document(repositoryClass="Application\Document\SyslogRepository")
 */
class Syslog
{
    /**
     * @const LEVEL_INFO
     * @const LEVEL_ERROR
     * @const LEVEL_CRITICAL
     */
    const LEVEL_INFO = 'info';
    const LEVEL_ERROR = 'error';
    const LEVEL_CRITICAL = 'critical';

    /**
     * Log ID
     *
     * @var integer 
     *
     * @ODM\Id(strategy="AUTO")
     */
    private $id;

    /**
     * Date
     *
     * @var \DateTime
     * 
     * @ODM\Date
     */
    protected $when_happened;

    /**
     * Level
     *
     * @var string
     * 
     * @ODM\String
     */
    protected $level;

    /**
     * Message
     *
     * @var string
     * 
     * @ODM\String
     */
    protected $message;

    /**
     * Exception message
     *
     * @var string
     *
     * @ODM\String
     */
    protected $exception;

    /**
     * Source name
     *
     * @var string
     *
     * @ODM\String
     */
    protected $source_name;

    /**
     * Source ID
     *
     * @var string
     *
     * @ODM\String
     */
    protected $source_id;

    /**
     * Converts this object to array
     *
     * @return array
     */
    public function toArray()
    {
        $dt = $this->getValueDatetime();
        return array(
            'id'                => $this->getId(),
            'when_happened'     => $this->getWhenHappened(),
            'level'             => $this->getLevel(),
            'message'           => $this->getMessage(),
            'exception'         => $this->getException(),
            'source_name'       => $this->getSourceName(),
            'source_id'         => $this->getSourceId(),
        );
    }

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set whenHappened
     *
     * @param date $whenHappened
     * @return Syslog
     */
    public function setWhenHappened($whenHappened)
    {
        $this->when_happened = $whenHappened;
        return $this;
    }

    /**
     * Get when_happened
     *
     * @return \DateTime
     */
    public function getWhenHappened()
    {
        return $this->when_happened;
    }

    /**
     * Set level
     *
     * @param string $level
     * @return Syslog
     */
    public function setLevel($level)
    {
        $this->level = $level;
        return $this;
    }

    /**
     * Get level
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return Syslog
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set exception
     *
     * @param string $exception
     * @return Syslog
     */
    public function setException($exception)
    {
        $this->exception = $exception;
        return $this;
    }

    /**
     * Get exception
     *
     * @return string
     */
    public function getException()
    {
        return $this->exception;
    }

    /**
     * Set source_name
     *
     * @param string $sourceName
     * @return Syslog
     */
    public function setSourceName($sourceName)
    {
        $this->source_name = $sourceName;
        return $this;
    }

    /**
     * Get source_name
     *
     * @return string
     */
    public function getSourceName()
    {
        return $this->source_name;
    }

    /**
     * Set source_id
     *
     * @param string $sourceId
     * @return Syslog
     */
    public function setSourceId($sourceId)
    {
        $this->source_id = $sourceId;
        return $this;
    }

    /**
     * Get source_id
     *
     * @return string
     */
    public function getSourceId()
    {
        return $this->source_id;
    }

    /**
     * Get levels higher than or equal to $level
     *
     * @param string $level
     * @return array
     */
    public static function getLevels($level = null)
    {
        $levels = [];
        switch ($level) {
            case null:
            case self::LEVEL_INFO:
                $levels[] = self::LEVEL_INFO;
            case self::LEVEL_ERROR:
                $levels[] = self::LEVEL_ERROR;
            case self::LEVEL_CRITICAL:
                $levels[] = self::LEVEL_CRITICAL;
        }
        return $levels;
    }
}
