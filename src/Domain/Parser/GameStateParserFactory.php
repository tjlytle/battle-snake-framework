<?php

declare(strict_types=1);

namespace BattleSnake\Domain\Parser;

final class GameStateParserFactory
{
    public static function make(): GameStateParser
    {
        $coordinateFactory = new CoordinateFactory();
        $battlesnakeParser = new BattlesnakeParser($coordinateFactory);
        $rulesetSettingsParser = new RulesetSettingsParser();

        return new GameStateParser(
            new GameParser($rulesetSettingsParser),
            new BoardParser($battlesnakeParser, $coordinateFactory),
            $battlesnakeParser,
        );
    }
}
