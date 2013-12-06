<?php
require_once(dirname(__FILE__) . '/core.php');

//Load the configuration from the surl_config_json.php file
Config::Load();

if(Helper::Get('saved',$_POST))
{
    Config::$deletionEnabled = Helper::Get('deletionEnabled',$_POST);
    Config::$passwordProtected = Helper::Get('passwordProtected',$_POST);
    Config::$loadStatsAsynchronous = Helper::Get('loadStatsAsynchronous',$_POST);
    Config::$sortAlphabetically = Helper::Get('sortAlphabetically',$_POST);
    Config::$publicCookies = Helper::Get('publicCookies',$_POST);
    Config::$choosableShorten = Helper::Get('choosableShorten',$_POST);
    if(Config::$passwordMD5Encrypted != Helper::Get('password',$_POST))
        Config::$passwordMD5Encrypted = md5(Helper::Get('password',$_POST));
    Config::$storageDir = Helper::Get('storageDir',$_POST) . '/';
    Config::$limitDisplayedShorten = Helper::Get('limitDisplayedShorten',$_POST);
    Config::Save();

    if($newConfig = Helper::Get('newConfig',$_POST))
    {
        rename(dirname(__FILE__) . '/config.php', dirname(__FILE__) . '/' . $newConfig . '.php');
        Helper::Redirect($newConfig . '.php');
    }
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
    <h1><img src="https://raw.github.com/pawy/icons/master/sUrl_icons/1_Desktop_Icons/icon_048.png" /><?= strtoupper(SERVER) ?> <small>URL Shortener Configuration</small></h1>
    <form method="post" role="form" class="well">
        <input type="hidden" name="saved" value="saved" />
        <div class="form-group">
            <label>
                <input type="checkbox" name="deletionEnabled" <?= Config::$deletionEnabled ? 'checked':''; ?>> Enable Deletion
            </label>
            <p class="help-block">Enable or disable the delete button for the shortened urls</p>
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="loadStatsAsynchronous" <?= Config::$loadStatsAsynchronous ? 'checked':''; ?>> Load Statistics asynchronously
            </label>
            <p class="help-block">Load the statistics asynchronously when clicking on the ? - button. This will reduce server read access to logfiles</p>
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" id="sortAlphabetically" name="sortAlphabetically" <?= Config::$sortAlphabetically ? 'checked':''; ?>> Alphabetic Order
            </label>
            <p class="help-block">Sort the shortened URLs alphabetically, otherwise they are sorted by creation date</p>
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="publicCookies" <?= Config::$publicCookies ? 'checked':''; ?>> Use Cookies
            </label>
            <p class="help-block">If you want to make the site public, show each visitor only the shortener URLs that he/she created by saving them to a cookie</p>
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="choosableShorten" <?= Config::$choosableShorten ? 'checked':''; ?>> Choosable shorten
            </label>
            <p class="help-block">Show the textfield to freely choose the shortened url (otherwise its hidden and a random shortened url will alway be used)</p>
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" id="passwordProtected" name="passwordProtected" <?= Config::$passwordProtected ? 'checked':''; ?>> Password protection
            </label>
            <p class="help-block">Enable oder disable the password protection of the site</p>
        </div>
        <div class="form-group">
            <label for="passwordMD5Encrypted">Password</label>
            <input type="password" class="form-control" placeholder="password" name="password" id="password" value="<?= Config::$passwordMD5Encrypted ?>" />
            <p class="help-block">The password (if "Password protection" is enabled)</p>
        </div>
        <div class="form-group">
            <label for="storateDir">Storage Directory Name</label>
            <input type="text" class="form-control" placeholder="Storage Directory Name" name="storageDir" value="<?= substr(Config::$storageDir,0,strlen(Config::$storageDir)-1) ?>" />
            <p class="help-block">The Folder to store the link and logfiles. You also need to manually change the folder! Change this only if you know what you are doing and if your webserver also has a directory called s</p>
        </div>
        <div class="form-group">
            <label for="storateDir">Limit Displayed Surls</label>
            <input type="number" class="form-control" placeholder="Storage Directory Name" id="limitDisplayedShorten" name="limitDisplayedShorten" value="<?= Config::$limitDisplayedShorten == null ? 0 : Config::$limitDisplayedShorten ?>" />
            <p class="help-block">Show only the last n shortened URLs, this only works when alphabetic order is disabled (0 means no limit)</p>
        </div>
        <?php if(file_exists(dirname(__FILE__) . '/config.php')): ?>
            <div class="alert alert-danger">
                <div class="form-group">
                    <p>
                        <strong>Okay, so you have configured it the way you like. Now we must ensure that no one else can change your configuration!</strong>
                    </p>
                    <p>
                        <strong>You have two options:</strong><br>
                        <strong>Option 1:</strong> If you do not plan do modify your configuration, delete this file from your server (config.php). You can always download it again and modify the settings.<br>
                        <strong>Option 2:</Strong> Rename it! Name it something only you know and remember it. Your configuration will be accessible from <strong>http://<?= SERVER ?>/<span id="newConfigSpan">Your New Config.php</strong></strong>
                    </p>
                    <label for="newConfig">Rename your new config here (recommended)</label>
                    <input type="text" class="form-control" placeholder="Your New Config" id="newConfig" name="newConfig" />
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-success">
                Congratulations, your configuration is now accessible from <strong>http://<?= SERVER ?>/<?= basename(__FILE__) ?></strong>. Remember this url!
                <a href="http://<?= SERVER ?>/surl" class="btn btn-default">
                    To your sUrl <span class="glyphicon glyphicon-chevron-right"></span>
                </a>
            </div>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary">Save</button>
    </form>
</div>

<!-- jQuery -->
<script src="//code.jquery.com/jquery.min.js"></script>
<!-- Bootstrap: Latest compiled and minified JavaScript -->
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>

<script type="text/javascript">
    $(document).ready(function(){

        $('#password').prop('disabled', !$('#passwordProtected').is(':checked'));
        $('#passwordProtected').click(function(){
            $('#password').prop('disabled', !$('#passwordProtected').is(':checked'));
        });

        $('#limitDisplayedShorten').prop('disabled', $('#sortAlphabetically').is(':checked'));
        $('#sortAlphabetically').click(function(){
            $('#limitDisplayedShorten').prop('disabled', $('#sortAlphabetically').is(':checked'));
        });

        $('#newConfig').keypress(function(){
            $('#newConfigSpan').html($(this).val() + '.php');
        });
    });
</script>
</body>
</html>