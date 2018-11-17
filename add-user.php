<?php

//Declare a new shortcode and its associated function to add user info:
add_shortcode( 'cm-add-user-form', 'rcCM_add_user_form' );

function rcCM_add_user_form() {
    
     //create new campaign monitor object
     $cm = new cmMaintenance();
     
     //create new db object
     //$db = new db($config);

    //Retrieve plugin configuration options from database
    $options = get_option( 'rcCM_options' ); 
     
     if (isset($_POST["submit"])) {
         
        
        
        if ( $options[ 'display_name_field' ] == "Yes" )  {
            $personName = $_POST['personName'];
        } else {
            $personName = '';
        }
        
        $email = $_POST['email'];
        $province = $_POST['province'];
        $lang = $_POST['lang'];

        $subscribe = $_POST['subscribe'];
        
        //determine if there is a form element "subscribe" - if so convert into an array
        if ( !empty( $subscribe )) {

            $selectedPreferences = $subscribe;

            //convert $selectedPreferences into an array
            $selectedPreferences = explode( ",", $selectedPreferences );
        } else {
            //assign user's preferences
            $selectedPreferences = $cm->getSelectedPreferences($email);
      
            //received the unselected preferences base from the selected preferences
            //$unSelectedPreferences = $cm->getUnselectedPreferences($selectedPreferences);
        }
     }   
   
?>
    <form method="post" id="addUserForm" action="">
        
        <?php if ( ( $options[ 'display_name_field' ] == "Yes" && trim($personName) == "") || valid_email($email) == false || empty($selectedPreferences) ) { 
              ///if ( trim($personName) == "" || valid_email($email) == false || empty($selectedPreferences) ) { 
                //if ( valid_email($email) == false ) {?>
       
                    <!--Nonce fields to verify visitor provenance -->
                    <?php wp_nonce_field( 'add_user_form', 'rcCM_add_user_info' ); 


                    if (isset($_POST["submit"]) && ( valid_email($email) == false || empty($selectedPreferences) )) { 
                    //if (isset($_POST["submit"]) && ( trim($personName) == "" || valid_email($email) == false || empty($selectedPreferences) )) {  ?>
                        <div style="margin:8px 0; padding:8px 0; color:#f00;">
                            Form contains errors.  Please check again.
                        </div>
                    <?php    
                    }

                    if ( $options[ 'display_list_type'] =="All" ) {
                    ?>
                        <p>To hear our recent news, please provide your email address, province, language preference, and what sort of information you would like to received from us:</p>
                    <?php } else { ?>
                        <p>To hear our recent news, please provide your email address, province, and your language preference</p>  
                    <?php
                    }
                    
                    ?>
                         
                     <!-- Post variable to indicate user-submission items -->
                    <input type="hidden" name="rcCM_add_user_info" value="1" /> 
        <?php  if ($options[ 'display_name_field' ] == "Yes" ) {?>
                    <label for="personName" style="<?php if (isset($_POST["submit"]) && trim($personName) == "") { ?> color:#F00;  <?php } ?>">Name:</label><br/><input type="text" name="personName" id="personName"  size="30" value="<?php echo trim($personName); ?>" /><?php if (isset($_POST["submit"]) && trim($personName) == "") { ?> <span style="color:#F00;">Required</span> <?php } ?><br/><br/>
        <?php } ?>
            <label for="email" style="<?php if (isset($_POST["submit"]) && valid_email($email) == false) { ?> color:#F00; <?php } ?>">Email Address:</label><br/><input type="text" name="email" id="email" size="30" value="<?php echo trim($email); ?>" /><?php if (isset($email) && trim( $email ) == "") { ?> <span style="color:#F00;">Required</span> <?php } ?><?php if (isset($_POST["submit"]) && $email !="" && valid_email($email) == false) { ?> <span style="color:#F00;">Email is not correct syntax</span> <?php } ?><br/><br/>
            <label for="province">Province:</label><br/>
            <select name="province" id="province">
                    <option value="Alberta" <?php if ($province =="Alberta") { ?> selected="selected" <?php }?>>Alberta</option>
                    <option value="British Columbia" <?php if ($province =="British Columbia") { ?> selected="selected" <?php }?>>British Columbia</option>
                    <option value="Manitoba" <?php if ($province =="Manitoba") { ?> selected="selected" <?php }?>>Manitoba</option>
                    <option value="New Brunswick" <?php if ($province =="New Brunswick") { ?> selected="selected" <?php }?>>New Brunswick</option>
                    <option value="Newfoundland and Labrador" <?php if ($province =="Newfoundland and Labrador") { ?> selected="selected" <?php }?>>Newfoundland &amp; Labrador</option>
                    <option value="North West Territories" <?php if ($province =="North West Territories") { ?> selected="selected" <?php }?>>North West Territories</option>
                    <option value="Nova Scotia" <?php if ($province =="Nova Scotia") { ?> selected="selected" <?php }?>>Nova Scotia</option>
                    <option value="Nunavut" <?php if ($province =="Nunavut") { ?> selected="selected" <?php }?>>Nunavut</option>
                    <option value="Ontario" <?php if ($province =="Ontario") { ?> selected="selected" <?php }?>>Ontario</option>
                    <option value="Prince Edward Island" <?php if ($province =="Prince Edward Island") { ?> selected="selected" <?php }?>>Prince Edward Island</option>
                    <option value="Quebec" <?php if ($province =="Quebec") { ?> selected="selected" <?php }?>>Quebec</option>
                    <option value="Saskatchewan" <?php if ($province =="Saskatchewan") { ?> selected="selected" <?php }?>>Saskatchewan</option>
                    <option value="Yukon" <?php if ($province =="Yukon") { ?> selected="selected" <?php }?>>Yukon</option>
                    <option value="Other" <?php if ($province =="Other") { ?> selected="selected" <?php }?>>USA/International</option>
            </select><br/><br/>

        <?php } ?>
    
<!--determine which language should be selected.-->
<?php 
if ($lang == "French") {
    $EnglishCheck = "";
    $FrenchCheck = 'checked="checked"';
} else {
   $EnglishCheck = 'checked="checked"';
    $FrenchCheck = ""; 
} 

//if ( trim($personName) == "" || valid_email($email) == false || empty($selectedPreferences) ) {
if ( ( $options[ 'display_name_field' ] == "Yes" && trim($personName) == "") || valid_email($email) == false || empty($selectedPreferences) ) {    
?>
<p>Preferred Language: <input type="radio" name="lang" value="English" <?php echo $EnglishCheck; ?>>&nbsp;English&nbsp;&nbsp;<input type="radio" name="lang" value="French" <?php echo $FrenchCheck; ?>>&nbsp;French</option></p>

<?php 
    //Determine if all lists are displayed or just one list
    
    if ( $options[ 'display_list_type'] =="All" ) {
?>

<p style="margin-bottom:0px; padding-bottom:0px;">I would like to receive e-mails on the following: <?php if (isset($_POST["submit"]) && empty($selectedPreferences)) { ?> <span style="color:#F00;"> Must select at least one </span> <?php } ?> </p>
<?php 
    }
}
       if (!isset($_POST["submit"]) AND !isset($_POST["update"])) {
           //dummy@earthday.ca is use just to display the lists without any checkboxes ticked
           $cm->getLists("dummy@earthday.ca", "Add", $selectedPreferences , false);
       } else {
           
            //Check to see form is valid
            if (isset($_POST["submit"]) && ( ( $options[ 'display_name_field' ] == "Yes" && trim($personName) == "") || valid_email($email) == false || empty($selectedPreferences) )) {
            //if (isset($_POST["submit"]) && ( trim($personName) == "" || valid_email($email) == false || empty($selectedPreferences) )) {
           //if (isset($_POST["submit"]) && ( valid_email($email) == false )) {
                //$cm->getLists("dummy@earthday.ca", "Add");
                $cm->getLists("dummy@earthday.ca", "Add", $selectedPreferences, true);
            } else {
               
                //update list on Campaign Monitor side
                //$cm->UpdateUser($personName, $email, $province, $lang, $selectedPreferences, "Add");
                $cm->UpdateUser($email, $province, $lang, $selectedPreferences, "Add");
               
                //echo "<p>" . trim($personName) . ",</p>";
                    
                foreach ($selectedPreferences as $preference) {
                    
                    $listName = $cm->getListName($preference); 
                    
                    if ( $cm->isUserOnList($email, $preference) == "active") {
                        echo "<p>Great news!  We see that you are already subscribed to <strong>$listName</strong> newsletter.</p>";   
                    } else if ( $cm->isUserOnList($email, $preference) == "unconfirmed") {
                        echo "<p>It looks like you are already on <strong>$listName</strong> mailing list, but haven't confirmed your subscription. ";
                        echo "We've sent you another confirmation email.  Please check your <strong>$email</strong> account and confirm to $listName newsletter.</p>"; 
                    } else {
                        echo "<p>Thank you for subscribing to <strong>" . $cm->getListName($preference) . "</strong>. ";
                        echo "An email has been sent to <strong>$email</strong>. Please check your <strong>$email</strong> account and confirm your subscription to $listName newsletter.</p>";
                    }          
                }
                
                echo "<p>&mdash;The Earth Day Canada Team</p>";
                
                //see if user is already in WordPress campaign_monitor table AND is a list on CM side
                //if ( $db->checkForSubscriber($personName, $email) ) {

                    //update list on Campaign Monitor side
                    //$cm->UpdateUser($personName, $email, $province, $lang, $selectedPreferences, "Add");
                    //$cm->UpdateUser($personName, $email, $province, $lang , $unSelectedPreferences, "Remove");

                //} else {
                    //echo "insert user";
                    // enter user into database
                    //$db->InsertUser($personName, $email, $province, $lang); 
                //}

                //update user info in database
                /*if (!$cm->checkListsForSubscriber($email)) { 
                    echo "changed status in database to unsubscribe";
                    //$db->UnsubscribeUser($email, $_POST['province'], $_POST['lang']);   
                } else {
                    echo "update user info in database";
                    //$db->UpdateUser($email, $_POST['province'], $_POST['lang']);
                }
                 
                 */
                
               //$cm->getLists($email, "Update");
           }   
       }      
?>
    </form>
<?php 
}