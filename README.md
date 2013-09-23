url_shortener
=============

Lightweight, file based, url shortener in PHP with OOP. No Database and just three files needed!

- Create customized shortened URLs like "yourdomain.com/link"
  - It is more personalized than using any public url-shorteners
  - You can make the shortened url self speaking, unlike with the public url shorteners
- Optional password protection
- Click statistics
- easy integration in existing website
  - standard configuration for admin interface is "yourdomain.com/short"
  - you can also make a dedicated website for this service
- API calls to create an url

Installation
------------
1. Copy the files into your root directory
  a If you already have a directory called s you can rename the storage folder to anything you like
2. Congratulations, you're done!

Configuration
-------------

To change the configuration set the values of the static Config-Class to fit your needs. 
Find the settings in shorten.php right on top.
- Enable Password Protection
  - Set "Config::$passwordProtected = true;"
  - Default password is "url_shortener" replace it with your md5 encrypted passwort (set Config::$passwordMD5Encrypted)
    - To enrypt a passwort google for "md5 generator"
- Dedicated Service
  - To use the website only for this script, uncomment the corresponding line in the .htaccess file
- Change the storage directory
  - Config::$storageDir = 's/'; change s/ to any directory you want the url and logfiles to be stored
- Sort Alphabetically
  - Change the variable Config::$sortAlphabetically = false; to true
  - Default sort is: newest on top
- Load statistics asynchronous
  - To avoid a lot of file read access on your server you can load the statistics asynchronous when needed
  - Config::$loadStatsAsynchronous = true;

API Call
--------

To create an Shortened URL via a HTTP GET Call with the attribute "APICreate".
If you have your generator passwort protected also provide the md5 encrypted passwort with "authKey".

Example: http://URL/short?APICreate=http://www.google.ch&authKey=bdc95b7532e651f3c140b95942851808

The Result is a JSON Object {name, shortenedLink} containing the shortened URL.

Feel free to change the script to your needs and notify me if you have some improvements!
