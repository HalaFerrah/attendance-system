<?php
require_once '../backend/auth.php';

logout();
header("Location: login.php");
exit();
?>
