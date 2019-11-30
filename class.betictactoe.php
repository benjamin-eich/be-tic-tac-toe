<?php

class BETicTacToe {
    private static $initiated = false;

    public static function init() {
        if (!self::$initiated) {
            self::register_session_new();

            self::init_shortcodes();
            
            self::enqueue_frontend_stuff();

            add_action('wp_ajax_tictactoe_get_state', [self::class, 'game_ajax_get_state']);
            add_action('wp_ajax_tictactoe_set_move', [self::class, 'game_ajax_set_move']);
            add_action('wp_ajax_tictactoe_reset_state', [self::class, 'game_ajax_reset_state']);

            add_action('wp_ajax_nopriv_tictactoe_get_state', [self::class, 'game_ajax_get_state']);
            add_action('wp_ajax_nopriv_tictactoe_set_move', [self::class, 'game_ajax_set_move']);
            add_action('wp_ajax_nopriv_tictactoe_reset_state', [self::class, 'game_ajax_reset_state']);
            
            self::$initiated = true;
        }
    }

    private static function register_session_new(){
        if( !session_id() ) {
           session_start();
         }
    }

    private static function init_shortcodes() {
        add_shortcode(TICTACTOE__SHORTCODE_GAME, [self::class, 'shortcode_game']);
    }

    /**
     * Shortcodes
     */
    public static function shortcode_game($atts = [], $content = null) {
        $state = self::game_get_state();

        $content = '<div class="tictactoe" data-symbol-player-x="' . TICTACTOE__SYMBOL_PLAYER_X . '" data-symbol-player-o="' . TICTACTOE__SYMBOL_PLAYER_O . '">';
        
        if ($state->winner === null) {
            $content .= '  <div class="tictactoe_status">Next player: ' . ($state->xIsNext ? TICTACTOE__SYMBOL_PLAYER_X : TICTACTOE__SYMBOL_PLAYER_O) . '</div>';
        } else {
            $content .= '  <div class="tictactoe_status">Winner is: ' . $state->winner . '</div>';
        }
        
        $content .= '  <div class="tictactoe_squares">';
            for ($i=0; $i<9; $i++) {
                $content .= '    <div class="tictactoe_cell" rel="' . $i . '">' . ($state->squares[$i] ?? '') . '</div>';
            }
        $content .= '  </div>';
        $content .= '  <button class="tictactoe_reset">Reset Game</button>';
        $content .= '</div>';
        return $content;
    }

    public static function enqueue_frontend_stuff() {
        wp_enqueue_script( 'tic-tac-toe-javascript', plugins_url( '/js/tic-tac-toe.js', __FILE__ ), array('jquery') );

        // in JavaScript, object properties are accessed as ajax_object.ajax_url, ajax_object.we_value
        $ajax_nonce = wp_create_nonce( "tic-tac-toe-ajax-nonce" );
        wp_localize_script( 'tic-tac-toe-javascript', 'tic_tac_toe_ajax_object',
                array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'nonce' => $ajax_nonce ) );

        wp_enqueue_style( 'tic-tac-toe-styles', plugins_url( '/css/tic-tac-toe.css', __FILE__ ) );
    }

    public static function game_ajax_get_state() {
        check_ajax_referer( 'tic-tac-toe-ajax-nonce', 'security' );

        echo json_encode(self::game_get_state());
        wp_die();
    }

    public static function game_ajax_reset_state() {
        check_ajax_referer( 'tic-tac-toe-ajax-nonce', 'security' );

        self::game_reset_state();

        echo json_encode(self::game_get_state());
        wp_die();
    }

    public static function game_ajax_set_move() {
        check_ajax_referer( 'tic-tac-toe-ajax-nonce', 'security' );

        $square = $_POST['square'];
        $player = $_POST['player'];

        self::make_move($square, $player);

        // Do AI move
        $state = self::game_get_state();
        if ($state->xIsNext === true) {
            self::make_move($state->bestNextMove, TICTACTOE__SYMBOL_PLAYER_X);
        }

        echo json_encode(self::game_get_state());
        wp_die();
    }

    private static function make_move($square, $player) {
        
        $state = self::game_get_state();

        if ($state->squares[$square] === null) {
            $state->squares[$square] = $player;
            $state->xIsNext = !$state->xIsNext;
            $state->winner = self::game_get_winner($state->squares);

            self::minimax($state->squares, $state->xIsNext ? TICTACTOE__SYMBOL_PLAYER_X : TICTACTOE__SYMBOL_PLAYER_O, 0, $bestNextMove);
            $state->bestNextMove = $bestNextMove;
        }

        $_SESSION[TICTACTOE__SESSION_STATE_VAR_NAME] = $state;
    }

    private static function game_reset_state() {
        $_SESSION[TICTACTOE__SESSION_STATE_VAR_NAME] = new BETicTacToe_GameState();
    }

    private static function game_get_state() {
        if (
            !isset($_SESSION[TICTACTOE__SESSION_STATE_VAR_NAME])
            || get_class($_SESSION[TICTACTOE__SESSION_STATE_VAR_NAME]) !== BETicTacToe_GameState::class
            || $_SESSION[TICTACTOE__SESSION_STATE_VAR_NAME]::$version !== BETicTacToe_GameState::$version
            ) {
            self::game_reset_state();
        }

        return $_SESSION[TICTACTOE__SESSION_STATE_VAR_NAME];
    }

    private static function game_get_winner($squares) {
        $lines = [
            [0, 1, 2],
            [3, 4, 5],
            [6, 7, 8],
            [0, 3, 6],
            [1, 4, 7],
            [2, 5, 8],
            [0, 4, 8],
            [2, 4, 6],
          ];
          foreach ($lines as $line) {
            list($a, $b, $c) = $line;
            if ($squares[$a] !== null && $squares[$a] === $squares[$b] && $squares[$a] === $squares[$c]) {
              return $squares[$a];
            }
          }
          return null;
    }

    private static $playerRawValues = [
        TICTACTOE__SYMBOL_PLAYER_X => -1,
        TICTACTOE__SYMBOL_PLAYER_O => 1
    ];

    private static function minimax($board, $player, $depth = 0, &$bestNextMove = -1) {
        if (($winner = self::game_get_winner($board)) !== null) {
          return self::$playerRawValues[$winner] * self::$playerRawValues[$player]; // -1 * -1 || 1 * 1
        }
    
        $move = -1;
        $score = -2;
    
        for ($i = 0; $i < 9; ++$i) { // For all moves
            if ($board[$i] === null) { // Only possible moves
                $boardWithNewMove = $board; // Copy board to make it mutable
                $boardWithNewMove[$i] = $player; // Try the move
                $scoreForTheMove = -self::minimax($boardWithNewMove, ($player === TICTACTOE__SYMBOL_PLAYER_X ? TICTACTOE__SYMBOL_PLAYER_O : TICTACTOE__SYMBOL_PLAYER_X), $depth+1); // Count negative score for oponnent
                if ($scoreForTheMove > $score) {
                    $score = $scoreForTheMove;
                    $move = $i;
                } // Picking move that gives oponnent the worst score
            }
        }

        if ($depth === 0) {
            $bestNextMove = $move;
        }

        if ($move === -1) {
            return 0; // No move - it's a draw
        }

        return $score;
    }
}

class BETicTacToe_GameState implements JsonSerializable {
    public $squares;
    public $winner;
    public $xIsNext;
    public $bestNextMove;
    public static $version = 2;

    public function __construct() {
        $this->reset();
    }

    public function reset() {
        $this->squares = [
            null, null, null,
            null, null, null,
            null, null, null
        ];
        $this->winner = null;
        $this->xIsNext = false;
        $this->bestNextMove = null;
    }

    public function jsonSerialize() {
        return [
            "squares" => $this->squares,
            "winner" => $this->winner,
            "xIsNext" => $this->xIsNext,
            "bestNextMove" => $this->bestNextMove,
        ];
    }
}