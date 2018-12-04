<?php
include "simple_html_dom.php";


function MatchIMDB()
{
    $curl = curl_init();
    $url = "https://www.imdb.com/search/title?title_type=feature&release_date=2017-01-01,2018-12-31&count=100";
//echo $url;
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
//curl_setopt($curl, CURLOPT_TIMEOUT, 5)     ;
    set_time_limit(0);

    $response = curl_exec($curl);
//echo curl_error($curl);
    curl_close($curl);

//echo $response;

    $html = new simple_html_dom();
//echo implode("",$doc[1]);
//var_dump( $doc[0]);
    $html->load($response);
//print_r ($html);
    $list = $html->find('div[class="lister list detail sub-list"]',0);

    global $list;
    $html = new simple_html_dom();
    global $movies;
    if(!empty($list)) {
        global $movies;
        $movies = array();
        $count = 0;
        //echo $list[0] ." ".'<br>';
        foreach ($list->find('div[class="lister-item mode-advanced"]') as $item) {
            $count++;
            // match title and movie
            preg_match_all('![<]\s?div\s?class\s?=\s?["\']\s?lister\s?[-]\s?item\s?[-]\s?content\s?[\'"]\s?>\s?.*\s?[<]\s?a\s?href=["\']\/title\/(.*?)\/\?ref_=adv_li_tt\s?["\']\s[>]\s?(.*?)\s?[<].*!', $item, $match);

            $movie = array();
            $plainText = $item->plaintext;
            $id = implode("", $match[1]);
            $title = implode("", $match[2]);
            $movie["id"] = $id;
            $movie["title"] = $title;

            // match Image source
            preg_match_all("![<]\s?div\s?class\s?=\s?[\"']\s?lister\s?[-]\s?item\s?[-]\s?image\s?[^\"]\s?.*['\"]\s?[>].*?\s?[<]\s?img\s?alt\s?=\s?.*?\s?class\s?=\s?[\"']\s?loadlate\s?[\"']\s?.*?loadlate\s?=\s?[\"'](.*?)[\"']!", $item, $match);

            $imgsrc = implode("", $match[1]);
            $movie["img"] = $imgsrc;

            // match release year
            preg_match_all("![<]\s?span\s?class\s?=\s?[\"']\s?lister\s?[-]\s?item\s?[-]\s?year.*?[\"']\s?[>]\s?.*?(\d{4}).*?\s?[<]\s?\/\s?span\s?[>]!", $item, $match);
            $releaseyear = implode("", $match[1]);
            $movie["releaseYear"] = $releaseyear;

            // match duration
            preg_match_all("![<]\s?span\s?class\s?=\s?[\"']\s?runtime\s?[\"']\s?[>](.*?)\s?[<]\s?\/\s?span[>]!", $item, $match);
            $duration = implode("", $match[1]);
            $movie["duration"] = $duration;

            // match imd rating
            preg_match_all("![<]\s?span\s?class\s?=\s?[\"']\s?global\s?[-]\s?sprite\s?.*imdb\s?[-]\s?rating\s?[\"']\s?[>].*?(\d*\.?[\d*?]).*?[<]\s?\/\s?div\s?[>]!", $item, $match);
            $imdrating = implode("", $match[1]);
            $movie["rating"] = $imdrating;

            //matching genre
            preg_match_all("![<]\s?span\s?class\s?=\s?[\"']\s?genre\s?[\"']\s?[>]\s?(.*?)\s?[<]\s?\/\s?span\s?[>]!", $item, $match);
            $genre = implode("", $match[1]);
            $movie["genre"] = $genre;

            // matching the plot
            preg_match_all("![<]\s?div\s?class\s?=\s?[\"']\s?inline\s?[-]\s?block\s?ratings\s?[-]\s?user\s?[-]\s?rating\s?[\"']\s?[>].*[<]\s?p\s?class\s?=\s?[\"']\s?text\s?[-]\s?muted\s?[\"']\s?[>].*?(.*?)[.*?][<]\s?\/\s?p\s?[>]!", $item, $match);
            $plot = implode("", $match[1]);
            $movie["plot"] = $plot;

            //matching director
            preg_match_all("![<]\s?p\s?class\s?=\s?[\"']\s?[\"']\s?[>]\s?(.*?)\s?[<]\s?a\s?.*?[>]\s?(.*?)\s?[<]!", $item, $match);
            if (count($match) >= 3)
                $director = implode("", $match[1]) . implode("", $match[2]);
            else
                $director = "";

            $movie["director"] = $director;

            //matching the cast
            preg_match_all("![<]\s?p\s?class\s?=\s?[\"']\s?[\"']\s?[>].*?[<]\s?span\s?class\s?=\s?[\"']\s?ghost\s?[\"']\s?[>].*?[>]\s?(.*?)\s?[<]\s?\/\s?p\s?[>]!", $item, $match);
            $castraw = "<div id='castraw'>" . implode("", $match[1]) . "</div>";
            $html->load($castraw);
            $cast = $html->find("div[id='castraw']")[0]->plaintext;
            preg_match_all("!\s?Stars\s?:\s?(.*?)$!", $cast, $match);
            $movie["cast"] = implode("", $match[1]);

            $movies[$id] = $movie;
            //print_r($movies);
            /*echo "id: ".$id."<br> title: ".$title."<br>";
            //echo $plainText;
            //var_dump("<div id='content'>".$item."</div>");
           // echo "<div id='matches'>".implode("<br>",$match[1])."<span id='second' style='font-weight: bold'> Second Match</span><br>".implode("<br>",$match[3])."<br><div id='plaintext'>".$plainText. "</div>"."</div>";
            if($count >1)
            {
                $testFile = fopen("log.html", "w") or die("Unable to open or create file");
                fwrite($testFile,$item);
                fclose($testFile);
            }
            */
            //echo "<div id='content'><br><p><strong>" . $movie["title"] . "</strong></p><span>" . $movie["rating"] . "</span></div>";
            //die();

        }
    }
    return $movies;
}

function getElements($list)
{
    $animes_latest_episodes = array();
    if(!empty($list))
    {
        $infos = $list->find('div[class="iep"]');

        //echo count($infos)."<br>";
        $count = 0;
        foreach ($infos as $info )
        {
            //$anime_url = $info->find("div[class='ieppic']a[class='an']",0)->href;
            //preg_match_all("![<]\s?div\s?class\s?=\s?[\"']\s?ieppic\s?[\"']\s?[>]\s?[<]\s?a\s?class\s?=\s?[\"']\s?an\s?[\"']\s?href\s?=\s?[\"']\s?(.*?)\s?[\"']\s?[>]\s?[<]\s?img\s?class\s?=\s?[\"']\s?coveri\s?[\"']\s?src\s?=\s?[\"']\s?(.*?)\s?[\"']\s?alt\s?=\s?[\"']\s?(.*?)\s?[\"']\s?[>].*?[<]\s?div\s?class\s?=\s?[\"']\s?iepsbox\s?[\"']\s?[>]\s?[<]\s?a\s?href\s?=\s?[\"']\s?(.*?)\s?[\"']\s?[>].*?[<]\s?div\s?class\s?=\s?[\"']\s?iepst3r\s?[\"']\s?[>]\s?[<]\s?div\s?class\s?=\s?[\"']centerv\s?[\"']\s?[>]\s?(.*?)\s?[<]\s?!",$info,$matches);
            $ieppic = implode("",$info->find("div[class='ieppic']"));
            preg_match_all("![<]\s?a\s?class\s?=\s?[\"']\s?an\s?[\"']\s?href\s?=\s?[\"']\s?(.*?)\s?[\"']\s?[>]\s?[<]\s?img\s?class\s?=\s?[\"']\s?coveri\s?[\"']\s?src\s?=\s?[\"']\s?(.*?)\s?[\"']\s?alt\s?=\s?[\"']\s?(.*?)\s?[\"']\s?[>]!",$ieppic,$match);
            $anime_latest_episode["url"] = "http://animeheaven.eu/". implode("",$match[1]);
            $anime_latest_episode["coverImage"] = "http://animeheaven.eu/". implode("",$match[2]);
            $anime_latest_episode["title"] = implode("",$match[3]);

            $var = $info->find("div[class='iepst2r']",0);

            if(is_null($var))
            {

                $var = $info->find("div[class='iepst2']",0);
            }
            $var = $var->plaintext;
            $anime_latest_episode["episodeNo"] = $var;

            $var = $info->find("div[class='iepst3r']",0);

            if(is_null($var))
            {
                $var = $info->find("div[class='iepst3']",0);
            }
            $var = $var->plaintext;
            $anime_latest_episode["releaseDate"] = $var;

            array_push($animes_latest_episodes,$anime_latest_episode);

            //$animes_latest_episodes[] = $anime_latest_episode;
            //echo "<br>".$iepst2."<br>";

            //echo $anime_latest_episode["title"]."<br>";
            /*
             * echo "<pre>";
             * echo "</pre>";
             * echo $info->plaintext;
            $testFile = fopen("log.html", "w") or die("Unable to open or create file");
            fwrite($testFile,$info);
            fclose($testFile);
             */
        }
        //echo "<br>".$count."<br>";
    }

    return $animes_latest_episodes;
}
// latest episodes on Anime Heaven
function GetLatestAnimeEpisodes()
{
    //Initialize curl
    $curl = curl_init();
    $url = "http://animeheaven.eu/";

    // set curl properties
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_TIMEOUT, 5)     ;
    set_time_limit(0);

    //set response variable
    $response = curl_exec($curl);
    curl_close($curl);

    $html = new simple_html_dom();
    $html->load($response);
    //echo $html;
    $list = $html->find('div[class="iepbox"]',0);
    return getElements($list);

}

// return all the episodes of a particular Anime on AnimeHeaven
function GetAnimeEpisodes($url)
{

    $curl = curl_init();

    // set curl properties
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_TIMEOUT, 5)     ;
    set_time_limit(0);

    //set response variable
    $response = curl_exec($curl);
    curl_close($curl);

    $html = new simple_html_dom();
    $html->load($response);

    $infodeskbox = $html->find("div[class='infodesbox']",0);
    $info = array();

    if(!empty($infodeskbox))
    {
        $info["synopsis"] = html_entity_decode(($infodeskbox->find("div[class='infodes2']",0))->plaintext,ENT_QUOTES) ;

        $temp = ($infodeskbox->find("div[class='infodes2']",1))->plaintext;

        preg_match_all("!\s?Alias\s?:\s?(.*?)\s?Genres\/Tags\s?:\s?(.*?)\s?Status\s?:\s?(.*?)\s?Episodes\s?:\s?(.*?)\s?Year\s?:\s?(.*?)$!", $temp,$matches);

        if(!empty($matches))
        {
            $info["alias"] = preg_replace("!\B[A-Z]!"," $0",implode("",$matches[1]));
            $info["genre"] = implode("",$matches[2]);
            $info["status"] = implode("",$matches[3]);
            $info["episodes"] = implode("",$matches[4]);
            $info["year"] = implode("",$matches[5]);
        }

        //htmlspecialchars_decode
       /* $testFile = fopen("log.html", "w") or die("Unable to open or create file");
        fwrite($testFile,$temp);
        fclose($testFile);*/
    }
    $anime["info"] = $info;

    $infodeskmain = $html->find("div[class='infoepboxmain'] div[class='infoepbox']",0);
    $episodes = array();

    if(!empty($infodeskmain))
    {
        $infovanr = $infodeskmain->find("a[class='infovanr']");
        $infovan = $infodeskmain->find("a[class='infovan']");


        foreach ($infovanr as $item)
        {
            $href = $item->href;
            preg_match_all("!.*?[&]\s?[e]\s?=\s?(.*?)$!",$href,$matches);
            $num = implode("",$matches[1]);

            $episodes[$num] = "http://animeheaven.eu/".$href;

        }
        foreach ($infovan as $item)
        {
            $href = $item->href;
            preg_match_all("!.*?[&]\s?[e]\s?=\s?(.*?)$!",$href,$matches);
            $num = implode("",$matches[1]);

            $episodes[$num] = "http://animeheaven.eu/".$href;

        }

    }
    $anime["episodes"] = $episodes;

    $similarboxmain = $html->find("div[class='similarboxmain'] div[class='similarbox']",0);
    $similar = array();

    if(!empty($similarboxmain))
    {
        $an = $similarboxmain->find("a[class='an']");

        foreach ($an as $item)
        {
            $sim["href"] = "http://animeheaven.eu/".$item->href;
            $img = ($item->find("div[class='similarcmain'] div[class='similarc'] div[class='similarpic'] img[class='coveri']",0));
            $sim["title"] = $img->alt;
            $sim["imgsrc"] = "http://animeheaven.eu/".$img->src;

            array_push($similar,$sim);
        }

    }
    $anime["similar"] = $similar;

    return $anime;

}

function SearchAnimeHeaven($query)
{
    $query = explode(" ",$query);
    $query = implode("+",$query);
    $url = "http://animeheaven.eu/search.php?q=".$query;

    $curl = curl_init();

    // set curl properties
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_TIMEOUT, 5)     ;
    set_time_limit(0);

    //set response variable
    $response = curl_exec($curl);
    curl_close($curl);

    $html = new simple_html_dom();
    $html->load($response);

    $list = $html->find('div[class="lisbox"]',0);
    return getElements($list);
}

//echo "<pre>";
//print_r(MatchAnimeheaven());
//echo "</pre>";

$url = "http://animeheaven.eu/watch.php?a=Laytons%20Mystery%20Detective%20Agency&e=16";


/*$json = json_encode(MatchAnimeheaven());

echo "<pre>";
print_r($json);
echo "</pre>";

/*                                                                                                                   
 *                                                                                                                   
 * curl_setopt($ch, CURLOPT_TIMEOUT, 5);                                                                             
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);                                                                         
 */                                                                                                                  
                                                                                                                     
?>                                                                                                                   