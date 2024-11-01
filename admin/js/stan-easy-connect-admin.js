(function( $ ) {
	'use strict';

	const settings = {
		client_id: 'client_id',
		client_secret: 'client_secret',
		redirect_uri: 'redirect_uri',
		test_connection_container: 'test_connection',
		test_connection_btn: 'test_connection_btn',
		test_result: 'test_result',
		auth_url: 'stan_api_auth_url',
	}

	const testConnection = {
		init: function() {
			$('#' + settings.test_connection_btn).click( (event) => {
				event.preventDefault();
				testConnection.run();
			});

			$( '#' + settings.test_connection_container ).append( '<p id="' + settings.test_result + '"></p>' );
		},
		run: function() {
			const clientID = $( '#' + settings.client_id ).val();
			const clientSecret = $( '#' + settings.client_secret ).val();
			const url = $( '#' + settings.auth_url ).val();

			$.ajax(url + '/v1/oauth', {
				type: 'GET',
				headers: {
					'Accept': 'application/json',
					'Authorization': 'Basic ' + btoa( clientID + ':' + clientSecret)
				},
			}).done((_data, _status, res) => {
				testConnection.displayResult(res.status);
			}).fail((res, _status, err) => {
				testConnection.displayResult(res.status, err);
			});
		},
		displayResult: function(status, err = undefined) {
			var msg = 'Votre configuration est parfaite ! Enregistrez vos modifications et votre Stan Connect sera prêt à accueillir les Stanners sur votre site';
			var success = true;
			if (status == 401) {
				msg = 'Votre identifiant client ou votre clé secrète ne sont pas valides. Trouvez vos identifiants Stan Connect sur votre compte Stan';
				success = false;
			} else if (status > 400 || typeof err !== 'undefined') {
				msg = 'Woops les serveurs de Stan ne répondent pas actuellement. Retentez plus tard les Stanners vont régler ce soucis';
				success = false;
			}

			const result = $( '#' + settings.test_result );
			result.removeClass();

			result.addClass(success ? 'result-success' : 'result-failed');

			result.text( function() {
				return msg;
			});

		}
	}

	const init = () => {
		testConnection.init();
	}

	$( init );

	$( '#display-logs-btn' ).click( function(evt) {
		evt.preventDefault();
		$( '#logger-table' ).toggle();
	});

	$( '.stan-connect-settings input[type=hidden]' ).parents( 'tr' ).css( 'display', 'none' );

})( jQuery );
