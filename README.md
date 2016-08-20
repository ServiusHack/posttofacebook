# PostToFacebook Plugin


## Getting a page access token for real accounts

## Testing the plugin before going live

In case you want to test this plugin before using it on real Facebook pages here are the steps. These requests can be executed with the [Facebook Graph Explorer](https://developers.facebook.com/tools/explorer/) or any other Facebook API client.

### 1. Get an App Access Token to manage test accounts in that app

    GET https://graph.facebook.com/oauth/access_token
            ?client_id=APP_ID
            &client_secret=APP_SECRET
            &grant_type=client_credentials

The response contains an `access_token` which is the App Access Token for the developer account.

### 2. Create a test user which is associated with the app

Use the `access_token` from the previous step.

    POST /APP_ID/accounts/test-users
            installed=true
            permissions=publish_pages

The response contains:

* `access_token` which is the App Access Token for the test account
* `login_url` which can be used to login in as the test account

### 3. Create a test page

Login as the test account using the `login_url` from the previous step and visit https://www.facebook.com/pages/create.php to create a new page.

### 4. Obtain the page ID

Visit the just created Facebook page and open the 'Info' tab. You'll find the page ID there. Enter this page ID in the settings of this plugin.

### 5. Get the Page Access Token

    GET PAGE_ID
            ?fields=access_token

The response contains an `access_token` which is the Page Access Token. Enter this access token in the settings of this plugin.

### 6. Optional: Extend the Page Access Token

Visit the [Facebook Access Token Debugger](https://developers.facebook.com/tools/debug/accesstoken) to get a new page access token that has a longer validity. Enter this access token in the settings of this plugin.
