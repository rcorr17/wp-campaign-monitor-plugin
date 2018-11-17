<?php

//Declare a new shortcode and its associated function to get user info:
add_shortcode( 'cm-get-user-form', 'rcCM_get_user_form' );

//Add the following code segment to provide an implementation for the
//   rcCM_get_user_form function:
function rcCM_get_user_form() { ?>
    <form method="post" id="getUserForm" action="">
        
        <?php 
        
        if (isset($_POST["submit"]) || isset($_POST["update"]) )
            $email=$_POST["email"];
        else 
            $email = "";
        
        if (isset($_POST["submit"])) {
            
            //echo 'submit';
            
            if ( !valid_email($email)) { ?>
                <div style="margin:8px; padding:8px; color:#f00;">
                Form contains errors.  Please check again.
            </div>
            <?php
                $emailErr = 'style="color:#ff0000;"';     
            }
       } 
        ?>
        
        <!--Nonce fields to verify visitor provenance -->
        <?php wp_nonce_field( 'get_user_form', 'rcCM_get_user' ); ?>
        
         <!-- Post variable to indicate user-submission items -->
        <input type="hidden" name="rcCM_user_info" value="1" />
        
        <label <?php echo $emailErr; ?>>Please type in an email address:&nbsp;&nbsp;</label><input type="text" name="email" value="<?php echo $email; ?>" size="50" />
       <br/><br/>
        <input type="submit" name="submit" value="Get User" /><br /><br/>
    

    <?php 
    // "Get User" button is clicked    
    if (isset($_POST["submit"]) && (valid_email($email))) {
       
        //create new campaign monitor object
        $cm = new cmMaintenance();

        //create new db object
        $db = new db($config);
	   
        //get subscriber's details
        //$db->getSubscriberDetails( $email ); 

        if ($db->checkForSubscriber( $email )) {
			
            //get the lists		
            $cm->getLists( $email, "Update" );
            echo "<br ><br />";
        }     
    } else {
		echo "hey you update";
        //"Update User" button is clicked
        if (isset($_POST["update"]) && (valid_email($email))) {
            
            //create new campaign monitor object
            $cm = new cmMaintenance();

            $config ="";

            //create new db object
            $db = new db($config);
            
            //assign user's preferences
            $selectedPreferences = $cm->getSelectedPreferences($email);
            
            echo "hey " . $selectedPreferences;
               
            //received the unselected preferences base from the selected preferences
            //$unSelectedPreferences = $cm->getUnselectedPreferences($selectedPreferences);

            //assign subscriber's name base on his/her email address
            $subscriberName = $db->getSubscriberName($email);

            //update user on Campaign Monitor side
            $cm->UpdateUser($subscriberName, $email, $_POST['province'], $_POST['lang'], $selectedPreferences, "Add");

            //$cm->UpdateUser($subscriberName, $email, $_POST['province'], $_POST['lang'], $unSelectedPreferences, "Remove");

            //update user in our database
            /*if (!$cm->checkListsForSubscriber($email)) {
                $db->UnsubscribeUser($email, $_POST['province'], $_POST['lang']);
            } else {
                $db->UpdateUser($email, $_POST['province'], $_POST['lang']);
            }*/
            
            // refresh subscriber's details
            //$db->getSubscriberDetails( $email );
            
            // refresh the lists
            //$cm->getLists( $email, "Update" );     
        } 
    }
    ?>
    </form>

<?php }