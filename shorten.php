<?php
error_reporting(E_ALL);
require_once(dirname(__FILE__) . '/core.php');

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

//open shortened link
if($name = Helper::Get('redirect',$_GET))
{
    Shorten::Redirect($name);
}

//API CreateCall (return JSON Encoded Shorten Object)
//Call via /short?APICreate=THEURL
//If passwordprotected also add &authKey=MD5ENCRYPTEDPASSWORD
if(($url = Helper::Get('APICreate',$_GET)) && Config::$allowAPICalls)
{
    try
    {
        if(!Config::$passwordProtected || Helper::get('authKey',$_GET) == Config::$passwordMD5Encrypted)
        {
            Helper::ValidateURL($url);
            $shorten = Shorten::Create(Shorten::GetRandomShortenName(), $url);
            die(json_encode($shorten));
        }
    }
    catch(Exception $e)
    {
        die($e->getMessage());
    }
}

//API Service Alive Request
if(Helper::Get('APIVersion',$_GET))
{
    die(json_encode(array("V" => 1.0)));
}


//asynchronous request for statistics
if($name = Helper::Get('getLog',$_POST))
{
    $shorten = new Shorten($name);
    die($shorten->getStatisticsJSON());
}

//Password protection
if(Config::$passwordProtected)
{
    session_start();
    if($pw = Helper::Get('pw',$_POST))
    {
        $_SESSION['pw'] = md5($pw);
    }

    if(Helper::Get('pw',$_SESSION,'') != Config::$passwordMD5Encrypted)
    {
        Helper::Redirect('shortenerlogin.html');
    }
}

//create shortened Link
if($url = Helper::Get('url',$_POST))
{
    try
    {
        $name = Helper::Get('shorten',$_POST,Shorten::GetRandomShortenName());
        $shorten = Shorten::Create($name, $url);
        Helper::Redirect("/short#{$shorten->name}");
    }
    catch(Exception $e)
    {
        $message = $e->getMessage();
    }
}

//delete shortened Link (depending on Config::$deletionEnabled)
if(Config::$deletionEnabled && $name = Helper::Get('delete',$_GET))
{
    try
    {
        $shorten = new Shorten($name);
        $shorten->delete();
        Helper::Redirect('/short');
    }
    catch(Exception $e)
    {
        $message = $e->getMessage();
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

    <style>
        .container
        {
            margin-bottom: 50px;
        }
        h2
        {
            font-weight: bold;
        }
        a:hover
        {
            text-decoration: none;
        }
        .badge-s
        {
            background-color: #428bca;
        }
        .badge-s:hover
        {
            background-color: #2a6496;
        }
        .form-control-short
        {
            width: 200px;
        }
        .margin-left-10
        {
            margin-left: 10px;
        }
        .statistics
        {
            font-size: 10px;
        }
        .loading-input
        {
            background:url('https://raw.github.com/pawy/icons/master/ajax-loader-lightgray.gif') no-repeat right center;
        }
    </style>

</head>
<body data-spy="scroll" data-target="#navbar-main">

<nav class="navbar navbar-default navbar-fixed-bottom" role="navigation">
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-main-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
    </div>
    <div class="collapse navbar-collapse navbar-main-collapse" id="navbar-main">
        <ul class="nav navbar-nav">
            <?php
            foreach(Shorten::GetAllShorteners() as $shorten):
                ?>
                <li><a href="#<?= $shorten->name ?>"><?= $shorten->name ?></a></li>
            <?php
            endforeach;
            ?>
        </ul>
    </div>
</nav>

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
    <?php if(Config::$choosableShorten): ?>
        <div class="form-group">
            <label for="shorten">Shortened URL <small>http://<?= SERVER ?>/</small></label>
            <input class="form-control form-control-short" onclick="select()" type="text" name="shorten" id="shorten" value="<?= Shorten::GetRandomShortenName() ?>" placeholder="Shortener URL..." required />
        </div>
    <?php endif; ?>
        <div class="form-group">
            <label for="url">URL</label>
            <input class="form-control" type="url" name="url" id="url" placeholder="Target URL..." required />
        </div>
        <input type="submit" class="btn btn-primary" value="Shorten URL" />
        <small class="pull-right text-muted">Get your own shortener service <a href="https://github.com/pawy/url_shortener">@github</a></small>
    </form>
    <input id="search" type="search" class="form-control" placeholder="Search..." onclick="select()" />
    <?php
    foreach(Shorten::GetAllShorteners() as $shorten):
        ?>
        <section id="<?= $shorten->name ?>">
            <h2>
                <a href="<?= $shorten->shortenedLink ?>" title="Open shortened URL" target="_blank">
                    <?= $shorten->name ?>
                </a>
            </h2>
            <p>
                <input type="text" class="form-control input-sm shorten" value="<?= $shorten->shortenedLink ?>" />
            </p>
            <p>
                <a title="Show statistics" class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#stats<?= $shorten->name ?>">
                    <span class="badge badge-s" shorten="<?= $shorten->name ?>">
                        <?= Config::$loadStatsAsynchronous ? '?' : $shorten->getStatistics()->numberOfHits ?>
                    </span>
                </a>
                <small class="text-muted"><?= $shorten->getCreationDate() ?></small>
                <?= $shorten->getUrl() ?>
                <a class="margin-left-10" href="<?= $shorten->getUrl() ?>" target="_blank" title="Open the URL">
                    <span class="glyphicon glyphicon-tag"></span>
                </a>
                <?php if(Config::$deletionEnabled): ?>
                    <a href="shorten.php?delete=<?= $shorten->name ?>" title="Delete the shortened URL" onclick="return confirm('Are you sure?')">
                        <span class="glyphicon glyphicon-remove-circle"></span>
                    </a>
                <?php endif; ?>
            </p>
            <div id="stats<?= $shorten->name ?>" class="collapse well well-sm statistics">
                <p>
                    <?= Config::$loadStatsAsynchronous ? '' : $shorten->getStatistics()->entries ?>
                </p>
            </div>
        </section>
    <?php
    endforeach;
    ?>
</div>
<div id="bottom-filler"></div>
<!-- jQuery -->
<script src="//code.jquery.com/jquery.min.js"></script>
<script src="//code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
<!--<script src="http://www.steamdev.com/zclip/js/jquery.zclip.min.js"></script>-->
<script src="<?= Config::$storageDir ?>jquery.zclip.min.js"></script>

<!-- Bootstrap: Latest compiled and minified JavaScript -->
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js"></script>

<script type="text/javascript">
    $(document).ready(function(){

        // Search
        $('#search').keyup(function() {
            $(this).addClass('loading-input');
            $searchString = $(this).val();
            $('section').each(function() {
                if($(this).attr('id').toLowerCase().indexOf($searchString.toLowerCase()) >= 0)
                {
                    $(this).show(500);
                    $('a[href=#' + $(this).attr('id') + ']').show();
                }
                else
                {
                    $(this).hide(500);
                    $('a[href=#' + $(this).attr('id') + ']').hide();
                }
            });
            setTimeout(function(){
                setZclip();
                $('#search').removeClass('loading-input');
            }, 500 );
        });

        <?php
            if(Config::$loadStatsAsynchronous) :
        ?>
        //Load Statistics with asynchronous request
        $('.badge').click( function(){
            $shorten = $(this).attr('shorten');
            //only query when showing, not when hiding
            if($('#stats'+$shorten).hasClass('collapse'))
            {
                $this = $(this);
                $.ajax({
                    type: 'POST',
                    url: 'shorten.php',
                    data: {getLog: $shorten},
                    success: function(response)
                    {
                        $json_response = $.parseJSON(response);
                        $this.html($json_response.numberOfHits);
                        $('#stats'+$shorten).find('p').html($json_response.entries);
                        $this.animate({backgroundColor:'red'},500).animate({backgroundColor:'#428bca'},500);
                        setTimeout(function(){
                            setZclip();
                        }, 500 );
                    }
                })
            }
        });
        <?php
            else:
         ?>
        // Catch show toggle events from Bootstrap to reposition the zclip flash
        $('.statistics').on('shown.bs.collapse', function () {
            setTimeout(function(){
                setZclip();
            }, 200 );
        });
        <?php
            endif;
        ?>
        // Catch hide toggle events from Bootstrap to reposition the zclip flash
        $('.statistics').on('hidden.bs.collapse', function () {
            setTimeout(function(){
                setZclip();
            }, 200 );
        });

        // Copy to Clipboard using Jquery ZClip plugin; see http://www.steamdev.com/zclip/
        $('input.shorten').zclip({
            setHandCursor: false,
            path:'<?= Config::$storageDir ?>ZeroClipboard.swf',
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

        $(window).resize(function(){resizeBottom();});
        resizeBottom();
    });

    // Reposition the zClip's Flash overlay to catch the click
    function setZclip()
    {
        $('input.shorten').zclip('remove');
        $('input.shorten').zclip({
            setHandCursor: false,
            //path:'http://davidwalsh.name/demo/ZeroClipboard.swf',
            path:'<?= Config::$storageDir ?>ZeroClipboard.swf',
            copy:function(){return $(this).val();},
            //the triggered function fires allthoug we removed the zClip, therefore we do not need to set it again
            afterCopy:function(){}
        });
    }

    //Resize the bottom filler to make the scrollspy look better
    function resizeBottom() {
        $('#bottom-filler').height($(window).height() - $('section').last().height() - $('nav').height());
    }

</script>

</body>
</html>