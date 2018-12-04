
<body xmlns="http://www.w3.org/1999/html">
<script src="http://code.jquery.com/jquery-2.1.4.min.js"></script>

<p id="result" >Result:</p>
</br>

Enter name: <input type="text" id="name">
</br>
<button type='button' onclick="javascript:invokeCSCode($('#jsonData').text())" ;>Invoke C# Code</button>
<button type='button' onclick="alert('testing')">Show Alert</button>
<button type="button" onclick="createLog($('#name').val())" id="btn" >Create log</button>

<script type="text/javascript">


    function createLog(text) {
        var $a = $("<a>",{href:"index.php?contenttype=anime&l="+text,id:"log"});
        //$a.click();
        $('#btn').append($a);

        $('#log')[0].click();
        log(text);
    }
    function log(str) {
        $('#result').text($('#result').text() + " " + str);
    }
    var interval;
    var duration = 1000;
    var stime = Date.now();

    function invokeCSCode(data) {

        data = JSON.stringify(data).toString();
        interval = setInterval(send(data), 500);
        //jsBridge.invokeAction(data);
        //invokeCSharpAction(data);
    }

    function send(data)
    {
        log("Sending Data:" + data + "</br>" + stime);
        if (Date.now() - stime >= duration) {
            clearInterval(interval);
        }
        try {

            if (window.JsBridge) {
                window.JsBridge.onDataReceived(data);
            }
        } catch (err) {
            log(err);

            try {
                if (jsBridge) {
                    clearInterval(interval);
                    jsBridge.invokeAction(data);
                }
            } catch (err) {
                log(err);
                try {
                    if (JsBridge) {
                        JsBridge.onDataReceived(data);
                    }
                } catch (err) {
                    log(err);
                }
            }

        }

    }

</script>
</body>

<?php

include_once "Server/Mitv.php";

/* foreach ($_REQUEST as $item)
{
    //echo $item;
    // echo "</br>";
} */
$contentType = $_GET["ContentType"];
if(empty($contentType))
{
    $contentType = $_GET["contentType"	];
}

if(empty($contentType))
{
    $contentType = $_GET["contenttype"	];
}

$search = $_GET["q"];
if(empty($search))
{
    $search = $_GET["Q"];
}
global $json;

echo "</br> <pr>";
if(!empty($contentType))
{
    global $json;
    $contentType = strtolower($contentType);
    switch ($contentType)
    {
        case "anime":
        case "animes":
            if(!empty($search))
            {

                $json = json_encode(SearchAnimeHeaven($search));
            }else
            {
                $json = json_encode(GetLatestAnimeEpisodes());

            }
            echo "<div id='jsonData' hidden>".$json."</div>";
            //print_r($json);

            break;
        case "movie":
        case "movies":
            $json = json_encode( MatchIMDB());
            print_r($json);
            break;
        case "tv-show":
        case "tv-shows":
        case "tv show":
        case "tv shows":

            echo "Nothing at the moment";
            break;
    }
}
if(isset($_GET['l']))
{
    createLog($_GET['l']);
}
function createLog($content)
{
    $testFile = fopen("log.html", "w") or die("Unable to open or create file");
    fwrite($testFile,$content);
    fclose($testFile);
}
echo "</pre></</br>";

echo "<script type='text/javascript'>invokeCSCode({$json});</script>";
?>