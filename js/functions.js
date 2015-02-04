/***** Front-end scripts for WP-MP-Contact *********/

// Define this out here so that it's persistent
var mpMessage;

jQuery(document).ready(function($){

	// Define some handy variables 
	var gform = $('.wpmpc').parents('.gform_wrapper');
	var gformId = gform.attr('id').slice(-1);
	var gformSubmit = gform.find('[id^=gform_submit_button]');
	var lookupMPButton = $('.lookup-mp');
	var email = $('input.mp-email');
	var results = $('.lookup-results');
	var constituentName = $('.lookup-fields.name');
	var mpName = $('.mp-name');
	var message = $('.mp-contact.message');
	var startAgainButton = $('.start-again');
	
	// Enable the form fields on the front end
	$('.mp-contact').each(function(){
		$(this).prop('disabled', false);
	});

	// Move a the 'message' label somewhere more appropriate 
	$(".lookup-results").parent().has("input[type='email'],input[type='text'],input[type='password'],select,textarea").find("label").each(function() {
	    var e = $(this), fielddesc = $("<div>").append(e.clone()).remove().html();
	    e.siblings("input,select,textarea").before(fielddesc);
	    e.remove();
	});

	/* Disable the search and submit buttons unless the fields have values*/
	lookupMPButton.attr('disabled', true);

	// Re-enable the Lookup MP button if Name, Email and Postcode (together .lookup-fields) are all complete
	$('.lookup-fields').on('keyup paste cut focus change blur autocompleteselect', function(){
		
		// Flag to see if we want to enable the button at the end
		enableButton = false;
		
		// Don't use $(this) for this next iteration as we're trying to loop each one of the fields (not simply the one that the function was bound to above)
		$('.lookup-fields').each(function(){
			if($(this).attr("value").length == 0){
				enableButton = false;
				return false; // Works as 'break' with a jQuery loop
			}
			enableButton = true;
		});

		// Set the status of the button
		lookupMPButton.attr('disabled', !enableButton);
	});

	// Bind an AJAX call to the to the .lookup-mp button 
	lookupMPButton.on('click', function(event){

		// Add a 'loading' class to the form
		lookupMPButton.parents('form').addClass('loading');

		// Get the postcode from the parent element
		postcode = ($(this).parent().find('.postcode').val());
		
		// Clear existing search results
		$('.lookup-output').each(function(){
			// $(this).removeAttr('value');
			// $(this).empty();
		});

		//
		getMPJSON(postcode, function(MP){
			if(MP.error){
				// switch
				$('.error-message').append(MP.error);
				$('.error-message').append('<br/>Please enter your MP\'s email address below or contact the administrator.');
				lookupMPButton.parents('form').removeClass('loading');
				lookupMPButton.parents('form').addClass('loaded');
				lookupMPButton.parents('form').addClass('error');
			}
			else{
				// Got the MP object from the API call, now manipulate the form
				$('.mp-constituency').html(MP.constituency);
				$('.mp-name').html(MP.name);

				// Add some things to the default message
				if (mpMessage){
					message.val('Dear ' + mpName.html() + ',\r\r' + mpMessage);
					message.val(message.val() + '\r\rYours sincerely,\r\r' + constituentName.val());
				}
				else{
					mpMessage = message.val();
					message.val('Dear ' + mpName.html() + ',\r\r' + message.val());
					message.val(message.val() + '\r\rYours sincerely,\r\r' + constituentName.val());
				}
				
				// Check if the API returned an e-mail address
				if(MP.email){
					$('.mp-email').each(function(){
						$(this).html(MP.email);
						$(this).val(MP.email);
					});
					gformSubmit.attr('disabled', false);
				}
				else{
					$('.error-message').append('We couldn\'t find an email address for your MP. <br/>Please enter your MP\'s email address below or contact the administrator.');
				}
				$('.mp-website').attr('href', MP.website);
				$('.mp-photo').children('img').attr("src", MP.image);

				// Remove the loading state, add class 'loaded'
				lookupMPButton.parents('form').removeClass('loading');
				lookupMPButton.parents('form').addClass('loaded');
			}

		});

	});

	// Bind an AJAX call to the to the .startAgainButton button 
	startAgainButton.on('click', function(event){

		event.preventDefault();

		// Remove any loading/loaded classes
		lookupMPButton.parents('form').removeClass('loading');
		lookupMPButton.parents('form').removeClass('loaded');
		lookupMPButton.parents('form').removeClass('error');

		// Clear existing search results
		$('.mp-constituency').empty();

		// Clear the search
		$('.postcode, .mp-email').removeAttr('value');

		// Clear any errors
		$('.error-message').empty();

	});
	
});


function getMPJSON(postCode, callback){
	var data = {
		action: 'get_mp',
        postcode: postCode
	};

 	jQuery.post(the_ajax_script.ajaxurl, data, function(response) {
 	
		// Turn it into a JS object
		response = JSON.parse(response);
		// Call the callback
		if (typeof callback === 'function') {
			callback(response);
		}

 	});
}


