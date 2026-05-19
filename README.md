# battlesnake-platform

A PHP platform for building [Battlesnake](https://play.battlesnake.com/) bots. Bring a strategy class, get a deployable HTTP service.

- **PHP 8.5** with PSR-7/PSR-15 HTTP, PHP-DI 7, EventSauce 3 event sourcing, Doctrine DBAL/ORM/Migrations.
- **SQLite by default** (zero config), **MySQL** optional via a Compose profile.
- **Docker Compose** stack with Caddy + PHP-FPM, plus the official `battlesnake/rules` image for local play.

## Requirements

- Docker + Docker Compose, **or** PHP 8.5 with the `pdo_sqlite`/`pdo_mysql` extensions and Composer.
- `make` (the Makefile wraps the common Compose commands; everything works without it too).

## Quick start

```bash
make build       # build the dev image
make install     # composer install (inside the container)
make migrate     # create the schema in var/snake.db
make up          # start php-fpm + caddy on http://localhost:8088
```

Point the Battlesnake engine (or `make play`) at `http://localhost:8088`. To use a different port: `make up PORT=9099`.

`make help` lists every target.

## Build your snake (3 steps)

### 1. Write

Create a class under `src/Snake/` that implements `BattleSnake\Strategy\Strategy`:

```php
namespace BattleSnake\Snake;

use BattleSnake\Domain\Direction;
use BattleSnake\Domain\GameState;
use BattleSnake\EventSourcing\Game;
use BattleSnake\Strategy\Strategy;

final class MySnake implements Strategy
{
    public function __invoke(Game $game, GameState $state): Direction
    {
        // $state  — the current turn's GameState (board, snakes, you, etc.)
        // $game   — the aggregate; expose history via public read-only props
        return Direction::UP;
    }
}
```

Useful building blocks:

- `src/Domain/` — `Board`, `Battlesnake`, `Coordinate`, `Direction`, `GameState`. Methods like `Board::contains()`, `Board::isThreatenedFor()`, `Battlesnake::couldReach()`, `Coordinate::adjacent()` give you cheap predicates instead of hand-rolled grid math.
- `src/Analyzer/MoveClassifier` — for each direction returns a `Move` flagged `SAFE` / `FOOD` / `END`. `RandomSnake` is the reference consumer.
- `src/Strategy/Aggregate/` — `FirstSafe`, `Majority`, `Scored`, `Conditional`. Composition primitives: each takes `Strategy ...$strategies` and picks one to invoke.
- `src/Strategy/Decorator/` — `Fallback`, `Nudgeable`. Wrap any strategy to add behaviour. `Nudgeable` lets `/manual` override the next move.

### 2. Test

Drop a SnekSpec ASCII board into a PHPUnit test and ask your strategy what it would do:

```
-----
--S--      S = your head, t = your tail
--t--      U/u = opponent, 0 = food, / = hazard
-----
```

See `test/Unit/Domain/BoardTest.php` for the parser pattern. Aggregate-level given/when/then scenarios live in `test/Unit/EventSourcing/GameTest.php` (extend `BattleSnake\Tests\Support\AggregateRootTestCase`).

Run: `make test`.

### 3. Wire

Edit `src/Core/di/snake.php` and point the `Strategy::class` binding at your class:

```php
return [
    Strategy::class => DI\get(MySnake::class),
    MySnake::class  => DI\autowire(),
];
```

Restart (`make restart`) and your snake is live at `/`, `/start`, `/move`, `/end`.

## HTTP endpoints

| Method | Path     | Notes                                                         |
| ------ | -------- | ------------------------------------------------------------- |
| GET    | `/`      | Battlesnake `info` response.                                  |
| POST   | `/start` | Engine notifies us a game is starting.                        |
| POST   | `/move`  | Engine asks for a move; returns `{"move":"up\|down\|left\|right"}`. |
| POST   | `/end`   | Engine notifies us a game ended; stats row gets written.      |
| POST   | `/manual`| Push a one-shot direction nudge for the next `/move`. Requires the `Nudgeable` decorator (default).|

The OpenAPI spec is checked into `open-api.yaml` and is enforced by the Behat scenario.

## Configuration

| Env var      | Default                                          | Notes |
| ------------ | ------------------------------------------------ | ----- |
| `SNAKE_DSN`  | `sqlite:////var/www/html/var/snake.db` in Docker | SQLite absolute paths need **four** slashes after `sqlite:`. For MySQL: `mysql://battlesnake:battlesnake@mysql:3306/battlesnake`. |
| `PORT`       | `8088`                                           | Host port for Caddy. Used by `make up PORT=N`. |
| `XDEBUG_MODE`| `off`                                            | Set to `debug,develop` to enable Xdebug. |

### MySQL instead of SQLite

```bash
make mysql-up                                # starts the mysql service
SNAKE_DSN='mysql://battlesnake:battlesnake@mysql:3306/battlesnake' make migrate
SNAKE_DSN='mysql://battlesnake:battlesnake@mysql:3306/battlesnake' make up
```

(Persist by adding `SNAKE_DSN=...` to a `.env` file in the repo root — Compose picks it up automatically.)

### Play a local game

With the stack up:

```bash
make play
```

This runs the official `battlesnake play` rules engine against your snake at `http://caddy`.

### Expose your local snake to the Battlesnake site

To register your snake on [play.battlesnake.com](https://play.battlesnake.com/) and play against others' bots, the site needs to reach your machine over HTTPS. The easiest way during a hackathon is [ngrok](https://ngrok.com/):

```bash
make up                       # snake listening on localhost:8088
ngrok http 8088               # in another terminal
```

ngrok prints a `https://<random>.ngrok-free.app` URL — paste that as the URL when you create your Battlesnake on the site. The tunnel stays up as long as the `ngrok` process is running; the URL changes on each restart unless you have a reserved domain. Watch the ngrok inspector at `http://127.0.0.1:4040` to see every `/start`, `/move`, and `/end` the engine fires at you — invaluable for debugging.

## Quality gate

```bash
make check      # phpunit + behat + phpstan + phpcs
```

Or individually: `make test`, `make behat`, `make stan`, `make cs`.

## Running without Docker

```bash
composer install
composer migrate            # creates var/snake.db
composer serve              # php -S localhost:8000 -t public
```

## Layout

```
src/
  Analyzer/      MoveClassifier and Move value objects
  Core/          Config, DI definitions (src/Core/di/*.php)
  Domain/        Board, Battlesnake, Coordinate, Direction, GameState, parsers
  EventSourcing/ Game aggregate, events, serializer
  Http/          PSR-15 middleware, route handlers
  Snake/         RandomSnake (replace or supplement with your own)
  Strategy/      Strategy interface + Aggregate/ and Decorator/ composition primitives
test/
  Unit/          Domain + aggregate unit tests
  Behat/         End-to-end OpenAPI-validated scenario
  SnekSpec/      ASCII board parser used by tests
database/
  migrations/    Doctrine migrations (event_messages, snapshots, game_stats)
docker/          Caddyfile, php.ini, FPM config
```
