# WP-MP-Contact #
**Contributors:** shankie
  
**Tags:** Gravity Forms, TWFY, They Work for You, MP
  
**Requires at least:** 4.0
  
**Tested up to:** 4.0.1
  
**License:** GPL
  
**License URI:** http://www.gnu.org/copyleft/gpl.html
  

WP-MP-Contact is a Gravity Forms add-in for UK Member of Parliament email campaigns.

## Description ##
Tested on Gravity Forms 1.8.19, will probably work under versions from 1.6.

WP-MP-Contact is a Gravity Forms add-in for UK Member of Parliament email campaigns. Using API calls to They Work for You and The Guardian's politics API, a campaigning organisation can add an 'MP-Contact' field to their gravity form which allows pre-population of an (editable) message to send to an MP.

On the front-end, the user enters their UK postcode. If the postcode is valid, WP-MP-Contact will show a slide-down drawer with the MP's details and a pre-populated message from the campaigning organisation (set by the administrator in Gravity Forms settings).

The user has the ability to edit the message that the campaigner has pre-populated before sending an email to the MP.

In the Gravity Forms back end, all submissions are recorded in the usual Gravity Forms entries interface.

MP-Contact comes with minimal styling out of the box and doesn't pre-suppose how it will look in your theme. Go on, get stuck into styling it, it's only a bit of flexbox.

Feel free to contribute to the project. You can find the repo on Github at https://github.com/shankiesan/wp-mp-contact

WP-MP-Contact was developer by Proper Design (http://properdesign.rs)

 Acknowledgements: 

* Renewable UK (http://www.renewableuk.com/) and Action for Renewables (http://www.actionforrenewables.org/) for funding the initial development of this Gravity Forms add-in
* They Work for You's consituency API (http://www.theyworkforyou.com/api/)
* The Guardian's Politics API (http://www.theguardian.com/open-platform/politics-api/getting-started)
* rubenarakelyan's PHP class for They Work for You (https://github.com/rubenarakelyan/twfyapi)
* WPSmith for the tutorial that got it all started (http://wpsmith.net/2011/plugins/how-to-create-a-custom-form-field-in-gravity-forms-with-a-terms-of-service-form-field-example/)
* The Agency for showing how to work with complex fields (http://theagencyonline.co.uk/2014/07/custom-multiple-input-form-for-gravity-fields/)
* Pippin Williamson for the usual and oft-forgotten explanation of the proper way to do AJAX in WordPress (https://pippinsplugins.com/process-ajax-requests-correctly-in-wordpress-plugins/)

## Installation ##
1. Make sure that Gravity Forms is installed on your WordPress site
2. Upload this plugin to your WordPress site as you would any other plugin
3. Get a theyworkforyou.com API key by going to http://www.theyworkforyou.com/api/key
4. Enter the key into Forms->Settings->WP MP Contact and click 'Update Settings'
5. Create a new form and add the MP contact field to your form. You'll find it under 'Advanced' fields. It's not really that advanced, don't worry.
6. Add a default campaign message by going to Advanced Settings for your MP Contact field and adding a default value. This will appear as the campaign message to site visitors.
7. Add your form to a page as you would any other Gravity Form
8. Create a new notification for your form by going to [Your form]->Form Settings->Notifications. There are a couple of points to note about notifications, so please read the section 'Notifications' below
9. Run some tests! The plugin will always send to the address in the front-end field 'MP E-Mail (send to)', so to test the plugin, you can overwrite the returned email address with your own to check how your notification emails appear. Make sure that you do this every time as you will otherwise send test emails to your MP! (Sorry, Jenny)

Notifications:

Due to some core Gravity Forms functionality, MP-Contact's MP Email field doesn't appear in the 'Select a field' drop-down for 'Send to Email'. Instead, use the 'Enter Email' option. Unfortunately, this field doesn't have the field-selection drop-down to the right as most fields do (are you listening, Gravity?), so simply use one of the other fields to generate the curly-brackets code for you MP Email field, e.g. {MP Contact (MP Email Address):1.4}

It's also worth noting that you should set the From Email address to an email address in the same domain as your server to minimise the chances of your message being seen as spam. You can, however, set the reply-to address to the site visitor's email address so that the MP can reply directly to his or her constituent. 

You can further reduce the chances of your emails being marked as spam by configuring WordPress to use your own SMTP servers, rather than the PHP mail() built into WordPress by using this plugin: https://wordpress.org/plugins/wp-mail-smtp/

## Changelog ##
1.0 - Initial Release
1.0.2 - Changes handling of Guardian API calls to CURL
