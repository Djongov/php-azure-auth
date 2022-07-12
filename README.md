
# php-azure-auth
Native PHP Authentication for Azure AD/Personal MS Accounts.

This has two types - define your own app registration or use the App Services built-in EasyAuth mechanism. Both use oAuth with a JWT claims token.

Works with PHP 7.4+

Every piece of code has a comment on what it does so this app. This app is not ready to be integrated into your own app as is. If you want to try this into your existing app, just include the login-check.php file and control what you want to display or not based on the $logged_in true or false value. Just an example of course, if you want you can rename them, put these files into folders and etc.

## App Services built-in EasyAuth
This implementation supports the built-in Azure App Service "Easy Auth" authentication mechanism where you just enable it in the App Service and that's it! Of course make sure to enable the AllowAnonymous action or else this is all in vein. All you have to do it is enable it from the portal.

When EasyAuth is handling the tokens it will stamp them into special HTTP headers which this app uses to get the token. So no extra cookies needed.

## Your own App Registration
If you want to use your own App Registration then you need to pass on the Tenant id and Client id of the App registration as environmental variables called Tenant_ID and Client_ID. That's it!

How this works is with a custom cookie called "auth_cookie" which will store the JWT token

## License
MIT