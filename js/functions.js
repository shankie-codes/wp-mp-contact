/***** Front-end scripts for WP-MP-Contact *********/

jQuery(document).ready(function($){

	// Define some handy variables 
	var gform = $('.wpmpc').parents('.gform_wrapper');
	var gformId = gform.attr('id').slice(-1);
	var gformSubmit = gform.find('[id^=gform_submit_button]');
	var gformLookupMp = $('.lookup-mp');
	var email = $('input.mp-email');
	var results = $('.lookup-results');
	
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
	gformLookupMp.attr('disabled', true);
	gformSubmit.attr('disabled', true);

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
		gformLookupMp.attr('disabled', !enableButton);
	});

	// Re-enable the submit button if the MP email address is completed
	email.on('keyup paste cut focus change blur autocompleteselect', function(){
		if($(this).attr("value").length !== 0){
			gformSubmit.attr('disabled', false);
		}
		else{
			gformSubmit.attr('disabled', true);
		}
	});

	// Bind an AJAX call to the to the .lookup-mp button 
	gformLookupMp.on('click', function(event){

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

			// Enable the submit button if there's an email address in the email field
			if(email.attr("value").length !== 0){
				gformSubmit.attr('disabled', false);
			}

			//
			getMPJSON(postcode, function(MP){
				if(MP.error){
					// switch
					$('.error-message').append(MP.error);
					$('.error-message').append('<br/>Please enter your MP\'s email address below or contact the administrator.');
					$('.mp-container').hide();
					
				}
				else{
					// Got the MP object from the API call, now manipulate the form
					$('.mp-constituency').html(MP.constituency);
					$('.mp-name').html(MP.name);
					
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
					$('.mp-photo').children('img').attr("src", MP.image)
				}

				// A search has been executed. Change the button text and start over
				gformLookupMp.val('Start Over');

				//Reveal our results
				results.slideDown();

			});
		}
		
		else{
			// Hide the results panel
			results.slideUp();

			// Show the results in case they were hidden
			$('.mp-container').show();

			// Change the button text
			gformLookupMp.val('Lookup MP');

			// Clear the search
			$('.postcode, .mp-email').removeAttr('value');

			// Clear any errors
			$('.error-message').empty();
			
			//Disable the search button
			gformLookupMp.attr('disabled', true);

		}

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


