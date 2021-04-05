<?php

namespace App\Elasticsearch;

use Elastica\Document;
use App\Model\Tutorial;
use App\Repository\TutorialRepository;
use JoliCode\Elastically\Messenger\DocumentExchangerInterface;

/**
 *
 */
class DocumentExchanger implements DocumentExchangerInterface
{
    /**
     * @var TutorialRepository
     */
    protected $tutorialRepository;

    public function __construct(TutorialRepository $tutorialRepository)
    {
        $this->tutorialRepository = $tutorialRepository;
    }

    public function fetchDocument(string $className, string $id): ?Document
    {
        if ($className === Tutorial::class) {
            $tutorial = $this->tutorialRepository->find($id);

            if ($tutorial) {
                return new Document($id, $tutorial->toModel());
            }
        }

        return null;
    }
}
