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
 * Acknowledgements: Renewable UK (http://www.renewableuk.com/) for funding the development of this plugin
 */

/*  Copyright 2014  Proper Design  (email : support@properdesign.rs)

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
            
            //Add custom text to the bottom of the form
            add_filter("gform_submit_button", array($this, "form_submit_button"), 10, 2);

            if(is_admin()){
                //Add a MP Contact Search button
                add_filter( 'gform_add_field_buttons' , array($this, 'add_mp_contact_field'));

                //Add the fields to the new field
                add_action( "gform_field_input" , "mp_contact_field_settings", 10, 5 );
                // add_action( 'gform_field_standard_settings' , array($this , 'mp_contact_field_settings' ), 10, 2);

                // Adds title to GF custom field
                add_filter( 'gform_field_type_title' , 'mp_contact_title' );
                
                function mp_contact_title( $field_type ) {
                    if ( $field_type == 'mp_contact' )
                    return __( 'MP Contact Postcode Search' , 'gravityformsmpcontact' );
                    // return 'wow';
                }

                function mp_contact_field_settings ( $input, $field, $value, $lead_id, $form_id ){
                    if ( $field["type"] == "mp_contact" ) {
                        // $max_chars = "";
                        
                        // if(!IS_ADMIN && !empty($field["maxLength"]) && is_numeric($field["maxLength"]))
                       
                        // $max_chars = self::get_counter_script($form_id, $field_id, $field["maxLength"]);
                       
                        // $input_name = $form_id .’_’ . $field["id"];
                       
                        // $tabindex = GFCommon::get_tabindex();
                        
                        // $css = isset( $field['cssClass'] ) ? $field['cssClass'] : ”;
                        
                        // return sprintf("<div class=’ginput_container’><textarea readonly name=’input_%s’ id=’%s’ class=’textarea gform_tos %s’ $tabindex rows=’10’ cols=’50’>%s</textarea></div>{$max_chars}", $field["id"], ‘tos-‘.$field['id'] , $field["type"] . ‘ ‘ . esc_attr( $css ) . ‘ ‘ . $field['size'] , esc_html($value));
                        return 'wow';
                    }
                    return $input;
                }
            }
        }

        

        // Add the text in the plugin settings to the bottom of the form if enabled for this form
        function form_submit_button($button, $form){
            $settings = $this->get_form_settings($form);
            if(isset($settings["enabled"]) && true == $settings["enabled"]){
                $text = $this->get_plugin_setting("mytextbox");
                $button = "<div>{$text}</div>" . $button;
            }
            return $button;
        }


        // public function plugin_page() {
            // echo 'This page appears in the Forms menu';
        // }

        public function form_settings_fields($form) {
            return array(
                array(
                    "title"  => "Campaign Settings Fields",
                    "fields" => array(
                        array(
                            "label"   => "My checkbox",
                            "type"    => "checkbox",
                            "name"    => "enabled",
                            "tooltip" => "This is the tooltip",
                            "choices" => array(
                                array(
                                    "label" => "Enabled",
                                    "name"  => "enabled"
                                )
                            )
                        ),
                        array(
                            "label"   => "My checkboxes",
                            "type"    => "checkbox",
                            "name"    => "checkboxgroup",
                            "tooltip" => "This is the tooltip",
                            "choices" => array(
                                array(
                                    "label" => "First Choice",
                                    "name"  => "first"
                                ),
                                array(
                                    "label" => "Second Choice",
                                    "name"  => "second"
                                ),
                                array(
                                    "label" => "Third Choice",
                                    "name"  => "third"
                                )
                            )
                        ),
                        array(
                            "label"   => "My Radio Buttons",
                            "type"    => "radio",
                            "name"    => "myradiogroup",
                            "tooltip" => "This is the tooltip",
                            "choices" => array(
                                array(
                                    "label" => "First Choice"
                                ),
                                array(
                                    "label" => "Second Choice"
                                ),
                                array(
                                    "label" => "Third Choice"
                                )
                            )
                        ),
                        array(
                            "label"   => "My Horizontal Radio Buttons",
                            "type"    => "radio",
                            "horizontal" => true,
                            "name"    => "myradiogrouph",
                            "tooltip" => "This is the tooltip",
                            "choices" => array(
                                array(
                                    "label" => "First Choice"
                                ),
                                array(
                                    "label" => "Second Choice"
                                ),
                                array(
                                    "label" => "Third Choice"
                                )
                            )
                        ),
                        array(
                            "label"   => "My Dropdown",
                            "type"    => "select",
                            "name"    => "mydropdown",
                            "tooltip" => "This is the tooltip",
                            "choices" => array(
                                array(
                                    "label" => "First Choice",
                                    "value" => "first"
                                ),
                                array(
                                    "label" => "Second Choice",
                                    "value" => "second"
                                ),
                                array(
                                    "label" => "Third Choice",
                                    "value" => "third"
                                )
                            )
                        ),
                        array(
                            "label"   => "My Text Box",
                            "type"    => "text",
                            "name"    => "mytext",
                            "tooltip" => "This is the tooltip",
                            "class"   => "medium",
                            "feedback_callback" => array($this, "is_valid_setting")
                        ),
                        array(
                            "label"   => "My Text Area",
                            "type"    => "textarea",
                            "name"    => "mytextarea",
                            "tooltip" => "This is the tooltip",
                            "class"   => "medium merge-tag-support mt-position-right"
                        ),
                        array(
                            "label"   => "My Hidden Field",
                            "type"    => "hidden",
                            "name"    => "myhidden"
                        ),
                        array(
                            "label"   => "My Custom Field",
                            "type"    => "my_custom_field_type",
                            "name"    => "my_custom_field"
                        )
                    )
                )
            );
        }

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
                    "title"  => "Simple Add-On Settings",
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
                    $group["fields"][] = array("class" => "button", "value" => __("MP Contact", "gravityformsmpcontact"), "onclick" => "StartAddField('mp_contact');");
                    break;
                }
            }

            return $field_groups;
        }

    }


    new GF_WP_MP_Contact();
}