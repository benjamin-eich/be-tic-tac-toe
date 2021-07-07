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
class BETicTacToe_Player implements JsonSerializable {
	public static $PLAYER_O = 'o';
	public static $PLAYER_X = 'x';
	
	private $player = null;
	
	public function __construct($player) {
		$this->player = $player;
	}
}
