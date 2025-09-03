<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\VoteRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: VoteRepository::class)]
#[ORM\Table(name: 'vote', uniqueConstraints: [
    new ORM\UniqueConstraint(name: 'uniq_vote_user_post', columns: ['user_id', 'post_id'])
])]
#[UniqueEntity(fields: ['user', 'post'], message: 'Vous avez déjà voté pour ce post.')]
class Vote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // ✅ bool non-null + nom de colonne explicite
    #[ORM\Column(name: 'is_like', type: 'boolean', nullable: false)]
    private bool $isLike = true;

    // ✅ horodatage simple
    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $created;

    // ✅ join non-null + cascade DB
    #[ORM\ManyToOne(inversedBy: 'votes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'votes')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Post $post = null;

    public function __construct()
    {
        $this->created = new \DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function isLike(): bool { return $this->isLike; }
    public function setIsLike(bool $isLike): self { $this->isLike = $isLike; return $this; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }

    public function getPost(): ?Post { return $this->post; }
    public function setPost(?Post $post): self { $this->post = $post; return $this; }

    public function getCreated(): \DateTimeImmutable { return $this->created; }
}
