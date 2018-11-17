<?php

require_once 'csrest_subscribers.php';
require_once 'csrest_clients.php';

class cmMaintenance
{
    
    //Retrieve plugin configuration options from database
    private static function getAuthKey() {
        
        //Retrieve plugin configuration options from database
        $options = get_option( 'rcCM_options' );
	$auth = array('api_key' => $options[ 'api_key' ] );
        
        return $auth;
    }

    private static function getClientKey($auth) {
        
        //Retrieve plugin configuration options from database
        $options = get_option( 'rcCM_options' );
        $client = new CS_REST_Clients( $options[ 'client_key' ], $auth );
         
        return $client;
    }

    private static function getSubscriberKey($auth) {
        
        //Retrieve plugin configuration options from database
        $options = get_option( 'rcCM_options' );
        $subscriber = new CS_REST_Subscribers( esc_html( $options[ 'subscriber_key' ] ), $auth);

        return $subscriber;	
    }
     
    public function testConnection () {
        $auth = $this->getAuthKey();
       
        $client = $this->getClientKey($auth);

        $clientLists = $client->get_lists();
        
        if($clientLists->was_successful()) {
            return true;
        } else {
            return false;
        }
    }
    
    public function isOnSupressionList($email) {

        $bFound = false;

        $auth = $this->getAuthKey();

        $client = $this->getClientKey($auth);

        $suppressionListResult = $client->get_suppressionlist(1, 50, 'email', 'asc');

        if($suppressionListResult->was_successful()) {

            // Check if user is on suppression list
            foreach ($suppressionListResult->response->Results as $field) {
                    if ($field->EmailAddress == $email) {
                            $bFound = true;
                    }
            }

            return $bFound;
        }
    }

    public function checkListsForSubscriber( $email) {

        $auth = $this->getAuthKey();

        $client = $this->getClientKey($auth);

        $bFound = false;

        $listsForEmail = $client->get_lists_for_email($email);

        if($listsForEmail->was_successful()) {

            foreach ($listsForEmail->response as $field) {

                //check is user is active
                if ($field->SubscriberState == "Active") {
                    $bFound = true;
                }
            }
        }

        return $bFound;
    }
    
    public function isUserOnList( $email , $listID ) {
        $auth = $this->getAuthKey();
       
        $client = $this->getClientKey($auth);

        $clientLists = $client->get_lists();

        $userSubscribeToLists = $client->get_lists_for_email( $email );

        if($clientLists->was_successful()) {

            //varible to determine subscriber's status is active
            $subscriberStatus = "not on list";

            foreach( $clientLists->response as $list) {
                    
                //obtain detail values for each list
                //$listID = $list->ListID;
                if ( $list->ListID == $listID ) {
                    $listName = $list->Name;
                }

                // determine if list has a space in it then remove it for the field name
                if  ( preg_match('/\s/', $list->Name ) ) {
                        $fieldName = str_replace(' ', '', $list->Name);
                } else {
                        $fieldName = $list->Name;
                }

                foreach ($userSubscribeToLists->response as $field) {
                    //variable to add to input tag that determines if checkbox is checked or not
                    if ($field->ListName == $listName && $field->SubscriberState == "Active") {
                        $subscriberStatus = "active";
                    } else if ($field->ListName == $listName && $field->SubscriberState == "Unconfirmed")  {
                        $subscriberStatus = "unconfirmed";
                    }                
                }
            }
        }
        
        return $subscriberStatus;
    }
   
    
    //use when user is not subscribed on any lists
    public function getLists ( $email, $action ) {
    //public function getLists ( $email, $action, $listPreference, $formSubmitted ) {

        $auth = $this->getAuthKey();
       
        $client = $this->getClientKey($auth);

        $clientLists = $client->get_lists();

        $userSubscribeToLists = $client->get_lists_for_email( $email );

        if($clientLists->was_successful()) {

            //varible to determine if subscriber is active
            $bActive = false;
            
            //Retrieve plugin configuration options from database
            $options = get_option( 'rcCM_options' );
            
            //determine if all the lists are in use or just a specific list
            if ( $options[ 'display_list_type'] =="All" ) {
                foreach( $clientLists->response as $list) {

                    $checked = " ";
                    //obtain detail values for each list
                    $listID = $list->ListID;
                    $listName = $list->Name;

                    // determine if list has a space in it then remove it for the field name
                    if  ( preg_match('/\s/', $list->Name ) ) {
                            $fieldName = str_replace(' ', '', $list->Name);
                    } else {
                            $fieldName = $list->Name;
                    }

                    foreach ($userSubscribeToLists->response as $field) {
                            //variable to add to input tag that determines if checkbox is checked or not
                            if ($field->ListName == $listName && $field->SubscriberState == "Active") {
                                    $checked = 	' checked="checked"';
                                    $bActive = true;
                            }
                    }

                    echo '<label><input type="checkbox" name="' . $fieldName . '" value="' . $listID .'" onclick="checkForUnsubscribeAll(this)"' . $checked . '> ' . $listName . '</label><br />';

                }
            } else {
               
                foreach( $clientLists->response as $list) {

                    $checked = 	' checked="checked"';
                    //obtain detail values for each list
                    $listID = $list->ListID;
                    $listName = $list->Name;

                    // determine if list has a space in it then remove it for the field name
                    if  ( preg_match('/\s/', $list->Name ) ) {
                        $fieldName = str_replace(' ', '', $list->Name);
                    } else {
                        $fieldName = $list->Name;
                    }
                   
                    //determine if form has been submitted or not
                    if ( !$formSubmitted ) {
                        $style = "";
                        $checked = "";
                    } else if ( $formSubmitted && empty($listPreference)) {
                        $style ='style="color:#F00;"';
                        $checked = "";
                    } else {
                        $style = "";
                        $checked = 'checked="checked"';
                    }
                    
                    if ($listID == $options[ 'subscriberList' ] ) {
                        echo '<div ' . $style .'><label><input type="checkbox" name="subscribe" value="' . $listID .'"' . $checked . '>' . ' I would like to recieve information from ' . $listName . '</label></div>';  
                        break;
                    }

                }
                
            }
            
            if ($bActive == true ) {
                $client->unsuppress($email );  // remove user from suppression list.
            } else {
                    $client->suppress($email);
                    $checked = 	' checked="checked"'; // tick off "Unsubscribe from all email communications" checkbox
            }

            if ($action != "Add") {
                if ( $bActive == 1 ) {
                    $checked = 	' '; // tick off "Unsubscribe from all email communications" checkbox
                } else {
                    $checked = 	' checked="checked"'; // tick off "Unsubscribe from all email communications" checkbox
                }

                echo '<p><br/><input type="checkbox" name="chkUnsubscribeAll" id="chkUnsubscribeAll" onclick="unsubscribeAll()"' . $checked;

                echo '/>Unsubscribe from all email communications</p>';


                echo '<input type="submit" name="update" value="Update">';
            }
            else {
                echo '<p><br/><input type="submit" name="submit" value="Subscribe" /></p>';
            }
        } else {
            echo "<p>Uh, no! Your settings are not correct.</p>";
        }
    }
    
    public function adminGetLists ( $type, $subscribe_list ) {

        //if subscribe list is not present then retrieve from database
        if ( empty( $subscribe_list)) {
          
            //Retrieve plugin configuration options from database
            $options = get_option( 'rcCM_options' );
            $subscribe_list = $options[ 'subscriberList' ];
           
        }
        
        $auth = $this->getAuthKey();
		
        $client = $this->getClientKey($auth);

        $clientLists = $client->get_lists();

        /*$userSubscribeToLists = $client->get_lists_for_email( $email );

        if($clientLists->was_successful()) {

            //varible to determine if subscriber is active
            $bActive = false;

            foreach( $clientLists->response as $list) {

                    $checked = " ";
                    //obtain detail values for each list
                    $listID = $list->ListID;
                    $listName = $list->Name;

                    // determine if list has a space in it then remove it for the field name
                    if  ( preg_match('/\s/', $list->Name ) ) {
                            $fieldName = str_replace(' ', '', $list->Name);
                    } else {
                            $fieldName = $list->Name;
                    }

                    foreach ($userSubscribeToLists->response as $field) {
                            //variable to add to input tag that determines if checkbox is checked or not
                            if ($field->ListName == $listName && $field->SubscriberState == "Active") {
                                    $checked = 	' checked="checked"';
                                    $bActive = true;
                            }
                    }

                    if ($type != "Radio") {
                        echo '<label><input type="checkbox" name="' . $fieldName . '" value="' . $listID .'"> ' . $listName . '</label><br />';
                    } else {
                        
                        if ( $listID == $subscribe_list ) {
                            $checked = 'checked="checked"';
                        } else {
                            $checked = '';
                        }
                        
                        echo '<label><input type="radio" name="subscriberList" value="' . $listID  . '" ' . $checked . '/>' . $listName . '</label><br/>';
                       
                    }
            }
            
        } */
    }
    
    public function getListName($id) {
        
        $auth = $this->getAuthKey();
       
        $client = $this->getClientKey($auth);

        $clientLists = $client->get_lists();
        
        if($clientLists->was_successful()) {
                
            foreach( $clientLists->response as $list) {

                //obtain detail values for each list
                if ($list->ListID == $id) {
                    return $list->Name;
                    break;
                }
            }
        }   
    }

    public function getSelectedPreferences ($email) {

        $auth = $this->getAuthKey();

        $client = $this->getClientKey($auth);

        $clientLists = $client->get_lists();

        if($clientLists->was_successful()) {

                //create an array for selected preferences
                $selectedPreferences = array();

                // loop through each list in client to determine if user is on it
                foreach ($clientLists->response as $field) {

                        // determine if list has a space in it then remove it for the field name
                        if  ( preg_match('/\s/', $field->Name ) ) {
                                $fieldName = str_replace(' ', '', $field->Name);
                        } else {
                                $fieldName = $field->Name;
                        }

                        // check to see if checkbox has been ticked.  If so add it to the $selectedPreferences array
                        if ( isset($_POST[$fieldName])) {
                                array_push($selectedPreferences, $_POST[$fieldName]);	
                        }
                }

                return $selectedPreferences;
        }
    }


    //return an array of unslected preferences
    public function getUnselectedPreferences ($selectedPreferences) {

        $auth = $this->getAuthKey();

        $client = $this->getClientKey($auth);

        $clientLists = $client->get_lists();

        if($clientLists->was_successful()) {

                //create an array for unselected preferences
                $unselectedPreferences = array();

                // loop through each list in client to determine if user is on it
                foreach ($clientLists->response as $field) {
                        $bFound = false;

                        foreach( $selectedPreferences as $preference) {
                                if ( $field->ListID == $preference ) {
                                    $bFound = true;
                                }
                        }

                        //if list ID was not present, add it to unselectedPreferences array
                        if ( $bFound == false ) {
                                array_push($unselectedPreferences, $field->ListID);
                        }
                }

                return $unselectedPreferences;
        }
    }

    //public function UpdateUser( $name, $email, $province, $lang, $lists, $action) {
    public function UpdateUser( $email, $province, $lang, $lists, $action) {    

        $auth = $this->getAuthKey();

        foreach ($lists as $list) {

                $subscriber = new CS_REST_Subscribers($list, $auth);

                if ($action == "Add") {

                        $subscriber->add(array(
                        'EmailAddress' => $email,
                        //'Name' => $name,
                        'CustomFields' => array(
                                        array(
                                                'Key' => 'Province',
                                                'Value' => $province
                                        ),
                                        array(
                                                'Key' => 'Language',
                                                'Value' => $lang
                                        ), 
                                ),
                                'Resubscribe' => true
                        ));

                } else if ($action == "Remove") {
                        $subscriber->unsubscribe($email);	
                }
        }
    }	
}