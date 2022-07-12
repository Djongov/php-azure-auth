<nav>
    <ul>
<?php
// We will be using the URLs that are defined by the constants
include_once 'settings.php';
$login_url = Login_Button_URL;
$logout_url = Logout_Button_URL;

if (!$logged_in) {
    echo '<li><a href="' . $login_url . '">Login</a></li>';
} else {
    echo '<li><a href="/">Root page</a></li>';
    echo '<li><a href="./another-page.php">Another page</a></li>';
    echo '<li><a href="' . $logout_url . '">Logout</a></li>';
}
?>
    </ul>
</nav>