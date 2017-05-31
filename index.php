<?php
include("crawlHeader.html"); // Just design junks
include ("func.php"); // All functions, such as CURL stuffs

// ----- Check for existance of URL
if ($_GET["customUrl"] == NULL){
?>
<script type="text/javascript">
function ButtonClicked(event){
$(".se-pre-con").fadeIn("slow");
progressJs().start().autoIncrease(10, 750).onprogress(function(targetElm,percent) {if(percent > 99){progressJs().set(1);}});
return true;
}
</script>

<center><h1><u>Original Crawler</u></h1></center><p>


  <div style='margin-left:auto;margin-right:auto;position:relative;width:300px;border-style:solid;border-radius:25px;padding:25px;'>
    Enter the URL: (Example:http://www.myhomexperts.com/)<p>

      <form class='crawlform' name=getResuls action='<?php $_SERVER['PHP_SELF']?>' method='get'>
        <input type='text' class='customUrl' name='customUrl' value="" style='width:300px'>
        <input type='checkbox' class='curlAll' name='curlAll'> Check Redirects in Content (A Bit Slower)<br>
        <div id="formsubmitbutton" style="margin:30px;">
          <input type="submit" name="submitter" value="Run Now" onclick="ButtonClicked(event)">
        </form>
  </div>

<?php

}else{
//=================================================================================================================================
//======================================================= SECTION: Determine Main URL so we can Crawl it ==========================
//=================================================================================================================================
//echo "<center>Locating URL...</center><p>";
$rawUrl = $_GET["customUrl"]; // Bring in the Raw URL

// Check for a Valid URL before moving forward
if (preg_match('/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/', $rawUrl) !== 1) {
echo "<center><b>You didn't enter a valid URL. Please Go back and <a href=index.php>Run Another Site</a></center></b>";
exit;
}

//Using CURL to get the resulting URL - Including Redirects
$urlResult = getURL($rawUrl); // Use CURL to get any redirects and resulting URL
$url = strtolower($urlResult[url]); //Set the URL with what CURL returns in the array
$statusCheck = GetUrlStatus($url); //Gets the status of the URL

//Show them the site they entered - cause they prolly forgot
echo "<center><h2>Currently Viewing: <a href='".$url."' target='_new'>".$url."</a> <font size=2>(Request Status: ".$statusCheck[http_code].")</font></h2> <h4>(<a href=index.php>Run Another Site</a>)</h4></center><p>&nbsp;<p>";
//echo "<div id='working'><center><font color=red><i>I'm crawling the site, this may take some time . . .</i></font></center><p></div>";

//Show an ERROR if we don't get a 200 back
if ($statusCheck[http_code] != 200){
  echo "<h2><font color=red>".$url."</font> is returning a status of <font color=red>".$statusCheck[http_code]."</font> <font size=3>(<a href=https://en.wikipedia.org/wiki/List_of_HTTP_status_codes target=_new>HTTP Status Codes</a>)</font> and cannot be reached for crawling.<br /> Please <a href=/crawler/index.php>Run Another Site</a></h2>";
  exit;
}

//=================================================================================================================================
//========================================== SECTION: Get all Unique URL's on Site (Just Looking through Index Page) ==============
//=================================================================================================================================
$curlContentsWixCheck = GetUrlContents($url); //Get Contents of the Index Page
preg_match("/var usersDomain = \"https:\/\/users.wix.com\/wix-users\";/", $curlContentsWixCheck, $checkForWix); //Check For WIX Site

// Need to see if it's a WIX site and react accordingly
if (empty($checkForWix)){
  $curlContents = GetUrlContents($url); //No Wix - Get Contents
}else{
  $url = $url."?_escaped_fragment_=";
  $curlContents = GetUrlContents($url); //Append URL for WIX Sites
}

preg_match_all("/\b(href=.|src=.)(?:[^\s,.!?]|[,.!?](?!\s))+/", $curlContents, $pageMatches); //Look for URLS on the page

$removeNonPageLinks = preg_grep("/(?s)^((?!\.png|\.jpg|\.css|\.js|\.xml|\.pdf|\.ico|\.axd|\.svg|\.gif|src=|javascript.void).)*$/", $pageMatches[0]); // Remove all Non-page URLS (images, js, etc)
$removeTrash = preg_replace("/href=|\"|'|\/>+.*|>+.*/", "", $removeNonPageLinks); //Removing all quotes and other junk not needed

$rawPageMatches = array_unique($removeTrash); //Raw page matches to start

$removeIdx = preg_grep("/^((?!\/idx\/).)+$/", $rawPageMatches); // Remove IDX Links
$removePdf = preg_grep("/^((?!\.pdf).)+$/", $removeIdx); // Remove PDF Links
$removeSavedLinks = preg_grep("/^((?!\/i\/).)+$/", $removePdf); // Remove Saved Links

//When /something is returned in the URL I need to remove it
$urlArray = preg_split("/\/[^\/]*$/", $url);
$url = $urlArray[0];


// Work on the URL to return only the good stuffs
$matchesUniqueAll = array(); //Init Array for the push

foreach ($removeSavedLinks as $eachPageMatch){
$pageMatchesTrim = trim($eachPageMatch,"''"); // Remove ' from URL's

// This is fun, look for all relative URL's
  if (preg_match('/^(\.*\/)$/', $value) === 1){ // This matches all ./ with any number of .
    $pageMatchesTrim=$url; //Replaces all ./ with the FQDN
    array_push($matchesUniqueAll, $pageMatchesTrim); //Push that Into the array

  //Now we look for any Relative Domains that aren't ./
  }elseif (preg_match('/^((?!\.(abogado|academy|accountant|accountants|active|actor|ads|adult|africa|agency|airforce|alsace|amsterdam|analytics|apartments|app|arab|archi|architect|army|art|associates|attorney|auction|audible|audio|auto|autos|baby|band|bank|banque|bar|barcelona|bargains|baseball|basketball|bayern|beauty|beer|berlin|best|bet|bible|bid|bike|bingo|bio|black|blackfriday|blog|blue|boats|boo|book|booking|boston|boutique|box|broadway|broker|brussels|budapest|build|builders|business|buy|buzz|bway|bzh|cab|cafe|cam|camera|camp|capetown|capital|car|cards|care|career|careers|cars|casa|cash|casino|catalonia|catering|center|ceo|cfd|charity|chat|cheap|christmas|church|city|cityeats|claims|cleaning|click|clinic|clothing|cloud|club|coach|codes|coffee|college|cologne|com|community|company|computer|comsec|condos|construction|consulting|contact|contractors|cooking|cool|corp|country|coupon|coupons|courses|cpa|credit|creditcard|creditunion|cricket|cruises|cymru|cyou|dad|dance|data|date|dating|day|dds|deal|deals|degree|delivery|democrat|dental|dentist|desi|design|diamonds|diet|digital|direct|directory|discount|diy|docs|doctor|dog|domains|dot|download|earth|eat|eco|ecom|education|email|energy|engineer|engineering|enterprises|epost|equipment|esq|estate|eus|events|exchange|expert|exposed|express|fail|faith|family|fan|fans|farm|fashion|feedback|film|final|finance|financial|financialaid|fish|fishing|fit|fitness|flights|florist|flowers|fly|foo|food|football|forsale|forum|foundation|free|frl|fun|fund|furniture|futbol|fyi|gal|gallery|game|games|garden|gay|gent|gift|gifts|gives|glass|global|gmbh|gold|golf|goo|gop|graphics|gratis|green|gripe|group|guide|guitars|guru|hair|halal|hamburg|haus|health|healthcare|heart|help|helsinki|here|hiphop|hiv|hockey|holdings|holiday|home|homes|horse|hospital|host|hosting|hot|hotel|hotels|house|how|icu|idn|immo|immobilien|inc|indians|industries|ing|ink|institute|insurance|insure|international|investments|ira|irish|islam|ist|istanbul|jetzt|jewelry|joburg|juegos|kaufen|kid|kids|kim|kitchen|kiwi|kosher|kyoto|land|lat|latino|law|lawyer|lds|lease|legal|lgbt|life|lifestyle|lighting|limited|limo|link|live|living|llc|llp|loan|loans|lol|london|lotto|love|ltd|luxe|luxury|madrid|mail|maison|management|map|market|marketing|markets|mba|med|media|medical|meet|melbourne|meme|memorial|men|menu|miami|mls|mobile|mobily|moda|moe|mom|money|mormon|mortgage|moscow|moto|motorcycles|mov|movie|music|nagoya|navy|network|new|news|ngo|ninja|now|nowruz|nrw|nyc|okinawa|one|ong|online|ooo|organic|osaka|paris|pars|partners|parts|party|pay|persiangulf|pet|pets|pharmacy|phd|phone|photo|photography|photos|physio|pics|pictures|pid|pink|pizza|place|play|plumbing|plus|poker|porn|press|productions|prof|promo|properties|property|pub|qpon|quebec|racing|radio|realestate|realtor|realty|recipes|red|rehab|reise|reisen|reit|ren|rent|rentals|repair|report|republican|rest|restaurant|review|reviews|rich|rip|rocks|rodeo|roma|rsvp|rugby|ruhr|run|ryukyu|safe|sale|salon|sarl|save|scholarships|school|schule|science|scot|search|secure|security|services|sex|sexy|shia|shiksha|shoes|shop|shopping|show|singles|site|ski|soccer|social|software|solar|solutions|soy|spa|space|sport|sports|spot|srl|stockholm|storage|store|studio|style|sucks|supplies|supply|support|surf|surgery|swiss|sydney|systems|taipei|tatar|tattoo|tax|taxi|team|tech|technology|tennis|thai|theater|theatre|tickets|tienda|tips|tires|tirol|today|tokyo|tools|top|tour|tours|town|toys|trade|trading|training|translations|tube|university|uno|vacations|vegas|ventures|versicherung|vet|viajes|video|villas|vin|vip|vision|vlaanderen|vodka|vote|voting|voto|voyage|wales|wang|watch|web|webcam|website|wed|wedding|weibo|whoswho|wien|wiki|win|wine|work|works|world|wow|wtf|xin|xyz|yachts|yoga|yokohama|you|zip|zone|zuerich|zulu|com|realtor|net|biz|org|info|ca|us|mobi|tel|name|coop)($|\/)).)*$/', $pageMatchesTrim) === 1) {

  $relValue = preg_replace("/^(\.*\/)?/m", "/", $pageMatchesTrim); //Replace anything with ./ with any number of . and replace with /

  if (preg_match('/^((?!#).)+$/', $relValue) === 1){ // Don't put any Rel URL's in the array with #
    //echo "REL VALUE==>".$relValue."<P>";
    $pageMatchesTrim=trim($url,"/").$relValue; // PUt the URL back together making a FQDN
    array_push($matchesUniqueAll, $pageMatchesTrim); //URL is back together, push into array
}
}else{
    array_push($matchesUniqueAll, $pageMatchesTrim); //Any URLS left, push them into the array
}
}

$onlySiteUrls = currentSiteUrls($url,$matchesUniqueAll); //Function to remove non-local urls



//We need to build the proper URL based on whether this is a WIX site
if (empty($checkForWix)){
   $cleanUrlList = preg_grep("/^((?!#).)+$/", $onlySiteUrls); //No WIX - Strip #
}else{

$wixUrls = preg_replace("/#!/", "?_escaped_fragment_=", $onlySiteUrls);
$cleanUrlList = $wixUrls; //WIX - Don't strip #
}

$returnedList = ValidStatUrlListTabled($cleanUrlList); // Gets STatus and Redirs and Builds List
$removeIdxFinal = preg_grep("/^((?!\/idx\/).)+$/", $returnedList); // Remove IDX Links
$matchesUnique = array_unique($removeIdxFinal); //Strip all duplicates
$countMatchesUnique = count($matchesUnique); //Count the final list

echo "<center><h5>Found ".$countMatchesUnique." pages on this site.</h5></center><p>";
//=================================================================================================================================
//========================================== SECTION: PAGE BY PAGE RESULTS ========================================================
//=================================================================================================================================

//Define some VARS to use in this foreach loop
$runonce = 0;
$allFirstPageNonIdx = $matchesUnique;
$allFirstPageIdx = array();
$totalUpdatesSum = 0;

echo "<div id='working' style='width: 85%; margin: auto;'>";
foreach ($matchesUnique as $u ){
$page_data = GetUrlContents($u);  // Get the Contents of the current page


//------------------------------------------------------------------------------
//Adds contents of any frame to the page data that will be crawled

preg_match_all("/\b(src=.)(?:[^\s,.!?]|[,.!?](?!\s))+.*<\/iframe>/", $page_data, $innerPageIframes); //Look for frames in the content
$removeIframeTrash = preg_replace("/href=|\"|'|\/>+.*|>+.*| |src|=/", "", $innerPageIframes[0]); //Get Down to Iframe URL

foreach ($removeIframeTrash as $i ){

$frame_data = GetUrlContents($i);  // Get the Contents of the frame
$frame_contents .= $frame_data; //Build String URL with contents of all frames
}
$page_data .= $frame_contents; //Add Frame Contents to Crawl Contents
//------------------------------------------------------------------------------

preg_match_all("/\b(href=.|src=.|action=.)(?:[^\s,.!?]|[,.!?](?!\s))+/", $page_data, $onPageMatches); //Look for URLS on the page
$onPageRemoveNonPageLinks = preg_grep("/(?s)^((?!\.png|\.jpg|\.css|\.js|\.xml|\.pdf|.ico).)*$/", $onPageMatches[0]); // Remove all Non-page URLS (images, js, etc)
$onPageRemoveTrash = preg_replace("/href=|\"|'|\/>+.*|>+.*/", "", $onPageRemoveNonPageLinks); //Removing all quotes and other junk not needed




$rawPageMatches = array_unique($onPageRemoveTrash); //Raw page matches to start

$nonIdxUrls = preg_grep("/^((?!\/idx\/).)+$/", $rawPageMatches); //Get all NON IDX URLS
$locateIdxUrls = preg_grep("/(\/idx\/)/", $rawPageMatches); //Grep the Array to find all IDX links
$idxUrls = array_diff($locateIdxUrls, $allFirstPageIdx); //ONlY run URLS NOT on first page

//FIRST RUN: STORE IDX URLS
if($runonce == 0){
  $allFirstPageIdx = $idxUrls;
  }


if ($_GET["curlAll"] == 'on'){ //Only run if they turn on the option in the form
//Find out if any NON IDX URL's are redirects to IDX Stuffs
//-----------------------------------------------------------
$cleanNonIdxUrls = array();
foreach ($nonIdxUrls as $singleNonIdxUrls) {
preg_match('/(https:\/\/)|(http:\/\/).([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?/',$singleNonIdxUrls,$nonIdxurlOnly); //Strip down to only the URLS
array_push($cleanNonIdxUrls,$nonIdxurlOnly[0]);
}

$stripCleanNonIDxUrls = preg_grep("/^((?!png|jpg|css|js|xml).)*$/", $cleanNonIdxUrls); //Remove all images, stylesheets, and js
$localSiteUrls = currentSiteUrls($url,$stripCleanNonIDxUrls); //Remove all URLs not of this site
$localUrlsDeDup = array_unique($localSiteUrls); //Remove Duplicates from the List
$nonIdxThisPageOnly = array_diff($localUrlsDeDup, $allFirstPageNonIdx); //ONLY run URLS NOT on First Page
$pagesReturnedList = ValidStatUrlList($nonIdxThisPageOnly); // Gets STatus and Redirs and Builds List
$resultingIdxUrls = preg_grep("/(\/idx\/)/", $pagesReturnedList); //Grep the Array to find all IDX links

//FIRST RUN: STORE NON IDX URLS
if($runonce == 0){
    $allFirstPageNonIdx = $localUrlsDeDup;
  //echo "<h1>runonce->".$runonce."</h1><p>";
}
//---------------------------------------------------------

$allIdxUrls = array_merge($resultingIdxUrls, $idxUrls); //Combine to get all IDX Urls
}else{
  $allIdxUrls =  $idxUrls;
}

/*
echo "<pre>ALL IDX";
print_r($allIdxUrls);
echo "</pre>";
*/

//============= Get the Custom Links, Widgets, and Details Pages
$checkForLinks = preg_grep("/.*results.php\?.*/", $allIdxUrls); // Match all Custom Links
$checkForWidgets = preg_grep("/.*JS.*/", $allIdxUrls); // Match all Widgets
$checkForDetails = preg_grep("/(details.php?)/", $allIdxUrls); // Match all Details Pages
$checkForCustomForms = preg_grep("/action=/",$allIdxUrls);//Check for Custom Form Actions

/*
echo "<pre>links";
print_r($checkForLinks);
echo "</pre>";
echo "<pre>widgets";
print_r($checkForWidgets);
echo "</pre>";
echo "<pre>details";
print_r($checkForDetails);
echo "</pre>";
echo "<pre>Custom Forms";
print_r($checkForCustomForms);
echo "</pre>";
*/

$totalUpdates = count($checkForCustomForms) + count($checkForLinks) + count($checkForWidgets) + count($checkForDetails); // Total Updates
$totalUpdatesSum+= $totalUpdates; //for the sum at the bottom of the page

//------------- Here is where the real work begins
//-------------------------------------------------
if ($checkForCustomForms == NULL && $checkForLinks == NULL && $checkForWidgets == NULL && $checkForDetails == NULL){
echo "";
}else{

  // I'm gonna tell you the page I'm searching
  echo "<div style='background-color:#8CC645;width:100%;padding:10px;margin-top:10px;margin-bottom:10px'>";
  echo "<h2>PAGE: <a href='".$u."' target='_new'>".$u."</a> (Updates: ".$totalUpdates.")</h2>";
  if (!empty($frame_contents)){
    echo "<h3><i><font color=#fff>Some results found in frames on this page</font></i></h3>";
  }
  echo "</div>";

// Show the frames on this page
  if (!empty($frame_contents)){
    echo "<div class='framesReturnedContainer'><button type='button' class='btn btn-info' data-toggle='collapse' data-target='#frames'>Frames On This Page</button><div id='frames' class='collapse'>";
    foreach ($removeIframeTrash as $frameUrl){
      echo " <b>Iframe Source:</b> <a href='".$frameUrl."' target='_new'>".$frameUrl."</a><p>";
    }
    echo "</div></div>";
}



//------------- Let's get those CUSTOM LINKS buddy----------------------------------------------
if ($checkForLinks != NULL){
echo "<div style='background-color:#E8E8E8;width:100%;padding:10px;margin-top:10px;margin-bottom:10px'><h3>Custom Links to Update: ".count($checkForLinks)." </h3></div>";

foreach ($checkForLinks as $customLinks) {
            //=========================================== CUSTOM LINKS
          preg_match("/.*results.php\?.*/", $customLinks, $cleanLinks);
        	echo "<p><b>Value: </b>".$cleanLinks[0]."</p>";
}
}


//------------- Dude, we totes need to get the WIDGETS----------------------------------------------
if ($checkForWidgets != NULL){
echo "<div style='background-color:#E8E8E8;width:100%;padding:10px;margin-top:10px;margin-bottom:10px'><h3>Widgets To Update: ".count($checkForWidgets)."  </h3></div>";
foreach ($checkForWidgets as $widgets) {
        $search = array('<li','<a');
        $widgetsPure = str_replace($search,'',$widgets);
        //=========================================== WIDGETS
	    	echo "<p><b>Value: </b>".$widgetsPure."</p>";
}
}

//------------- Dude, we totes need to get the DETAILS----------------------------------------------
if ($checkForDetails != NULL){
echo "<div style='background-color:#E8E8E8;width:100%;padding:10px;margin-top:10px;margin-bottom:10px'><h3>Details Links To Update: ".count($checkForDetails)."  </h3></div>";
foreach ($checkForDetails as $details) {
        $search = array('<li','<a');
        $detailsPure = str_replace($search,'',$details);
        //=========================================== WIDGETS
	    	echo "<p><b>Value: </b>".$detailsPure."</p>";
}
}



//------------- Dude, we totes need to get the CUSTOM FORMS URLS ----------------------------------------------
if ($checkForCustomForms != NULL){
echo "<div style='background-color:#E8E8E8;width:100%;padding:10px;margin-top:10px;margin-bottom:10px'><h3>Custom Forms To Update: ".count($checkForCustomForms)."  </h3></div>";
foreach ($checkForCustomForms as $forms) {
        $search = array('<li','<a');
        $formsPure = str_replace($search,'',$forms);
        //=========================================== WIDGETS
	    	echo "<p><b>Value: </b>".$formsPure."</p>";
}
}

}
//------------ Real Work is done
//------------------------------

unset($frame_contents);//Need to clear this var for next run
$runonce++; // Increments the VAR so onetime stuff doesnt run again.
} //Foreach Statement

echo "<center><hr style='color:#919090; background-color:#919090; height:4px; border:none;'></center>";
echo "<center><h3>We searched your site for Widgets, Custom Links, Results, and Custom Forms...</h3></center>";
echo "<center><p><h1>Total Manual Updates Found: <font color=#800000>".$totalUpdatesSum."</font></h1><p><h3>Tool not support by IDX Broker and should only be used as a starting point to determine what manual work will be required for a successful migration.</h3></p> </center>";
echo "</div>";
}

include ("crawlFooter.html");
?>
