yourls-api-edit-url
===================

YOURLS plugin to add two additional commands to the YOURLS API.
- update - a function to update the long URL associated with a short code
- geturl - a function to get the current long URL associated with a short code

The geturl function does not create a new short code if the URL does not exist, it's purely designed to verify if the URL has been set up. Currently this has not been tested on a site using duplicate URLs.

The update function lets a site update the long URL associated with a short code. There is no security checking on this, as long as the API key is valid it will update, so make sure your key is secure if you plan to use this plugin!
