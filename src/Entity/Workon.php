<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WorkonRepository")
 */
class Workon
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Personne")
     * @ORM\JoinColumn(name="personne", referencedColumnName="id")
     */
    private $personne;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Activite")
     * @ORM\JoinColumn(name="activite", referencedColumnName="id")
     */
    private $activite;

    public function getId()
    {
        return $this->id;
    }

    public function getPersonne()
    {
        return $this->personne;
    }

    public function setPersonne($personne)
    {
        $this->personne = $personne;

        return $this;
    }

    public function getActivite()
    {
        return $this->activite;
    }

    public function setActivite($activite)
    {
        $this->activite = $activite;

        return $this;
    }
}