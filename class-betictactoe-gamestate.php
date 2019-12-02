<?php
/**
 * BE Tic Tac Toe
 *
 * A little plugin that provides a shortcode to play Tic Tac Toe.
 *
 * @package           BETicTacToe
 * @author            Benjamin Eich
 * @copyright         2019 Your Name or Company Name
 * @license           GPL-2.0-or-later
 */

/**
 * BE Tic Tac Toe is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Tic Tac Toe is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tic Tac Toe. If not, see {URI to Plugin License}.
 */

/**
 * BETicTacToe_GameState
 *
 * This stores the game's state and handles serialization.
 * This is exchanged via ajax with the frontend.
 */
class BETicTacToe_GameState implements JsonSerializable {
	/**
	 * Contains the current state of the 9 squares.
	 *
	 * @var array $squares
	 */
	public $squares;

	/**
	 * Contains the winner if there is one.
	 *
	 * @var string $winner
	 */
	public $winner;

	/**
	 * Boolean showing who's turn it is.
	 *
	 * @var boolean $x_is_next
	 */
	public $x_is_next;

	/**
	 * Containes the field index of the next move AI would make.
	 *
	 * @var integer $best_next_move
	 */
	public $best_next_move;

	/**
	 * Contains the version for the gamestate schema.
	 *
	 * Upon initialization the gamestate will reset if an older version is stored in the session.
	 *
	 * @var integer $version
	 */
	public static $version = 2;

	/**
	 * Constructor resets the gamestate.
	 */
	public function __construct() {
		$this->reset();
	}

	/**
	 * Resets the gamestate to an empty gamestate.
	 */
	public function reset() {
		$this->squares        = array(
			null,
			null,
			null,
			null,
			null,
			null,
			null,
			null,
			null,
		);
		$this->winner         = null;
		$this->x_is_next      = false;
		$this->best_next_move = null;
	}

	/**
	 * Implementation of the serialization for JsonSerializable interface
	 */
	public function jsonSerialize() {
		return array(
			'squares'        => $this->squares,
			'winner'         => $this->winner,
			'x_is_next'      => $this->x_is_next,
			'best_next_move' => $this->best_next_move,
		);
	}
}
