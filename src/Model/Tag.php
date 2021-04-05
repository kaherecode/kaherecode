<?php

namespace App\Model;

/**
 * This class is the DTO for every field we want to search with elasticsearch
 */
class Tag
{
    protected $label;

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = strtolower(trim($label));

        return $this;
    }
}
