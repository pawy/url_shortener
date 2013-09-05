<?php
require_once('core.php');

define('STORAGE_DIR','s/');
$enableDeletion = true;

//open shortened link
if($shorten = get('shorten',$_GET))
{
    if(file_exists($urlFileName = STORAGE_DIR . $shorten))
    {
        $link = file_get_contents($urlFileName);

        $ip = $_SERVER['REMOTE_ADDR'];

        $statistics =
            '"' . date('d.m.Y H:i') . '";' .
            '"' . $ip . '";' .
            '"' .$_SERVER['HTTP_USER_AGENT'] . '";';

        //Location tracking; See http://ipinfo.io
        try{
            $geoLocation = json_decode(url_get_contents("http://ipinfo.io/{$ip}/json"));
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

        file_put_contents("{$urlFileName}.log",
            $statistics . "\n",
            FILE_APPEND);

        die(header('Location: ' . $link));
    }
    die(header('Location: http://' . SERVER));
}

$randomShorten = randString(4);

//create shortened Link
if($url = get('url',$_POST))
{
    $shorten = get('shorten',$_POST);

    if(!file_exists($urlFileName = STORAGE_DIR . $shorten))
    {
        file_put_contents($urlFileName,$url);
        file_put_contents("{$urlFileName}.log",'');
        die(header("Location: /shorten.php#{$shorten}"));
    }
    else
    {
        $message = "Shorten url '{$shorten}' already exists";
    }
}

//delete shortened Link (depending on $enableDeletion)
if($enableDeletion && $toDelete = get('delete',$_GET))
{
    if(file_exists(STORAGE_DIR . $toDelete. '.log'))
    {
        unlink(STORAGE_DIR . $toDelete);
        unlink(STORAGE_DIR . $toDelete. '.log');
        die(header("Location: /short"));
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= strtoupper(SERVER) ?> URL shortener</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">

    <link rel="stylesheet" href="shorten.css">

</head>
<body>
    <div class="container">
        <h1><?= strtoupper(SERVER) ?> <small>URL shortener</small></h1>
        <?php
            if(isset($message)):
        ?>
            <div class="alert alert-danger"><?= $message ?></div>
        <?php
            endif;
        ?>
        <form method="post" role="form" class="well">
            <div class="form-group">
                <label for="shorten">Shortened URL <small>http://<?= SERVER ?>/</small></label>
                <input class="form-control form-control-short" onclick="select()" type="text" name="shorten" id="shorten" value="<?= $randomShorten ?>" placeholder="Shortener URL..." />
            </div>
            <div class="form-group">
                <label for="url">URL</label>
                <input class="form-control" type="url" name="url" id="url" placeholder="Target URL..." />
            </div>
            <input type="submit" class="btn btn-primary" value="Shorten URL" />
        </form>
        <input id="search" type="search" class="form-control" placeholder="Search..." onclick="select()" />
        <?php
            $files = glob(STORAGE_DIR . '[a-z]*');
            //filter out the logfiles, because glob is not able to return files according to REGEX properly
            $files = array_filter($files, create_function('$item', 'return !strpos($item,".log");'));
            //Sort the array of Files, newest first
            usort($files, create_function('$a,$b', 'return filemtime($b) - filemtime($a);'));

            foreach($files as $file):
                $shorten = substr($file,strlen(STORAGE_DIR));
                $url = file_get_contents($file);
                $shortenedLink = 'http://' . SERVER . '/' . $shorten;
                $logFile = file($file . '.log');
        ?>
        <section id="<?= $shorten ?>">
            <h2>
                <a href="<?= $shortenedLink ?>" title="Open shortened URL" target="_blank">
                    <?= $shorten ?>
                </a>
            </h2>
            <p>
                <input type="text" class="form-control input-sm shorten" value="<?= $shortenedLink ?>" />
            </p>
            <p>
                <a title="Show statistics" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#stats<?= $shorten ?>">
                    <span class="badge badge-s"><?= count($logFile) ?></span>
                </a>
                <?= $url ?>
                <a class="margin-left-10" href="<?= $url ?>" target="_blank" title="Open the URL">
                    <span class="glyphicon glyphicon-tag"></span>
                </a>
                <a href="shorten.php?delete=<?= $shorten ?>" title="Delete the shortened URL" onclick="return confirm('Are you sure?')">
                    <span class="glyphicon glyphicon-remove-circle"></span>
                </a>
            </p>
            <ul id="stats<?= $shorten ?>" class="list-unstyled collapse well well-sm statistics">
        <?php
                foreach($logFile as $logEntry):
        ?>
                <li>
                    <?= $logEntry ?>
                </li>
        <?php
                endforeach;
        ?>
            </ul>
        </section>
        <?php
            endforeach;
        ?>
    </div>

<!-- jQuery -->
<script src="//code.jquery.com/jquery.min.js"></script>
<script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<script src="http://www.steamdev.com/zclip/js/jquery.zclip.min.js"></script>

<!-- Bootstrap: Latest compiled and minified JavaScript -->
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>

<script src="shorten.js"></script>

</body>
</html>
