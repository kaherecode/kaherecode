<?php

namespace App\EntityListener;

use App\Entity\Tutorial;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 *
 */
class TutorialEntityListener
{
    /**
     * @var SluggerInterface
     */
    private $_slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->_slugger = $slugger;
    }

    public function prePersist(Tutorial $tutorial, LifecycleEventArgs $event)
    {
        $tutorial->generateSlug($this->_slugger);
    }

    public function preUpdate(Tutorial $tutorial, LifecycleEventArgs $event)
    {
        if (!$tutorial->getIsPublished() && $tutorial->getPublishedAt() !== null) {
            $tutorial->generateSlug($this->_slugger);
        }
    }
}
