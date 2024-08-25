<?php

declare(strict_types=1);

namespace Tests\Dried\Humanizer\List;

use Dried\Humanizer\List\ListTranslator;
use Dried\Humanizer\Translation\ArrayDateTranslations;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ListTranslatorTest extends TestCase
{
    public function testArrayGlue(): void
    {
        $translator = new ListTranslator(
            new ArrayDateTranslations([
                'list' => [', ', ' and '],
            ]),
        );

        self::assertSame('a', $translator->stringify(['a']));
        self::assertSame('a and b', $translator->stringify(['a', 'b']));
        self::assertSame('a, b and c', $translator->stringify(['a', 'b', 'c']));

        $translator = new ListTranslator(
            new ArrayDateTranslations([
                'list' => [', '],
            ]),
        );

        self::assertSame('a', $translator->stringify(['a']));
        self::assertSame('a, b', $translator->stringify(['a', 'b']));
        self::assertSame('a, b, c', $translator->stringify(['a', 'b', 'c']));
    }

    public function testStringGlue(): void
    {
        $translator = new ListTranslator(
            new ArrayDateTranslations([
                'list' => ', ',
            ]),
        );

        self::assertSame('a', $translator->stringify(['a']));
        self::assertSame('a, b', $translator->stringify(['a', 'b']));
        self::assertSame('a, b, c', $translator->stringify(['a', 'b', 'c']));
    }

    public function testCallableGlue(): void
    {
        $translator = new ListTranslator(
            new ArrayDateTranslations([
                'list' => static function (array $list): string {
                    $texts = [];

                    foreach ($list as $index => $item) {
                        $texts []= "$index:$item";
                    }

                    return implode("\n", $texts);
                },
            ]),
        );

        self::assertSame('0:a', $translator->stringify(['a']));
        self::assertSame("0:a\n1:b", $translator->stringify(['a', 'b']));
        self::assertSame("0:a\n1:b\n2:c", $translator->stringify(['a', 'b', 'c']));
    }

    public function testInvalidKeyType(): void
    {
        self::expectExceptionObject(new RuntimeException(
            'Translation for "list" should be callable, string or array.',
        ));

        $translator = new ListTranslator(
            new ArrayDateTranslations([
                'list' => 5,
            ]),
        );
        $translator->stringify(['a']);
    }
}
