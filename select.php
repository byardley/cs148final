<?php include ("top.php");
    include ("header.php");
    include ("nav.php");
    require_once("connect.php");
    ?>

    <section id="main">
        <h1>Register </h1>
<form action="Update.php"
      method="post"
      id="frmRegister">  
    
<?

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
print "<input type='submit' name='cmdSubmitted' value='Submit' />";
print "</fieldset>\n";
print "</form>\n";
?>

    
    
