<?php

namespace App\Entity;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 */
class Task
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="date")
     */
    private $deadline;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="task")
     */
    private ?User $user_id;

    /**
     * @ORM\Column(type="boolean")
     */
    private $checked;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Attachment", mappedBy="task")
     */
    private $attachment;

    /**
     * @ORM\Column(type="datetime")
     */
    private $creation_date;

    public function __construct()
    {
        $this->attachment = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDeadline(): ?DateTimeInterface
    {
        return $this->deadline;
    }

    public function setDeadline(DateTimeInterface $deadline): self
    {
        $this->deadline = $deadline;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getChecked(): ?bool
    {
        return $this->checked;
    }

    public function setChecked(bool $checked): self
    {
        $this->checked = $checked;

        return $this;
    }

    /**
     * @return Collection|Attachment[]
     */
    public function getAttachment(): Collection
    {
        return $this->attachment;
    }

    public function addAttachment(Attachment $attachment): self
    {
        if (!$this->attachment->contains($attachment)) {
            $this->attachment[] = $attachment;
            $attachment->setTask($this);
        }

        return $this;
    }

    public function removeAttachment(Attachment $attachment): self
    {
        if ($this->attachment->removeElement($attachment)) {
            // set the owning side to null (unless already changed)
            if ($attachment->getTask() === $this) {
                $attachment->setTask(null);
            }
        }

        return $this;
    }

    public function getCreationDate(): ?\DateTimeInterface
    {
        return $this->creation_date;
    }

    public function setCreationDate(\DateTimeInterface $creation_date): self
    {
        $this->creation_date = $creation_date;

        return $this;
    }

}
