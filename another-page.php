<!DOCTYPE html>
<html>
<head>
    <title>PHP Azure Auth</title>
</head>
<body>
<?php
include_once 'login-check.php';
include_once 'navigation.php';
?>
<?=($logged_in) ? '<p>This is another page that is also under login. Try to logout and then attempt to open this page directly. It will ask you to login but after login you will end up back here!</p>' : null;?>
</body>
</html>