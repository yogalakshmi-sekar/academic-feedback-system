<?php
session_start();
session_unset();  // remove all session variables
session_destroy(); // destroy the session

// Redirect to login page
header("Location: index.php");
exit();
