<?php

namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: PostRepository::class)]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Length(
    max: 60,
    maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[ORM\Column(length: 60)]
        private ?string $title = null;

    #[Assert\Length(
    max: 150,
    maxMessage: 'Le contenu ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    private ?User $author = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $lastEditedAt = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    private ?User $lastEditedBy = null;

    /**
     * @var Collection<int, Vote>
     */
    #[ORM\OneToMany(targetEntity: Vote::class, mappedBy: 'post', orphanRemoval: true)]
    private Collection $votes;

    public function __construct()
    {
        $this->votes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreated(): ?\DateTimeImmutable
    {
        return $this->created;
    }

    public function setCreated(\DateTimeImmutable $created): static
    {
        $this->created = $created;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): static
    {
        $this->author = $author;

        return $this;
    }

    public function getLastEditedAt(): ?\DateTimeImmutable
    {
        return $this->lastEditedAt;
    }

    public function setLastEditedAt(\DateTimeImmutable $lastEditedAt): static
    {
        $this->lastEditedAt = $lastEditedAt;

        return $this;
    }

    public function getLastEditedBy(): ?User
    {
        return $this->lastEditedBy;
    }

    public function setLastEditedBy(?User $lastEditedBy): static
    {
        $this->lastEditedBy = $lastEditedBy;

        return $this;
    }

    /**
     * @return Collection<int, Vote>
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): static
    {
        if (!$this->votes->contains($vote))
        {
            $this->votes->add($vote);
            $vote->setPost($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): static
    {
        if ($this->votes->removeElement($vote)) {
            // set the owning side to null (unless already changed)
            if ($vote->getPost() === $this) {
                $vote->setPost(null);
            }
        }

        return $this;
    }

    public function getScore(): int
    {
        return $this->votes->filter(fn($v) => $v->getIsLike())->count() -
        $this->votes->filter(fn($v) => !$v->getIsLike())->count();
    }
}
