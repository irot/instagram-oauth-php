instagram-oauth-php
===================

A simple PHP library I made to make my life easier when working with Instagram's REST API.

In a nutshell, this library will take care of getting OAuth authorization and obtaining an access token to send to the API.

It's then up to the user to utilize the convenience wrapper methods to POST, GET, PUT, and DELETE things to the correct API endpoints with the correct parameters. I didn't hard-code any API endpoints or parameters bar the ones needed for OAuth.

## Features

- Handles authorization and access token request upon instantiation  
- Saves initial user data returned by Instagram upon access token request
- Provides convenience wrappers for POST, GET, PUT, and DELETE
- Provides convenience method for retrieving images

## Usage

A few of things you'll need to do before using this library:

1. Set up an Instagram client (http://instagram.com/developer/clients/manage/)
2. Note down your client ID, secret and redirect URI (or leave a tab open with the details open)
3. Decide what extra permissions you'll need (if any - permission constants are available from the class)

Once you have everything prepared, all you need is to pass all those into the constructor and wait for a response before running any queries, like so:

````PHP
require "path/to/instagramoauth.class.php";

$instagram = new InstagramOAuth(
	"[YOUR CLIENT ID]",
	"[YOUR CLIENT SECRET]",
	"[YOUR REDIRECT URI]",
	array("[YOUR PERMISSIONS]")
);

if ($instagram->isReady()) {
	// Yay, now we can start API-ing
}
````

Take a look at the source and example files to get a better understanding of how to use it properly.

## Bugs and feature requests

I made this library to use on my own projects so I make no guarantees that it can cater for all your needs. So far it's been able to do everything I need it to. YMMV.

With that said, if you've found a bug or have a feature request [please open a new issue](https://github.com/irot/instagram-oauth-php/issues). 

I made this library in part as a learning experience, so please feel free to fork or pull or whatever if you would like to build on or improve it. 

## Copyright and license

Copyright 2013 Bagus Tri K under [the MIT license](LICENSE).