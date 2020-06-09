<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonneRepository")
 */
class Personne
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20, nullable=false)
     * @Assert\Regex(
     *     pattern="/^[A-Za-zéèêë\-]+$/"
     * )
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=20, nullable=false)
     * @Assert\Regex(
     *     pattern="/^[A-Za-zéèêë\-]+$/"
     * )
     */
    private $lastname;

    /**
     * @ORM\Column(type="datetime", nullable=false)
     */
    private $birthdate;

        /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Regex("/^[A-Z][A-Za-zéèêë\-]+$/")
     */
    private $placebirth;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Regex("/^(0)[1-9](\d{2}){4}$/")
     */
    private $homephone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Regex("/^(0)[1-9](\d{2}){4}$/")
     */
    private $mobilephone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Regex("/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/")
     */
    private $mail;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mailGeeps;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $civilite;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $img;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $office;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $building;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $tutelle;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $ingeeps;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $arrivaldate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $departuredate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Status")
     * @ORM\JoinColumn(name="status", referencedColumnName="id")
     */
    private $status;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Compte")
     * @ORM\JoinColumn(name="compte", referencedColumnName="id")
     */
    private $compte;



    public function getId()
    {
        return $this->id;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getBirthdate()
    {
        return $this->birthdate;
    }

    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getPlacebirth()
    {
        return $this->placebirth;
    }

    public function setPlacebirth($placebirth)
    {
        $this->placebirth = $placebirth;

        return $this;
    }

    public function getHomephone()
    {
        return $this->homephone;
    }

    public function setHomephone($homephone)
    {
        $this->homephone = $homephone;

        return $this;
    }

    public function getMobilephone()
    {
        return $this->mobilephone;
    }

    public function setMobilephone($mobilephone)
    {
        $this->mobilephone = $mobilephone;

        return $this;
    }

    public function getMail()
    {
        return $this->mail;
    }

    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    public function getOffice()
    {
        return $this->office;
    }

    public function setOffice($office)
    {
        $this->office = $office;

        return $this;
    }

    public function getBuilding()
    {
        return $this->building;
    }

    public function setBuilding( $building)
    {
        $this->building = $building;

        return $this;
    }

    public function getTutelle()
    {
        return $this->tutelle;
    }

    public function setTutelle($tutelle)
    {
        $this->tutelle = $tutelle;

        return $this;
    }

    public function getIngeeps()
    {
        return $this->ingeeps;
    }

    public function setIngeeps($ingeeps)
    {
        $this->ingeeps = $ingeeps;

        return $this;
    }

    public function getArrivaldate()
    {
        return $this->arrivaldate;
    }

    public function setArrivaldate($arrivaldate)
    {
        $this->arrivaldate = $arrivaldate;

        return $this;
    }

    public function getDeparturedate()
    {
        return $this->departuredate;
    }

    public function setDeparturedate($departuredate)
    {
        $this->departuredate = $departuredate;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getCompte()
    {
        return $this->compte;
    }

    public function setCompte($compte)
    {
        $this->compte = $compte;
    }

    public function getMailGeeps()
    {
        return $this->mailGeeps;
    }

    public function setMailGeeps($mailGeeps)
    {
        $this->mailGeeps = $mailGeeps;
    }

    public function getCivilite()
    {
        return $this->civilite;
    }

    public function setCivilite($civilite)
    {
        $this->civilité = $civilite;
    }

    public function getImg()
    {
        return $this->img;
    }

    public function setImg($img)
    {
        $this->img = $img;
    }



}