<?php

declare(strict_types=1);

namespace Dried\Humanizer;

use Dried\Contracts\Translation\DateTranslationsGetter;
use Dried\Humanizer\List\ListStringifier;
use Dried\Humanizer\List\ListTranslator;
use Dried\Humanizer\Translation\ArrayDateTranslations;
use Dried\Humanizer\Translation\EnglishTranslator;
use Dried\Humanizer\UnitAmount\UnitAmountStringifier;
use Dried\Humanizer\UnitAmount\UnitAmountTranslator;
use Dried\Utils\UnitAmount;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class DateHumanizer
{
    public function __construct(
        private UnitAmountStringifier $unitAmountStringifier,
        private ListStringifier $listStringifier,
    ) {
    }

    public static function create(
        TranslatorInterface $translator,
        DateTranslationsGetter $translations,
    ): self {
        return new self(
            new UnitAmountTranslator($translator, $translations),
            new ListTranslator($translations),
        );
    }

    public static function english(): self
    {
        return self::create(new EnglishTranslator(), ArrayDateTranslations::english());
    }

    public function unitForHumans(UnitAmount $unitAmount): string
    {
        return $this->unitAmountStringifier->stringify($unitAmount);
    }

    /** @param list<UnitAmount> $unitAmounts */
    public function unitsForHumans(array $unitAmounts): string
    {
        return $this->listStringifier->stringify(array_map($this->unitForHumans(...), $unitAmounts));
    }
}
