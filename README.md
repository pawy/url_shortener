![URL Shortener](https://raw.github.com/pawy/icons/master/sUrl_icons/1_Desktop_Icons/icon_048.png "URL Shortener") url_shortener
=============

Lightweight, file based, url shortener in PHP with OOP and Bootstrap-UI. No Database needed!

- Create customized shortened URLs like "http://yourdomain.com/link"
  - It is more personalized than using any public url-shorteners
  - You can make the shortened url self speaking, unlike with the public url shorteners
- Optional password protection
- Click statistics
- easy integration in domains with existing websites
- RESTful API


DEMO
----

http://surl.bz

Chrome Extension
----------------

There is a chrome extension available to directly shorten the current url with just one click.
http://surl.bz/chromext

The source is available here: https://github.com/pawy/url_shortener_chrome_extension

Windows 8.1 App
---------------

There is a windows 8.1 app in the windows store. Directly share any url with the app and create a shortened one.
http://surl.bz/msapp

[![Download From Windows Store](http://i.msdn.microsoft.com/dynimg/IC671223.png)](http://surl.bz/msapp/ "Download from Windows Store")



Installation
------------
1. Copy the files into your root directory
2. Congratulations, you're done!

Configuration
-------------

There is a config.php file to which you get redirected after copying the files to your server.
Adjust the configuration to your needs and then either delete the config.php file from your server or rename it using the wizard.

API
--------

It's a RESTful API according to the specifications.

URL: http://yourdomain/surlapi
- /version GET -> returns the version
- /md5/[Value] GET -> returns the encrypted [Value]
- /surl
  - /[ShortenedURL]/redirec GET -> redirects to the url
  - /[ShortenedURL]/log GET -> click log
- /surl POST -> Create
  - url: The url
  - auth (optional): If the server requires authentication its the encrypted (md5) password
  - surl (optional): If the server allows choosable shorten
- /surl DELETE -> Delete
  - surl: The shortened Url
  - auth (optional): If the server requires authentication its the encrypted (md5) password


Feel free to change the script to your needs and notify me if you have some improvements!
