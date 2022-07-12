<?php
include_once 'settings.php';
// Parse the payload of a JWT token as an array function
function parseJWTTokenPayLoad($jwt) {
    $tokenParts = explode('.', $jwt);
    $header = base64_decode($tokenParts[0]);
    $payload = base64_decode($tokenParts[1]);
    $signature_provided = $tokenParts[2];

    $payload_array = json_decode($payload, true);
    return $payload_array;
}
// Check if a JWT token is expired
function checkJWTTokenExpiry($jwt) {
    $payload_array = parseJWTTokenPayLoad($jwt);
    if ($payload_array['exp'] - time() < 0) {
        return false;
    } else {
        return true;
    }
}
// Check JWT Token if we use our own App Registration
function checkJWTToken($token, $tenant) {
    $tokenParts = explode('.', $token);
    $header = base64_decode($tokenParts[0]);
    $payload = base64_decode($tokenParts[1]);
    $signature_provided = $tokenParts[2];
    // Turn the JWT parts into arrays
    $header_array = json_decode($header, true);
    $payload_array = json_decode($payload, true);
    if ($payload_array['aud'] !== Client_ID) {
        throw new Exception("Incorrect Client ID");
        return false;
    }
    if ($payload_array['iss'] !== 'https://login.microsoftonline.com/' . $tenant . '/v2.0') {
        throw new Exception("Incorrect token issuer");
        return false;
    }
    if ($payload_array['nbf'] - time() > 0) {
        throw new Exception("Token not yet valid");
        return false;
    }
    if ($payload_array['exp'] - time() < 0) {
        header('Location:' . Login_Button_URL);
    }
    if ($payload_array['nonce'] !== 'supersecret882') {
        throw new Exception("Incorrect nonce");
        return false;
    }
    if ($payload_array['tid'] !== $tenant) {
        throw new Exception("Incorrect tenant");
        return false;
    }
    return true;
}
// Base 64 URL encode (missing from native php). Needed for the checkIdTokenAzureAuth function
function base64url_encode($input,$nopad=1,$wrap=0) {
    $data  = base64_encode($input);

    if($nopad) {
	$data = str_replace("=","",$data);
    }
    $data = strtr($data, '+/=', '-_,');
    if ($wrap) {
        $datalb = ""; 
        while (mb_strlen($data) > 64) { 
            $datalb .= substr($data, 0, 64) . "\n"; 
            $data = substr($data,64); 
        } 
        $datalb .= $data; 
        return $datalb; 
    } else {
        return $data;
    }
}
// Base 64 URL decode (missing from native php). Needed for the checkIdTokenAzureAuth function
function base64url_decode($input) {
    return base64_decode(strtr($input, '-_,', '+/='));
}

// Checks Token for when we use the Azure built-in EasyAuth
function checkIdTokenAzureAuth($jwt, $appid, $tenant) {
	// split the jwt
	$tokenParts = explode('.', $jwt);
	$header = base64_decode($tokenParts[0]);
	$payload = base64_decode($tokenParts[1]);
	$signature_provided = $tokenParts[2];
	
	/*
	$public_key = '-----BEGIN CERTIFICATE-----' . PHP_EOL;
	$public_key .= getSignatures('a071563c-161e-46c0-af76-9667132a7960', '86c909a2-695c-4f9c-84fe-0371d2e68653', 'l3sQ-50cCH4xBVZLHTGwnSR7680')['x5c'][0];
	$public_key .= PHP_EOL . '-----END CERTIFICATE-----' . PHP_EOL;
	*/
    
	// Let's check aud
	$aud = json_decode($payload)->aud;
	
	if ($aud !== $appid) {
		return 'Incorrect appid';
	}

    // Let's check nonce
	$nonce = json_decode($payload)->nonce;
	
	if ($nonce !== 'supersecret882') {
		return 'Incorrect nonce';
	}
		
	// Let's check is the tenant is correct
	
	$iss = json_decode($payload)->iss;
	$regex_tenant = "/$tenant/m";
	$tenant_check = preg_match($regex_tenant, $iss); // Outputs 1 if tenant matches, 0 if it doesn't
	
	if ($tenant_check != 1) {
		return 'Incorrect tenant';
	}	

	// check the expiration time - note this will cause an error if there is no 'exp' claim in the jwt
	$expiration = json_decode($payload)->exp;
	$is_token_expired = ($expiration - time()) < 0;
	
	if ($is_token_expired) {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . '/.auth/login/aad?post_login_redirect_uri=https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		//return 'Token expired';
	}
    /*
	// build a signature based on the header and payload using the secret
	$secret = json_decode($payload)->nonce;
	$base64_url_header = base64url_encode($header);
	$base64_url_payload = base64url_encode($payload);
	$signature = hash_hmac('SHA256', $base64_url_header . "." . $base64_url_payload, $secret, true);
	$base64_url_signature = base64url_encode($signature);
	
	// verify it matches the signature provided in the jwt
	$is_signature_valid = ($base64_url_signature === $signature_provided);
		
	if ($base64_url_signature !== $signature_provided) {
		return 'Incorrect signature';
	}
    */
}
?>