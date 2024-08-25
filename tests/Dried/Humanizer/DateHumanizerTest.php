<?php

declare(strict_types=1);

namespace Tests\Dried\Humanizer;

use Dried\Humanizer\DateHumanizer;
use Dried\Humanizer\Exception\MissingTranslation;
use Dried\Humanizer\List\ListJoiner;
use Dried\Humanizer\List\ListTranslator;
use Dried\Humanizer\Translation\ArrayDateTranslations;
use Dried\Humanizer\Translation\EnglishTranslator;
use Dried\Humanizer\Translation\FileDateTranslations;
use Dried\Humanizer\UnitAmount\UnitAmountEnglishHumanizer;
use Dried\Humanizer\UnitAmount\UnitAmountTranslator;
use Dried\Translation\DateTranslations;
use Dried\Utils\UnitAmount;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;
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


    #[DataProvider('getEnglishPluralCases')]
    public function testEnglishPlural(string $expected, UnitAmount $unitAmount): void
    {
        $translator = new EnglishTranslator();
        $translationsGetter = DateTranslations::forLocale('en');
        $humanizer = new DateHumanizer(
            new UnitAmountTranslator($translator, $translationsGetter),
            new ListTranslator($translationsGetter),
        );

        self::assertSame($expected, $humanizer->unitForHumans($unitAmount));
    }

    public static function getEnglishPluralCases(): array
    {
        return [
            ['0 hours', UnitAmount::hours(0)],
            ['1 hour', UnitAmount::hours(1)],
            ['1.5 hours', UnitAmount::hours(1.5)],
            ['2 hours', UnitAmount::hours(2)],
        ];
    }

    public function testEnglishPluralWithRanges(): void
    {
        $translator = new EnglishTranslator();
        $translationsGetter = new ArrayDateTranslations([
            'hour' => '[10,30]a lot of hours|]30,infinity]too many hours|%count% hour|%count% hours',
        ]);
        $humanizer = new DateHumanizer(
            new UnitAmountTranslator($translator, $translationsGetter),
            new ListTranslator($translationsGetter),
        );

        self::assertSame('1 hour', $humanizer->unitForHumans(UnitAmount::hours(1)));
        self::assertSame('2 hours', $humanizer->unitForHumans(UnitAmount::hours(2)));
        self::assertSame('9 hours', $humanizer->unitForHumans(UnitAmount::hours(9)));
        self::assertSame('a lot of hours', $humanizer->unitForHumans(UnitAmount::hours(10)));
        self::assertSame('a lot of hours', $humanizer->unitForHumans(UnitAmount::hours(30)));
        self::assertSame('too many hours', $humanizer->unitForHumans(UnitAmount::hours(30.1)));
        self::assertSame('too many hours', $humanizer->unitForHumans(UnitAmount::hours(INF)));

        $translator = new EnglishTranslator();
        $translationsGetter = new ArrayDateTranslations([
            'hour' => '[10,30]a lot of hours|]30,infinity]too many hours',
        ]);
        $humanizer = new DateHumanizer(
            new UnitAmountTranslator($translator, $translationsGetter),
            new ListTranslator($translationsGetter),
        );

        self::assertSame('a lot of hours', $humanizer->unitForHumans(UnitAmount::hours(10)));
        self::assertSame('a lot of hours', $humanizer->unitForHumans(UnitAmount::hours(30)));
        self::assertSame('too many hours', $humanizer->unitForHumans(UnitAmount::hours(30.1)));
        self::assertSame('too many hours', $humanizer->unitForHumans(UnitAmount::hours(INF)));

        $translator = new EnglishTranslator();
        $translationsGetter = new ArrayDateTranslations([
            'hour' => 'square-bracket[10,30]|curly-bracket{1}',
        ]);
        $humanizer = new DateHumanizer(
            new UnitAmountTranslator($translator, $translationsGetter),
            new ListTranslator($translationsGetter),
        );

        self::assertSame('square-bracket[10,30]', $humanizer->unitForHumans(UnitAmount::hours(1)));
        self::assertSame('curly-bracket{1}', $humanizer->unitForHumans(UnitAmount::hours(2)));

        $translator = new EnglishTranslator();
        $translationsGetter = new ArrayDateTranslations([
            'hour' => '{INF}too much|fine',
        ]);
        $humanizer = new DateHumanizer(
            new UnitAmountTranslator($translator, $translationsGetter),
            new ListTranslator($translationsGetter),
        );

        self::assertSame('too much', $humanizer->unitForHumans(UnitAmount::hours(INF)));
        self::assertSame('fine', $humanizer->unitForHumans(UnitAmount::hours(99999999)));
    }

    public function testMissingTranslation(): void
    {
        self::expectExceptionObject(new RuntimeException(
            'Unable to choose a translation for "[10,30]a lot of hours|]30,infinity]too many hours" with locale for value 9',
        ));

        $translator = new EnglishTranslator();
        $translationsGetter = new ArrayDateTranslations([
            'hour' => '[10,30]a lot of hours|]30,infinity]too many hours',
        ]);
        $humanizer = new DateHumanizer(
            new UnitAmountTranslator($translator, $translationsGetter),
            new ListTranslator($translationsGetter),
        );

        $humanizer->unitForHumans(UnitAmount::hours(9));
    }

    public function testMissingKey(): void
    {
        $missingTranslation = MissingTranslation::forKey('day');

        self::assertSame("Translation for 'day' is not found.", $missingTranslation->getMessage());

        self::expectExceptionObject($missingTranslation);

        $translator = new EnglishTranslator();
        $translationsGetter = new ArrayDateTranslations([
            'hour' => '[10,30]a lot of hours|]30,infinity]too many hours',
        ]);
        $humanizer = new DateHumanizer(
            new UnitAmountTranslator($translator, $translationsGetter),
            new ListTranslator($translationsGetter),
        );

        $humanizer->unitForHumans(UnitAmount::days(9));
    }

    public function testTranslationFile(): void
    {
        $translator = new EnglishTranslator();
        $translationsGetter = new FileDateTranslations(__DIR__ . '/../../Fixtures/en.php');
        $humanizer = new DateHumanizer(
            new UnitAmountTranslator($translator, $translationsGetter),
            new ListTranslator($translationsGetter),
        );

        self::assertSame('9 months', $humanizer->unitForHumans(UnitAmount::months(9)));
    }
}
