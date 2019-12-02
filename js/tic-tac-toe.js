let tictactoe = {
    'state': null,
    'game_element': null,
    'player_symbol_o': 'O',
    'player_symbol_x': 'X',
    'request_running': false,

    init(game_element_selector) {
        // TODO: Allow multiple instances
        tictactoe.game_element = jQuery(game_element_selector).first();
        tictactoe.player_symbol_o = tictactoe.game_element.data('symbolPlayerO');
        tictactoe.player_symbol_x = tictactoe.game_element.data('symbolPlayerX');

        tictactoe.game_element.find('.tictactoe_cell').on('click', (event) => {
            let i = event.srcElement.getAttribute('rel');
            tictactoe.set_move(i);
        });
    
        tictactoe.game_element.find('button.tictactoe_reset').on('click', (event) => {
            event.preventDefault();
            tictactoe.reset_game_state();
        });

        tictactoe.load_game_state();
    },
    load_game_state() {
        var request = {
            'action': 'tictactoe_get_state',
            'security': tic_tac_toe_ajax_object.nonce,
        };
    
        jQuery.post(tic_tac_toe_ajax_object.ajax_url, request, function(data) {
            tictactoe.state = data;
            tictactoe.render_game_state();
        }, 'json');
    },
    reset_game_state() {
        var request = {
            'action': 'tictactoe_reset_state',
            'security': tic_tac_toe_ajax_object.nonce,
        };
    
        jQuery.post(tic_tac_toe_ajax_object.ajax_url, request, function(data) {
            tictactoe.state = data;
            tictactoe.render_game_state();
        }, 'json');
    },
    render_game_state() {
        tictactoe.game_element.find('.tictactoe_cell.hint').removeClass('hint');
        for (var i in tictactoe.state.squares) {
            switch (tictactoe.state.squares[i]) {
                case null:
                        tictactoe.game_element.find('.tictactoe_cell[rel="' + i + '"]').html('');
                        if (i == tictactoe.state.best_next_move) {
                            tictactoe.game_element.find('.tictactoe_cell[rel="' + i + '"]').addClass('hint');
                        }
                    break;
                default:
                        tictactoe.game_element.find('.tictactoe_cell[rel="' + i + '"]').html(tictactoe.state.squares[i]);
                    break;
            }
        }
    
        if (tictactoe.state.winner === null && tictactoe.state.best_next_move !== -1) {
            tictactoe.game_element.find('.tictactoe_status').text('Next player: ' + (tictactoe.state.x_is_next ? tictactoe.player_symbol_x : tictactoe.player_symbol_o));
        } else if (tictactoe.state.winner === null && tictactoe.state.best_next_move === -1) {
            tictactoe.game_element.find('.tictactoe_status').text('Good game! It\'s tied.');
        } else {
            tictactoe.game_element.find('.tictactoe_status').text('Winner is: ' + tictactoe.state.winner);
        }
    },
    set_move(i) {
        if (!tictactoe.request_running && tictactoe.state.squares[i] === null) {
            tictactoe.request_running = true;
    
            let player_symbol = tictactoe.state.x_is_next ? tictactoe.player_symbol_x : tictactoe.player_symbol_o;
    
            var request = {
                'action': 'tictactoe_set_move',
                'security': tic_tac_toe_ajax_object.nonce,
                'square': i,
                'player': player_symbol
            };
            
            jQuery.post(tic_tac_toe_ajax_object.ajax_url, request, function(data) {
                tictactoe.state = data;
                tictactoe.render_game_state();
                tictactoe.request_running = false;
            }, 'json');
    
            tictactoe.game_element.find('.tictactoe_cell[rel="' + i + '"]').html(player_symbol);
        }
    }
};

jQuery(function () {
    if (jQuery('.tictactoe').length === 0) {
        return;
    }
    
    tictactoe.init('.tictactoe');
});