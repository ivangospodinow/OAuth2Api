<?php

namespace App\Entity;

use Andante\SoftDeletableBundle\SoftDeletable\SoftDeletableInterface;
use Andante\SoftDeletableBundle\SoftDeletable\SoftDeletableTrait;
use App\Repository\ProjectRepository;
use App\Traits\EntityHydratorTrait;
use DateInterval;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;

/**
 * @ORM\Entity(repositoryClass=ProjectRepository::class)
 */
class Project implements SoftDeletableInterface
{
    use EntityHydratorTrait, SoftDeletableTrait;

    const STATUS_NOT_STARTED = 'not_started';
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_DONE = 'done';

    /**
     * @ORM\Id
     * @ORM\Column(type="guid", unique=true)
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\CustomIdGenerator(class=UuidGenerator::class)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=128)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $status;

    /**
     * @ORM\Column(type="dateinterval")
     */
    private $duration;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $client;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $company;

    /**
     * @ORM\OneToMany(targetEntity=Task::class, mappedBy="project")
     */
    private $tasks;

    public function __construct(array $props = [])
    {
        $this->tasks = new ArrayCollection();
        $this->exchangeArray($props);
    }

    public function exchangeArray(array $props): self
    {
        if (isset($props['duration']) && is_string($props['duration'])) {
            $props['duration'] = new DateInterval($props['duration']);
        }

        $this->hydrateEntity($props);
        return $this;
    }

    public function getId(): ?string
    {
        return (string) $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus($status): self
    {
        if (!in_array($status, [self::STATUS_NOT_STARTED, self::STATUS_PENDING, self::STATUS_ACTIVE, self::STATUS_DONE])) {
            throw new \InvalidArgumentException("Invalid status");
        }
        $this->status = $status;

        return $this;
    }

    public function getDuration(): ?\DateInterval
    {
        return $this->duration;
    }

    public function setDuration(\DateInterval $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getClient(): ?string
    {
        return $this->client;
    }

    public function setClient(?string $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(?string $company): self
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks[] = $task;
            $task->setTasks($this);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->removeElement($task)) {
            // set the owning side to null (unless already changed)
            if ($task->getTasks() === $this) {
                $task->setTasks(null);
            }
        }

        return $this;
    }
}
