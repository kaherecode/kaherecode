<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TutorialRepository;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * @ORM\Entity(repositoryClass=TutorialRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Tutorial
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $pictureURL;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $publishedAt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPublished;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isValidated;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $sourceCodeLink;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $demoLink;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $videoLink;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $markdownContent;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $uuid;

    /**
     * @ORM\ManyToMany(
     *     targetEntity=Tag::class,
     *     inversedBy="tutorials",
     *     cascade="persist"
     * )
     */
    private $tags;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $thumbnailURL;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tutorials")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="tutorial", orphanRemoval=true)
     */
    private $comments;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __construct()
    {
        $this->isPublished = false;
        $this->isValidated = true;
        $this->tags = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    public function generateSlug(SluggerInterface $slugger)
    {
        if (!$this->slug || $this->slug === '-') {
            $this->slug = (string) $slugger
                ->slug((string) $this->title)->lower(). "-" .uniqid();
        }
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getPictureURL(): ?string
    {
        return $this->pictureURL;
    }

    public function setPictureURL(?string $pictureURL): self
    {
        $this->pictureURL = $pictureURL;

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

    public function getPublishedAt(): ?\DateTimeInterface
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?\DateTimeInterface $publishedAt): self
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @ORM\PrePersist()
     */
    public function setCreatedAt(): self
    {
        $this->createdAt = new \DateTime();

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): self
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getIsValidated(): ?bool
    {
        return $this->isValidated;
    }

    public function setIsValidated(bool $isValidated): self
    {
        $this->isValidated = $isValidated;

        return $this;
    }

    public function getSourceCodeLink(): ?string
    {
        return $this->sourceCodeLink;
    }

    public function setSourceCodeLink(?string $sourceCodeLink): self
    {
        $this->sourceCodeLink = $sourceCodeLink;

        return $this;
    }

    public function getDemoLink(): ?string
    {
        return $this->demoLink;
    }

    public function setDemoLink(?string $demoLink): self
    {
        $this->demoLink = $demoLink;

        return $this;
    }

    public function getVideoLink(): ?string
    {
        return $this->videoLink;
    }

    public function setVideoLink(?string $videoLink): self
    {
        $this->videoLink = $videoLink;

        return $this;
    }

    public function getMarkdownContent(): ?string
    {
        return $this->markdownContent;
    }

    public function setMarkdownContent(?string $markdownContent): self
    {
        $this->markdownContent = $markdownContent;

        return $this;
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    /**
     * @ORM\PrePersist()
     */
    public function setUuid(): self
    {
        $this->uuid = uniqid();

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    public function setTags(array $tags): self
    {
        $isAllTags = true;
        for ($i=0; $i < count($tags); $i++) {
            if (! $tags[$i] instanceof Tag) {
                $isAllTags = false;
            }
        }

        if ($isAllTags) {
            $this->tags = $tags;
        }

        return $this;
    }

    public function getThumbnailURL(): ?string
    {
        return $this->thumbnailURL;
    }

    public function setThumbnailURL(?string $thumbnailURL): self
    {
        $this->thumbnailURL = $thumbnailURL;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setTutorial($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getTutorial() === $this) {
                $comment->setTutorial(null);
            }
        }

        return $this;
    }
}
