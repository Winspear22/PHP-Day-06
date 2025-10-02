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

    #[Assert\NotBlank(message: 'Le titre ne peut pas être vide.')]
    #[Assert\Length(
        min: 5,
        minMessage: 'Le titre doit contenir au moins {{ limit }} caractères.',
        max: 60,
        maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[ORM\Column(length: 60)]
    private ?string $title = null;

    #[Assert\NotBlank(message: 'Le contenu ne peut pas être vide.')]
    #[Assert\Length(
        min: 10,
        minMessage: 'Le contenu doit contenir au moins {{ limit }} caractères.',
        max: 150,
        maxMessage: 'Le contenu ne peut pas dépasser {{ limit }} caractères.'
    )]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $content = null;


    #[ORM\Column]
    private ?\DateTimeImmutable $created = null;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $author = null;

    #[ORM\OneToMany(targetEntity: Vote::class, mappedBy: 'post', cascade: ['remove'], orphanRemoval: true)]
    private Collection $votes;

    #[ORM\ManyToOne(inversedBy: 'editedPosts')]
    #[ORM\JoinColumn(onDelete: 'SET NULL', nullable: true)]
    private ?User $lastEditedBy = null;


    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastEditedAt = null;

    public function countLikes(): int
    {
        $c = 0; foreach ($this->votes as $v) { if ($v->isLike()) { ++$c; } } return $c;
    }
    public function countDislikes(): int
    {
        $c = 0; foreach ($this->votes as $v) { if (!$v->isLike()) { ++$c; } } return $c;
    }

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

    /**
     * @return Collection<int, Vote>
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): static
    {
        if (!$this->votes->contains($vote)) {
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

    public function getLastEditedBy(): ?User
    {
        return $this->lastEditedBy;
    }

    public function setLastEditedBy(?User $lastEditedBy): static
    {
        $this->lastEditedBy = $lastEditedBy;

        return $this;
    }

    public function getLastEditedAt(): ?\DateTimeImmutable
    {
        return $this->lastEditedAt;
    }

    public function setLastEditedAt(?\DateTimeImmutable $lastEditedAt): static
    {
        $this->lastEditedAt = $lastEditedAt;

        return $this;
    }
}
