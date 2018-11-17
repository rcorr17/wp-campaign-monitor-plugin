<?php

/*
Plugin Name: RC Campaign Monitor
Plugin URI: 
Description: A web app to manage your Campaign Monitor account. Settings can be found under the Settings menu.
Version: 1.0.0
Author: Ronan Corr
Author URI: 
License: GPLv2
*/

//Add a PHP constant to specify an internal version number that will be used
//   throughout the plugin code:
define( "VERSION", "1.0" ); 

// include Campaign Monitor and database classes
require_once 'classes/cmMaintenanceClass.php';
require_once 'classes/dbClass.php';

//Add the following line of code to register a function that will be called
//when WordPress activates the plugin:
register_activation_hook(__FILE__, 'rcCM_set_default_options');

//Add the following to provide an implementation for the rcCM_set_default_options
//   function to set default plugin options:
function rcCM_set_default_options(){
    if ( get_option( 'rcCM_options' ) === false ) {
        $new_options[ 'api_key' ] = '00000000000000000000000000000000';
        $new_options[ 'client_key' ] = '00000000000000000000000000000000';
        //$new_options[ 'subscriber_key' ] = '00000000000000000000000000000000';
        $new_options[ 'display_name_field' ] = 'Yes';
        $new_options[ 'display_list_type' ] = 'All';
        $new_options[ 'subscriberList' ] = 'None';
        $new_options[ 'version' ] = VERSION;
        add_option( 'rcCM_options', $new_options );
    } 
    
    // Get access to global database access class
    global $wpdb;
    
   // Create table on main blog in network mode or single blog
    rcCM_create_table( $wpdb->get_blog_prefix() );
}

//create WordPress table to hold campaign monitor's user info if unsubscribe
function rcCM_create_table( $prefix ) {
    // Prepare SQL query to create database table using function parameter
    
    $creation_query =
            'CREATE TABLE ' . $prefix . 'campaign_monitor (
            `firstName` varchar(255) NOT NULL,
            `lastName` varchar(255) NOT NULL,
            `email` varchar(255) NOT NULL,
            `province` varchar(50) NOT NULL,
            `lang` enum(\'English\', \'French\'),
            `status` varchar(50) NOT NULL,
            `subscribeDate` date DEFAULT NULL,
            PRIMARY KEY ( `email` )
            );';

    require_once ( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $creation_query );
}

//Add the following line of code to register a function to be called when WordPress
//   is building the administration pages menu:
add_action( 'admin_menu',  'rcCM_settings_menu' );

//Add the following code section to provide an implementation for the rcCM_settings_menu function: 
function rcCM_settings_menu() {
   
    global $options_page;
    
    $options_page = add_options_page( 'Campaign Monitor Configuration', 'Campain Monitor', 'manage_options',
                                      'rcCM-cm', 'rcCM_config_page' ); 
}

function rcCM_config_page() {
    
    //Retrieve plugin configuration options from database
    $options = get_option( 'rcCM_options' );
	
	//check if plugin is at it's inital state
	if ( esc_html( $options[ 'api_key' ] ) == "00000000000000000000000000000000" && esc_html( $options[ 'client_key' ] ) == "00000000000000000000000000000000") {
		$api_key = "";
		$client_key = "";
	} else {
		$api_key = esc_html( $options[ 'api_key' ] );
		$client_key = esc_html( $options[ 'client_key' ] );
	}
   
?>
    <div id="rcCM-general" class="wrap">
        <h2>Campaign Monitor Settings</h2>
    
        <?php 
   
        //create new campaign monitor object
        $cm = new cmMaintenance();
    
        if ( isset( $_GET[ 'message' ]) && $cm->testConnection() && $_GET[ 'subscriberList' ] != "" ) { ?>
            <div id="message" class="updated fade">
                <p><strong>Settings Saved</strong></p>
            </div>
        <?php } else if ( isset( $_GET[ 'message' ]) &&  $cm->testConnection() == false ) { ?>
            <div id="message" class="error fade">
                <p><strong>Settings Not Correct.  Please check again the API &amp; Client keys.</strong></p>
            </div>
        <?php } else if ( isset( $_GET[ 'message' ]) &&  $cm->testConnection() == true && $_GET[ 'subscriberList' ] == "" ) { ?>
        
            <div id="message" class="error fade">
                <p><strong>Please select a list</strong></p>
            </div>
    <?php } ?>
        
        <form method="post" action="admin-post.php">
          
            <ol>
                <li>
        
                    <p>This plugin requires the API Key &amp; the Client ID Key to connect the app to Campaign Monitor.&nbsp;&nbsp;Login into Campaign Monitor to find these two keys.</p>

                    <div style="width:300px; float:left; margin-right: 10px;">
                        <p>The API key can be found under &ldquo;Account Settings&rdquo; &rAarr; Click &ldquo;Show API Key&rdquo;.<br/>
                    	<?php echo '<img src="' . plugins_url( 'images/api-key.gif', __FILE__ ) . '" alt="API Key" title="API Key" width="270" height="209" />'; ?></p>	
                    </div>

                    <div style="float:left;">
                        <p>The Client ID Key is located under &ldquo;Client Settings&rdquo; &rAarr; &ldquo;Edit list name&rdquo;.<br/><br/>
                    <?php echo '<img src="' . plugins_url( 'images/client-key.gif', __FILE__ ) . '" alt="Client Key" title="Client Key" width="426" height="220" />'; ?></p>
                    </div>

                    <br style="clear:both;">

                    <input type="hidden" name="action" value="save_rcCM_options" />

                    <!--adding security through hidden referrer field -->
                    <?php wp_nonce_field('rcCM'); ?>

                    <table width="400" cellspacing="0" cellpadding="0" border="0">
                        <tr>
                            <td colspan="2">&nbsp;</td>
                        </tr>
                        <tr>
                            <td>API Key:</td>
                            <td><input type="text" name="api_key" size="33" maxlength="32" value="<?php echo $api_key; ?>"/></td>
                        </tr>
                        <tr>
                            <td>Client ID Key:</td>
                            <td><input type="text" name="client_key" size="33" maxlength="32" value="<?php echo $client_key; ?>"/></td>
                        </tr>
                        <!---<tr>
                            <td>Subscriber Key:</td>
                            <td><input type="text" name="subscriber_key" size="30" maxlength="32" value="<?php //echo esc_html( $options[ 'subscriber_key' ] ); ?>"/></td>
                        </tr>-->
                    </table>

                    <p>&nbsp;</p>
                </li>
                
                <?php  
					// determine if connection is good.  if so display the following options
					if ( esc_html( $options[ 'api_key' ] ) != "00000000000000000000000000000000" && esc_html( $options[ 'client_key' ] ) != "00000000000000000000000000000000") { ?>
                       	<li>
                            <p>Would you like to collect the subscriber's name? <input type="radio" name="display_name_field" value="Yes" <?php if ( $options[ 'display_name_field' ]  == "Yes" ) { echo 'checked="checked"'; } ?>  />Yes&nbsp;&nbsp;
                                <input type="radio" name="display_name_field" value="No"  <?php if ( $options[ 'display_name_field' ]  == "No" ) { echo 'checked="checked"'; } ?> />No</p>
                        </li>
                        
                        <li><p>Would you like to work with all the lists or just one list within the client?  
                            <select id="display_list_type" name="display_list_type">
                                <option value="All" <?php if ( $options[ 'display_list_type' ]  == "All" ) { echo "selected"; } ?>>All the lists</option>
                                <option value="One" <?php if ( $options[ 'display_list_type' ]  == "One" ) { echo "selected"; } ?>>Just one</option>
                            </select></p></li>
                        
                        <div id="display_list_typeResult">
                            <p>Select which list you would like to use.</p>
                            <?php $cm->adminGetLists("Radio", $_GET["subscriberList"]); ?>
                        </div>
 
               <?php } ?>
                
            </ol>
            <div id="poststuff" class="metabox-holder">
                <div id="post-body">
                    <div id="post-body-content">
                        <input type="submit" value="Submit" class="button-primary" />
                    </div>
                </div>
                <br class="clear">
            </div>  
              
        </form>

    </div> 
<?php
}

// Add the following line of code to register a function to be called when WordPress
//    first identifies that the requested page is an administration page:
add_action( 'admin_init', 'rcCM_admin_init' );

function rcCM_admin_init() {
   add_action( 'admin_post_save_rcCM_options' , 'process_rcCM_options' );
}

function process_rcCM_options() {
    
    //Check that user has proper security level
    if ( !current_user_can( 'manage_options' ) ) {
        wp_die( 'Not allowed' );
    }
    
    // Check  that nonce field created in configuration form is present
    check_admin_referer( "rcCM" );
    
    // Retrieve original plugin options array
    $options = get_option( 'rcCM_options' );
    
    //Cycle through all text form fields and store their values in the options array
    foreach ( array( 'api_key' ) as $option_name ) {
        if ( isset( $_POST[$option_name] ) ) {
            $options[$option_name] = sanitize_text_field( $_POST[$option_name] );
        }
    }
    
    foreach ( array( 'client_key' ) as $option_name ) {
        if ( isset( $_POST[$option_name] ) ) {
            $options[$option_name] = sanitize_text_field( $_POST[$option_name] );
        }
    }
    
    foreach ( array( 'display_name_field' ) as $option_name ) {
        if ( isset( $_POST[$option_name] ) ) {
            $options[$option_name] = esc_html( $_POST[$option_name] );
        }
    }
    
    foreach ( array( 'display_list_type' ) as $option_name ) {
        if ( isset( $_POST[$option_name] ) ) {
            $options[$option_name] = esc_html( $_POST[$option_name] );
        }
    }
    
    foreach ( array( 'subscriberList' ) as $option_name ) {
        
        if ( isset( $_POST[$option_name]) && isset( $_POST['display_list_type']) && $_POST['display_list_type'] == "One") {
            $options[$option_name] = esc_html( $_POST[$option_name] );
        } else {
            $options[$option_name] = "None";
        }
    }
    
    /*foreach ( array( 'subscriber_key' ) as $option_name ) {
        if ( isset( $_POST[$option_name] ) ) {
            $options[$option_name] = sanitize_text_field( $_POST[$option_name] );
        }
    }*/
    
    //Store updated options array to database
    update_option( 'rcCM_options', $options );
    
    wp_redirect( add_query_arg( array( 'page' => 'rcCM-cm', 'message' => '1', 'subscriberList' => $_POST[ "subscriberList" ] ), admin_url( 'options-general.php' ) )); // Has confirmation message     
    exit;  
}

add_action( 'admin_enqueue_scripts', 'load_admin_style' );
function load_admin_style() {
    wp_register_style( 'admin_css', get_template_directory_uri() . '/css/styles.css', false, '1.0.0' );
}

add_action( 'admin_footer', 'more_info_footer_code' );
function more_info_footer_code() { 
   
    $screen = get_current_screen();
    
    //only add this script when Campaign Monitor settings page is view
    if ( $screen->id == "settings_page_rcCM-cm") { 
        
        wp_enqueue_script( 'jquery' ); 
        ?>
        
        <script type="text/javascript">    
        <!--
             
             
        jQuery(function($) {
            var selectedOption =  $('#display_list_type option:selected');
            //reset to default if page is refreshed
            $(document).ready( function(){
                toggleDisplay();
            });
            
            $('#display_list_type').change(function(){
                selectedOption =  $('#display_list_type option:selected');
                toggleDisplay();
             
           });
           
           function toggleDisplay() {
               
                if (selectedOption.val() == "One") {
                    $("#display_list_typeResult").css("display","block");
                } else {
                    $("#display_list_typeResult").css("display","none");
                } 
           }
        });
        -->
        </script>
<?php
    }
}


//include get user
require_once 'get-user.php';

//include add user
require_once 'add-user.php';

// email validator function
function valid_email($email) {
    if (ereg("^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$", $email)) {
        return true;
    } 
    else {
        return false;
    }
}

/*
//Register a function that will intercept the submitted form: 
add_action( 'template_redirect', 'rcCM_add_user_info' );

//Implement the rcCM_add_user_info function: 
function rcCM_add_user_info( $template ) {
    
    if ( !empty( $_POST[ 'rcCM_add_user_info' ] ) ) {
        rcCM_process_add_user_info();
    } else {
        return $template;
    }
}

//Implementation for the rcCM_process_get_user_info:
function rcCM_process_add_user_info(){
    
    
    //Check that all required fields are present and non-empty
    if ( wp_verify_nonce( $_POST[ 'rcCM_add_user_info' ], 'add_user_form') && 
        !empty( $_POST[ 'email' ] ) ) {
        
        // Variable used to determined if submission is valid
        $valid = true;
        
        if ( !valid_email($_POST[ 'email' ])) {
            $valid = false;
        }       
    }
} */

//add javascript file to footer
//add_action( 'wp_footer', 'rcCM_footer_code' );

function rcCM_footer_code() {
    ?>
<script type="text/javascript">

        function _(el) {
            return document.getElementById(el);	
        }

        function unsubscribeAll() {
                // definitely unsubscribe all

                var form = document.getElementById('getUserForm');
            var inputs = form.elements;

                if(!inputs){
                //no inputs found
                return;
            }

            if(!inputs.length){
                //only one elements, forcing into an array"
                inputs = new Array(inputs);        
            }

            for (var i = 0; i < inputs.length; i++) {  
              //checking input
              if (inputs[i].type == "checkbox" && inputs[i].id != "chkUnsubscribeAll"  && inputs["chkUnsubscribeAll"].checked == true) {  
                        inputs[i].checked = false;
              }  
            }  
        }

        function checkForUnsubscribeAll() {
                //possibility of unsubscribe all - check if all the other checkboxes are unchecked and if so checked the unsubscribe checkbox

                var form = document.getElementById('getUserForm');
            var inputs = form.elements;
                var bUnsubscribeAll = true;

                if(!inputs){
                //no inputs found
                return;
            }

            if(!inputs.length){
                //only one elements, forcing into an array"
                inputs = new Array(inputs);        
            }

            for (var i = 0; i < inputs.length; i++) {  
              //check to see if any checkbox has been selected; if so unsubscribe all is false.
              if (inputs[i].type == "checkbox" && inputs[i].checked == true) {  
                        bUnsubscribeAll = false;
              }  
            } 

                // if all checkboxes are empty, then check off the unsubscribe all check box
                if (bUnsubscribeAll)
                        _("chkUnsubscribeAll").checked = true;
                else
                        _("chkUnsubscribeAll").checked = false;
        }
    </script>
<?php
}
