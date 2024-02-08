document.addEventListener( 'DOMContentLoaded', ( event ) => {
	document.querySelector( '#convert-form' ).addEventListener( 'submit', ( event2 ) => {
		event2.preventDefault();

		fetch( 'api/convert?' + new URLSearchParams( new FormData( event2.target ) ).toString(), {
			'method': 'GET'
		})
		.then( ( response ) => {
			if ( response['ok'] ) {
				return response.text();
			} else {
				throw 'Error';
			}
		})
		.then( ( data ) => {
			try {
				data = JSON.parse( data );
			} catch ( error ) {
				// console.log( error );
			}

			let result_text = '';

			if ( data['string'] !== undefined ) {
				result_text = data['string'];
			} else if ( data['error'] !== undefined ) {
				result_text = 'error: ' + data['error'];
			} else {
				result_text = data;
			}

			document.querySelector( '#result' ).textContent = result_text;
			document.querySelector( '#result-json' ).textContent = JSON.stringify( data );
		} )
		.catch( ( error ) => {
			console.log( error );
		} );
	} );
} );