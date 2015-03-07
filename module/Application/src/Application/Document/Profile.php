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

/**
 * User profile document
 * 
 * @category    Application
 * @package     Document
 * 
 * @ODM\Document(repositoryClass="Application\Document\ProfileRepository")
 */
class Profile
{
    /**
     * Client email
     *
     * @var string
     *
     * @ODM\Id(strategy="NONE")
     */
    protected $client_email;

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
     * @ODM\Boolean
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
        $dt = $this->getValueDatetime();
        return array(
            'client_email'      => $this->getClientEmail(),
            'first_name'        => $this->getFisrtName(),
            'middle_name'       => $this->getMiddleName(),
            'last_name'         => $this->getLastName(),
            'gender'            => $this->getGender(),
            'company'           => $this->getCompany(),
            'position'          => $this->getPosition(),
        );
    }

    /**
     * Set clientEmail
     *
     * @param custom_id $clientEmail
     * @return self
     */
    public function setClientEmail($clientEmail)
    {
        $this->client_email = $clientEmail;
        return $this;
    }

    /**
     * Get clientEmail
     *
     * @return custom_id $clientEmail
     */
    public function getClientEmail()
    {
        return $this->client_email;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     * @return self
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;
        return $this;
    }

    /**
     * Get firstName
     *
     * @return string $firstName
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Set middleName
     *
     * @param string $middleName
     * @return self
     */
    public function setMiddleName($middleName)
    {
        $this->middle_name = $middleName;
        return $this;
    }

    /**
     * Get middleName
     *
     * @return string $middleName
     */
    public function getMiddleName()
    {
        return $this->middle_name;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return self
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;
        return $this;
    }

    /**
     * Get lastName
     *
     * @return string $lastName
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Set gender
     *
     * @param boolean $gender
     * @return self
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
        return $this;
    }

    /**
     * Get gender
     *
     * @return boolean $gender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set company
     *
     * @param string $company
     * @return self
     */
    public function setCompany($company)
    {
        $this->company = $company;
        return $this;
    }

    /**
     * Get company
     *
     * @return string $company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set position
     *
     * @param string $position
     * @return self
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    /**
     * Get position
     *
     * @return string $position
     */
    public function getPosition()
    {
        return $this->position;
    }
}
