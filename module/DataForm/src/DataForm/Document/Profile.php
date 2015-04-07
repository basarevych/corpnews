<?php
/**
 * CorpNews
 *
 * @link        https://github.com/basarevych/corpnews
 * @copyright   Copyright (c) 2015 basarevych@gmail.com
 * @license     http://choosealicense.com/licenses/mit/ MIT
 */

namespace DataForm\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use DataForm\Document\AbstractDataFormDocument;

/**
 * User profile document
 * 
 * @category    DataForm
 * @package     Document
 * 
 * @ODM\Document(repositoryClass="DataForm\Document\ProfileRepository")
 */
class Profile extends AbstractDataFormDocument
{
    /**
     * First name
     *
     * @var string
     * 
     * @ODM\String
     */
    protected $first_name;

    /**
     * Middle name
     *
     * @var string
     * 
     * @ODM\String
     */
    protected $middle_name;

    /**
     * Last name
     *
     * @var string
     * 
     * @ODM\String
     */
    protected $last_name;

    /**
     * Gender ("male" or "female")
     *
     * @var string
     *
     * @ODM\String
     */
    protected $gender;

    /**
     * Company name
     *
     * @var string
     *
     * @ODM\String
     */
    protected $company;

    /**
     * Position title
     *
     * @var string
     *
     * @ODM\String
     */
    protected $position;

    /**
     * Converts this object to array
     *
     * @return array
     */
    public function toArray()
    {
        $whenUpdated = $this->getWhenUpdated();
        $dtFormat = 'Y-m-d H:i:s P';

        return array(
            'id'                => $this->getId(),
            'client_email'      => $this->getClientEmail(),
            'when_updated'      => $whenUpdated ? $whenUpdated->format($dtFormat) : null,
            'first_name'        => $this->getFirstName(),
            'middle_name'       => $this->getMiddleName(),
            'last_name'         => $this->getLastName(),
            'gender'            => $this->getGender(),
            'company'           => $this->getCompany(),
            'position'          => $this->getPosition(),
        );
    }

    /**
     * Sets properties from array
     *
     * @param array $data
     * @return AbstractDataFormDocument
     */
    public function fromArray($data)
    {
        $keys = array_keys($data);

        if (in_array('id', $keys))
            $this->setId($data['id']);

        if (in_array('client_email', $keys))
            $this->setClientEmail($data['client_email']);

        if (in_array('when_updated', $keys)) {
            $whenUpdated = null;
            if (strlen($data['when_updated']) > 0) {
                $dtFormat = 'Y-m-d H:i:s P';
                $whenUpdated = \DateTime::createFromFormat($dtFormat, $data['when_updated']);
            }
            $this->setWhenUpdated($whenUpdated);
        }

        if (in_array('first_name', $keys))
            $this->setFirstName($data['first_name']);

        if (in_array('middle_name', $keys))
            $this->setMiddleName($data['middle_name']);

        if (in_array('last_name', $keys))
            $this->setLastName($data['last_name']);

        if (in_array('gender', $keys))
            $this->setGender($data['gender']);

        if (in_array('company', $keys))
            $this->setCompany($data['company']);

        if (in_array('position', $keys))
            $this->setPosition($data['position']);
    }
 
    /**
     * Set first_name
     *
     * @param string $firstName
     * @return Profile
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;
        return $this;
    }

    /**
     * Get first_name
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set middle_name
     *
     * @param string $middleName
     * @return Profile
     */
    public function setMiddleName($middleName)
    {
        $this->middle_name = $middleName;
        return $this;
    }

    /**
     * Get middle_name
     *
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middle_name;
    }

    /**
     * Set last_name
     *
     * @param string $lastName
     * @return Profile
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;
        return $this;
    }

    /**
     * Get last_name
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set gender
     *
     * @param string $gender
     * @return Profile
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * Get gender
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set company
     *
     * @param string $company
     * @return Profile
     */
    public function setCompany($company)
    {
        $this->company = $company;
        return $this;
    }

    /**
     * Get company
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set position
     *
     * @param string $position
     * @return Profile
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }
}
