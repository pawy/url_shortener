url_shortener
=============

Lightweight, file based, url shortener in PHP with OOP and Bootstrap-UI. No Database and just three files needed!

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


DEMO
----

www.doubleu.ch

Chrome Extension
----------------

There is a chrome extension available to directly shorten the current url with just one click.
http://pawy.net/chromext

The source is available here: https://github.com/pawy/url_shortener_chrome_extension

Configuration
-------------

To change the configuration set the values of the static Config-Class to fit your needs. 
Find the settings in shorten.php right on top.

  ```
  //configure -> see core.php Config Class for explanation
  Config::$storageDir = 's/';
  Config::$deletionEnabled = true;
  Config::$passwordProtected = false;
  Config::$passwordMD5Encrypted = 'bdc95b7532e651f3c140b95942851808';
  Config::$loadStatsAsynchronous = false;
  Config::$sortAlphabetically = false;
  Config::$limitDisplayedShorten = 0;
  Config::$allowAPICalls = true;
  Config::$publicCookies = false;
  Config::$choosableShorten = false;
  ```

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
- If you want to use the Tool for publicity, then you should consider not showing all shorteners to everyone but instead show them cookie based
  - Enable it via Config::$publicCookies = true;
- You can let the user choose the shortened url or always use a generated one
- You can limit the display to only show the latest 10 (when not sorted alphabetically). This in either the file based mode or the cookie mode.

Excerp from core.php explanation of Config Class

    ```
    /**
     * @var The Folder to store the link and logfiles //for non DB use
     */
    public static $storageDir;
    /**
     * @var Enable oder disable the delete button
     */
    public static $deletionEnabled;
    /**
     * @var Protect the overview site with a password (redirection will work anyway)
     */
    public static $passwordProtected;
    /**
     * @var Set the password (as md5 encrypted)
     */
    public static $passwordMD5Encrypted;
    /**
     * @var Load the statistics asynchronously when clicking on the ? - button. This will reduce server read access to logfiles
     */
    public static $loadStatsAsynchronous;
    /**
     * @var Sort the shortened URLs alphabetically, otherwise they are sorted by creation date
     */
    public static $sortAlphabetically;
    /**
     * @var Show only the last n shortened URLs, this only works when alphabetic order is disabled (0 means no limit)
     */
    public static $limitDisplayedShorten;
    /**
     * @var Allow API-Calls to create shortened URLs by HTTP-GET-Request, this service will also be password protected if the site is
     */
    public static $allowAPICalls;
    /**
     * @var If you want to make the site public, show each visitor only the shortener URLs that he/she created by saving them to a cookie
     */
    public static $publicCookies;
    /**
     * @var Show the textfield to freely choose the shortened url (otherwise its hidden and a random shortened url will alway be used)
     */
    public static $choosableShorten;
    ```

API Call
--------

To create an Shortened URL via a HTTP GET Call with the attribute "APICreate".
If you have your generator passwort protected also provide the md5 encrypted passwort with "authKey".

Example: [http://doubleu.ch/short?APICreate=http://www.google.ch&authKey=bdc95b7532e651f3c140b95942851808](http://doubleu.ch/short?APICreate=http://www.google.ch&authKey=bdc95b7532e651f3c140b95942851808)

The Result is a JSON Object {name, shortenedLink} containing the shortened URL.

Feel free to change the script to your needs and notify me if you have some improvements!
