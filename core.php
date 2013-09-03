<?php
define('SERVER',$_SERVER['SERVER_NAME']);

function get($index, $scope, $default = null)
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

function randString($length, $charset='abcdefghijklmnopqrstuvwxyz')
{
    $str = '';
    $count = strlen($charset);
    while ($length--) {
        $str .= $charset[mt_rand(0, $count-1)];
    }
    return $str;
}

function url_get_contents ($Url) {
    if (!function_exists('curl_init')){
        throw new Exception('CURL is not installed!');
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $Url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}
