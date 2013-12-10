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
$yourURL = $baseURL . $folderPath . "admin.php";

require_once("connect.php");

//#############################################################################
// set all form variables to their default value on the form. for testing i set
// to my email address. you lose 10% on your grade if you forget to change it.

$route = "";
$boulder = "";
$colorB = "";
$colorR="";
$gradeB="";
$gradeR="";
$date="";


//#############################################################################
// 
// flags for errors




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
  
    
    $route = htmlentities($_POST["txtRoute"],ENT_QUOTES,"UTF-8");
    $boulder = htmlentities($_POST["txtBoulder"],ENT_QUOTES,"UTF-8");
    $colorR = htmlentities($_POST["txtColorR"],ENT_QUOTES,"UTF-8");
    $colorB = htmlentities($_POST["txtColorB"],ENT_QUOTES,"UTF-8");
    $gradeB = htmlentities($_POST["lstGradeB"],ENT_QUOTES,"UTF-8");
    $gradeR = htmlentities($_POST["lstGradeR"],ENT_QUOTES,"UTF-8");

   
   

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
        $date= new dateTime();
        $timestamp=$date->format('Y-m-d H:i:s');
        $primaryKey = "";
        $dataEntered = false;
        
        try {
            $db->beginTransaction();
           
            $sql = 'INSERT INTO tblRoute(fldName,fldColor,fldGrade) ';
            $sql.= 'VALUES ("' . $route . '","' . $colorR . '","'.$gradeR.'");';
                   
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
            $db->beginTransaction();
           
            $sql = 'INSERT INTO tblBoulder(fldName,fldColor,fldGrade) ';
            $sql.= 'VALUES ("' . $boulder . '","' . $colorB . '","'.$gradeB.'");';
                   
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

        // If the transaction was successful, give success message
        if ($dataEntered) {
            if ($debug) print "<p>data entered now prepare keys ";
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

            $messageA = '<h2>Thank you for registering.</h2>';

            $messageB = "<p>Click this link to confirm your registration: ";
            $messageB .= '<a href="' . $baseURL . $folderPath  . 'confirmation.php?q=' . $key1 . '&amp;w=' . $key2 . '">Confirm Registration</a></p>';
            $messageB .= "<p>or copy and paste this url into a web browser: ";
            $messageB .= $baseURL . $folderPath  . 'confirmation.php?q=' . $key1 . '&amp;w=' . $key2 . "</p>";

            $messageC .= "<p><b>Email Address:</b><i>   " . $email . "</i></p>";

            //##############################################################
            //
            // email the form's information
            //
            
            $subject = "CS 148 registration that i forgot to change text";
            include_once('mailMessage.php');
            $mailed = sendMail($email, $subject, $messageA . $messageB . $messageC);
        } //data entered   
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
            <form action="<? print $_SERVER['PHP_SELF']; ?>"
                  enctype="multipart/form-data"
                  method="post"
                  id="frmRegister">
                <fieldset class="contact">
                    <legend>Input Route</legend>

                    <label class="required" for="txtRoute">Route Name</label>

                    <input id ="txtRoute" name="txtRoute" class="element text medium" type="text" maxlength="255" value="<?php echo $route; ?>" placeholder="enter the routes name" onfocus="this.select()"  tabindex="30"/>
                    
                    <label class="required" for="txtColorR"> Tape Color</label>

                    <input id ="txtColorR" name="txtColorR" class="element text medium" type="text" maxlength="255" value="<?php echo $colorR; ?>" placeholder="enter the tape color" onfocus="this.select()"  tabindex="30"/>
                    
                </fieldset>
                <fieldset class="lists">	
	<legend>What is the grade of the Route??</legend>
	<select id="lstGradeR" name="lstGradeR" tabindex="281" size="1">
		<option value="" <?php if($gradeR=="") echo ' selected="selected" ';?>>--</option>
                <option value="5.7" <?php if($gradeR=="5.7") echo ' selected="selected" ';?>>5.7</option>
		<option value="5.8" <?php if($gradeR=="5.8") echo ' selected="selected" ';?>>5.8</option>
		<option value="5.9" <?php if($gradeR=="5.9") echo ' selected="selected" ';?>>5.9</option>
                <option value="5.10" <?php if($gradeR=="5.10") echo ' selected="selected" ';?>>5.10</option>
                <option value="5.11" <?php if($gradeR=="5.11") echo ' selected="selected" ';?>>5.11</option>
                <option value="5.12" <?php if($gradeR=="5.12") echo ' selected="selected" ';?>>5.12</option>
                <option value="5.13" <?php if($gradeR=="5.13") echo ' selected="selected" ';?>>5.13</option>
	</select>
</fieldset>
            
                <fieldset class="contact">
                    <legend>Input Boulder Problem</legend>
                    <label class="required" for="txtBoulder">Boulder Name </label>

                    <input id ="txtBoulder" name="txtBoulder" class="element text medium" type="text" maxlength="255" value="<?php echo $boulder; ?>" placeholder="enter the boulder problems name" onfocus="this.select()"  tabindex="30"/>
                    
                    <label class="required" for="txtColorB"> Tape Color</label>

                    <input id ="txtColorB" name="txtColorB" class="element text medium" type="text" maxlength="255" value="<?php echo $colorB; ?>" placeholder="enter the tape color" onfocus="this.select()"  tabindex="30"/>
                
                   
                   <!-- <textarea id="txtComment" name="txtComment" wrap="physical" placeholder=""</textarea> -->
                </fieldset> 
                   <fieldset class="lists">	
	<legend>What is the grade of the Boulder problem??</legend>
	<select id="lstGradeB" name="lstGradeB" tabindex="281" size="1">
		<option value="" <?php if($gradeB=="") echo ' selected="selected" ';?>>--</option>
                <option value="v0" <?php if($gradeB=="v0") echo ' selected="selected" ';?>>v0</option>
		<option value="v1" <?php if($gradeB=="v1") echo ' selected="selected" ';?>>v1</option>
		<option value="v2" <?php if($gradeB=="v2") echo ' selected="selected" ';?>>v2</option>
                <option value="v3" <?php if($gradeB=="v3") echo ' selected="selected" ';?>>v3</option>
                <option value="v4" <?php if($gradeB=="v4") echo ' selected="selected" ';?>>v4</option>
                <option value="v5" <?php if($gradeB=="v5") echo ' selected="selected" ';?>>v5</option>
                <option value="v6" <?php if($gradeB=="v6") echo ' selected="selected" ';?>>v6</option>
                <option value="v7" <?php if($gradeB=="v7") echo ' selected="selected" ';?>>v7</option>
                <option value="v8" <?php if($gradeB=="v8") echo ' selected="selected" ';?>>v8</option>
                <option value="v9" <?php if($gradeB=="v9") echo ' selected="selected" ';?>>v9</option>
                <option value="vHARD" <?php if($gradeB=="vHARD") echo ' selected="selected" ';?>>vHARD</option>
	</select>
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