/*!
 * Batch helper
 *
 * @handle wpametu-batch-helper
 * @deps jquery-form, jquery-effects-highlight
 */

/*global WpametuBatch: true*/

const $ = jQuery;

const Batch = {

	$form: $( '#batch-form' ),

	$btn: $( 'input[type=submit]', '#batch-form' ),

	$pre: $( '.console', '#batch-result' ),

	/**
	 * Reset form
	 */
	reset: function() {
		this.$btn.attr( 'disabled', false );
		this.$form.removeClass( 'loading' );
		$( '#page_num' ).val( '1' );
	},

	/**
	 * Execute batch
	 */
	execute: function() {
		Batch.$form.addClass( 'loading' );
		Batch.$btn.attr( 'disabled', true );
		Batch.$pre.empty();
		Batch.console( 'Start Processing...' );
		Batch.ajax();
	},

	/**
	 * Send request
	 */
	ajax: function() {
		this.$form.ajaxSubmit( {
			success: function( result ) {
				if ( result.success ) {
					Batch.console( result.message );
					if ( result.next ) {
						const curPage = $( '#page_num' );
						curPage.val( parseInt( curPage.val(), 10 ) + 1 );
						Batch.ajax();
					} else {
						Batch.console( WpametuBatch.done, 'success' );
						Batch.reset();
					}
				} else {
					Batch.addError( result.message );
					Batch.reset();
				}
			},
			error: function( xhr, status, error ) {
				Batch.addError( error );
				Batch.reset();
			},
		} );
	},

	/**
	 * Add message to console
	 *
	 * @param {string} msg
	 * @param {string} className
	 */
	console: function( msg, className ) {
		const $p = $( '<p></p>' );
		$p.text( msg );
		if ( className ) {
			$p.addClass( className );
		}
		this.$pre.append( $p );
	},

	/**
	 * Add error message
	 *
	 * @param {string} msg
	 */
	addError: function( msg ) {
		this.console( '[Error] ' + msg, 'error' );
	},

	/**
	 * Set page number
	 *
	 * @param {number} pageNum
	 */
	setPage: function( pageNum ) {
		$( '#page_num' ).val( pageNum );
	},
};

Batch.$form.submit( function( e ) {
	e.preventDefault();
	if ( ! $( this ).find( 'input[name=batch_class]:checked' ).length ) {
		Batch.addError( WpametuBatch.alert );
	} else if ( confirm( WpametuBatch.confirm ) ) {
		Batch.execute();
	}
} );
