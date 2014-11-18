/***** Front-end scripts for WP-MP-Contact *********/

jQuery(document).ready(function($){
	
	// Enable the form fields on the front end
	$('.mp-contact').each(function(){
		$(this).prop('disabled', false);
	});

	// Add a closer to the modal window
	// Create the .modal-close and .modal-content
	var modalCloser = '<span class="modal-close">&times;</span>';
	var modalContent = '<div class="modal-content"></div>';

	$('#modal-window').prepend(modalCloser, modalContent);

	// Bind an AJAX call to the to the button
	$('.lookup-mp').on('click', function(){

		// Get the postcode
		postcode = ($(this).parent().find('.postcode').val());

		openModal(postcode);
	});

	
});

function getMP(postcode, callback){
	
}

/* OpenModal
*
* Clears previous content and injects content into a container (.modal-content). Slides it down.
*
*/

function openModal(content, callback){

	// Empty content injected by previous calls
	jQuery('.modal-content').empty();

	// Slide down the modal window
	jQuery('#modal-window').slideDown(function(){

		jQuery('.modal-content').position({my: 'center top', at: 'center top', of: '#modal-window'});
		jQuery('.modal-content').append(content);
	});
				
	setTimeout(function(){
		// make sure the callback is a function
		if (typeof callback == 'function') { 
			// brings the scope to the callback
			callback.call(this);
		}
	}, 2000);

	jQuery('.modal-close').position({my: 'center', at: 'left top', of: '.modal-content'});

	jQuery('.modal-close').click(function(){
		jQuery('#modal-window').slideUp(function(){
			jQuery('.modal-content').empty();
		});
	});

}