<?php
require_once(dirname(__FILE__) . '/core.php');

//Load the configuration from the surl_config_json.php file
Config::Load();

$checkboxes = array(
    'deletionEnabled','passwordProtected','loadStatsAsynchronous','sortAlphabetically','publicCookies','choosableShorten'
);
$texts = array(
    'storageDir','passwordMD5Encrypted','limitDisplayedShorten'
);

if(Helper::Get('saved',$_POST))
{
    Config::$deletionEnabled = Helper::Get('deletionEnabled',$_POST);
    if(Config::$passwordMD5Encrypted != md5(Helper::Get('password',$_POST)))
        Config::$passwordMD5Encrypted = md5(Helper::Get('password',$_POST));
    Config::$storageDir = Helper::Get('storageDir',$_POST) . '/';
    Config::Save();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>URL shortener Configuration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h1><img src="https://raw.github.com/pawy/icons/master/sUrl_icons/1_Desktop_Icons/icon_048.png" /><?= strtoupper(SERVER) ?> <small>URL shortener</small></h1>
    <form method="post" role="form" class="well">
        <input type="hidden" name="saved" value="saved" />
        <h1>Configuration</h1>
        <div class="alert alert-danger">Delete or rename this file when you're done!</div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="deletionEnabled" <?= Config::$deletionEnabled ? 'checked':''; ?>> Enable Deletion
            </label>
            <p class="help-block">Enable oder disable the delete button for the shortened urls</p>
        </div>
        <div class="form-group">
            <label for="passwordMD5Encrypted">MD5 encrypted password</label>
            <input type="password" class="form-control" placeholder="password" name="password" value="<?= Config::$passwordMD5Encrypted ?>" />
            <p class="help-block">Blabla</p>
        </div>
        <div class="form-group">
            <label for="storateDir">Storage Directory Name</label>
            <input type="text" class="form-control" placeholder="Storage Directory Name" name="storageDir" value="<?= substr(Config::$storageDir,0,strlen(Config::$storageDir)-1) ?>" />
            <p class="help-block">Blabla</p>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
</div>

<!-- jQuery -->
<script src="//code.jquery.com/jquery.min.js"></script>
<!-- Bootstrap: Latest compiled and minified JavaScript -->
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>

</body>
</html>