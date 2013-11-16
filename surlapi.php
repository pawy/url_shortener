<?php
require_once(dirname(__FILE__) . '/core.php');
require_once(dirname(__FILE__) . '/config.php');

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
 * Class LogController
 * Returns the content of the Logfile
 */
class surl implements iApiController
{
    private $shorten;

    // surlapi/surl/[NAME]/[optional:Attribute] GET
    // surlapi/surl/ POST
    public function handleRequest(array $request)
    {
        try
        {
            //decide whether its an action or its a specific shorten
            switch($_SERVER['REQUEST_METHOD'])
            {
                case 'POST':
                    $this->create();
                    break;
                case 'GET':
                    // surlapi/surl/[NAME]/[optional:Attribute] GET
                    //identify the shorten
                    $this->shorten = new Shorten($request[1]);
                    //call the action method of the shorten if none call redirect
                    if($action = $request[2])
                        $this->$request[2]();
                    else
                        $this->redirect();
                    break;
            }
        }
        catch(Exception $e)
        {
            die($e->getMessage());
        }
    }

    private function create()
    {
        if(($url = Helper::Get('url',$_POST)))
        {
            if(!Config::$passwordProtected || Helper::get('auth',$_POST) == Config::$passwordMD5Encrypted)
            {
                Helper::ValidateURL($url);
                $shorten = Shorten::Create(Shorten::GetRandomShortenName(), $url);
                header("Content-Type: application/json");
                die(json_encode($shorten));
            }
        }
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

//Entry point, handle request
try
{
    if($request = Helper::Get('request',$_GET))
    {
        //split the request by slash
        $params = explode('/', $request);
        //find a controller for the first parameter of the request
        $controller = new $params[0]();
        $controller->handleRequest($params);
    }
}
catch(Exception $e)
{
    die($e->getMessage());
}