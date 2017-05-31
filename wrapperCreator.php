<?php
include("crawlHeader.html"); // Just design junks
include ("func.php"); // All functions, such as CURL stuffs


//==============================================================================
//================= SECTION: Delete Temp File on window close ==================
//==============================================================================
print <<< END
<script>
$(window).bind('beforeunload', function() {

var frame = document.getElementById('wrapperSplitter').src;
var pageOnly = frame.match(/wrap.*.html/g);

  var iframe;
  iframe = document.createElement('iframe');
  iframe.src = 'http://idxcrawler.co/deleteFile.php?file='+pageOnly;
  iframe.style.display = 'none';
  document.body.appendChild(iframe);

  return false;

});
</script>
END;


//==============================================================================
//================= SECTION: Find URL and parse it (from form) =================
//==============================================================================

if ($_POST["customUrl"] == NULL && $_POST["htmlText"] == NULL){
  //--------- If no URL present the form to enter one
?>
<script type="text/javascript">
function ButtonClicked(event){
$(".se-pre-con").fadeIn("slow");
progressJs().start().autoIncrease(10, 750).onprogress(function(targetElm,percent) {if(percent > 99){progressJs().set(1);}});
return true;
}
</script>
<center><h1><u>Static Wrapper Creator</u><h1></center><p>

  <div style='margin-left:auto;margin-right:auto;position:relative;width:350px;border-style:solid;border-radius:25px;padding:25px;'>
        <h3>Tell Me the Page You Want To Use:</h3>

            <form class='crawlform' name=getResuls action='<?php $_SERVER['PHP_SELF']?>' method='post'>
              <input type="checkbox" name="phponly"> No Split (noJs)<p>
                <input type="checkbox" name="noTidy" checked> No Tidy<p>
                  <hr>
        <h4>Scrape HTML</h4><i>(Example:http://tonywp.idxsandbox.com/trappawrappa/)</i>: <input type='text' class='customUrl' name='customUrl' value="" style='width:300px'>
        <center><h4>(OR)</h4></center>
          <h4>Site Address</h4><i>(Example:http://tonywp.idxsandbox.com/)</i>: <input type='text' class='customUrl' name='siteAddress' value="" style='width:300px'>
        <h4>Enter HTML:</h4>
            <textarea name='htmlText' rows="10" cols="100" style="width:300px;"></textarea>
                <div id="formsubmitbutton" style="margin:30px;">
          <input type="submit" name="submitter" value="Run Now" onclick="ButtonClicked(event)">
        </form>

  </div>

<?php

}else{
//-----------If URL exists start URL parsing process
$rawUrl = $_POST["customUrl"]; // Bring in the Raw URL
$htmlText = $_POST["htmlText"];// Bring in Raw HTML
$noJs = $_POST["phponly"];//NoJs Selection
$siteAddress = $_POST["siteAddress"];//No CURL URL
$noTidy = $_POST["noTidy"];//No CURL URL



if ($siteAddress != NULL){
  $url = $siteAddress;
}

if($rawUrl == NULL & $siteAddress == NULL){
  echo "<center><b>You didn't enter a valid URL. Please Go back and <a href=wrapperCreator.php>Run Another Site</a></center></b>";
  exit;
}
if ($rawUrl != NULL){
// Check for a Valid URL before moving forward
if (preg_match('/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/', $rawUrl) !== 1) {
echo "<center><b>You didn't enter a valid URL. Please Go back and <a href=wrapperCreator.php>Run Another Site</a></center></b>";
exit;
}

//Using CURL to get the resulting URL - Including Redirects
$urlResult = getURL($rawUrl); // Use CURL to get any redirects and resulting URL
$url = strtolower($urlResult[url]); //Set the URL with what CURL returns in the array
$statusCheck = GetUrlStatus($url); //Gets the status of the URL


//Show them the site they entered - cause they prolly forgot
echo "<center><h2>Currently Viewing: <a href='".$url."' target='_new'>".$url."</a> <font size=2>(Request Status: ".$statusCheck[http_code].")</font></h2> <h4>(<a href=wrapperCreator.php>Run Another Site</a>)</h4></center><p>&nbsp;<p>";
//echo "<div id='working'><center><font color=red><i>I'm crawling the site, this may take some time . . .</i></font></center><p></div>";


if ($statusCheck[http_code] != 200){
  echo "<h2><font color=red>".$url."</font> is returning a status of <font color=red>".$statusCheck[http_code]."</font> <font size=3>(<a href=https://en.wikipedia.org/wiki/List_of_HTTP_status_codes target=_new>HTTP Status Codes</a>)</font> and cannot be reached for crawling.<br /> Please <a href=/wrapperCreator.php>Run Another Site</a></h2>";
  exit;
}

}
//==============================================================================
//====== SECTION: Header Content, What we're doing and click instructions ======
//==============================================================================

//Tell them what we did
print <<<END
<style>
.li
{
display: inline;
list-style-type: none;
padding-right: 20px;
}
</style>
<h3>If the following existed, we...</h3><p>
Converted Relative URLs to FQDN | Commented ASPX Form Tag | Replaced Title Text | Removed all OG links | Removed base tag | Added Font Awesome CDNs | Added Map CSS 100% Width | Remove Current and Active in Links | Remove any idx-robot or nofollow tags<p>
END;


//==============================================================================
//========== SECTION: Create HTML Temp File and Load IFRAME ====================
//==============================================================================
// HTML Contents of the Wrapper Page (Use CURL or Pasted Contents)
if ($rawUrl != NULL){
$wrapperContents = GetUrlContents($url); //Get Contents of the Wrapper Page
}else{
  echo "<center><h2>(<a href=wrapperCreator.php>Run Another Site</a>)</h2></center>";
  $wrapperContents = $htmlText; //Get Contents of the Wrapper Page
}

preg_match("/<frame\ssrc=([^\s]+)/", $wrapperContents,$frameUrl); //Find frame on the page
$frameUrl = preg_replace("/\"|'/", "", $frameUrl[1]); //Remove Quotes from URL

if($frameUrl != NULL){
  echo "<center><h2><font color=red>I found an iframe. Try running on iframe contents:</font> <a href=http://idxcrawer.co/wrapperCreator.php?customUrl=".urlencode($frameUrl).">Click Here</a></h2></center>";
}

//------------ DO ALL THE WORK WE CAN BEFORE WRITING THE PRETTY HTML -----------
//------------------------------------------------------------------------------

// WE DO ALL FQDN WORK FIRST
preg_match("/^.+?[^\/:](?=[?\/]|$)/", $url, $baseUrl);// Need to remove any subs and get to the base url

$baseUrlString = $baseUrl[0]."/";//convert the URL array into a normal string
$replaceDotSlash = preg_replace("/\.+\//", $url, $wrapperContents); //Replace all ./ with FQDN
//Replace all Rel URLS with FQDN
$replaceRelUrl = preg_replace("/((href=|src=|background=|url\()([\"']))(?!http|#|javascript|\"|'|\&lt|>|mailto:|tel:|\.\.|\?|\/\/)/i", "$1$baseUrlString", $replaceDotSlash);
//I might have doubled '//' so we'll take care of that now
$removeDoubleSlash = preg_replace("/(\.broker|\.business|\.city|\.community|\.company|\.condos|\.contact|\.corp|\.estate|\.expert|\.forsale|\.home|\.homes|\.hotel|\.hotels|\.house|\.inc|\.industries|\.investments|\.mls|\.mobile|\.mortgage|\.properties|\.property|\.realestate|\.realtor|\.realty|\.rent|\.rentals|\.sale|\.search|\.team|\.villas|\.com|\.realtor|\.net|\.biz|\.org|\.info|\.ca|\.us|\.mobi|\.tel|\.name)\/+/", "$1/", $replaceRelUrl);


//NOW WE DO GENERAL REPLACEMENTS
$removeCurrent = preg_replace("/(class=.*)current-menu-item|current_page_item|current-menu-parent|active/", "$1", $removeDoubleSlash); //Kill Active Links
$changeTitle = preg_replace("/<title.*<\/title>/", "<title>Home Search</title>", $removeCurrent); //Replace Title
$removeOgTags = preg_replace("/<meta [^>]*property=[\\\"']og:.*[\\\"'] [^>]*content=[\\\"']([^'^\\\"]+?)[\\\"'][^>]*>/", "", $changeTitle); //Remove OG Tags

$addAwesomeAndMapCss = preg_replace("/(<\/title>)/", "$1\n<link href='//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.2/css/bootstrap-combined.no-icons.min.css' rel='stylesheet'>\n<link href='//netdna.bootstrapcdn.com/font-awesome/3.2.1/css/font-awesome.css' rel='stylesheet'>\n<link href='//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css' rel='stylesheet'/>\n<style>#IDX-mapContainer{width:100% !important;}</style>", $removeOgTags); //Add Font Awesome and IDX Map 100%

$removeBaseTag = preg_replace("/<base.*>/", "", $addAwesomeAndMapCss); //Remove Base Tag
$removeRobots = preg_replace("/<meta.*robot.*/", "", $removeBaseTag); //Remove Robots

$removeAsp = preg_replace("/(<form.*aspnetform.*)/", "<!---- THIS MIGHT BREAK MENUS ----><br /><!-- NOTE: If this breaks drop-down menus you may need to un-comment this form tag and wrap each ASPX input set in their own form tags --><br /><!-- $1 --><br /><!----------------------->", $removeRobots); //Remove ASP Form

$addJs = preg_replace("/(<\/body)/", "<script src='http://idxcrawler.co/splitScript.js' id='splitScript'></script>$1", $removeAsp);
//------------- Lets give tidy a try -------------------------------------------
// Specify configuration
$config = array(
           'indent'         => true,
           'output-html'   => true,
           'wrap'           => 0);

// Tidy
$tidy = new tidy;
$tidy->parseString($addJs, $config, 'utf8');
$tidy->cleanRepair();

if($noTidy == NULL){
  echo "<div class='pagesReturnedContainer'><button type='button' class='btn btn-info' data-toggle='collapse' data-target='#demo'>Tidy Errors/Warnings</button><div id='demo' class='collapse'>";
  echo "<pre>";
  $tidy->diagnose();
  echo $tidy->errorBuffer;
  echo "</pre>";
  echo "</div></div>";
}



echo "<center><h3><font color=#008000>Click to Select the Split Area Below</font></h3></center>";
//------------------ Clean Tidy ------------------------------------------------
//Might have to clean up some of the tiday code, do that now
//------------------------------------------------------------------------------
$cleanTidy = preg_replace("!<\\\/!", "</", $tidy);


//Final Pretty HTML for an awesome static wrapper
if ($noTidy != NULL){
  $finalizedHTML = $addJs;
}else{
  $finalizedHTML = $cleanTidy;
}


//echo "<xmp>";
//echo $cleanTidy;
//echo "</xmp>";
//------------------------------------------------------------------------------
//------------------------------------------------------------------------------

$tmpfname = tempnam("/home/idxcrawler/public_html/tmp", "wrap"); //Write a temp file with the wrapper contents
$tmpfnameExt = $tmpfname.".html"; //Add the extension so it's DOM ready
rename($tmpfname, $tmpfnameExt); //Rename the actual file
chmod($tmpfnameExt, 0755); //Set File Permissions to Writable
$myfile = fopen($tmpfnameExt, "w"); //Open File Handle

//Need to determine if we're spliting or just giving the HTML - noJs
if ($noJs === "on"){
  $xmpstart = "<xmp>";
  $xmpstop = "</xmp>";
fwrite($myfile, $xmpstart.$finalizedHTML.$xmpstop); //Add the Wrapper Content
//fwrite($myfile, $xmpstop.$finalizedHTML.$xmpstop); //Add the Wrapper Content
//fwrite($myfile, $xmpstop.$finalizedHTML.$xmpstop); //Add the Wrapper Content
}else{
fwrite($myfile, $finalizedHTML); //Add the Wrapper Content
}



fclose($myfile); //Close The File

$path_parts = pathinfo($tmpfnameExt); //Need just the basename to load the iframe

// Load the IFRAME with the temp file
echo "<iframe id='wrapperSplitter' src=http://idxcrawler.co/tmp/".$path_parts['basename']." width=1300 height=750></iframe>";

}
//unlink($tmpfnameExt); // Destroy Temp file when all done.
include ("crawlFooter.html");
?>
