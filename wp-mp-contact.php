<?php
/**
 * Plugin Name: WP MP Contact Gravity Add-on
 * Plugin URI: https://wordpress.org/plugins/wp-mp-contact
 * Description: Gravity Forms extension for UK Member of Parliament email campaigns
 * Version: 0.0.1
 * Author: Proper Design
 * Author URI: http://properdesign.rs
 * License: GPL2
 *
 * Acknowledgements: 
 * Renewable UK (http://www.renewableuk.com/) and Action for Renewables (http://www.actionforrenewables.org/) for funding the initial development of this Gravity Forms add-in
 * WPSmith for the tutorial that got it all started (http://wpsmith.net/2011/plugins/how-to-create-a-custom-form-field-in-gravity-forms-with-a-terms-of-service-form-field-example/)
 * The Agency for showing how to work with complext fields (http://theagencyonline.co.uk/2014/07/custom-multiple-input-form-for-gravity-fields/)
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

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (class_exists("GFForms")) {
    GFForms::include_addon_framework();

    class GF_WP_MP_Contact extends GFAddOn {

        protected $_version = "0.0.1";
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
                new Input( field.id + 0.1, '<?php echo esc_js(__("UK Postcode", "gravityforms")); ?>'),
                new Input( field.id + 0.2, '<?php echo esc_js(__("Message to MP", "gravityforms")); ?>'),
                new Input( field.id + 0.3, '<?php echo esc_js(__("MP Email Address", "gravityforms")); ?>'),
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
                    <input disabled type="text" name="input_<?php echo $field['id'] ?>.1" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_1" class="input gform_wpmpcontact postcode <?php echo $field['type'] . ' ' . esc_attr( $css ) . ' ' . $field['size']  ?>" <?php echo $tabindex ?> value=""/>
                    <label for="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_1" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_1_label"><?php _e( 'UK Postcode' , 'gravityformsmpcontact' ) ?></label>
                </span>

                <?php if(false == is_admin()){ ?>
                    <!-- Button to initiate AJAX call to return MP email address -->
                    <a href="#" class="button wp-mp-contact lookup-mp"><?php _e( 'Lookup MP', 'gravityformsmpcontact') ?></a>
                    
                    <div class="lookup-results">
                      <div class="message"></div>
                      <h3>Your MP</h3>
                      <div class="mp-container">
                        <div class="mp-photo lookup-output">
                          <img src="http://static.guim.co.uk/sys-images/Guardian/Pix/pictures/2009/5/14/1242300471314/Jenny-Willott-MP-001.jpg" alt="" />
                        </div>
                        <div class="mp-details">
                          <div class="detail-item">
                            <div class="label"><?php _e( 'Name', 'gravityformsmpcontact' ); ?>:</div>
                            <div class="detail lookup-output">Jenny Willott</div>
                          </div>
                          <div class="detail-item">
                            <div class="label"><?php _e( 'E-mail', 'gravityformsmpcontact' ); ?>:</div>
                            <div class="detail lookup-output">
                                <a href="mailto:jenny@jennywillott.com">jenny@jennywillott.com</a>
                            </div>
                          </div>   
                          <div class="detail-item">
                            <div class="label"><?php _e( 'E-mail', 'gravityformsmpcontact' ); ?>:</div>
                            <div class="detail lookup-output">
                                <a href="http://jennywillott.com">http://jennywillott.com</a>
                            </div>
                          </div>  
                        </div>
                      </div>
                      <div class="mp-email">
                        <label for="email">Send my message to:</label>
                        <input name="email" id="email" class="email lookup-output" type="email" value="jenny@jennywillott.com"/>
                      </div>
                    <!-- </div> - deliberately commented out and conditionally included below -->
                <?php } ?>

                <span class="ginput_full" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_2_container">
                    <textarea disabled type="text" name="input_<?php echo $field['id'] ?>.2" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_2" class="textarea gform_wpmpcontact <?php echo $field['type'] . ' ' . esc_attr( $css ) . ' ' . $field['size']  ?>" <?php echo $tabindex ?> cols="50" rows="10"><?php echo $field['defaultValue'] ?></textarea>
                    <label for="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_2" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_1_label"><?php _e( 'Message:' , 'gravityformsmpcontact') ?></label>
                </span>
                
                <?php if(false == is_admin()) echo '</div>';?>

                <input hidden type="text" name="input_<?php echo $field['id'] ?>.3" id="input_<?php echo $field['formId'] . '_' . $field['id'] ?>_1" class="input gform_wpmpcontact <?php echo $field['type'] . ' ' . esc_attr( $css ) . ' ' . $field['size']  ?>" <?php echo $tabindex ?> value=""/>
            
            </div>

            <?php
            return ob_get_clean();

            //Possibly change textarea to text to see what happens
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

        // Add the text in the plugin settings to the bottom of the form if enabled for this form
        // function form_submit_button($button, $form){
        //     $settings = $this->get_form_settings($form);
        //     if(isset($settings["enabled"]) && true == $settings["enabled"]){
        //         $text = $this->get_plugin_setting("mytextbox");
        //         $button = "<div>{$text}</div>" . $button;
        //     }
        //     return $button;
        // }
        
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


        // public function plugin_page() {
            // echo 'This page appears in the Forms menu';
        // }

        // public function form_settings_fields($form) {
        //     return array(
        //         array(
        //             "title"  => "Campaign Settings Fields",
        //             "fields" => array(
        //                 array(
        //                     "label"   => "My checkbox",
        //                     "type"    => "checkbox",
        //                     "name"    => "enabled",
        //                     "tooltip" => "This is the tooltip",
        //                     "choices" => array(
        //                         array(
        //                             "label" => "Enabled",
        //                             "name"  => "enabled"
        //                         )
        //                     )
        //                 ),
        //                 array(
        //                     "label"   => "My checkboxes",
        //                     "type"    => "checkbox",
        //                     "name"    => "checkboxgroup",
        //                     "tooltip" => "This is the tooltip",
        //                     "choices" => array(
        //                         array(
        //                             "label" => "First Choice",
        //                             "name"  => "first"
        //                         ),
        //                         array(
        //                             "label" => "Second Choice",
        //                             "name"  => "second"
        //                         ),
        //                         array(
        //                             "label" => "Third Choice",
        //                             "name"  => "third"
        //                         )
        //                     )
        //                 ),
        //                 array(
        //                     "label"   => "My Radio Buttons",
        //                     "type"    => "radio",
        //                     "name"    => "myradiogroup",
        //                     "tooltip" => "This is the tooltip",
        //                     "choices" => array(
        //                         array(
        //                             "label" => "First Choice"
        //                         ),
        //                         array(
        //                             "label" => "Second Choice"
        //                         ),
        //                         array(
        //                             "label" => "Third Choice"
        //                         )
        //                     )
        //                 ),
        //                 array(
        //                     "label"   => "My Horizontal Radio Buttons",
        //                     "type"    => "radio",
        //                     "horizontal" => true,
        //                     "name"    => "myradiogrouph",
        //                     "tooltip" => "This is the tooltip",
        //                     "choices" => array(
        //                         array(
        //                             "label" => "First Choice"
        //                         ),
        //                         array(
        //                             "label" => "Second Choice"
        //                         ),
        //                         array(
        //                             "label" => "Third Choice"
        //                         )
        //                     )
        //                 ),
        //                 array(
        //                     "label"   => "My Dropdown",
        //                     "type"    => "select",
        //                     "name"    => "mydropdown",
        //                     "tooltip" => "This is the tooltip",
        //                     "choices" => array(
        //                         array(
        //                             "label" => "First Choice",
        //                             "value" => "first"
        //                         ),
        //                         array(
        //                             "label" => "Second Choice",
        //                             "value" => "second"
        //                         ),
        //                         array(
        //                             "label" => "Third Choice",
        //                             "value" => "third"
        //                         )
        //                     )
        //                 ),
        //                 array(
        //                     "label"   => "My Text Box",
        //                     "type"    => "text",
        //                     "name"    => "mytext",
        //                     "tooltip" => "This is the tooltip",
        //                     "class"   => "medium",
        //                     "feedback_callback" => array($this, "is_valid_setting")
        //                 ),
        //                 array(
        //                     "label"   => "My Text Area",
        //                     "type"    => "textarea",
        //                     "name"    => "mytextarea",
        //                     "tooltip" => "This is the tooltip",
        //                     "class"   => "medium merge-tag-support mt-position-right"
        //                 ),
        //                 array(
        //                     "label"   => "My Hidden Field",
        //                     "type"    => "hidden",
        //                     "name"    => "myhidden"
        //                 ),
        //                 array(
        //                     "label"   => "My Custom Field",
        //                     "type"    => "my_custom_field_type",
        //                     "name"    => "my_custom_field"
        //                 )
        //             )
        //         )
        //     );
        // }

        public function settings_my_custom_field_type(){
            ?>
            <div>
                My custom field contains a few settings:
            </div>
            <?php
                $this->settings_text(
                    array(
                        "label" => "A textbox sub-field",
                        "name" => "subtext",
                        "default_value" => "change me"
                    )
                );
                $this->settings_checkbox(
                    array(
                        "label" => "A checkbox sub-field",
                        "choices" => array(
                            array(
                                "label" => "Activate",
                                "name" => "subcheck",
                                "default_value" => true
                            )

                        )
                    )
                );
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
                            "class"   => "medium",
                            "feedback_callback" => array($this, "is_valid_setting")
                        )
                    )
                )
            );
        }

        public function is_valid_setting($value){
            return strlen($value) < 10;
        }

        // public function mp_contact_field_settings($position, $form_id){
        //     //Field innards here

        //     //create settings on position 25 (right after Field Label)
        //     if ($position == 25) {
        //       echo 'wow';
        //     }
        // }


        // public function scripts() {
        //     $scripts = array(
        //         array("handle"  => "my_script_js",
        //               "src"     => $this->get_base_url() . "/js/my_script.js",
        //               "version" => $this->_version,
        //               "deps"    => array("jquery"),
        //               "strings" => array(
        //                   'first'  => __("First Choice", "simpleaddon"),
        //                   'second' => __("Second Choice", "simpleaddon"),
        //                   'third'  => __("Third Choice", "simpleaddon")
        //               ),
        //               "enqueue" => array(
        //                   array(
        //                       "admin_page" => array("form_settings"),
        //                       "tab"        => "simpleaddon"
        //                   )
        //               )
        //         ),

        //     );

        //     return array_merge(parent::scripts(), $scripts);
        // }

        // public function styles() {

        //     $styles = array(
        //         array("handle"  => "my_styles_css",
        //               "src"     => $this->get_base_url() . "/css/my_styles.css",
        //               "version" => $this->_version,
        //               "enqueue" => array(
        //                   array("field_types" => array("poll"))
        //               )
        //         )
        //     );

        //     return array_merge(parent::styles(), $styles);
        // }

        public function add_mp_contact_field($field_groups) { //Was public static function

            foreach ($field_groups as &$group) {
                if ($group["name"] == "advanced_fields") {
                    $group["fields"][] = array("class" => "button", "value" => __("MP Contact", "gravityformsmpcontact"), "onclick" => "StartAddField('mp-contact');");
                    break;
                }
            }

            return $field_groups;
        }
    }


    new GF_WP_MP_Contact();
}