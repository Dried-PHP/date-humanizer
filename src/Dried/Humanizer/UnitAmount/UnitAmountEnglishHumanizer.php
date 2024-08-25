<?php

declare(strict_types=1);

namespace Dried\Humanizer\UnitAmount;

use Dried\Utils\UnitAmount;

final readonly class UnitAmountEnglishHumanizer implements UnitAmountStringifier
{
    public function __construct(
        private string $glue = ' ',
    ) {
    }

    public function stringify(UnitAmount $unitAmount, array $parameters = []): string
    {
        $unitName = match ($unitAmount->amount) {
            1.0 => $unitAmount->unit->value,
            default => $unitAmount->unit->plural(),
        };

        return $unitAmount->amount . $this->glue . $unitName;
    }
}
