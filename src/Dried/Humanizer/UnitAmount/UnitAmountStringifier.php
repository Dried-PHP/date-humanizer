<?php

declare(strict_types=1);

namespace Dried\Humanizer\UnitAmount;

use Dried\Utils\UnitAmount;

interface UnitAmountStringifier
{
    public function stringify(UnitAmount $unitAmount, array $parameters = []): string;
}
