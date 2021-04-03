<?php

namespace App\Model;

/**
 * This class is the DTO for every field we want to search with elasticsearch
 */
class Tutorial
{
    protected $title;
    protected $slug;
    protected $description;
    protected $publishedAt;
    protected $tags;
    protected $author;

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

    /**
     * @return Tag[]
     */
    public function getTags()
    {
        return $this->tags;
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

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;

        return $this;
    }
}
