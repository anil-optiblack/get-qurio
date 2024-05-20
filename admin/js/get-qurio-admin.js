(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 */
	 $(function() {
		$('#qurio_shortcode_copy').on('click', function() {
			const qurio_shortcode = $('#qurio_shortcode_field').val();
			qurio_copy_to_clipboard(qurio_shortcode);
		});
	 });
	 /*
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

})( jQuery );

function qurio_copy_to_clipboard(text) {
	navigator.clipboard.writeText(text).then(function() {
		console.log('Copying to clipboard was successful!');
		alert('Copying to clipboard was successful!');
	}, function(err) {
		console.error('Could not copy text: ', err);
		alert('Could not copy text: ', err);
	});
}