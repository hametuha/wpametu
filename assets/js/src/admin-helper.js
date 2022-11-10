/*!
 * General admin screen helper
 *
 * @package WPametu
 * @version 1.0.0
 * @handle wpametu-admin-helper
 * @deps jquery-ui-dialog, jquery-ui-tooltip
 */

/* global wpametuAdminHelper:true */

const $ = jQuery;

//
// Global functions
//
window.WPametu = {
	alert: function ( msg ) {
		const dialog = $( '<div id="wpametu-alert"></div>' );
		dialog.html( '<p>' + msg + '</p>' );
		dialog.dialog( {
			title: wpametuAdminHelper.error,
			resizable: false,
			modal: true,
			buttons: [
				{
					text: wpametuAdminHelper.close,
					click: function () {
						$( this ).dialog( "close" );
						$( this ).remove();
					}
				}
			]
		} );
	}
};

//
// Tooltips
//
$( document ).ready( function () {
	$( document ).tooltip( {
		items: 'a[data-tooltip-title]',
		content: function () {
			return $( this ).attr( 'data-tooltip-title' );
		},
		track: true
	} );
} );
