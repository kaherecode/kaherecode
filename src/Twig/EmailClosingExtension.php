<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmailClosingExtension extends AbstractExtension
{
    private static $closings = [
        'email_closing.champion',
        'email_closing.be_well',
        'email_closing.thank_you',
        'email_closing.good_day',
        'email_closing.excellent',
        'email_closing.glad',
        'email_closing.make_it_day',
        'email_closing.take_care',
        'email_closing.stay_awesome',
        'email_closing.rock_star',
    ];

    /**
     * @var TranslatorInterface
     */
    private $_translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->_translator = $translator;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('email_closing', [$this, 'generateEmailClosing']),
        ];
    }

    public function generateEmailClosing()
    {
        $index = rand(0, count(self::$closings) - 1);
        return $this
            ->_translator
            ->trans(self::$closings[array_rand(self::$closings)]);
    }
}
