<?php

namespace App\Entity;

use App\Repository\AgendauserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AgendauserRepository::class)
 */
class Agendauser
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @ORM\OneToMany(targetEntity="Agendaparticipant",mappedBy="agendauser")
     */
    private $agendaparticipant;
    /**
     * @ORM\OneToOne(targetEntity="User")
     */
    private $user;

    public function __construct()
    {
        $this->agendaparticipant = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Agendaparticipant[]
     */
    public function getAgendaparticipant(): Collection
    {
        return $this->agendaparticipant;
    }

    public function addAgendaparticipant(Agendaparticipant $agendaparticipant): self
    {
        if (!$this->agendaparticipant->contains($agendaparticipant)) {
            $this->agendaparticipant[] = $agendaparticipant;
            $agendaparticipant->setAgendauser($this);
        }

        return $this;
    }

    public function removeAgendaparticipant(Agendaparticipant $agendaparticipant): self
    {
        if ($this->agendaparticipant->removeElement($agendaparticipant)) {
            // set the owning side to null (unless already changed)
            if ($agendaparticipant->getAgendauser() === $this) {
                $agendaparticipant->setAgendauser(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        // set (or unset) the owning side of the relation if necessary
        // $newAgendauser = null === $user ? null : $this;
        // if ($user->getAgendauser() !== $newAgendauser) {
        //     $user->setAgendauser($newAgendauser);
        // }

        return $this;
    }

}
