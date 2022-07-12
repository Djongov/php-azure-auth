<?php
// If we end up here, then we are relying on our own app registration and the auth JWT token is in the "auth_cookie" so if we want to really log out, we need to clear the cookie.
if (isset($_COOKIE['auth_cookie'])) {
    unset($_COOKIE['auth_cookie']);
    setcookie('auth_cookie', false, -1, '/', $_SERVER["HTTP_HOST"]);
}
// Redirect to root page
header("location: /");
exit;
?>