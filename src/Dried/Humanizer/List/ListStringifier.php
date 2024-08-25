<?php

declare(strict_types=1);

namespace Dried\Humanizer\List;

interface ListStringifier
{
    /** @param list<string> $list */
    public function stringify(array $list): string;
}
