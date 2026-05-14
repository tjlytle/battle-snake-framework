Feature: Snake responds to /move with a valid direction
  In order to play Battlesnake
  As the snake server
  I must return one of up/down/left/right for any well-formed /move request

  Scenario: Starts a new game then moves
    Given the board is:
      """
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
      """
    When the snake receives a "/start" request
    Then the response status is 204
    When the snake receives a "/move" request
    Then the snake moves in some valid direction
