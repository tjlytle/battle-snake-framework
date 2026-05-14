<?php

declare(strict_types=1);

namespace BattleSnake\Tests\SnekSpec;

class Parser
{
    public function parse(string $board): array
    {
        // coords start from the bottom left corner
        $lines = \explode("\n", $board);
        $lines = \array_reverse($lines);

        $height = \count($lines);
        $width = \strlen($lines[0]);

        $i = -1;

        $food = [];
        $hazards = [];
        $snake_segments = [];

        foreach ($lines as $y => $line) {
            foreach (\str_split($line) as $x => $char) {
                $i++;
                if ($x === $width) {
                    if ($char !== "\n") {
                        throw new \UnexpectedValueException("Mock is missing expected newline at $i");
                    }
                    continue;
                }

                $coord = ['x' => $x, 'y' => $y];

                if ($char === '-') {
                    continue;
                }

                if ($char === '0') {
                    $food[] = $coord;
                    continue;
                }

                if ($char === '/') {
                    $hazards[] = $coord;
                    continue;
                }

                if (! \preg_match('#[A-Ya-y]#', $char)) {
                    throw new \UnexpectedValueException("Mock has unexpected character $char at $i");
                }

                if (! isset($snake_segments[$char])) {
                    $snake_segments[$char] = [];
                }

                $snake_segments[$char][] = $coord;
            }
        }

        // find heads as having only one coordinate and not having a matching body
        $heads = \array_filter($snake_segments, function (array $coords, string $key) use ($snake_segments): bool {
            return \count($coords) === 1 && ! isset($snake_segments[\strtolower($key)]);
        }, \ARRAY_FILTER_USE_BOTH);

        // sort heads by order to handle two segment snakes
        \ksort($heads);
        $heads = \array_filter($heads, fn(string $key): bool => ! isset($heads[\chr(\ord($key) - 1)]), \ARRAY_FILTER_USE_KEY);

        $snakes = [];
        foreach ($heads as $head => $head_coords) {
            $snake = $this->traverseSnakeBody((string)$head, $snake_segments);

            $snakes[] = [
                'id' => \strtolower($head),
                'name' => \strtolower($head),
                'head' => $head_coords[0],
                'body' => $snake,
                'length' => \count($snake),
                'health' => 90,
                'shout' => 'boo!',
                'squad' => '',
                'latency' => '111',
            ];
        }

        return [
            'game' => [
                'id' => 'generated-scenario',
                'ruleset' => [
                    'name' => 'standard',
                    'version' => '1.2.3',
                ],
                'timeout' => 500,
            ],
            'turn' => 1,
            'board' => [
                'height' => $height,
                'width' => $width,
                'food' => $food,
                'hazards' => $hazards,
                'snakes' => $snakes,
            ],
            'you' => $snakes[0],
        ];
    }

    private function traverseSnakeBody(string $head, array $snake_segments)
    {
        $coords = [];
        $coords[] = $snake_segments[$head][0];

        $tail = \chr(\ord($head) + 1);

        if (! isset($snake_segments[$tail])) {
            // single segment snake
            return $coords;
        }

        $body = \strtolower($tail);

        if (! isset($snake_segments[$body])) {
            // two segment snake
            $coords[] = $snake_segments[$tail][0];
            return $coords;
        }

        $coords = \array_merge($coords, $snake_segments[$body], $snake_segments[$tail]);

        $length = \count($coords);
        for ($i = 0; $i < $length; $i++) {
            for ($j = 0; $j < $length; $j++) {
                if ($i === $j) {
                    continue;
                }
                $dx = \abs($coords[$i]['x'] - $coords[$j]['x']);
                $dy = \abs($coords[$i]['y'] - $coords[$j]['y']);
                if ($dx + $dy === 1) {
                    $adj[$i][] = $j;
                }
            }
        }

        $paths = [];
        $visited = [];
        $visited[0] = true;
        $path = [0];

        $this->findPaths(0, $length - 1, $length, $adj, $visited, $path, $paths);

        if (empty($paths)) {
            throw new \Exception("No path from head to tail found.");
        }

        $bestPath = $paths[0];

        $bodyCoords = [];
        foreach ($bestPath as $idx) {
            $bodyCoords[] = ['x' => $coords[$idx]['x'], 'y' => $coords[$idx]['y']];
        }

        return $bodyCoords;
    }

    private function findPaths(
        int $current,
        int $tailIndex,
        int $N,
        array $adj,
        array &$visited,
        array &$path,
        array &$paths,
    ) {
        if ($current === $tailIndex) {
            // only record if we've hit every segment exactly once
            if (\count($path) === $N) {
                $paths[] = $path;
            }
            return;
        }

        foreach ($adj[$current] as $nbr) {
            if (! isset($visited[$nbr])) {
                $visited[$nbr] = true;
                $path[] = $nbr;

                $this->findPaths($nbr, $tailIndex, $N, $adj, $visited, $path, $paths);
                if (\count($paths) > 0) {
                    return;
                }
                \array_pop($path);
                unset($visited[$nbr]);
            }
        }
    }
}
