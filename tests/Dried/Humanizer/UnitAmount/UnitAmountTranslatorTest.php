<?php

declare(strict_types=1);

namespace Tests\Dried\Humanizer\UnitAmount;

use Dried\Humanizer\Translation\ArrayDateTranslations;
use Dried\Humanizer\UnitAmount\UnitAmountStringifier;
use Dried\Humanizer\UnitAmount\UnitAmountTranslator;
use Dried\Utils\UnitAmount;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UnitAmountTranslatorTest extends TestCase
{
    public function testInvalidKeyType(): void
    {
        self::expectExceptionObject(new RuntimeException(
            UnitAmountTranslator::class . ' only supports string translations, ' .
            'to work with other types, implement your own ' . UnitAmountStringifier::class,
        ));

        $translator = new UnitAmountTranslator(
            $this->createStub(TranslatorInterface::class),
            new ArrayDateTranslations([
                'year' => static fn (int $year): string => "$year years",
            ]),
        );
        $translator->stringify(UnitAmount::years(4));
    }
}
