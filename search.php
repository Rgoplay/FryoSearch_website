<!DOCTYPE html>
<?php
$cookie_name = "warningAccepted";
$cookie_value = true;
$warningAccepted = false;
if(isset($_COOKIE[$cookie_name]) && ($_COOKIE[$cookie_name] == $cookie_value)) {
    $warningAccepted = true;
}
?>
<html lang="en">
<head>
    <title>FryoSearch</title>
    <link rel="stylesheet" type="text/css" href="search.css">
    <meta name="charset" content="utf-8">
    <link rel="search" type="application/opensearchdescription+xml" title="FryoSearch" href="search.xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="FryoSearch is a small private search engine with no ads and no AI.">
    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
</head>
<body>
    <div id="searchBar">
    <h1>FryoSearch</h1>
    <form action="search.php" method="get">
        <input placeholder="Enter your query" type="text" name="search" required id="searchbox" autofocus>
        <input type="submit" value="Search" id="searchButton">
    </form>
    </div>

    <script>
        function warningCookie() {
            // Set new cookie
            const d = new Date();
            d.setTime(d.getTime() + (1500*24*60*60*1000)); //1500 jours
            let expires = "expires="+ d.toUTCString();
            document.cookie = `warningAccepted=${true}; ${expires}; path=/`;
            location.reload();
        }
    </script>

    <?php
    if(!$warningAccepted) {
        echo "<h3>Before searching</h3>";
        echo '<div class="warning_row">';
        echo "<p>Please note that the links displayed on this search engine are not filtered, and some results may contain malicious content.</p>";
        echo '<input type="submit" value="Ok!" id="searchButton" onclick=warningCookie()>';
        echo '</div>';
        exit();
    }

    if(!isset($_GET['search'])) {
        echo "<h3>Welcome on FryoSearch !</h3>";
        exit();
    }
    
    $unwanted_array = array('Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
    'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
    'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'ae', 'ç'=>'c',
    'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'œ'=>'oe',
    'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );

    $initial_text = trim($_GET ['search']);

    $search = strtr($initial_text, $unwanted_array );

    $search = preg_replace("/[^A-Za-z0-9 ]/", ' ', $search);
    $search = preg_replace("/\s+/", ' ', $search);
    $search = mb_strtolower($search);

    $start_time =  microtime(true);

    $con = new SQLite3('./hostingDb.sqlite');

    $researchSplit = explode(" ", $search);
    $r = [];

    // Generate i number of LIKE clause
    $query_like = "";
    for ($i=0; $i < count($researchSplit); $i++) {
        $query_like .= "(title LIKE :element". $i . " OR url LIKE :element" . $i . ")";
        if($i < count($researchSplit) - 1){
            $query_like .= " OR ";
        }
    }
    


    $query = "SELECT url,title,domain,lang,pageRank,desc FROM indexed_url WHERE ";
    $query .= $query_like." LIMIT 100000";
    $prepared = $con->prepare($query);

    for ($i=0; $i < count($researchSplit); $i++) {
        $prepared->bindValue(":element" . $i, "%".$researchSplit[$i]."%", SQLITE3_TEXT);
    }

    $fetch = $prepared->execute();
    while($i=$fetch->fetchArray()) {
        try {
            //echo "<pre>"; print_r($i); echo "</pre>";
            //echo "<br>";
            $clean_title = $i[1];

            $title_str = mb_strtolower($clean_title);
            $title_str = strtr($title_str, $unwanted_array);
            $title_str = preg_replace("/[^A-Za-z0-9 ]/", ' ', $title_str);
            $title_str = preg_replace("/\s+/", ' ', $title_str);

            $url_str = mb_strtolower(urldecode($i[0])); #url
            if(substr($url_str, -1) == '/'){
                $url_str = substr($url_str, 0, -1);
            }
            $corrected_url = str_replace(' ', '_', urldecode($i[0]));

            if($i[3] == "unknown"){
                $i[3] = "";
            }

            #desc_list = [i[4]] #desc
            $domain_str = mb_strtolower($i[2]); #domain
            $pageScore = $i[4];

            $desc = $i[5];
            if ($desc !== null) {
                if(mb_substr($desc, -1) != ".") {
                    $desc = $desc."...";
                }
            }
            if( $desc == null or $desc == ""){
                $desc = " ";
            }

            //$search = strtr($search, $unwanted_array);
            //$search = preg_replace("/[^A-Za-z0-9 ]/", '', $search);

            $title = 0;
            $url = 0;
            $domain = 0;

            // ===== TITLE COMPARISON =====
            
            /*$lev_cost = 1;
            $title = levenshtein($search,$title_str, $lev_cost, $lev_cost, $lev_cost); #titre
            $title = 50/exp(0.08*($title));*/
            //similar_text($search, $title_str, $title);
       

            $titleSplit = explode(" ", $title_str);
            $nbElementMatchingTitle = 0;
            $nbElementNotMatchingTitle = 0;

            compareStringListElements($researchSplit, $titleSplit, $nbElementMatchingTitle, $nbElementNotMatchingTitle);

            
            $title += 4*(log(4*$nbElementMatchingTitle+1) - log(6*$nbElementNotMatchingTitle+1));

            if ($nbElementNotMatchingTitle == 0){
                $title += 6;
            }
            if ($nbElementMatchingTitle == 0){
                $title -= 30;
            }

            $lenResearch = count($researchSplit)>1?(count($researchSplit)**0.7):1;
            $lenTitle = count($titleSplit)>1?(count($titleSplit)**0.7):1;

            $title += $lenResearch - $lenTitle;

            // ===== END TITLE COMPARISON =====
            

            // ===== URL COMPARISON =====

            $url = levenshtein($search, $url_str);
            $url = 60/exp(0.08*($url));
            //similar_text($search, $url_str,$url); #url

            $nbElementMatchingUrl = 0;
            $nbElementNotMatchingUrl = 0;

            compareStringListElements($researchSplit, array($url_str), $nbElementMatchingUrl, $nbElementNotMatchingUrl);
            
            $url += 0.5*($nbElementMatchingUrl - $nbElementNotMatchingUrl);

            $nbSlashes = mb_substr_count($url_str, "/") - 2;
            if($lenResearch == 1 && $nbSlashes == 0 && str_contains($url_str, needle: $search)){
                $nbDots = mb_substr_count($url_str, ".");
                $url += 50/(0.4*$nbDots+0.02);
            }

            // ===== END URL COMPARISON =====


            // ===== DOMAIN COMPARISON =====
            $nbElementMatchingDomain = 0;
            $nbElementNotMatchingDomain = 0;
            compareStringListElements($researchSplit, explode(".", $domain_str), $nbElementMatchingDomain, $nbElementNotMatchingDomain);
            $domain = 10*$nbElementMatchingDomain;
            if($nbElementMatchingDomain == 0){
                $domain = -10;
            }
            //$domain = levenshtein($search, $domain_str, insertion_cost:3);
            //$domain = 60/exp(0.08*($domain));
            //similar_text($search, $domain_str, $domain); #domain

            // ===== END DOMAIN COMPARISON =====


            // ===== PAGERANK =====
            $pageScore = 2*(($pageScore*20000*10)**0.6);


            // ===== FINAL FORMULA =====

            //$ratio = (0.5*($lenResearch)*$title + 0.2*$url + 0.05*$domain + 0.5*(1/$lenResearch)*$pageScore);
            $ratio = (1*($lenResearch)*$title + 0.2*$url + 0.2*(1/$lenResearch)*$domain + 0.5*(1/($lenResearch**1.5))*$pageScore);

            if ($ratio > 3){
                $r[] = [$corrected_url, $i[1], round($ratio, 1, PHP_ROUND_HALF_EVEN), round(1*($lenResearch)*$title, 2, PHP_ROUND_HALF_EVEN), round(0.2*$url, 2, PHP_ROUND_HALF_EVEN), round(0.2*(1/$lenResearch)*$domain, 2, PHP_ROUND_HALF_EVEN), round(0.5*(1/($lenResearch**1.5))*$pageScore, 2, PHP_ROUND_HALF_EVEN), $i[3], $desc];
            }
                
            
            
        }
        catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }
    }

    $r = array_unique($r, SORT_REGULAR);
    /*$serialized = array_map('serialize', $r);
    $unique = array_unique($serialized);
    $r = array_intersect_key($r, $unique);*/

    function cmp($b, $a) {
        return $a[2] <=> $b[2];
    }
    
    $answerL = uasort($r, "cmp");

    $answerL = array_slice($r, 0, 100);

    $end_time =  microtime(true);
    $execution_time = round(($end_time - $start_time), 2, PHP_ROUND_HALF_EVEN);
    echo "<h3>".count($r)." results for: ".htmlspecialchars($initial_text)." in ".$execution_time." seconds </h3>";
    echo "<div class='results'>";
    foreach ($answerL as $row) {
        echo "<div class='result'>";
        echo "<div class='first_row'>
                <p class='score'>".$row[2]."</p>
                <a class='title' href=".htmlspecialchars($row[0]).">".htmlspecialchars($row[1])."</a>
            </div>";
        echo "<div class='snd_row'>";
        echo "<p class='lang'>".mb_strtoupper($row[7])."</p>";
        echo "<p class='link'>".htmlspecialchars(urldecode($row[0]))."</p>";
        echo "</div>";
        echo "<p class='desc'>".htmlspecialchars(urldecode($row[8]))."</p>";
        echo "</div>";
    }
    echo "</div>";

    # For tests purposes
    # " - T: ".$row[3]." U: ".$row[4]." D: ".$row[5]." P: ".$row[6].

    #Close the connection
    $con->close();

    function compareStringListElements($elementsToMatch, $toMatchIn,  &$nbElementMatching, &$nbElementNotMatching) {
        foreach ($elementsToMatch as $element1) {
            if($element1 == "") {
                continue;
            }
            $atLeastOneMatch = false;
            foreach ($toMatchIn as $element2) {
                if($element1 === $element2 || substr($element1, 0, -1) === $element2 || substr($element2, 0, -1) === $element1) {
                    $atLeastOneMatch = true;
                    break;
                }
            }

            if($atLeastOneMatch) { //str_contains($title_str, needle: mb_strtolower($element1))
                $nbElementMatching++;
            }
            else {
                $nbElementNotMatching++;
            }
        }
    }
?>

</body>
</html>
