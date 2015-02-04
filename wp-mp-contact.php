<?php
/**
 * Plugin Name: WP MP Contact Gravity Add-on
 * Plugin URI: https://wordpress.org/plugins/wp-mp-contact
 * Description: Gravity Forms add-in for UK Member of Parliament email campaigns
 * Version: 1.0.1
 * Author: Proper Design
 * Author URI: http://properdesign.rs
 * License: GPL2
 *
 * Acknowledgements: 
 * Renewable UK (http://www.renewableuk.com/) and Action for Renewables (http://www.actionforrenewables.org/) for funding the initial development of this Gravity Forms add-in
 * WPSmith for the tutorial that got it all started (http://wpsmith.net/2011/plugins/how-to-create-a-custom-form-field-in-gravity-forms-with-a-terms-of-service-form-field-example/)
 * The Agency for showing how to work with complex fields (http://theagencyonline.co.uk/2014/07/custom-multiple-input-form-for-gravity-fields/)
 * Pippin Williamson for the usual and oft-forgotten explanation of the proper way to do AJAX in WordPress (https://pippinsplugins.com/process-ajax-requests-correctly-in-wordpress-plugins/)
 */

/*  Copyright 2014  Proper Design  (email : hello@properdesign.rs)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (class_exists("GFForms")) {
    GFForms::include_addon_framework();

    class GF_WP_MP_Contact extends GFAddOn {

        protected $_version = "1.0.0";
        protected $_min_gravityforms_version = "1.6.000";
        protected $_slug = "wp-mp-contact";
        protected $_path = "wp-mp-contact/wp-mp-contact.php";
        protected $_full_path = __FILE__;
        protected $_title = "WP MP Contact Gravity Add-on";
        protected $_short_title = "WP MP Contact";

        public function init(){
            parent::init();

            //Load translations
            load_plugin_textdomain('gravityformsmpcontact', FALSE, '/gravityformsmpcontact/languages');
            
            //Add custom text to the bottom of the form
            // add_filter("gform_submit_button", array($this, "form_submit_button"), 10, 2);
            
            //Add a MP Contact Search button
            add_filter( 'gform_add_field_buttons' , array( $this , 'add_mp_contact_field' ));

            //Add the fields to the new field
            add_action( 'gform_field_input' , array( $this , 'mp_contact_field_input' ), 10, 5 );

            // Adds title to GF custom field
            add_filter( 'gform_field_type_title' , array( $this, 'mp_contact_title' ) );

            //Add form JS
            add_action( "gform_editor_js", array( $this, 'editor_script' ) );

            // set label of field(s), and define inputs
            add_action( "gform_editor_js_set_default_values", array( $this, 'mp_contact_custom_field_labels' ));

            // Add a script to the display of the particular form only if tos field is being used
            add_action( 'gform_enqueue_scripts' , array($this, 'mp_contact_gform_enqueue_scripts' )); 

            // set any custom handling for output of our custom field(s) entry
            add_filter("gform_entry_field_value", array( $this, "gravity_form_custom_field_entry_output" ), 10, 4);           
        }

        function gravity_form_custom_field_entry_output($value, $field, $lead, $form){
            //Make our details appear in the entries list
            if ($field["type"] == "mp-contact"){
                $value = '';

                foreach ($field['inputs'] as $single_field) {
                    echo $single_field['id'];
                    $value .= $single_field['label'] . ': ' . $lead[(string)$single_field['id']] . '<br />';
                }
            }
            return $value;
        }

        function mp_contact_custom_field_labels(){
            // this hook is fired in the middle of a switch statement, so we need to add a case for our new field type ?>
            case "mp-contact" :
            field.label = "<?php _e("MP Contact", "gravityforms"); ?>"; // setting the default field label
            field.inputs = [
                new Input( field.id + 0.1, '<?php echo esc_js(__("Name", "gravityforms")); ?>'),
                new Input( field.id + 0.2, '<?php echo esc_js(__("Email", "gravityforms")); ?>'),
                new Input( field.id + 0.3, '<?php echo esc_js(__("Postcode", "gravityforms")); ?>'),
                new Input( field.id + 0.4, '<?php echo esc_js(__("Address", "gravityforms")); ?>'),
                new Input( field.id + 0.5, '<?php echo esc_js(__("MP Email Address", "gravityforms")); ?>'),
                new Input( field.id + 0.6, '<?php echo esc_js(__("Message", "gravityforms")); ?>'),
            ];
            break;
            <?php
        }

        function mp_contact_field_input ( $input, $field, $value, $lead_id, $form_id ){
            
            //Exit if not the mp_contact field
            if($field['type'] != 'mp-contact')
                return $input;

            //
            $input_name = $form_id .'_' . $field["id"];

            //Get Gravity's tab index
            $tabindex = GFCommon::get_tabindex();

            //Get any custom CSS classes
            $css = isset( $field['cssClass'] ) ? $field['cssClass'] : '';

            ob_start();

            ?>

            <div class="ginput_container ginput_complex wpmpc" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>">
                
                <span class="ginput_full" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_1_container">
                    <input value="" disabled type="text" name="input_<?php echo $field['id'] ?>.1" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_1" class="input gform_wpmpcontact lookup-fields name <?php echo $field['type'] . ' ' . esc_attr( $css ) . ' ' . $field['size']  ?>" <?php echo $tabindex ?> value=""/>
                    <label for="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_1" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_1_label"><?php _e( 'Your name*' , 'gravityformsmpcontact' ) ?></label>
                </span>

                <span class="ginput_full" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_2_container">
                    <input value="" disabled type="text" name="input_<?php echo $field['id'] ?>.2" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_2" class="input gform_wpmpcontact lookup-fields sender-email <?php echo $field['type'] . ' ' . esc_attr( $css ) . ' ' . $field['size']  ?>" <?php echo $tabindex ?> value=""/>
                    <label for="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_2" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_2_label"><?php _e( 'Your e-mail address*' , 'gravityformsmpcontact' ) ?></label>
                </span>

                <span class="ginput_full" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_3_container">
                    <input value="" disabled type="text" name="input_<?php echo $field['id'] ?>.3" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_3" class="input gform_wpmpcontact lookup-fields postcode <?php echo $field['type'] . ' ' . esc_attr( $css ) . ' ' . $field['size']  ?>" <?php echo $tabindex ?> value=""/>
                    <label for="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_3" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_3_label"><?php _e( 'Your postcode*' , 'gravityformsmpcontact' ) ?></label>
                </span>

                <span class="ginput_full" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_4_container">
                    <input value="" disabled type="text" name="input_<?php echo $field['id'] ?>.4" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_4" class="input gform_wpmpcontact address <?php echo $field['type'] . ' ' . esc_attr( $css ) . ' ' . $field['size']  ?>" <?php echo $tabindex ?> value=""/>
                    <label for="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_4" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_4_label"><?php _e( 'First line of your address' , 'gravityformsmpcontact' ) ?></label>
                </span>

                <?php if(false == is_admin()): ?>
                    <!-- Button to initiate AJAX call to return MP email address -->
                    <input type="button" class="gform_button button lookup-mp" value="<?php _e( 'Lookup MP', 'gravityformsmpcontact') ?>">
                    <input type="button" class="gform_button button start-again" value="<?php _e( 'Start again', 'gravityformsmpcontact') ?>">

                    <div class="lookup-results">
                      <div class="error-message"></div>
                      <h3><?php _e( 'MP for your constituency: ', 'gravityformsmpcontact' ); ?><span class="mp-constituency"></span></h3>
                      <div class="mp-container">
                        <div class="mp-photo lookup-output">
                          <img src="" alt="" />
                        </div>
                        <div class="mp-details">
                          <div class="detail-item">
                            <div class="label"><?php _e( 'Name', 'gravityformsmpcontact' ); ?>:</div>
                            <div class="detail lookup-output mp-name"></div>
                          </div>
                          <div class="detail-item">
                            <div class="label"><?php _e( 'E-mail', 'gravityformsmpcontact' ); ?>:</div>
                            <div class="detail lookup-output mp-email">
                            </div>
                          </div>   
                          <div class="detail-item">
                            <div class="label mp-website"><a href="" class="mp-website"><?php _e( 'MP Website', 'gravityformsmpcontact' ); ?></a> </div>
                          </div>  
                        </div>
                      </div>
                      <span class="ginput_full" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_4_container">
                          <input class="mp-email mp-contact" disabled value="" type="text" name="input_<?php echo $field['id'] ?>.5" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_5" class="input gform_wpmpcontact mp-email <?php echo $field['type'] . ' ' . esc_attr( $css ) . ' ' . $field['size']  ?>" <?php echo $tabindex ?> value=""/>
                          <label for="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_5" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_5_label"><?php _e( 'MP E-Mail (send to)' , 'gravityformsmpcontact' ) ?></label>
                      </span>
                    <!-- </div> - deliberately commented out and conditionally included below -->
                <?php endif; ?>

                <span class="ginput_full" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_5_container">
                    <textarea disabled type="text" name="input_<?php echo $field['id'] ?>.6" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_6" class="textarea gform_wpmpcontact mp-contact message <?php echo $field['type'] . ' ' . esc_attr( $css ) . ' ' . $field['size']  ?>" <?php echo $tabindex ?> cols="60" rows="10"><?php echo $field['defaultValue'] ?></textarea>
                    <label for="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_6" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_1_label"><?php _e( 'Message' , 'gravityformsmpcontact') ?></label>
                </span>
                
                <?php if( is_admin() ): ?>
                    <span class="ginput_full attrib">
                        <?php _e( 'Data provided by ', 'gravityformsmpcontact' ); ?><a href="http://www.theyworkforyou.com/">theyworkforyou.com</a> and <a href="http://theguardian.com"><img src="<?php echo plugins_url( 'img/poweredbyguardian.png' , __FILE__ )  ?> " alt="Powered by the Guardian"></a>
                    </span>
                <?php endif; ?>
                
                <?php if( false == is_admin() ) echo '</div>';?>
            
            </div>

            <?php
            return ob_get_clean();
        }

        function mp_contact_title( $field_type ) {
            if ( $field_type == 'mp-contact' )
            return __( 'MP Contact Postcode Search' , 'gravityformsmpcontact' );
            // return 'wow';
        }

        function editor_script(){
            ?>
            <script type='text/javascript'>

                jQuery(document).ready(function($) {
                    //adding setting to textarea fields
                    fieldSettings["mp-contact"] = fieldSettings["textarea"];
                });

            </script>
            <?php
        }
        
        function mp_contact_gform_enqueue_scripts( $form ) {
            
            // If MP-Contact is being used, enqueue our front-end scripts and styles
        
            foreach ( $form['fields'] as $field ) {
                
                if ( ( $field['type'] == 'mp-contact' ) ) {
                    
                    // Enqueue front-end scripts
                    wp_enqueue_script( 'gform_mp_contact_script', plugins_url( 'js/functions.js' , __FILE__ ) , array( 'jquery' ));

                    // Enqueue front-end styles
                    wp_enqueue_style( 'gform_mp_contact_style', plugins_url( 'css/style.css' , __FILE__ ));

                    // Enqueue jQuery (you can't be too sure) and jQuery UI
                    wp_enqueue_script( 'jquery' );
                    wp_enqueue_script( 'jquery-ui-position' );

                    break;
                }
            }
        }

        public function plugin_settings_fields() {
            return array(
                array(
                    "title"  => "MP Contact Settings",
                    "fields" => array(
                        array(
                            "name"    => "twfy_api_key",
                            "tooltip" => 'WP MP Contact is powered by They Work for You (TWFY) and The Guardian. TWYFI needs an API key – you can get one at <a href="http://www.theyworkforyou.com/api/key">http://www.theyworkforyou.com/api/key</a>',
                            "label"   => "TWFY API key",
                            "type"    => "text",
                            "class"   => "medium"
                        ),
                        array(
                            "name"    => "google_spreadsheet_json_feed",
                            "tooltip" => 'This plugin uses as Google Spreadsheet as its data source. Enter the key of the document here.',
                            "label"   => "Google Spreadsheet JSON API Feed",
                            "type"    => "text",
                            "class"   => "medium"
                        )
                    )
                )
            );
        }

        public function is_valid_setting($value){
            return strlen($value) < 10;
        }

        public function add_mp_contact_field($field_groups) { 

            foreach ($field_groups as &$group) {
                if ($group["name"] == "advanced_fields") {
                    $group["fields"][] = array("class" => "button", "value" => __("MP Contact", "gravityformsmpcontact"), "onclick" => "StartAddField('mp-contact');");
                    break;
                }
            }

            return $field_groups;
        }

        public function get_MP($postcode){
            /* Core API calls. Executed via an AJAX call from the front end */

            // Include the API binding
            require_once 'inc/twfyapi.php';

            // Get an instance of the TWFYAPI
            if( strlen($this->get_plugin_setting('twfy_api_key')) > 5){
                // Set up a new instance of the API binding using key from twfy_api_key
                $twfyapi = new TWFYAPI($this->get_plugin_setting('twfy_api_key'));
            }
            else{
                return array(
                    'error' => 'TWFY API key not set.'
                    );
            }

            // Get the Google Doc data source
            if( strlen($this->get_plugin_setting('google_spreadsheet_json_feed')) > 5){
                
                // Get the spreadsheet JSON feed from the form settings
                $constit_url = $this->get_plugin_setting('google_spreadsheet_json_feed');
            }
            else{
                return array(
                    'error' => 'Google Spreadsheet JSON feed not set.'
                    );
            }

            /* Query They Work for You (TWFY) */

            // Get the constituency
            $constituency = $twfyapi->query('getConstituency', array('postcode' => $postcode, 'output' => 'php'));
            $twfy_mp      = $twfyapi->query('getMP', array('postcode' => $postcode, 'output' => 'php'));

            // Unserialize the serialized PHP that comes back
            $constituency = unserialize($constituency);
            $twfy_mp = unserialize($twfy_mp);

            // Handle errors from the server call to TWFY
            if( $constituency['error'] ){
                // Handle the return appropriately
                
                return (object)($constituency);
            }
            if( $twfy_mp['error'] ){
                // Handle the return appropriately
                
                return (object)($twfy_mp);
            }

            // Set up CURL to get the Google Spreadsheet data
            $ch = curl_init($constit_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLINFO_HEADER_OUT, 1);

            // Execute the query
            $response = curl_exec($ch);
            
            // Get headers and response
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);
           
            // Get the response code
            $header_code = substr($header, 9, 3);
        
            // Get the output and decode it into a PHP object
            if($header_code == "200"){

                // Call was a success. Match the Guardian name from TWFY to the PPC list

                $constituency_obj = json_decode($body);
                
                $output['constituency'] = $constituency['guardian_name'];

            }else{

                return array(
                    'error' => 'Error connecting to MP email address database.'
                );
            }

            // Get the feed out of the returned GDoc
            $MP_feed = $constituency_obj->feed->entry;

            // Loop through the returned array and compose a new array of results
            foreach ($MP_feed as $MP) {
                
                if ($MP->{'gsx$guardianconstituency'}->{'$t'} == $output['constituency']) {
                    $output['name']    = $MP->{'gsx$fullname'}->{'$t'};
                    $output['party']   = $MP->{'gsx$party'}->{'$t'};
                    $output['email']   = $MP->{'gsx$email'}->{'$t'};
                    $output['website'] = 'http://www.theyworkforyou.com' . $twfy_mp['url'];

                    // Get the MP's image, or if not, show a mystery man
                    if(isset($twfy_mp['image'])){
                        $output['image'] = 'http://www.theyworkforyou.com' . $twfy_mp['image'];
                    }
                    else{
                        $output['image'] = plugins_url( 'img/mystery-man.png' , __FILE__ );
                    }
                }
                else{
                    // echo 'false';
                }
            }
            
            return $output;
                        
        }

        private function make_error($message){
            $error['error'] = $message;
            return (object)$error;
        }

        private function get_http_response_code($url) {
            $headers = get_headers($url);
            return substr($headers[0], 9, 3);
        }
    }

    /* Make us an object, will ya? */
    new GF_WP_MP_Contact();

    function mp_contact_ajax_load_scripts() {    
        
        // Make the ajaxurl var available to the plugin's scripts file
        wp_localize_script( 'gform_mp_contact_script', 'the_ajax_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );  

    }

    add_action('wp_print_scripts', 'mp_contact_ajax_load_scripts');

    function get_mp_ajax_process_request() {
        
        // Check if we're being sent a postcode
        if ( isset( $_POST['postcode'] ) ) {

            // Declare a new instance of the add-on
            $MP_contact = new GF_WP_MP_Contact();
            
            $response = json_encode($MP_contact->get_MP($_POST['postcode']));
            // Send the response back to the front end
            echo $response;
            die();
        }
    }
    add_action('wp_ajax_get_mp', 'get_mp_ajax_process_request');
    add_action('wp_ajax_nopriv_get_mp', 'get_mp_ajax_process_request');

}
