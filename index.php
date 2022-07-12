<!DOCTYPE html>
<html>
<head>
    <title>PHP Azure Auth</title>
</head>
<body>
<?php
include_once 'login-check.php';
include_once 'navigation.php';
// $logged_in is carried from login-check.php validations and should be true if logged in successfully or false it it's not
if ($logged_in) {
    echo "<p>Hello $username</p>";
    echo "<p>You are now seeing something only logged in users can see</p>";
    // And here is some little extra connection info, just for the sake of having something useful to show when logged in
    echo '<details>';
    echo '<summary>Connection Info</summary>';
    $connection_info = [];
    $request_time = date('d-m-Y H:i:s', $_SERVER['REQUEST_TIME']);
    if (isset($_SERVER['HTTP_X_AZURE_CLIENTIP'])) {
        $ip = $_SERVER['HTTP_X_AZURE_CLIENTIP'];
    } elseif (isset($_SERVER['HTTP_CF_CONNECTING_IP'])){
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $http_version = $_SERVER['SERVER_PROTOCOL']; // or ?
    $visitor_url = $_SERVER['REQUEST_URI'];
    $request_method = $_SERVER['REQUEST_METHOD'];
    $response_code = http_response_code();
    if (isset($_SERVER['HTTP_REFERER'])) {
        $referrer = $_SERVER['HTTP_REFERER'];
    } else {
        $referrer = null;
    }
    $connection_info["Request Time"] = $request_time;
    $connection_info["Source IP"] = $ip;
    $connection_info["User Agent"] = $user_agent;
    $connection_info["HTTP Version"] = $http_version;
    $connection_info["Request Method"] = $request_method;
    $connection_info["Referrer"] = $referrer;
    $connection_info["Status Code"] = $response_code;
    echo '<div class="table-responsive">';
    echo '<table class="table table-light caption-top table-bordered table-hover">';
    foreach ($connection_info as $name=>$value) {
        echo '<tr>';
        echo '<td>' . $name . '</td>';
        echo '<td>' . $value . '</td>';
        echo '</tr>';
    }
    foreach ($_SERVER as $key=>$v) {
        echo '<tr>';
        echo '<td>' . $key . '</td>';
        echo '<td>' . $v . '</td>';
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
    echo '</details>';
}


?>
</body>
</html>