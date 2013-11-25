<?php
define('SERVER',$_SERVER['SERVER_NAME']);

/**
 * Class Config
 */
class Config
{
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
     * @var If you want to make the site public, show each visitor only the shortener URLs that he/she created by saving them to a cookie
     */
    public static $publicCookies;
    /**
     * @var Show the textfield to freely choose the shortened url (otherwise its hidden and a random shortened url will alway be used)
     */
    public static $choosableShorten;

    public static function Load()
    {
        $config = json_decode(file_get_contents('surl_config_json.php'));
        $reflection = new ReflectionClass('Config');
        foreach($reflection->getStaticProperties() as $name => $value)
        {
            $reflection->setStaticPropertyValue($name,$config->$name);
        }
    }

    public static function Save()
    {
        $config = array();
        $reflection = new ReflectionClass('Config');
        foreach($reflection->getStaticProperties() as $name => $value)
        {
            $config[$name] = $value;
        }
        file_put_contents('surl_config_json.php',json_encode($config));
    }
}

/**
 * Class CookieHandler
 */
class CookieHandler
{
    private static $cookieName = 'shorteners';
    private static $expires = 7776000000;

    /**
     * @return array The Shorteners names
     */
    public static function GetShorteners()
    {
        $array = null;

        if(Config::$limitDisplayedShorten > 0 && !Config::$sortAlphabetically)
            $array = explode(',',self::GetCookieValue(),Config::$limitDisplayedShorten + 1);
        else
            $array = explode(',',self::GetCookieValue());

        array_pop($array);

        //Sort alphabetically
        if(Config::$sortAlphabetically)
            sort($array,SORT_NATURAL | SORT_FLAG_CASE);

        return $array;
    }

    private static function GetCookieValue()
    {
        return Helper::Get(self::$cookieName,$_COOKIE,'');
    }

    /**
     * @param $name Add a new shortener to the cookie
     */
    public static function AddShortener($name)
    {
        $values = $name . ',' . self::GetCookieValue();
        setcookie(self::$cookieName,$values,self::$expires);
    }

    /**
     * @param $name Remove this shortener from the cookie
     */
    public static function RemoveShortener($name)
    {
        $values = str_replace($name . ',','',self::GetCookieValue());
        setcookie(self::$cookieName,$values,self::$expires);
    }
}

/**
 * Class Shorten
 */
class Shorten
{
    /* Static */
    private static $shorteners = array();

    public static function GetAllShorteners()
    {
        if(!self::$shorteners)
        {
            self::$shorteners = array();

            //Load the shorteners from cookie
            if(Config::$publicCookies)
            {
                foreach(CookieHandler::GetShorteners() as $name)
                {
                    $shorten = new Shorten($name);
                    if(file_exists($shorten->filename))
                        self::$shorteners[] = $shorten;
                }
            }
            //Load the shorteners file based
            else
            {
                if($files = glob(Config::$storageDir . '[a-zA-Z0-9_]*'))
                {
                    //filter out the logfiles, because glob is not able to return files according to REGEX properly
                    $files = array_filter($files, create_function('$item', 'return !strpos($item,".");'));

                    //Sort the array of Files, newest first
                    if(!Config::$sortAlphabetically)
                    {
                        usort($files, create_function('$a,$b', 'return filemtime($b) - filemtime($a);'));
                        //Limit the array
                        if(Config::$limitDisplayedShorten)
                            $files = array_slice($files,0,Config::$limitDisplayedShorten);
                    }

                    foreach($files as $file)
                    {
                        $name = substr($file,strlen(Config::$storageDir));
                        self::$shorteners[] = new Shorten($name);
                    }
                }
            }
        }
        return self::$shorteners;
    }

    public static function ValidateShorten($shorten)
    {
        if(preg_match('/^[A-Za-z0-9_]+$/', $shorten) != 1)
            throw new InvalidShortenException();
    }

    public static function Create($name, $url)
    {
        $shorten = new Shorten($name);
        return $shorten->save($url);
    }

    public static function Redirect($name)
    {
        try
        {
            $shorten = new Shorten($name);
            $shorten->redirectToUrl();
        }
        catch(Exception $e)
        {
            Helper::Redirect('http://' . SERVER);
        }
    }

    public static function GetRandomShortenName()
    {
        $shorten = new Shorten(Helper::RandString(5));
        while(file_exists($shorten->filename)){
            $shorten = new Shorten(Helper::RandString(5));
        }
        return $shorten->surl;
    }

    /* Class members */
    public $surl;
    public $link;
    private $filename;
    private $logFilename;
    private $url;
    private $statistic;

    public function __construct($name)
    {
        Shorten::ValidateShorten($name);
        $this->surl = $name;
        $this->link = 'http://' . SERVER . '/' . $name;
        $this->filename = Config::$storageDir . $name;
        $this->logFilename = $this->filename . '.log';
    }

    public function delete()
    {
        if(file_exists($this->logFilename))
            unlink($this->logFilename);
        if(file_exists($this->filename))
            unlink($this->filename);

        if(Config::$publicCookies)
        {
            CookieHandler::RemoveShortener($this->surl);
        }
    }

    public function getUrl()
    {
        if(!$this->url)
        {
            $this->url = file_get_contents($this->filename);
        }
        return $this->url;
    }

    public function getStatistics()
    {
        if(!$this->statistic)
        {
            $this->statistic = new Statistic($this->logFilename);
        }
        return $this->statistic;
    }

    public function getStatisticsJSON()
    {
        return (json_encode($this->getStatistics()));
    }

    public function getCreationDate()
    {
        return date('d.M.y H:i',filemtime($this->filename));
    }

    public function redirectToUrl()
    {
        if(!file_exists($this->filename))
            throw new ShortenNotExistsException($this->surl);
        $this->track();
        Helper::Redirect($this->getUrl());
    }

    private function track()
    {
        $ip = Helper::Get('REMOTE_ADDR', $_SERVER);
        $referer = Helper::Get('HTTP_REFERER', $_SERVER);
        $userAgent = Helper::Get('HTTP_USER_AGENT', $_SERVER);

        // Do not track spiders
        if($userAgent == "Jakarta Commons-HttpClient/3.1")
            return;

        $statistics =
            '"' . date('d.m.Y H:i') . '";' .
            '"' . $ip . '";' .
            '"' . $userAgent . '";' .
            '"' . $referer . '";';

        //Location tracking; See http://ipinfo.io
        //Disabled due to performance issues
        /*
        try{
            $geoLocation = json_decode(Helper::UrlGetContents("http://ipinfo.io/{$ip}/json"));
            if(is_object($geoLocation))
            {
                $statistics .=
                    '"' . $geoLocation->country . '";' .
                    '"' . $geoLocation->region . '";' .
                    '"' . $geoLocation->city . '";' .
                    '"' . $geoLocation->org . '";' .
                    '"' . $geoLocation->loc . '";' .
                    '"' . $geoLocation->hostname . '";';
            }
        }catch(Exception $e){}
        */
        file_put_contents($this->logFilename, $statistics . "\n", FILE_APPEND);
    }

    private function save($url)
    {
        Helper::ValidateURL($url);

        if(file_exists($this->filename))
            throw new ShortenAlreadyExistsException($this->surl);

        file_put_contents($this->filename,$url);
        file_put_contents($this->logFilename,'');

        if(Config::$publicCookies)
        {
            CookieHandler::AddShortener($this->surl);
        }

        return $this;
    }
}

/**
 * Class Statistic
 */
class Statistic
{
    public $numberOfHits;
    public $entries;

    public function __construct($logFilename)
    {
        $this->entries = str_replace("\n",'<br>',file_get_contents($logFilename));
        $this->numberOfHits = substr_count($this->entries,'<br>');
    }
}

/**
 * Class Helper
 */
class Helper
{
    public static function Get($index, $scope, $default = null)
    {
        if (is_object($scope))
        {
            return isset($scope->$index) ? $scope->$index : $default;
        }
        else if (is_array($scope))
        {
            return array_key_exists($index, $scope) && !is_null($scope[$index]) && !(empty($scope[$index]) && !is_numeric($scope[$index])) ? $scope[$index] : $default;
        }
        return $default;
    }

    /**
     * Also check Shorten::ValidateShorten() to make sure to use the same characters, also check the .htaccess for the valid characters
     * @param $length
     * @param string $charset
     * @return string
     */
    public static function RandString($length, $charset='abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_')
    {
        $str = '';
        $count = strlen($charset);
        while ($length--) {
            $str .= $charset[mt_rand(0, $count-1)];
        }
        return $str;
    }

    /**
     * Function is using Curl, and if it is not installed, try file_get_contents directly
     * This function is used for asynchronous requests
     * @param $url
     * @return mixed|string (the content of the url
     */
    public static function UrlGetContents ($url) {
        if (!function_exists('curl_init')){
            return file_get_contents($url);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        return $output;
    }

    public static function Redirect($url)
    {
        die(header('Location: ' . $url));
    }

    public static function ValidateURL($url)
    {
        //filter_var is on older PHP version (<5.3) not trustworthy
        if(!filter_var($url, FILTER_VALIDATE_URL))
            throw new InvalidURLException();
    }
}

// -- Exceptions --

/**
 * Class ShortenNotExistsException
 */
class ShortenNotExistsException extends Exception
{
    public function __construct($shortenedURL)
    {
        parent::__construct("Shortened url {$shortenedURL} not found");
    }
}

/**
 * Class ShortenAlreadyExistsException
 */
class ShortenAlreadyExistsException extends Exception
{
    public function __construct($shortenedURL)
    {
        parent::__construct("The shortened  url {$shortenedURL} already exists");
    }
}

/**
 * Class IllegalCharacterException
 */
class IllegalCharacterException extends Exception
{
    public function __construct($shortenedURL)
    {
        parent::__construct("The shortened url {$shortenedURL} contains illegal characters. Only a-z, A-Z, 0-9 and _ is allowed.");
    }
}

/**
 * Class InvalidURLException
 */
class InvalidURLException extends Exception
{
    public function __construct()
    {
        parent::__construct("Invalid URL");
    }
}

/**
 * Class InvalidShortenException
 */
class InvalidShortenException extends Exception
{
    public function __construct()
    {
        parent::__construct("Invalid Shortened URL, please only use alphanumeric characters.");
    }
}


// -- API --


/**
 * Interface iApiController
 */
interface iApiController
{
    public function handleRequest(array $request);
}

/**
 * Class VersionController
 * Return the current Version of the API
 */
class version implements iApiController
{
    // surlapi/version GET
    public function handleRequest(array $request)
    {
        header("Content-Type: application/json");
        die(json_encode(array("V" => 2.0)));
    }
}

/**
 * Class Md5Controller
 * Return the current Version of the API
 */
class md5 implements iApiController
{
    // surlapi/md5/[VALUE] GET
    public function handleRequest(array $request)
    {
        header("Content-Type: application/json");
        die(json_encode(array("md5" => md5($request[1]))));
    }
}

/**
 * Class LogController
 * Returns the content of the Logfile
 */
class surl implements iApiController
{
    private $shorten;

    // surlapi/surl/[NAME]/[optional:Attribute] GET
    // surlapi/surl/ POST
    // surlapi/surl/ DELETE
    public function handleRequest(array $request)
    {
        try
        {
            //decide whether its an action or its a specific shorten
            switch($_SERVER['REQUEST_METHOD'])
            {
                // surlapi/surl POST -> Create new
                case 'POST':
                    $this->create();
                    break;
                case 'GET':
                    // surlapi/surl GET
                    if(!$request[1])
                        $this->all();

                    // surlapi/surl/[NAME]/[optional:Action] GET
                    //identify the shorten
                    $this->shorten = new Shorten($request[1]);

                    //call the action method of the shorten if none or not existing call redirect action
                    if($request[2] && method_exists($this,$request[2]))
                        $this->$request[2]();
                    else
                        $this->one();
                    break;
                case 'DELETE':
                    // surlapi/surl DELETE
                    $this->delete();
                    break;
            }
            throw new BadRequestException("Nothing Requested");
        }
        catch(Exception $e)
        {
            //Convert to APIException (default Bad Request) to add corresponding Header
            if(!is_a($e,'APIException'))
                $e = new BadRequestException($e->getMessage());

            die($e->getMessage());
        }
    }

    /**
     * Create a new shorten
     */
    private function create()
    {
        if(($url = Helper::Get('url',$_POST)))
        {
            if(Config::$passwordProtected && Helper::get('auth',$_POST) != Config::$passwordMD5Encrypted)
                throw new UnauthorizedException();
            Helper::ValidateURL($url);
            if(!Config::$choosableShorten && Helper::Get('surl',$_POST))
                throw new ForbiddenException('Choosable shorten is deactivated on this server');
            $name = Config::$choosableShorten ? Helper::Get('surl',$_POST,Shorten::GetRandomShortenName()) : Shorten::GetRandomShortenName();
            $shorten = Shorten::Create($name, $url);
            header('HTTP/1.0 201 Created');
            header("Content-Type: application/json");
            die(json_encode($shorten));
        }
    }

    private function delete()
    {
        parse_str(file_get_contents("php://input"),$post_vars);

        if(Config::$passwordProtected && Helper::get('auth',$post_vars) != Config::$passwordMD5Encrypted)
            throw new UnauthorizedException();

        if(!Config::$deletionEnabled)
            throw new ForbiddenException("Deletion is disabled on this server");


        $this->shorten = new Shorten(Helper::Get('surl',$post_vars));
        $this->shorten->delete();
        header('HTTP/1.0 200 OK');
        die();
    }

    private function all()
    {
        header("Content-Type: application/json");
        die(json_encode(Shorten::GetAllShorteners()));
    }

    private function one()
    {
        header("Content-Type: application/json");
        die(json_encode($this->shorten));
    }

    private function redirect()
    {
        $this->shorten->redirectToUrl();
    }

    /**
     * Return the logContent as JSON Object
     */
    private function log()
    {
        header("Content-Type: application/json");
        die($this->shorten->getStatisticsJSON());
    }
}

//  -- Exceptions --
/**
 * Class APIException
 * Make sure every APIException adds a corresponding HTTP Header Status Code
 */
abstract class APIException extends Exception
{
    public function __construct($message = null)
    {
        parent::__construct($message);
    }
}

/**
 * Class BadRequestException
 */
class BadRequestException extends APIException
{
    public function __construct($message = null)
    {
        header('HTTP/1.0 400 Bad Request');
        parent::__construct($message);
    }
}

/**
 * Class UnauthorizedException
 */
class UnauthorizedException extends APIException
{
    public function __construct($message = null)
    {
        header('HTTP/1.0 401 Unauthorized');
        parent::__construct($message);
    }
}

/**
 * Class ForbiddenException
 */
class ForbiddenException extends APIException
{
    public function __construct($message = null)
    {
        header('HTTP/1.0 403 Forbidden');
        parent::__construct($message);
    }
}