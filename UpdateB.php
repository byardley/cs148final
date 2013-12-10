<?php
/* the purpose of this page is to display a form to allow a person to either
 * add a new record if not pk was passed in or to update a record if a pk was
 * passed in.
 * 
 * notice i have more than one submit button on the form and i need to make
 * sure they have different names
 * 
 * Written By: Robert Erickson robert.erickson@uvm.edu
 * Last updated on: November 5, 2013
 * 
 * 
 -- --------------------------------------------------------

    --
    -- Table structure for table `tblPoet`
    --

    CREATE TABLE IF NOT EXISTS `tblPoet` (
      `pkPoetId` int(11) NOT NULL AUTO_INCREMENT,
      `fldFname` varchar(20) DEFAULT NULL,
      `fldLastName` varchar(20) DEFAULT NULL,
      `fldBirthDate` date DEFAULT NULL,
      PRIMARY KEY (`pkPoetId`)
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

 */


//-----------------------------------------------------------------------------
// 
// Initialize variables
//  


$debug = false;
if (isset($_GET["debug"])) {
    $debug = true;
}

include("connect.php");

$baseURL = "http://www.uvm.edu/~byardley/";
$folderPath = "cs148/assignment7.1/";
// full URL of this form
$yourURL = $baseURL . $folderPath . "UpdateB.php";

$fromPage = getenv("http_referer");

if ($debug) {
    print "<p>From: " . $fromPage . " should match ";
    print "<p>Your: " . $yourURL;
}

//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
// initialize my form variables either to waht is in table or the default 
// values.
// display record to update
if (isset($_POST["lstBoulder"])) {
    

    // you may want to add another security check to make sure the person
    // is allowed to delete records.
    
    $id = htmlentities($_POST["lstBoulder"], ENT_QUOTES);

    $sql = "SELECT fldName, fldColor, fldGrade ";
    $sql .= "FROM tblBoulder ";
    $sql .= "WHERE pkBoulderID=" . $id;

    if ($debug)
        print "<p>sql " . $sql;

    $stmt = $db->prepare($sql);

    $stmt->execute();

    $boulders = $stmt->fetchAll();
    if ($debug) {
        print "<pre>";
        print_r($boulders);
        print "</pre>";
    }

    foreach ($boulders as $boulder) {
        $name = $boulder["fldName"];
        $color = $boulder["fldColor"];
        $grade = $boulder["fldGrade"];
    }
} else { //defualt values

    $id = "";
    $name = "";
    $color = "";
    $grade = "";


} // end isset lstPoets


//-----------------------------------------------------------------------------
//-----------------------------------------------------------------------------
//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
// simple deleting record. 
if (isset($_POST["cmdDelete"])) {
//-----------------------------------------------------------------------------
// 
// Checking to see if the form's been submitted. if not we just skip this whole 
// section and display the form
// 
//#############################################################################
// minor security check
    if ($fromPage != $yourURL) {
        die("<p>Sorry you cannot access this page. Security breach detected and reported.</p>");
    }

    // you may want to add another security check to make sure the person
    // is allowed to delete records.
    
    $delId = htmlentities($_POST["deleteId"], ENT_QUOTES);

    // I may need to do a select to see if there are any related records.
    // and determine my processing steps before i try to code.

    $sql = "DELETE ";
    $sql .= "FROM tblBoulder ";
    $sql .= "WHERE pkBoulderID=" . $delId;

    if ($debug)
        print "<p>sql " . $sql;

    $stmt = $db->prepare($sql);

    $DeleteData = $stmt->execute();

     //at this point you may or may not want to redisplay the form
    //if($DeleteData){
      // header('Location: select.php');
      // exit();
    }


//-----------------------------------------------------------------------------
//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
// if form has been submitted, validate the information both add and update
if (isset($_POST["btnSubmitted"])) {
    if ($fromPage != $yourURL) {
        die("<p>Sorry you cannot access this page. Security breach detected and reported.</p>");
    }
    
    // initialize my variables to the forms posting	
    $id = htmlentities($_POST["id"], ENT_QUOTES);
    $name = htmlentities($_POST["txtName"], ENT_QUOTES);
    $color = htmlentities($_POST["txtColor"], ENT_QUOTES);
    $grade = htmlentities($_POST["lstGrade"], ENT_QUOTES);

    
    // Error checking forms input
    include ("validation_functions.php");

    $errorMsg = array();

    //%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
    // begin testing each form element 
    if ($name == "") {
        $errorMsg[] = "Please enter the boulders name";
    } else {
        $valid = verifyAlphaNum($name); /* test for non-valid  data */
        if (!$valid) {
            $error_msg[] = " Name must be letters and numbers, spaces, dashes and ' only.";
        }
    }

    if ($color == "") {
        $errorMsg[] = "Please enter the color of the problem";
    } else {
        $valid = verifyAlphaNum($color); /* test for non-valid  data */
        if (!$valid) {
            $error_msg[] = "Last Name must be letters and numbers, spaces, dashes and ' only.";
        }
    }

    
    //- end testing ---------------------------------------------------
    
    //%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
    //%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
    // there are no input errors so form is valid now we need to save 
    // the information checking to see if it is an update or insert
    // query based on the hidden html input for id
    if (!$errorMsg) {
        
        if ($debug)
            echo "<p>Form is valid</p>";

        if (isset($_POST["id"])) { // update record
            $sql = "UPDATE ";
            $sql .= "tblBoulder SET ";
            $sql .= "fldName='$name', ";
            $sql .= "fldColor='$color', ";
            $sql .= "fldGrade='$grade' ";
            $sql .= "WHERE pkBoulderID=" . $id;
        } else { // insert record
            $sql = "INSERT INTO ";
            $sql .= "tblBoulder SET ";
            $sql .= "fldName='$name', ";
            $sql .= "fldColor='$color', ";
            $sql .= "fldGrade='$grade'";
        }
        // notice the SQL is basically the same. the above code could be replaced
        // insert ... on duplicate key update but since we have other procssing to
        // do i have split it up.

        if ($debug)
            echo "<p>SQL: " . $sql . "</p>";

        $stmt = $db->prepare($sql);

        $enterData = $stmt->execute();

        // Processing for other tables falls into place here. I like to use
        // the same variable $sql so i would repeat above code as needed.
        if ($debug){
            print "<p>Record has been updated";
        }
        
        // update or insert complete
        //if($enterData){
           // header('Location: select.php');
           // exit();
       // }
        
    }// end no errors	
} // end isset cmdSubmitted
 
include("top.php");
include("header.php");
include("nav.php");

//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
// display any errors at top of form page
if ($errorMsg) {
    echo "<ul>\n";
    foreach ($errorMsg as $err) {
        echo "<li style='color: #ff6666'>" . $err . "</li>\n";
    }
    echo "</ul>\n";
} //- end of displaying errors ------------------------------------

if ($id != "") {
    print "<h1>Edit Boulder Information</h1>";
    //%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
    // display a delete option
    ?>
    <form action="<? print $_SERVER['PHP_SELF']; ?>" method="post">
        <fieldset>
            <input type="submit" name="cmdDelete" value="Delete" />
            <?php print '<input name= "deleteId" type="hidden" id="deleteId" value="' . $id . '"/>'; ?>
        </fieldset>	
    </form>
    <?
    //%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^% 
} else {
    print "<h1>Add and edit Boulder Problem Information</h1>";
}
?>

<form action="<? print $_SERVER['PHP_SELF']; ?>" method="post">
    <fieldset>
        <label for="txtName"	>Name of Boulder Problem*</label><br>
        <input name="txtName" type="text" size="20" id="txtName" <? print "value='$name'"; ?>/><br>

        <label for="txtColor">Color of Tape*</label><br>
        <input name="txtColor" type="text" size="20" id="txtColor" <? print 'value="' . $color . '"'; ?>/><br>

        <fieldset>
            
            </fieldset> 
                   <fieldset class="lists">	
	<legend>What is the grade of the Boulder problem??</legend>
	<select id="lstGrade" name="lstGrade" tabindex="281" size="1">
		<option value="" <?php if($grade=="") echo ' selected="selected" ';?>>--</option>
                <option value="v0" <?php if($grade=="v0") echo ' selected="selected" ';?>>v0</option>
		<option value="v1" <?php if($grade=="v1") echo ' selected="selected" ';?>>v1</option>
		<option value="v2" <?php if($grade=="v2") echo ' selected="selected" ';?>>v2</option>
                <option value="v3" <?php if($grade=="v3") echo ' selected="selected" ';?>>v3</option>
                <option value="v4" <?php if($grade=="v4") echo ' selected="selected" ';?>>v4</option>
                <option value="v5" <?php if($grade=="v5") echo ' selected="selected" ';?>>v5</option>
                <option value="v6" <?php if($grade=="v6") echo ' selected="selected" ';?>>v6</option>
                <option value="v7" <?php if($grade=="v7") echo ' selected="selected" ';?>>v7</option>
                <option value="v8" <?php if($grade=="v8") echo ' selected="selected" ';?>>v8</option>
                <option value="v9" <?php if($grade=="v9") echo ' selected="selected" ';?>>v9</option>
                <option value="vHARD" <?php if($grade=="vHARD") echo ' selected="selected" ';?>>vHARD</option>
	</select>
</fieldset>

        <?
//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
// if there is a record then we need to be able to pass the pk back to the page
        if ($id != "")
            print '<input name= "id" type="hidden" id="id" value="' . $id . '"/>';
        ?>
        <input type="submit" name="btnSubmitted" value="Submit" />
    </fieldset>		
</form>
<?php

include ("footer.php");
?>
</body>
</html>