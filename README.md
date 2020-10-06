yourls-api-edit-url
===================

YOURLS plugin to add three additional commands to the YOURLS API.
- `update` - a function to update the long URL associated with a short code
- `geturl` - a function to get the shortcode associated with a long URL
- `change_keyword` - a function to update the short code associated with a URL

The commands are described in more detail below.

How to install this plugin
==========================
1. Create a new directory under the "user/plugins" directory
2. Save the "plugin.php" file into the directory you created in step 1
3. Activate the plugin using your YOURLS admin panel 

Command Reference
=================

`geturl`
--------
The `geturl` function does not create a new short code if the URL does not exist. It's purely designed to verify if the URL has been set up.

### Parameters

* `url`: The URL.
* `exactly_once`: Optional. If `True`, will return only one shorturl keyword. If `False`, will return all keywords associated with the URL. Defaults to `True`.

### Return values of the JSON response

* `keyword`: String or list of string, depending on `exactly_once`.

`update`
--------
The `update` function lets a site update the long URL associated with a short code. There is no security checking on this, as long as the API key is valid it will update, so make sure your key is secure if you plan to use this plugin!

### Parameters

* `shorturl`: The shorturl to update.
* `url`: The new URL.
* `title`: Optional. The title of the new URL. Pass `'keep'` to keep the current title or `'auto'` to infer the title from the url. Empty by default.

### Return values of the JSON response

None.

`change_keyword`
----------------
The `change_keyword`  lets a site update the short URL associated with a long URL.
On sites, where each long URL has at most one short URL, `oldshorturl` and `url` can be used interchangeable.
On sites, where a long URL can have multiple short URL, `oldshorturl` must be used and passing `url` will raise an error.

### Parameters

* `newshorturl`: The new short URL.
* `oldshorturl`: Optional. The short URL to update.
* `url`: Optional. The long URL whose short URL is to be updated.
* `title`: Optional. The title of the new URL. Pass `'keep'` to keep the current title or `'auto'` to infer the title from the url. Empty by default.

### Return values of the JSON response

None.
