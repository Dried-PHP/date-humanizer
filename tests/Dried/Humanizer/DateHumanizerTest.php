<?php

declare(strict_types=1);

namespace Tests\Dried\Humanizer;

use Dried\Humanizer\DateHumanizer;
use Dried\Humanizer\List\ListJoiner;
use Dried\Humanizer\List\ListTranslator;
use Dried\Humanizer\UnitAmount\UnitAmountEnglishHumanizer;
use Dried\Humanizer\UnitAmount\UnitAmountTranslator;
use Dried\Translation\DateTranslations;
use Dried\Utils\UnitAmount;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Formatter\MessageFormatter;
use Symfony\Component\Translation\Translator;

final class DateHumanizerTest extends TestCase
{
    public function testFallbackStringifiers(): void
    {
        $humanizer = new DateHumanizer(new UnitAmountEnglishHumanizer(), new ListJoiner());

        self::assertSame('3 hours', $humanizer->unitForHumans(UnitAmount::hours(3)));
        self::assertSame('1 day 3 hours 20 minutes', $humanizer->unitsForHumans([
            UnitAmount::days(1),
            UnitAmount::hours(3),
            UnitAmount::minutes(20),
        ]));
    }

    public function testTranslations(): void
    {
        $translator = new Translator('fr');
        $translationsGetter = DateTranslations::forLocale('fr');
        $humanizer = new DateHumanizer(
            new UnitAmountTranslator($translator, $translationsGetter),
            new ListTranslator($translationsGetter),
        );

        self::assertSame('3 heures', $humanizer->unitForHumans(UnitAmount::hours(3)));
        self::assertSame('1 jour, 3 heures et 20 minutes', $humanizer->unitsForHumans([
            UnitAmount::days(1),
            UnitAmount::hours(3),
            UnitAmount::minutes(20),
        ]));
    }

    #[DataProvider('getPluralCases')]
    public function testPlural(string $locale, string $expected, UnitAmount $unitAmount): void
    {
        $translator = new Translator(
            $locale,
            new MessageFormatter(),
        );
        $translationsGetter = DateTranslations::forLocale($locale);
        $humanizer = new DateHumanizer(
            new UnitAmountTranslator($translator, $translationsGetter),
            new ListTranslator($translationsGetter),
        );

        self::assertSame($expected, $humanizer->unitForHumans($unitAmount));
    }

    public static function getPluralCases(): array
    {
        return [
            ['fr', '1 heure', UnitAmount::hours(1)],
            ['en', '1 hour', UnitAmount::hours(1)],
            ['fr', '1,5 heure', UnitAmount::hours(1.5)],
            ['en', '1.5 hours', UnitAmount::hours(1.5)],
            ['fr', '2 heures', UnitAmount::hours(2)],
            ['en', '2 hours', UnitAmount::hours(2)],
        ];
    }
}