( function ( $ ) {
	'use strict';

	const MODE_FIELD = '_wccg_visibility_mode';
	const RESTRICTED_MODE = 'restricted';

	function toggleAllowedGroups() {
		const mode = $( 'input[name="' + MODE_FIELD + '"]:checked' ).val();
		const $field = $( '.wccg-allowed-groups-field' );

		if ( RESTRICTED_MODE === mode ) {
			$field.show();
		} else {
			$field.hide();
		}
	}

	$( function () {
		toggleAllowedGroups();
		$( document ).on( 'change', 'input[name="' + MODE_FIELD + '"]', toggleAllowedGroups );
	} );
}( jQuery ) );
