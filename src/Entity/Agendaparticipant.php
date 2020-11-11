<?php

namespace App\Entity;

use App\Repository\AgendaparticipantRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\Timestampable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AgendaparticipantRepository::class)
 * @ORM\HasLifecycleCallbacks
 */
class Agendaparticipant
{
    use Timestampable;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

     /**
     * @ORM\ManyToOne(targetEntity="User",inversedBy="agendaparticipant")
     */
    private $user;
    /**
     * @ORM\ManyToOne(targetEntity="Agendauser",inversedBy="agendaparticipant")
     */
    private $agendauser;
    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Please enter your first name")
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(message="Please enter your last name")
     */
    private $lastName;

    /**
     * @ORM\Column(type="string", length=180)
     * @Assert\NotBlank(message="Please enter your username")
     */
    private $username;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAgendauser(): ?Agendauser
    {
        return $this->agendauser;
    }

    public function setAgendauser(?Agendauser $agendauser): self
    {
        $this->agendauser = $agendauser;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }
}
