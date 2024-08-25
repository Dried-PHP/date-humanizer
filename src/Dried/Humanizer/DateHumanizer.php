<?php

declare(strict_types=1);

namespace Dried\Humanizer;

use Dried\Humanizer\List\ListStringifier;
use Dried\Humanizer\UnitAmount\UnitAmountStringifier;
use Dried\Utils\UnitAmount;

final readonly class DateHumanizer
{
    public function __construct(
        private UnitAmountStringifier $unitAmountStringifier,
        private ListStringifier $listStringifier,
    ) {
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
