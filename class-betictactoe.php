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

require_once 'class-betictactoe-gamestate.php';

/**
 * BETicTacToe
 *
 * This is core class
 */
class BETicTacToe {
	/**
	 * Keeps initiated state
	 *
	 * @var $initiated remembers if module is already initiated
	 */
	private static $initiated = false;

	/**
	 * Initiates the plugin and registers all required stuff
	 */
	public static function init() {
		if ( ! self::$initiated ) {
			self::register_session_new();

			self::init_shortcodes();

			self::enqueue_frontend_stuff();

			add_action( 'wp_ajax_tictactoe_get_state', array( self::class, 'game_ajax_get_state' ) );
			add_action( 'wp_ajax_tictactoe_set_move', array( self::class, 'game_ajax_set_move' ) );
			add_action( 'wp_ajax_tictactoe_reset_state', array( self::class, 'game_ajax_reset_state' ) );

			add_action( 'wp_ajax_nopriv_tictactoe_get_state', array( self::class, 'game_ajax_get_state' ) );
			add_action( 'wp_ajax_nopriv_tictactoe_set_move', array( self::class, 'game_ajax_set_move' ) );
			add_action( 'wp_ajax_nopriv_tictactoe_reset_state', array( self::class, 'game_ajax_reset_state' ) );

			self::$initiated = true;
		}
	}

	private static function register_session_new() {
		if ( ! session_id() ) {
			session_start();
		}
	}

	private static function init_shortcodes() {
		add_shortcode( TICTACTOE__SHORTCODE_GAME, array( self::class, 'shortcode_game' ) );
	}

	/**
	 * Shortcodes
	 */
	public static function shortcode_game( $atts = array(), $content = null ) {
		$state = self::game_get_state();

		$content = '<div class="tictactoe" data-symbol-player-x="' . TICTACTOE__SYMBOL_PLAYER_X . '" data-symbol-player-o="' . TICTACTOE__SYMBOL_PLAYER_O . '">';

		if ( null === $state->winner ) {
			$content .= '  <div class="tictactoe_status">Next player: ' . ( $state->x_is_next ? TICTACTOE__SYMBOL_PLAYER_X : TICTACTOE__SYMBOL_PLAYER_O ) . '</div>';
		} else {
			$content .= '  <div class="tictactoe_status">Winner is: ' . $state->winner . '</div>';
		}

		$content .= '  <div class="tictactoe_squares">';
		for ( $i = 0; $i < 9; $i++ ) {
			$content .= '    <div class="tictactoe_cell" rel="' . $i . '">' . ( $state->squares[ $i ] ?? '' ) . '</div>';
		}
		$content .= '  </div>';
		$content .= '  <button class="tictactoe_reset">Reset Game</button>';
		$content .= '</div>';
		return $content;
	}

	public static function enqueue_frontend_stuff() {
		wp_enqueue_script( 'tic-tac-toe-javascript', plugins_url( '/js/tic-tac-toe.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );

		// in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
		$ajax_nonce = wp_create_nonce( 'tic-tac-toe-ajax-nonce' );
		wp_localize_script(
			'tic-tac-toe-javascript',
			'tic_tac_toe_ajax_object',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => $ajax_nonce,
			)
		);

		wp_enqueue_style( 'tic-tac-toe-styles', plugins_url( '/css/tic-tac-toe.css', __FILE__ ), array(), '1.0.0' );
	}

	public static function game_ajax_get_state() {
		check_ajax_referer( 'tic-tac-toe-ajax-nonce', 'security' );

		echo wp_json_encode( self::game_get_state() );
		wp_die();
	}

	public static function game_ajax_reset_state() {
		check_ajax_referer( 'tic-tac-toe-ajax-nonce', 'security' );

		self::game_reset_state();

		echo wp_json_encode( self::game_get_state() );
		wp_die();
	}

	public static function game_ajax_set_move() {
		check_ajax_referer( 'tic-tac-toe-ajax-nonce', 'security' );

		$square = $_POST['square'];
		$player = $_POST['player'];

		self::make_move( $square, $player );

		// Do AI move
		$state = self::game_get_state();
		if ( true === $state->x_is_next ) {
			self::make_move( $state->best_next_move, TICTACTOE__SYMBOL_PLAYER_X );
		}

		echo wp_json_encode( self::game_get_state() );
		wp_die();
	}

	private static function make_move( $square, $player ) {

		$state = self::game_get_state();

		if ( null === $state->squares[ $square ] ) {
			$state->squares[ $square ] = $player;
			$state->x_is_next          = ! $state->x_is_next;
			$state->winner             = self::game_get_winner( $state->squares );

			self::minimax( $state->squares, $state->x_is_next ? TICTACTOE__SYMBOL_PLAYER_X : TICTACTOE__SYMBOL_PLAYER_O, 0, $best_next_move );
			$state->best_next_move = $best_next_move;
		}

		$_SESSION[ TICTACTOE__SESSION_STATE_VAR_NAME ] = $state;
	}

	private static function game_reset_state() {
		$_SESSION[ TICTACTOE__SESSION_STATE_VAR_NAME ] = new BETicTacToe_GameState();
	}

	private static function game_get_state() {
		if (
			! isset( $_SESSION[ TICTACTOE__SESSION_STATE_VAR_NAME ] )
			|| BETicTacToe_GameState::class !== get_class( $_SESSION[ TICTACTOE__SESSION_STATE_VAR_NAME ] )
			|| BETicTacToe_GameState::$version !== $_SESSION[ TICTACTOE__SESSION_STATE_VAR_NAME ]::$version
			) {
			self::game_reset_state();
		}

		return $_SESSION[ TICTACTOE__SESSION_STATE_VAR_NAME ];
	}

	private static function game_get_winner( $squares ) {
		$lines = array(
			array( 0, 1, 2 ),
			array( 3, 4, 5 ),
			array( 6, 7, 8 ),
			array( 0, 3, 6 ),
			array( 1, 4, 7 ),
			array( 2, 5, 8 ),
			array( 0, 4, 8 ),
			array( 2, 4, 6 ),
		);
		foreach ( $lines as $line ) {
			list($a, $b, $c) = $line;
			if ( null !== $squares[ $a ] && $squares[ $a ] === $squares[ $b ] && $squares[ $a ] === $squares[ $c ] ) {
				return $squares[ $a ];
			}
		}
		return null;
	}

	private static $player_raw_values = array(
		TICTACTOE__SYMBOL_PLAYER_X => -1,
		TICTACTOE__SYMBOL_PLAYER_O => 1,
	);

	private static function minimax( $board, $player, $depth = 0, &$best_next_move = -1 ) {
		$winner = self::game_get_winner( $board );
		if ( null !== $winner ) {
			return self::$player_raw_values[ $winner ] * self::$player_raw_values[ $player ]; // -1 * -1 || 1 * 1
		}

		$move  = -1;
		$score = -2;

		for ( $i = 0; $i < 9; ++$i ) { // For all moves.
			if ( null === $board[ $i ] ) { // Only possible moves.
				$board_with_new_move       = $board; // Copy board to make it mutable.
				$board_with_new_move[ $i ] = $player; // Try the move.
				$score_for_the_move        = -self::minimax( $board_with_new_move, ( TICTACTOE__SYMBOL_PLAYER_X === $player ? TICTACTOE__SYMBOL_PLAYER_O : TICTACTOE__SYMBOL_PLAYER_X ), $depth + 1 ); // Count negative score for oponnent
				if ( $score_for_the_move > $score ) {
					$score = $score_for_the_move;
					$move  = $i;
				} // Picking move that gives oponnent the worst score.
			}
		}

		if ( 0 === $depth ) {
			$best_next_move = $move;
		}

		if ( -1 === $move ) {
			return 0; // No move - it's a draw.
		}

		return $score;
	}
}
