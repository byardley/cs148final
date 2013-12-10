<?php
/* the purpose of this page is to display a form to allow a person to register
 * the form will be sticky meaning if there is a mistake the data previously 
 * entered will be displayed again. Once a form is submitted (to this same page)
 * we first sanitize our data by replacing html codes with the html character.
 * then we check to see if the data is valid. if data is valid enter the data 
 * into the table and we send and dispplay a confirmation email message. 
 * 
 * if the data is incorrect we flag the errors.
 * 
 * Written By: Robert Erickson robert.erickson@uvm.edu
 * Last updated on: October 10, 2013
 * 
 * 
  -- --------------------------------------------------------
  --
  -- Table structure for table `tblRegister`
  --

  CREATE TABLE IF NOT EXISTS `tblRegister` (
  `pkRegisterId` int(11) NOT NULL AUTO_INCREMENT,
  `fldEmail` varchar(65) DEFAULT NULL,
  `fldDateJoined` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fldConfirmed` tinyint(1) NOT NULL DEFAULT '0',
  `fldApproved` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`pkPersonId`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

 * I am using a surrogate key for demonstration, 
 * email would make a good primary key as well which would prevent someone
 * from entering an email address in more than one record.
 */

//-----------------------------------------------------------------------------
// 
// Initialize variables
//  

$debug = true;
if ($debug) print "<p>DEBUG MODE IS ON</p>";

$baseURL = "http://www.uvm.edu/~byardley/";
$folderPath = "cs148/assignment7.1/";
// full URL of this form
$yourURL = $baseURL . $folderPath . "register.php";

require_once("connect.php");

//#############################################################################
// set all form variables to their default value on the form. for testing i set
// to my email address. you lose 10% on your grade if you forget to change it.

$firstName = "Ben";
$lastName = "Yardley";
$email = "byardley@uvm.edu";
$route = "";
$boulder = "";
$soft = false;
$hard = false;
$reachy = false;
$poor= false;
$star = false;
$comments = "";
$again= "";
$rating="";

//#############################################################################
// 
// flags for errors
$firstNameERROR = false;
$lastNameERROR = false;
$emailERROR = false;





//#############################################################################
//  
$mailed = false;
$messageA = "";
$messageB = "";
$messageC = "";


//-----------------------------------------------------------------------------
// 
// Checking to see if the form's been submitted. if not we just skip this whole 
// section and display the form
// 
//#############################################################################
// minor security check

if (isset($_POST["btnSubmit"])) {
    $fromPage = getenv("http_referer");

    if ($debug)
        print "<p>From: " . $fromPage . " should match ";
        print "<p>Your: " . $yourURL;

    if ($fromPage != $yourURL) {
        die("<p>Sorry you cannot access this page. Security breach detected and reported.</p>");
    }


//#############################################################################
// replace any html or javascript code with html entities
//
    $firstName = htmlentities($_POST["txtFirstName"],ENT_QUOTES,"UTF-8");
    $lastName = htmlentities($_POST["txtLastName"],ENT_QUOTES,"UTF-8");
    $email = htmlentities($_POST["txtEmail"], ENT_QUOTES, "UTF-8");
    $comments = htmlentities($_POST["txtComments"],ENT_QUOTES,"UTF-8");
    $again = htmlentities($_POST["radAgain"],ENT_QUOTES,"UTF-8");
    $route = htmlentities($_POST["lstRoute"],ENT_QUOTES,"UTF-8");
    $boulder = htmlentities($_POST["lstBoulder"],ENT_QUOTES,"UTF-8");
    $rating = htmlentities($_POST["lstRating"],ENT_QUOTES,"UTF-8");
    
    if(isset($_POST["chksSoft"])) {
        $soft  = true;
    }else{
        $soft  = false;
    }
    
    if(isset($_POST["chkHard"])) {
        $hard  = true;
    }else{
        $hard  = false;
    }
     if(isset($_POST["chkReachy"])) {
        $reachy  = true;
    }else{
        $reachy  = false;
    }
     if(isset($_POST["chkPoor"])) {
        $poor  = true;
    }else{
        $poor  = false;
    }
     if(isset($_POST["chkStar"])) {
        $star  = true;
    }else{
        $star  = false;
    }
//#############################################################################
// 
// Check for mistakes using validation functions
//
// create array to hold mistakes
// 

    include ("validation_functions.php");

    $errorMsg = array();


//############################################################################
// 
// Check each of the fields for errors then adding any mistakes to the array.
//
    //^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^       Check email address
      if(empty($firstName)){
       $errorMsg[]="Please enter your First Name";
       $firstNameERROR = true;
    } else {
       $valid = verifyAlphaNum ($firstName); /* test for non-valid  data */
       if (!$valid){ 
           $errorMsg[]="First Name must be letters and numbers, spaces, dashes and single quotes only.";
           $firstNameERROR = true;
       }
    }
    
    if(empty($lastName)){
       $errorMsg[]="Please enter your Last Name";
       $lastNameERROR = true;
    } else {
       $valid = verifyAlphaNum ($lastName); /* test for non-valid  data */
       if (!$valid){ 
           $errorMsg[]="Last Name must be letters and numbers, spaces, dashes and single quotes only.";
           $lastNameERROR = true;
       }
    }
    if (empty($email)) {
        $errorMsg[] = "Please enter your Email Address";
        $emailERROR = true;
    } else {
        $valid = verifyEmail($email); /* test for non-valid  data */
        if (!$valid) {
            $errorMsg[] = "I'm sorry, the email address you entered is not valid.";
            $emailERROR = true;
        }
    }


//############################################################################
// 
// Processing the Data of the form
//

    if (!$errorMsg) {
        if ($debug) print "<p>Form is valid</p>";

//############################################################################
//
// the form is valid so now save the information
//    
        
        $primaryKey = "";
        $dataEntered = false;
        
        try {
            $db->beginTransaction();
           
            $sql = 'INSERT INTO tblUser(fldEmail,fldFirstName,fldLastName) ';
            $sql.= 'VALUES ("' . $email . '","' . $firstName . '","' . $lastName . '");';
                   
            $stmt = $db->prepare($sql);
            if ($debug) print "<p>sql ". $sql;
       
            $stmt->execute();
            
            $primaryKey = $db->lastInsertId();
            if ($debug) print "<p>pk= " . $primaryKey;

            // all sql statements are done so lets commit to our changes
            $dataEntered = $db->commit();
            if ($debug) print "<p>transaction complete ";
        } catch (PDOExecption $e) {
            $db->rollback();
            if ($debug) print "Error!: " . $e->getMessage() . "</br>";
            $errorMsg[] = "There was a problem with accpeting your data please contact us directly.";
        }
        try {
        
        $sql = 'INSERT INTO tblUserRoute(fkUserID,fkRouteID,fldRating,fldSoft,fldHard,fldReachy,fldPoor,fldStar,fldAgain) ';
        $sql.= 'VALUES ("' . $primaryKey . '","'.$route.'","' . $rating . '","' . $soft . '","' . $hard . '","' . $reachy . '","' . $poor . '","'.$star.'","'.$again.'");';
        if ($debug) print "<p>sql ". $sql;

        $stmt = $db->prepare($sql);
            
        $stmt->execute();} 
        catch (PDOExecption $e) {
            $db->rollback();
            if ($debug) print "Error!: " . $e->getMessage() . "</br>";
        $errorMsg[] = "There was a problem with accpeting your data please contact us directly.";}
    
        

        // If the transaction was successful, give success message
       // if ($dataEntered) {
        //    if ($debug) print "<p>data entered now prepare keys ";
            //#################################################################
            // create a key value for confirmation

            $sql = "SELECT fldDateJoined FROM tblRegister WHERE pkRegisterId=" . $primaryKey;
            $stmt = $db->prepare($sql);
            $stmt->execute();

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $dateSubmitted = $result["fldDateJoined"];

            $key1 = sha1($dateSubmitted);
            $key2 = $primaryKey;

           if ($debug) print "<p>key 1: " . $key1;
            if ($debug) print "<p>key 2: " . $key2;


            //#################################################################
            //
            //Put forms information into a variable to print on the screen
            //

            $messageA = '<h2>Thank you for submitting your feedback.</h2>';

            $messageB = "<p>Click this link to confirm your feedback: ";
            $messageB .= '<a href="' . $baseURL . $folderPath  . 'confirmation.php?q=' . $key1 . '&amp;w=' . $key2 . '">Confirm Registration</a></p>';
            $messageB .= "<p>or copy and paste this url into a web browser: ";
            $messageB .= $baseURL . $folderPath  . 'confirmation.php?q=' . $key1 . '&amp;w=' . $key2 . "</p>";

            $messageC .= "<p><b>Email Address:</b><i>   " . $email . "</i></p>";

            //##############################################################
            //
            // email the form's information
            //
            
            $subject = "Route Survey";
            include_once('mailMessage.php');
            $mailed = sendMail($email, $subject, $messageA . $messageB . $messageC);
        //} //data entered   
    } // no errors 
}// ends if form was submitted. 

    include ("top.php");

    $ext = pathinfo(basename($_SERVER['PHP_SELF']));
    $file_name = basename($_SERVER['PHP_SELF'], '.' . $ext['extension']);

    print '<body id="' . $file_name . '">';

    include ("header.php");
    include ("nav.php");
    ?>

    <section id="main">
        <h1>Register </h1>

        <?php
//############################################################################
//
//  In this block  display the information that was submitted and do not 
//  display the form.
//
        if (isset($_POST["btnSubmit"]) AND empty($errorMsg)) {
            print "<h2>Your Request has ";

            if (!$mailed) {
                echo "not ";
            }

            echo "been processed</h2>";

            print "<p>A copy of this message has ";
            if (!$mailed) {
                echo "not ";
            }
            print "been sent to: " . $email . "</p>";

            echo $messageA . $messageC;
        } else {


//#############################################################################
//
// Here we display any errors that were on the form
//

            print '<div id="errors">';

            if ($errorMsg) {
                echo "<ol>\n";
                foreach ($errorMsg as $err) {
                    echo "<li>" . $err . "</li>\n";
                }
                echo "</ol>\n";
            }

            print '</div>';
            ?>
            
      <form id="frmRegister">           
<?php
//make a query to get all the poets
$sql  = 'SELECT pkRouteID, fldName, fldColor, fldGrade ';
$sql .= 'FROM tblRoute ';
//$sql .= 'WHERE  ';
$sql .= 'ORDER BY fldName';
if ($debug) print "<p>sql ". $sql;

$stmt = $db->prepare($sql);
            
$stmt->execute(); 

$routes = $stmt->fetchAll(); 
if($debug){ print "<pre>"; print_r($routes); print "</pre>";}

// build list box
print '<fieldset class="listbox"><legend>Routes</legend><select name="lstRoute" size="1" tabindex="10">';

foreach ($routes as $route) {
    print '<option value="' . $route['pkRouteID'] . '">'.$route['fldName']. '--' . $route['fldColor'] . '-- ' . $route['fldGrade'] . "</option>\n";
}

print "</select>\n";
print "</fieldset>\n";
print "</form>\n";
 ?>
        
        <form action="<? print $_SERVER['PHP_SELF']; ?>"
                  enctype="multipart/form-data"
                  method="post"
                  id="frmRegister">
                <fieldset class="contact">
                    <legend>Contact Information</legend>

                    <label class="required" for="txtFirstName">Email </label>

                    <input id ="txtFirstName" name="txtFirstName" class="element text medium<?php if ($firstNameERROR) echo ' mistake'; ?>" type="text" maxlength="255" value="<?php echo $firstName; ?>" placeholder="enter your preferred first name" onfocus="this.select()"  tabindex="30"/>
                    
                    <label class="required" for="txtEmail">Email </label>

                    <input id ="txtLastName" name="txtLastName" class="element text medium<?php if ($lastNameERROR) echo ' mistake'; ?>" type="text" maxlength="255" value="<?php echo $lastName; ?>" placeholder="enter your preferred last name" onfocus="this.select()"  tabindex="30"/>
                    
                    
                    <label class="required" for="txtEmail">Email </label>

                    <input id ="txtEmail" name="txtEmail" class="element text medium<?php if ($emailERROR) echo ' mistake'; ?>" type="text" maxlength="255" value="<?php echo $email; ?>" placeholder="enter your preferred email address" onfocus="this.select()"  tabindex="30"/>
                </fieldset> 
       <form action="Update.php"
      method="post"
      id="frmRegister">           

 
                
                <fieldset class="lists">	
	<legend>Rate this problem from 1-5(5 being the highest)</legend>
	<select id="lstYear" name="lstRating" tabindex="281" size="1">
		<option value="1" <?php if($rating=="1") echo ' selected="selected" ';?>>1</option>
		<option value="2" <?php if($rating=="2") echo ' selected="selected" ';?>>2</option>
		<option value="3" <?php if($rating=="3") echo ' selected="selected" ';?>>3</option>
                <option value="4" <?php if($rating=="4") echo ' selected="selected" ';?>>4</option>
                <option value="5" <?php if($rating=="5") echo ' selected="selected" ';?>>5</option>
	</select>
</fieldset>
                
                <fieldset class="checkbox">
	<legend>Check any of the below if the route was one of them</legend>
  	<label><input type="checkbox" id="chkSoft" name="chkSoft" value="Soft" tabindex="221" 
			<?php if($soft) echo ' checked="checked" ';?>> Soft </label>
            
	<label><input type="checkbox" id="chkHard" name="chkHard" value="Hard" tabindex="223" 
			<?php if($hard) echo ' checked="checked" ';?>> Hard</label>
        
        <label><input type="checkbox" id="chkReachy" name="chkReachy" value="Reachy" tabindex="223" 
			<?php if($reachy) echo ' checked="checked" ';?>> Reachy </label>
        
        <label><input type="checkbox" id="chkPoor" name="chkPoor" value="Poor" tabindex="223" 
			<?php if($poor) echo ' checked="checked" ';?>> Poor Quality </label>
        
        <label><input type="checkbox" id="chkStar" name="chkStar" value="Star" tabindex="223" 
			<?php if($star) echo ' checked="checked" ';?>> Great Route </label>
       
            </fieldset>
                
          <fieldset class="radio">
	<legend>Would you do this climb again?</legend>
	<label><input type="radio" id="radYes" name="radAgain" value="Yes" tabindex="231" 
			<?php if($again=="Yes") echo ' checked="checked" ';?>>Yes</label>
            
	<label><input type="radio" id="radNo" name="radAgain" value="No" tabindex="233" 
			<?php if($again=="No") echo ' checked="checked" ';?>>No</label>
        </fieldset>      
                


                <fieldset class="buttons">
                    <input type="submit" id="btnSubmit" name="btnSubmit" value="Register" tabindex="991" class="button">
                    <input type="reset" id="butReset" name="butReset" value="Reset Form" tabindex="993" class="button" onclick="reSetForm()" >
                </fieldset>                    
               
       
                
           </form>
            <?php
        } // end body submit
        if ($debug)
            print "<p>END OF PROCESSING</p>";
        ?>
    </section>


    <?
    include ("footer.php");
    ?>

</body>
</html>