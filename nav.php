<nav>
     <ol>
<?php 
if(basename($_SERVER['PHP_SELF'])=="home.php"){
    print '<li class="activePage">Home</li>';
} else {
    print '<li><a href="home.php">Home</a></li>';
} 

?>
    <li><a href="register.php">Feedback!</a></li> 
    <li><a href="select.php">Select Routes to Update</a></li> 
    <li><a href="Update.php">Add and Update Routes</a></li> 
   
     </ol>
</nav>