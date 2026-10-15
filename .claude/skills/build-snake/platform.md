# The platform, mapped

A PHP 8.5 Battlesnake platform: bring a strategy class, get a deployable HTTP service.
PSR-7/PSR-15 HTTP (laminas-diactoros), PHP-DI 7, EventSauce 3 event sourcing, Doctrine
DBAL/ORM/Migrations, SQLite by default, Docker Compose with Caddy + PHP-FPM.

## Where things are

| Piece | Path | What it is |
|---|---|---|
| Strategy interface | `src/Strategy/Strategy.php` | `__invoke(Game $game, GameState $state): Direction` |
| Sample snake | `src/Snake/RandomSnake.php` | prefers safe food moves, then safe moves, then anything that is not `END` |
| Move classifier | `src/Analyzer/MoveClassifier.php`, `src/Analyzer/Move*.php` | labels each of the four directions with classifications (`SAFE`, `FOOD`, `END`, ...) from the board |
| Aggregates | `src/Strategy/Aggregate/{Majority,Scored,FirstSafe,Conditional}.php`, `src/Strategy/Aggregate.php` | combine several strategies: vote, score, first safe answer, or pick by condition |
| Decorators | `src/Strategy/Decorator/{Fallback,Nudgeable}.php` | wrap a strategy: fall back to another when it has no answer; let `POST /manual` nudge a direction |
| Domain | `src/Domain/{Game,GameState,Board,Battlesnake,Coordinate,Direction,Ruleset,...}.php` | value objects parsed from the request JSON (`src/Domain/Parser/*`) |
| HTTP | `src/Http/Handler/{Info,Start,Move,End,Manual}Handler.php`, `src/Http/Middleware/*`, `src/Core/QueueRequestHandler.php` | PSR-15 handlers and the middleware queue |
| DI | `src/Core/di/{core,database,eventsourcing,handlers,snake}.php` | PHP-DI definitions; `snake.php` binds `Strategy` |
| Event sourcing | `src/EventSourcing/{Game,GameId,Events/*,Projection/*}.php` | `Game` aggregate with Start, Turn, Move, Nudge, End events; `GameStat` projection |
| Tests | `test/Unit/*`, `test/Behat/features/moves.feature`, `test/SnekSpec/Parser.php` | PHPUnit, Behat with ASCII boards |
| Spec | `open-api.yaml` | the Battlesnake API this serves |

## Swapping in a snake

`src/Core/di/snake.php`:

```php
Strategy::class => DI\get(Nudgeable::class),
Nudgeable::class => DI\autowire()->constructorParameter('fallback', DI\get(RandomSnake::class)),
RandomSnake::class => DI\autowire(),
```

Add your class under `src/Snake/`, autowire it, and point the `fallback` parameter at it.
The classifier is injected by type: `public function __construct(private readonly MoveClassifier $classifier)`.

A strategy body usually looks like:

```php
foreach (($this->classifier)($state) as $move) {
    if ($move->is(Classification::END)) continue;      // walls, bodies, certain death
    if ($move->is(Classification::SAFE) && $move->is(Classification::FOOD)) ...
    $move->direction;                                   // a Direction
}
```

## Boards

Behat scenarios draw the board as text (`test/Behat/features/moves.feature`), 11 columns by
11 rows, top row first. The parser is `test/SnekSpec/Parser.php`; read it for the exact
legend before writing a new board. Conventions seen in the existing feature:

- `-` empty square
- `0` food
- `S` our head, `s` our body, `T`/`t` tails (upper case = ours)
- `U`/`V` and lower-case letters: other snakes' heads and bodies

A scenario:

```gherkin
Scenario: Takes the food next to its head
  Given the board is:
    """
    -----------
    ...
    """
  When the snake receives a "/move" request
  Then the snake moves "up"
```

Check `test/Behat/bootstrap/FeatureContext.php` for the step definitions that exist
(`the snake moves in some valid direction` is there; add an exact-direction step if missing).

## Running

```bash
make build && make install && make migrate && make up    # http://localhost:8088
make test        # PHPUnit
make behat       # the board scenarios
make stan        # phpstan (currently fails on master, pre-existing)
make cs          # phpcs (same)
make down
```

`make up PORT=9099` for a second instance. Logs: `docker compose logs php`.

## Playing

The official rules CLI (`battlesnake`, from github.com/BattlesnakeOfficial/rules releases)
plays any snake it can reach by URL:

```bash
battlesnake play -W 11 -H 11 --name mine --url http://localhost:8088 -t 1000 -d 300
battlesnake play -W 11 -H 11 --name mine --url http://localhost:8088 --name you --url https://their-snake.example -d 300 --browser
```

`--browser` opens board.battlesnake.com against a local engine on :8080 (run the CLI on the
machine whose screen shows the board). `-d` is the per-turn delay in ms, `-g royale` or
`constrictor` changes the ruleset. A clean game prints turn lines and
"Game completed after N turns" with no WARN/ERROR lines.

Tim's snake is published at https://snake.lytlelabs.com through a Cloudflare tunnel. A laptop
snake goes online with `cloudflared tunnel --url http://localhost:8088` or `ngrok http 8088`.

Known edge: two snakes served from the same URL in one game collide on the aggregate version
(the aggregate id is the game id alone). One snake per server.
