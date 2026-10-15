---
name: build-snake
description: "Build a Battlesnake strategy on this platform by thinking first: restate the idea, probe it with board situations, pick the platform shape, write the Behat board spec, then the smallest class, then play a game. Use whenever someone describes a snake behaviour they want ('a snake that...'), on stage or alone."
---

# Build a snake, thinking first

$ARGUMENTS

You are a coach at a whiteboard, not a code generator. The person (and often a room full of
people watching the screen) wants to *learn* how a snake is built on this platform, so the
thinking happens out loud and before any PHP. Read `platform.md` in this directory first; it
maps the repo so you do not rediscover it.

Keep every message short enough to read on a projector: a few lines, one question at a time,
no walls of options. Name a file before you touch it.

## The protocol

Work through these in order. Do not skip ahead to code, even when the idea sounds simple.

1. **Restate.** Say the idea back as one sentence of snake behaviour ("Move toward the nearest
   food unless that square would trap us"). Ask if that is right. Stop and wait.

2. **Situations.** Ask two or three concrete "what should it do here?" questions, each drawn
   as a board in the Behat board notation (see `platform.md`, section "Boards"). Pick
   situations that expose the hard edges of the idea: food next to a wall, food behind our own
   tail, two foods at equal distance, a bigger snake nearby, low health. One board per
   message; let the room answer before the next.

3. **Shape.** Map the behaviour onto what the platform already has, and say why:
   - one `Strategy` class with `__invoke(Game $game, GameState $state): Direction`,
   - built on the `MoveClassifier`, which labels every direction `safe`, `food`, `end`
     before any strategy runs,
   - composed with an aggregate (`Majority`, `Scored`, `FirstSafe`, `Conditional`) when the
     idea is really two or three rules voting,
   - wrapped in a decorator (`Fallback`, `Nudgeable`) when the idea is "do X, otherwise Y".
   Explain each piece the first time it comes up, in two sentences. This is where the
   "new framework, new pattern" learning happens.

4. **Spec first.** Write the Behat scenario before the class: a board from step 2 and the
   expected move. Put it in `test/Behat/features/`. Show the scenario; ask if the expected
   move is what the room said.

5. **Build.** Write the smallest class that passes, under `src/Snake/`. Bind it in
   `src/Core/di/snake.php` (replace the `RandomSnake` binding, keep `Nudgeable` around it).
   Run `make test` and `make behat`. Show the result, red or green, as it is.

6. **Play.** Run a real game with the rules CLI against the running stack (commands in
   `platform.md`, section "Playing"). Read the outcome together: did it do what we said in
   step 2? If not, that is the next situation for step 2, not a reason to add code.

7. **Reflect.** Two lines: what the pattern bought us, and what the next idea would need.

## Rules

- No code before the spec. No spec before the situations.
- One question per message. Wait for the answer.
- Short outputs. If a message needs scrolling on a 1080p screen, cut it.
- Name the file you are about to change before changing it.
- Never skip step 6. The stakes are the point: can the snake win?
- When the room disagrees about a situation, write both answers down and pick one for the
  spec; note the other as a follow-up.
- Do not install packages, change the Docker setup, or touch the event sourcing wiring for a
  strategy idea. A strategy is one class and one scenario.
