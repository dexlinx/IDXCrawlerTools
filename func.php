<?php

// CURL FUNCTION: Curl A Pages Contents
//============================================
function GetUrlContents($givenUrl) {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_HEADER, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13"); // Necessary. The server checks for a valid User-Agent.
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
      curl_setopt($ch, CURLOPT_URL, $givenUrl);
      $data = curl_exec($ch);
      curl_close($ch);
      return $data;
      }


// CURL FUNCTION: Get Redir URLs and Status
//============================================
function GetUrlStatus($givenUrl)
      {
      $ch = @curl_init();
      @curl_setopt($ch, CURLOPT_URL, $givenUrl);
      @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      @curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13");
      @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
      @curl_setopt($ch, CURLOPT_TIMEOUT, 10);

      $response       = @curl_exec($ch);
      $errno          = @curl_errno($ch);
      $error          = @curl_error($ch);

          $response = $response;
          $info = @curl_getinfo($ch);
          //echo "<pre>";
          //var_dump($info);
          //echo "</pre>";
      return $info;
      }


// CURL FUNCTION: Get The Resulting URL
//============================================
function getURL($givenUrl){

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $givenUrl);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HEADER, true);
      curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13"); // Necessary. The server checks for a valid User-Agent.
      curl_exec($ch);

      $info = curl_getinfo($ch);
      curl_close($ch);
      return $info;
      }


      // FUNCTION: Build List of URLs (Main Only - With Echo)
      //======================================================

function ValidStatUrlListTabled($givenList){

      $matchesStatus = array();

echo "<div class='pagesReturnedContainer'><button type='button' class='btn btn-info' data-toggle='collapse' data-target='#demo'>Pages Crawled w/Status</button><div id='demo' class='collapse'>";
echo "If you see <font color=red>0</font> Status on ALL links. Try <a id='reloadPage' title='RerunPage' href='".$_SERVER[REQUEST_URI]."' onclick='ButtonClicked(event);return false;'>Refresh</a><p>";
echo "<table>";

      foreach ($givenList as $urlStat ){
        if (!preg_match('/\/feed\/$/', $urlStat) && !preg_match('/\/wp-comments-post.php$/', $urlStat)){ //No need to check urls with /feed/

      $returnStatus = GetUrlStatus($urlStat);


if($returnStatus[http_code] != 200){
  echo "<tr><td><b>Located:</b> <a href='".$urlStat. "' target='_new'>".$urlStat. "</a></td><td> <b>Status:</b> <font color=red>".$returnStatus[http_code]." </font><font size=1><i><b>(not crawled)</b></i></font></td></tr>";
}else{
  echo "<tr><td><b>Located:</b> <a href='".$urlStat. "' target='_new'>".$urlStat. "</a></td><td> <b>Status:</b> <font color=green>".$returnStatus[http_code]." </font><font size=1><i><b>(crawled)</b></i></font></td></tr>";
}

      if ($returnStatus[http_code] == 200 || $returnStatus[http_code] == 301){
          if ($returnStatus[http_code] == 200){
              array_push($matchesStatus, $urlStat);
          }else{ //IF it's a 301 I need to grab the redirect URL to crawl
            $newUrl = $returnStatus[redirect_url]; //Set the URL with what CURL returns in the array
            //echo "---New Url---> ".$newUrl."<p>";
            array_push($matchesStatus, $newUrl);
          }
      }
      }
    }
echo "</table>";
echo "</div></div>";
return $matchesStatus;

}

// FUNCTION: Build List of URLs
//============================================

function ValidStatUrlList($givenList){

$matchesStatus = array();
foreach ($givenList as $urlStat ){
  if (!preg_match('/\/feed\/$/', $urlStat) && !preg_match('/\/wp-comments-post.php$/', $urlStat)){ //No need to check urls with /feed/

$returnStatus = GetUrlStatus($urlStat);

if ($returnStatus[http_code] == 200 || $returnStatus[http_code] == 301){
    if ($returnStatus[http_code] == 200){
        array_push($matchesStatus, $urlStat);
    }else{ //IF it's a 301 I need to grab the redirect URL to crawl
      $newUrl = $returnStatus[redirect_url]; //Set the URL with what CURL returns in the array
      //echo "---New Url---> ".$newUrl."<p>";
      array_push($matchesStatus, $newUrl);
    }
}
}
}
return $matchesStatus;
}
// FUNCTION: Remove all URLs not of this page
//============================================

function currentSiteUrls($currentUrl,$matchList){
//---------- Remove all non-site URL strings
$urlString = str_replace ('/', '\/', $currentUrl); //Setup URL for Pattern
$siteUrlPattern = "/(".$urlString.")/"; //This is the Pattern
$onlySiteUrls = preg_grep($siteUrlPattern, $matchList); //Build URL with only Site URL's

return $onlySiteUrls;
}

?>
