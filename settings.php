<?php
// Authentication Settings

// if this env var is available, then we must be deployed into an app service and therefore control the auth settings from the app's settings as various env variable set by the platform
if (getenv('WEBSITE_AUTH_CLIENT_ID')) {
    // The only exposure of the tenant is the openid_issuer env var, but it's a url so we use regex to catch the tenant id from it
    preg_match('/https:\/\/sts.windows.net\/(.*?)\/v2.0/', getenv('WEBSITE_AUTH_OPENID_ISSUER'), $match);
    define('Tenant_ID' , $match[1]);
    // The client id is in this environmental variable
    define('Client_ID' , getenv('WEBSITE_AUTH_CLIENT_ID'));
    // The client secret is in this environmental variable. It's not actually being used anywhere here.
    define('Client_Secret', getenv('MICROSOFT_PROVIDER_AUTHENTICATION_SECRET'));
    // Let's build the oauth URL which includes the tenant. This is where we will be sending the request to login
    define('OAUTHURL', 'https://login.microsoftonline.com/' . Tenant_ID . '/oauth2/v2.0/authorize?');
    // Let's form what the login url will be
    define('Login_Button_URL', '/.auth/login/aad?post_login_redirect_uri=https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    // And the logout URL
    define('Logout_Button_URL', '/.auth/logout?post_logout_redirect_uri=/');
    // The refresh token URL, for when the token needs to be refreshed. Note that here we generate a random nonce which is supposed to be kept somewhere like in a database. This is why we currently use a static nonce
    define('Refresh_token_URL', 'https://login.microsoftonline.com/' . $match[1] . '/oauth2/v2.0/authorize?response_type=code+id_token&redirect_uri=' . $_SERVER['HTTP_HOST'] . '/.auth/login/aad/callback&client_id=' . getenv('APPSETTING_WEBSITE_AUTH_CLIENT_ID') . '&scope=openid+profile+email&response_mode=form_post&nonce=supersecret882&state=redir=https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
// We end up here if we are using our own App Registration
} else {
    // Needs to be set as environmental variable
    define('Tenant_ID' , getenv('Tenant_ID'));
    // Needs to be set as environmental variable
    define('Client_ID' , getenv('Client_ID'));
    // This is how we form the redirect URL. Note that https:// is hardcoded, which is fine as app registrations do not allow for http:// unless it's http://localhost. If that' your case, you hardcore http:// here
    define('Redirect_URI', 'https://' . $_SERVER['HTTP_HOST']);
    // Let's build the oauth URL which includes the tenant. This is where we will be sending the request to login
    define('OAUTHURL', 'https://login.microsoftonline.com/' . Tenant_ID . '/oauth2/v2.0/authorize?');
    // Apart from the OAUTHURL, we need to pass on some query params. So let's build them
    $data = [
        'client_id' => Client_ID,
        'response_type' => 'id_token',
        'redirect_uri' => Redirect_URI,
        'response_mode' => 'form_post',
        'scope' => 'openid profile email',
        'state' => $_SERVER['REQUEST_URI'],
        // Note that the nonce is supposed to be checked on return but you need special settings to keep it somewhere, like in a database. This is why we currently use a static nonce but i leave here a line with random nonce
        // 'nonce' => bin2hex(random_bytes(24))
        'nonce' => 'supersecret882'
    ];
    // This basically merges OAUTH URL and $data
    $request_id_token_url = OAUTHURL . http_build_query($data);
    // Let's form what the login url will be
    define('Login_Button_URL', $request_id_token_url);
    // For this one, the logout will be our own script
    define('Logout_Button_URL', '/logout.php');
}
?>