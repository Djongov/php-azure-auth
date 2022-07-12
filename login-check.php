<?php
include_once 'settings.php';
include_once 'functions.php';
// Upon returning from MS OpenID authentication endpoint, it will be a POST request. This only happens when we have the "manual" option of choosing our own app registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // if error - throw it as an exception
    if (isset($_POST['error'], $_POST['error_description'])) {
        throw new Exception("Error: " . $_POST['error'] . " with Description: " . $_POST['error_description']);
        die();
    }
    // However, if all good, we should be returning with an argument called id_token
    if (isset($_POST['id_token'])) {
        // Let's decide whether the connection is over HTTP or HTTPS (later for setting up the cookie)
        $secure = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? true : false;
        // Let's call the function to check the JWT token which is returned. We are checking stuff like expiration, issuer, app id. We are not validating the signature as per MS article - https://docs.microsoft.com/en-us/azure/active-directory/develop/id-tokens#validating-an-id-token and https://docs.microsoft.com/en-us/azure/active-directory/develop/access-tokens#validating-tokens
        if (checkJWTToken($_POST['id_token'], Tenant_ID)) {
            // Let's set the "auth_cookie" and put the id token as it's value, set the expiration date to when the token should expire and the rest of the cookie settings
            setcookie('auth_cookie', $_POST['id_token'], parseJWTTokenPayLoad($_POST['id_token'])['exp'], '/', str_replace(strstr($_SERVER['HTTP_HOST'], ':'), '', $_SERVER['HTTP_HOST']), $secure, true);
            // Redirect after all good
            header("Location: " . $_POST['state']);
        }
    }
}

$logged_in = false;

if (!$logged_in) {

    // Let's require all the headers to be present first. This is when the Authentication is turned on on the App Service itself!
    if (isset($_SERVER['HTTP_X_MS_TOKEN_AAD_ACCESS_TOKEN'], $_SERVER['HTTP_X_MS_TOKEN_AAD_ID_TOKEN'], $_SERVER['HTTP_X_MS_TOKEN_AAD_EXPIRES_ON'], $_SERVER['HTTP_X_MS_CLIENT_PRINCIPAL'], $_SERVER['HTTP_X_MS_CLIENT_PRINCIPAL_IDP'], $_SERVER['HTTP_X_MS_CLIENT_PRINCIPAL_ID'], $_SERVER['HTTP_X_MS_CLIENT_PRINCIPAL_NAME'])) {
    	$token_check = checkIdTokenAzureAuth($_SERVER['HTTP_X_MS_TOKEN_AAD_ID_TOKEN'], Client_ID, Tenant_ID);
        // We do not check the signature for now as per MS article - https://docs.microsoft.com/en-us/azure/active-directory/develop/id-tokens#validating-an-id-token and https://docs.microsoft.com/en-us/azure/active-directory/develop/access-tokens#validating-tokens
    	if ($token_check === 'Token expired' || /*$token_check === 'Incorrect signature' || */$token_check === 'Incorrect tenant' || $token_check === 'Incorrect tenant' || $token_check === 'Incorrect aud') {
            header("Location: " . Login_Button_URL);
    	}
        /* It works without this as well
        if ($token_check === 'Token expired') {
            header("Location: " . Refresh_token_URL);
        }
        */
    	$logged_in = true;
    // This is if manual Authentication is happening and we have a cookie
    } elseif (isset($_COOKIE['auth_cookie'])) {
        if (checkJWTToken($_COOKIE['auth_cookie'], Tenant_ID)) {
                $logged_in = true;
        }
    }
}

// Let's save the username and try to display it later when logged in
if (isset($_SERVER['HTTP_X_MS_CLIENT_PRINCIPAL_NAME'])) {
    $username = $_SERVER['HTTP_X_MS_CLIENT_PRINCIPAL_NAME'];
} elseif (isset($_COOKIE['auth_cookie'])) {
    $username = parseJWTTokenPayLoad($_COOKIE['auth_cookie'])['preferred_username'];
} else {
    $username = 'unknown';
}
?>