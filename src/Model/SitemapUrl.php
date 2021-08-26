<?php

namespace App\Model;

use DateTime;

class SitemapUrl
{
    protected $loc;
    protected $lastmod;
    protected $priority = 1.00;

    public function __construct()
    {
        $this->lastmod = (new DateTime())->format('c');
    }

    public function getLoc(): ?string
    {
        return $this->loc;
    }

    public function setLoc(string $loc): self
    {
        $this->loc = strtolower(trim($loc));

        return $this;
    }

    public function getLastmod(): ?string
    {
        return $this->lastmod;
    }

    public function setLastmod(DateTime $lastmod): self
    {
        $this->lastmod = $lastmod->format('c');

        return $this;
    }

    public function getPriority(): ?float
    {
        return $this->priority;
    }

    public function setPriority(float $priority): self
    {
        $this->priority = $priority;

        return $this;
    }
}
