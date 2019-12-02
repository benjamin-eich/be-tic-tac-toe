<?php
/**
 * BE Tic Tac Toe
 *
 * @package           BETicTacToe
 * @author            Benjamin Eich
 * @copyright         2019 Your Name or Company Name
 * @license           GPL-2.0-or-later
 *
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
 * This stores the game's state and handles serialization
 */
class BETicTacToe_GameState implements JsonSerializable {
	public $squares;
	public $winner;
	public $x_is_next;
	public $best_next_move;
	public static $version = 2;

	public function __construct() {
		$this->reset();
	}

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

	public function jsonSerialize() {
		return array(
			'squares'        => $this->squares,
			'winner'         => $this->winner,
			'x_is_next'      => $this->x_is_next,
			'best_next_move' => $this->best_next_move,
		);
	}
}
