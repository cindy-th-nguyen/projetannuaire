<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ResponsableRepository")
 */
class Responsable
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Personne")
     * @ORM\JoinColumn(name="responsable", referencedColumnName="id")
     */
    private $responsable;

    /**
     * @ORM\Column(type="integer")
     */
    private $personne;

    public function getId()
    {
        return $this->id;
    }

    public function getResponsable()
    {
        return $this->responsable;
    }

    public function setResponsable($responsable)
    {
        $this->responsable = $responsable;

        return $this;
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
}