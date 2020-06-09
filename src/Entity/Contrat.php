<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContratRepository")
 */
class Contrat
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subject;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $funding;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $director;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $administrator;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $homeorganization;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $salary;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $securiteSocial;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $startdate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $enddate;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Personne")
     * @ORM\JoinColumn(name="personne", referencedColumnName="id")
     */
    private $personne;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Typeofcontrat")
     * @ORM\JoinColumn(name="type", referencedColumnName="id")
     */
    private $type;

    public function getId()
    {
        return $this->id;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    public function getFunding()
    {
        return $this->funding;
    }

    public function setFunding($funding)
    {
        $this->funding = $funding;

        return $this;
    }

    public function getDirector()
    {
        return $this->director;
    }

    public function setDirector($director)
    {
        $this->director = $director;

        return $this;
    }

    public function getAdministrator()
    {
        return $this->administrator;
    }

    public function setAdministrator($administrator)
    {
        $this->administrator = $administrator;

        return $this;
    }

    public function getHomeorganization()
    {
        return $this->homeorganization;
    }

    public function setHomeorganization($homeorganization)
    {
        $this->homeorganization = $homeorganization;

        return $this;
    }

    public function getSalary()
    {
        return $this->salary;
    }

    public function setSalary($salary)
    {
        $this->salary = $salary;

        return $this;
    }

    public function getSecuriteSocial()
    {
        return $this->securiteSocial;
    }

    public function setSecuriteSocial($securiteSocial)
    {
        $this->securiteSocial = $securiteSocial;

        return $this;
    }

    public function getStartdate()
    {
        return $this->startdate;
    }

    public function setStartdate($startdate): self
    {
        $this->startdate = $startdate;

        return $this;
    }

    public function getEnddate()
    {
        return $this->enddate;
    }

    public function setEnddate($enddate)
    {
        $this->enddate = $enddate;

        return $this;
    }

    public function getPersonne()
    {
        return $this->personne;
    }

    public function setPersonne($personne)
    {
        $this->personne = $personne;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }



}