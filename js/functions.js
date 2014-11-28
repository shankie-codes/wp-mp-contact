/***** Front-end scripts for WP-MP-Contact *********/

jQuery(document).ready(function($){

	/* Define some handy variables */
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

	/* Move a the 'message' label somewhere more appropriate */
	$(".lookup-results").parent().has("input[type='email'],input[type='text'],input[type='password'],select,textarea").find("label").each(function() {
	    var e = $(this), fielddesc = $("<div>").append(e.clone()).remove().html();
	    e.siblings("input,select,textarea").before(fielddesc);
	    e.remove();
	});

	/* Disable the search and submit buttons unless the fields have values*/
	gformLookupMp.attr('disabled', true);
	gformSubmit.attr('disabled', true);
	

	// Re-enable the Lookup MP button if Name, Email and Postcode (together .lookup-fields) are all complete
	$('.lookup-fields').keyup(function(){
		
		//Flag to see if we want to enable the button at the end
		enableButton = false;
		
		//Don't use $(this) for this next iteration as we're trying to loop each one of the fields (not simply the one that the function was bound to above)
		$('.lookup-fields').each(function(){
			if($(this).attr("value").length == 0){
				enableButton = false;
				return false; // Works as 'break' with a jQuery loop
			}
			enableButton = true;
		});

		//Set the status of the button
		gformLookupMp.attr('disabled', !enableButton);
	});

	// Re-enable the submit button if the MP email address is enabled
	email.keyup(function(){
		if(this.attr("value").length !== 0){
			gformSubmit.attr('disabled', false);
		}
		else{
			gformSubmit.attr('disabled', true);
		}
	});

	/** Bind an AJAX call to the to the .lookup-mp button **/
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

			// Make an AJAX call


			// Add our search results
			// results.append(postcode);

			// Enable the submit button if there's an email address in the email field
			if(email.attr("value").length !== 0){
				gformSubmit.attr('disabled', false);
			}

			//
			getMPJSON(postcode, function(MP){
				// Got the MP object from the API call, now manipulate the form
				console.log(MP);
				$('.mp-constituency').html(MP.constituency);
				$('.mp-name').html(MP.name);
				$('.mp-email').each(function(){
					$(this).html(MP.email);
					$(this).val(MP.email);
				});
				$('.mp-website').attr('href', MP.website);
				$('.mp-photo').children('img').attr("src", MP.image)
				

				//Reveal our results
				results.slideDown();

				// A search has been executed. Change the button text and start over
				gformLookupMp.text('Start Over');
			});
		}
		
		else{
			// Hide the results panel
			results.slideUp();

			//Change the button text
			gformLookupMp.text('Lookup MP');

			// Clear the search
			$('.postcode').removeAttr('value');

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
			console.log(response);
			response = JSON.parse(response);
			// Call the callback
			if (typeof callback === 'function') {
				callback(response);
			}
	 	});
}


