<?php
/**
 * BE Tic Tac Toe
 *
 * @package           BETicTacToe
 * @author            Benjamin Eich
 * @copyright         2019 Your Name or Company Name
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: BE Tic Tac Toe
 * Description: Plugin provides a shortcode to place a tic tac toe game on a page / post
 * Version: 1.0.0
 * Author: Benjamin Eich
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: be-tic-tac-toe
 */

 /*
BE Tic Tac Toe is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Tic Tac Toe is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with Tic Tac Toe. If not, see {URI to Plugin License}.
*/

define( 'TICTACTOE_VERSION', '1.0.0' );
define( 'TICTACTOE__MINIMUM_WP_VERSION', '5.2' );
define( 'TICTACTOE__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'TICTACTOE__TEXTDOMAIN', 'be-tic-tac-toe' );
define( 'TICTACTOE__SHORTCODE_GAME', 'be-tic-tac-toe' );
define( 'TICTACTOE__SESSION_STATE_VAR_NAME', 'be-tic-tac-toe-state' );
define( 'TICTACTOE__SYMBOL_PLAYER_O', '◯' );
define( 'TICTACTOE__SYMBOL_PLAYER_X', '╳' );

require_once( TICTACTOE__PLUGIN_DIR . 'class.betictactoe.php' );

add_action('init', ['BETicTacToe', 'init']);
