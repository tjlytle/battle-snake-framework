<?php

declare(strict_types=1);

namespace BattleSnake\Tests\Unit\SnekSpec;

use BattleSnake\Tests\SnekSpec\Parser as SUT;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    public const string SNAKES_AND_FOOD_1 = <<<EOT
    -----------
    -----Vvvv--
    ---0----v--
    --------U--
    -----------
    --tt---0---
    --tS-------
    --T------0-
    -----------
    ---0-------
    -----------
    EOT;

    public const string SINGLE_HEAD_SNAKE = <<<EOT
    -----------
    -----------
    -----------
    -----------
    -----------
    -----------
    ----S------
    -----------
    -----------
    -----------
    -----------
    EOT;

    public const string HEAD_AND_TAIL_SNAKE_1 = <<<EOT
    -----------
    -----------
    -----------
    -----------
    -----------
    ----T------
    ----S------
    -----------
    -----------
    -----------
    -----------
    EOT;

    public const string HEAD_AND_TAIL_SNAKE_2 = <<<EOT
    -----------
    -----------
    -----------
    -----------
    -----------
    ----S------
    ----T------
    -----------
    -----------
    -----------
    -----------
    EOT;

    public const string CURLY_SNAKE = <<<EOT
    -----------
    -----------
    -----------
    ---ttttT---
    ---S--tt---
    ------tt---
    -----------
    -----------
    -----------
    -----------
    -----------
    EOT;

    public const string FLOOD_FILL_SNAKE = <<<EOT
    -----------
    -----------
    -----------
    -----------
    -----------
    -----------
    -----------
    ----------S
    ttttttttttt
    ttttttttttt
    Ttttttttttt
    EOT;

    #[Test]
    #[DataProvider('provideBoardData')]
    public function parse_returns_expected_json(string $board, array $expected): void
    {
        $sut = new SUT();
        $result = $sut->parse($board);

        self::assertEquals($expected, $result);
    }

    #[Test]
    public function parse_returns_valid_path_for_multiples(): void
    {
        $sut = new SUT();
        $result = $sut->parse(self::FLOOD_FILL_SNAKE);
        $last = null;
        foreach ($result['you']['body'] as $segment) {
            if ($last !== null) {
                // check that the segments are adjacent
                $dx = \abs($segment['x'] - $last['x']);
                $dy = \abs($segment['y'] - $last['y']);
                self::assertTrue($dx + $dy === 1, "Segments are not adjacent: {$last['x']}, {$last['y']} to {$segment['x']}, {$segment['y']}");
            }
            $last = $segment;
        }
    }

    public static function provideBoardData(): \Generator
    {
        $examples = [
            'curly_snake' => self::CURLY_SNAKE,
            'head_and_tail_snake_1' => self::HEAD_AND_TAIL_SNAKE_1,
            'head_and_tail_snake_2' => self::HEAD_AND_TAIL_SNAKE_2,
            'single_head_snake' => self::SINGLE_HEAD_SNAKE,
            'snakes_and_food_1' => self::SNAKES_AND_FOOD_1,
        ];

        foreach ($examples as $label => $board) {
            $json = \file_get_contents(__DIR__ . "/json/{$label}.json");
            if ($json === false) {
                throw new \RuntimeException("Failed to read JSON file for {$label}");
            }
            $expected = \json_decode($json, true);
            yield $label => [
                $board,
                $expected,
            ];
        }
    }
}
