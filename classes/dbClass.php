<?php

class db
{
    function __construct($config)
    {
            $this->config = $config;
    }

    function __destruct()
    {

    }
	
    //public function checkForSubscriber( $personName, $email ) {
	public function checkForSubscriber( $email ) {

		$personName = "Ronan Corr";
        global $wpdb;
        
        //split name into first and last name
        $names = explode(" ", $personName);

        for( $i = 0; $i < count($names); $i++ ) {

            if ($i == 0 ) {
                    $firstName = $names[$i];
                    $lastName = ""; // delcare the varible
            } else
                    $lastName .= $names[$i] . " ";
        }

        // check if email exist in the database
        //$user_data = $wpdb->get_row( $wpdb->prepare('SELECT * FROM  ' . $wpdb->get_blog_prefix() . "campaign_monitor WHERE email = '%s'", $email ), ARRAY_A);
        $user_data = $wpdb->get_row( $wpdb->prepare('SELECT * FROM  ' . $wpdb->get_blog_prefix() . "campaign_monitor WHERE email = '%s' AND firstName = '%s' AND lastName = '%s'", $email, $firstName, $lastName ), ARRAY_A);

        if ($wpdb->num_rows > 0) {
            return true;
        } else {
            return false;
        }     
    }
	
    public function getSubscriberDetails ( $email ) {

        //create an array for provinces
        $provinceArray = array("Alberta", "British Columbia", "Manitoba", "New Brunswick", "North West Territories", "Nova Scotia", "Nunavut", "Ontario", "Prince Edward Island", "Quebec", "Saskatchewan", "Yukon");

        //check for subscriber - if presents get subscriber's details
        if ($this->checkForSubscriber( $email ) ) { 

            global $wpdb;

            // retrieve user's details from database
            $user_data = $wpdb->get_row( $wpdb->prepare('SELECT * FROM  ' . $wpdb->get_blog_prefix() . "campaign_monitor WHERE email = '%s'", $email ), ARRAY_A);

            //assigns user's details to variables
            $name = $user_data[ "firstName" ] . " " . $user_data[ "lastName" ];
            $province = $user_data[ "province" ];
            $status = $user_data[ "status" ];
            $subscribeDate = $user_data[ "subscribeDate" ];
            $lang = $user_data[ "lang" ];

            //build the output to show subscriber's details
            $output =  "<p>Name: " . $name;
            $output .= "<br/>Status: " . $status;
            if ($status == "Active")
                $output .= "<br>Active since: " . $subscribeDate;

            $output .= '<br/>Province: <select name="province">'; 

            // add all province to select box and select the subscriber's province
            foreach ($provinceArray as $prov) {

                $output .= '<option value="' . $prov . '"';

                if ($province == $prov) {
                    $output .= ' selected="selected"';	
                }

                $output .='>' . $prov . '</option>';
            }			

            $output .= '</select><br>';

            $englishCheck='checked="checked"';
            $frenchCheck='';

            if ($lang == "French") {
                $englishCheck='';
                $frenchCheck='checked="checked"';
            }

            $output .='Preferred Languagee: <input type="radio" name="lang" value="English"' .  
                    $englishCheck . '>&nbsp;English&nbsp;&nbsp;<input type="radio" name="lang" value="French"' . 
                    $frenchCheck . '>&nbsp;French</option>';

            $output .= "</p>";	
        }
        else 
            $output = "<p>No user found</p>";

        echo $output;
    }
    
    public function getSubscriberName ( $email ) {

        global $wpdb;

        // retrieve user's name from database
        $user_data = $wpdb->get_row( $wpdb->prepare('SELECT firstName, lastName FROM  ' . $wpdb->get_blog_prefix() . "campaign_monitor WHERE email = '%s'", $email ), ARRAY_A);

        //assigns user's details to variables
        $name = $user_data[ "firstName" ] . " " . $user_data[ "lastName" ];

        return $name;
    }
	
    //public function InsertUser( $database, $table, $name, $email, $province) {
    public function InsertUser($name, $email, $province, $lang) {  

        global $wpdb;
        
        //Place all user submitted values in an array (or empty strings in no value were sent)
        $user_data = array();
        
        //split name into first and last name
        $names = explode(" ", $name);

        for( $i = 0; $i < count($names); $i++ ) {

            if ($i == 0 ) {
                    $firstName = $names[$i];
                    $lastName = ""; // delcare the varible
            } else
                    $lastName .= $names[$i] . " ";
        }
        
        $user_data['firstName'] = $firstName;
        $user_data['lastName'] = $lastName;
        $user_data['email'] = $email;
        $user_data['province'] = $province;
        $user_data['status'] = "Active";
        $user_data['subscribeDate'] = date("Y-m-d");
        $user_data['lang'] = $lang;

        $wpdb->insert( $wpdb->get_blog_prefix() . 'campaign_monitor', $user_data );
			
    }
    
    public function UpdateUser ( $email, $province, $lang ) {

        global $wpdb;

         //Place all user submitted values in an array (or empty strings in no value were sent)
        $user_data = array();

        $user_data['status'] = "Active";
        $user_data['province'] = $province;
        $user_data['lang'] = $lang;

        //Call the wpdb update method
         $wpdb->update( $wpdb->get_blog_prefix() . 'campaign_monitor', $user_data, array( 'email' => $email ) );

    }

    public function UnsubscribeUser ( $email, $province, $lang ) {

        global $wpdb;

        $user_data['status'] = "Unsubscribed";
        $user_data['province'] = $province;
        $user_data['lang'] = $lang;

        //Call the wpdb update method
         $wpdb->update( $wpdb->get_blog_prefix() . 'campaign_monitor', $user_data, array( 'email' => $email ) );

    }
}