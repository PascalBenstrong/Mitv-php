<?php
header("Content-Type: application/json");
include_once "Server/Mitv.php";

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

            break;
        case "movie":
        case "movies":
            $json = json_encode( MatchIMDB());
            //print_r($json);
            break;
        case "tv-show":
        case "tv-shows":
        case "tv show":
        case "tv shows":

            $json = "nothing at the moment";
            break;
    }
}


$response = array(
    'StatusCode' => 200,
    'message' => 'Success',
    'data' => $json
);

$json_response = json_encode($response);
echo $json_response;

