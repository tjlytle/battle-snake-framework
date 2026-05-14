<?php

declare(strict_types=1);

namespace BattleSnake\Tests\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use BattleSnake\Core\Application;
use BattleSnake\Core\ApplicationFactory;
use BattleSnake\Domain\Direction;
use BattleSnake\Tests\SnekSpec\Parser as SnekSpecParser;
use Doctrine\DBAL\Schema\PrimaryKeyConstraint;
use Laminas\Diactoros\ServerRequest;
use Laminas\Diactoros\StreamFactory;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;

/**
 * Drives the platform end-to-end through the in-process PSR-7 stack.
 * No HTTP server needed; we hand the QueueRequestHandler a built request.
 */
final class FeatureContext implements Context
{
    private Application $app;
    private ?array $stateJson = null;
    private ?ResponseInterface $response = null;
    private string $gameId;

    public function __construct()
    {
        // Crell\Serde 1.5 emits an array_key_exists(null, ...) deprecation on PHP 8.5;
        // Behat treats those as fatal. Drop once upstream is patched.
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

        $this->app = (new ApplicationFactory())->make([
            // Force in-memory SQLite for Behat runs; migrations are applied below.
            [
                \BattleSnake\Core\Config::class => \DI\factory(static fn() => new \BattleSnake\Core\Config('sqlite:///:memory:')),
            ],
        ]);

        $this->gameId = Uuid::uuid4()->toString();
        $this->migrate();
    }

    private function migrate(): void
    {
        $conn = $this->app->getContainer()->get(\Doctrine\DBAL\Connection::class);
        $platform = $conn->getDatabasePlatform();

        $messages = new \Doctrine\DBAL\Schema\Schema();
        $t1 = $messages->createTable('domain_messages');
        $t1->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
        $t1->addColumn('event_id', 'string', ['length' => 36, 'notnull' => true]);
        $t1->addColumn('aggregate_root_id', 'string', ['length' => 36, 'notnull' => true]);
        $t1->addColumn('version', 'integer', ['notnull' => true]);
        $t1->addColumn('payload', 'text', ['notnull' => true]);
        $t1->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('id')->create());
        $t1->addUniqueIndex(['aggregate_root_id', 'version']);

        $t2 = $messages->createTable('game_stats');
        $t2->addColumn('aggregate_id', 'string', ['length' => 36, 'notnull' => true]);
        $t2->addColumn('win', 'boolean');
        $t2->addColumn('length', 'integer');
        $t2->addColumn('survived', 'integer');
        $t2->addPrimaryKeyConstraint(PrimaryKeyConstraint::editor()->setUnquotedColumnNames('aggregate_id')->create());

        foreach ($messages->toSql($platform) as $sql) {
            $conn->executeStatement($sql);
        }
    }

    /**
     * @Given the board is:
     */
    public function theBoardIs(PyStringNode $ascii): void
    {
        $parsed = (new SnekSpecParser())->parse($ascii->getRaw());
        $parsed['game']['id'] = $this->gameId;
        $this->stateJson = $parsed;
    }

    /**
     * @When the snake receives a :endpoint request
     */
    public function theSnakeReceivesARequest(string $endpoint): void
    {
        $request = $this->buildRequest($endpoint, $this->stateJson ?? []);
        $this->response = $this->app->handle($request);
    }

    /**
     * @Then the response status is :status
     */
    public function theResponseStatusIs(int $status): void
    {
        Assert::assertSame($status, $this->response?->getStatusCode());
    }

    /**
     * @Then the snake moves :direction
     */
    public function theSnakeMoves(string $direction): void
    {
        $body = (string) $this->response?->getBody();
        $data = \json_decode($body, true);
        Assert::assertIsArray($data);
        Assert::assertArrayHasKey('move', $data);
        $expected = Direction::tryFrom(\strtolower($direction));
        Assert::assertNotNull($expected, "Unknown direction: $direction");
        Assert::assertSame($expected->value, $data['move']);
    }

    /**
     * @Then the snake moves in some valid direction
     */
    public function theSnakeMovesAnyDirection(): void
    {
        $body = (string) $this->response?->getBody();
        $data = \json_decode($body, true);
        Assert::assertIsArray($data);
        Assert::assertArrayHasKey('move', $data);
        Assert::assertContains($data['move'], ['up', 'down', 'left', 'right']);
    }

    private function buildRequest(string $endpoint, array $body): ServerRequestInterface
    {
        $stream = (new StreamFactory())->createStream(\json_encode($body));
        return (new ServerRequest([], [], $endpoint, 'POST', $stream))
            ->withHeader('Content-Type', 'application/json');
    }
}
