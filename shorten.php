<?php
require_once('core.php');

$storage_dir = 's/';
$enableDeletion = true;

//open shortened link
if($shorten = get('shorten',$_GET))
{
    if(file_exists($urlFileName = $storage_dir . $shorten))
    {
        $link = file_get_contents($urlFileName);

        file_put_contents("{$urlFileName}.log",
            date('d.m.Y H:i') . ';' .
            $_SERVER['REMOTE_ADDR'] . ';' .
            $_SERVER['HTTP_USER_AGENT'] . "\n",
            FILE_APPEND);

        die(header('Location: ' . $link));
    }
    die(header('Location: http://' . SERVER));
}

$randomShorten = randString(6);

//create shortened Link
if($url = get('url',$_POST))
{
    $shorten = get('shorten',$_POST);

    if(!file_exists($urlFileName = $storage_dir . $shorten))
    {
        file_put_contents($urlFileName,$url);
        file_put_contents("{$urlFileName}.log",'');
        header("Location: /shorten.php#{$shorten}");
    }
    else
    {
        $message = "Shorten url '{$shorten}' already exists";
    }
}

//delete shortened Link (depending on $enableDeletion)
if($enableDeletion && $toDelete = get('delete',$_GET))
{
    if(file_exists($storage_dir . $toDelete. 'log'))
    {
        unlink($storage_dir . $toDelete);
        unlink($storage_dir . $toDelete. 'log');
        header("Location: /short");
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

    <style type="text/css">
        a:hover
        {
            text-decoration: none;
        }
        .badge
        {
            background-color: #428bca;
        }
        .badge:hover
        {
            background-color: #2a6496;
        }
    </style>
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
                <input class="form-control" onclick="select()" style="width:200px" type="text" name="shorten" id="shorten" value="<?= $randomShorten ?>" placeholder="Shortener URL..." />
            </div>
            <div class="form-group">
                <label for="url">URL</label>
                <input class="form-control" type="url" name="url" id="url" placeholder="Target URL..." />
            </div>
            <input type="submit" class="btn btn-primary" value="Shorten URL" />
        </form>
        <input id="search" type="search" class="form-control" placeholder="Search..." onclick="select()" />
        <?php
            foreach(glob($storage_dir . '*.log') as $log):
                $shorten = substr($log,2,strlen($log)-6);
                $url = file_get_contents($storage_dir . $shorten);
                $shortenedLink = 'http://' . SERVER . '/' . $shorten;
                $logFile = file($log);
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
                    <span class="badge"><?= count($logFile) ?></span>
                </a>
                <?= $url ?>
                <a style="margin-left:10px" href="<?= $url ?>" target="_blank" title="Open the URL">
                    <span class="glyphicon glyphicon-tag"></span>
                </a>
                <a href="shorten.php?delete=<?= $shorten ?>" title="Delete the shortened URL" onclick="return confirm('Are you sure?')">
                    <span class="glyphicon glyphicon-remove-circle"></span>
                </a>
            </p>
            <ul id="stats<?= $shorten ?>" class="list-unstyled collapse well well-sm">
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

<script type="text/javascript">
    /* Copy to Clipboard */
    $(document).ready(function(){
        $('input.shorten').zclip({
            setHandCursor: false,
            path:'http://davidwalsh.name/demo/ZeroClipboard.swf',
            copy:function(){return $(this).val();},
            afterCopy:function(){
                $this = $(this);
                $before = $(this).val();
                $(this).val('Copied to Clipboard');
                setTimeout(function() {
                        $this.val($before);
                    },1000);
                $(this).animate({backgroundColor:'red'},500).animate({backgroundColor:'white'},500);
            }
        });

        /* Search */
        $('#search').keyup(function() {
            $searchString = $(this).val();
            $('section').each(function() {
                if($(this).attr('id').toLowerCase().indexOf($searchString.toLowerCase()) >= 0)
                    $(this).show(500);
                else
                    $(this).hide(500);
            });
        });

    });
</script>
</body>
</html>