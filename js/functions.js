/***** Front-end scripts for WP-MP-Contact *********/

jQuery(document).ready(function($){

	/* Define some handy variables */
	var gform = $('.wpmpc').parents('.gform_wrapper');
	var gformId = gform.attr('id').slice(-1);
	var gformSubmit = gform.find('[id^=gform_submit_button]');
	var email = $('input.mp-email');
	var results = $('.lookup-results');
	
	// Enable the form fields on the front end
	$('.mp-contact').each(function(){
		$(this).prop('disabled', false);
	});

	/** Add a closer to the modal window **/
	// Create the .modal-close and .modal-content
	var modalCloser = '<span class="modal-close">&times;</span>';
	var modalContent = '<div class="modal-content"></div>';

	//Prepend the closer
	$('#modal-window').prepend(modalCloser, modalContent);

	/* Move a the 'message' label somewhere more appropriate */
	$(".lookup-results").parent().has("input[type='email'],input[type='text'],input[type='password'],select,textarea").find("label").each(function() {
	    var e = $(this), fielddesc = $("<div>").append(e.clone()).remove().html();
	    e.siblings("input,select,textarea").before(fielddesc);
	    e.remove();
	});

	/* Disable the submit button unless .email has a value*/
	// Disable it by default
	gformSubmit.attr('disabled', true);

	// Bind a function to detect when it changes
	email.keyup(function(){
		if($(this).attr("value").length !== 0){
			gformSubmit.attr('disabled', false);
		}
		else{
			gformSubmit.attr('disabled', true);
		}
	});

	// $('.sendButton').attr('disabled',true);
	//     $('#message').keyup(function(){
	//         if($(this).val().length !=0)
	//             $('.sendButton').attr('disabled', false);            
	//         else
	//             $('.sendButton').attr('disabled',true);
	//     })

	/** Bind an AJAX call to the to the button **/
	$('.lookup-mp').on('click', function(event){

		event.preventDefault();

		if ($('.lookup-results').is(":hidden")){
			// If lookup results aren't visible, i.e. we've not already done a search
			
			// Get the postcode from the parent element
			postcode = ($(this).parent().find('.postcode').val());
			
			// Clear existing search results
			$('.lookup-output').each(function(){
				// $(this).removeAttr('value');
				// $(this).empty();
			});

			// Add our search results
			results.append(postcode);

			// Enable the submit button if there's
			if(email.attr("value").length !== 0){
				gformSubmit.attr('disabled', false);
			}

			//Reveal our results
			results.slideDown();

			// A search has been executed. Change the button text and start over
			$('.lookup-mp').text('Start Over');
		}
		
		else{
			// Hide the results panel
			results.slideUp();

			//Change the button text
			$('.lookup-mp').text('Lookup MP');

			// Clear the search
			$('.postcode').removeAttr('value');

		}


		// openModal(postcode);
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