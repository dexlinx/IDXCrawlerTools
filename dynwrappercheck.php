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
<center><h1><u>Dynamic Wrapper Check</u></h1></center><p>
  <div style='margin-left:auto;margin-right:auto;position:relative;width:300px;border-style:solid;border-radius:25px;padding:25px;'>
    Enter the URL: (Example:http://www.myhomexperts.com/)<p>

      <form class='crawlform' name=getResuls action='<?php $_SERVER['PHP_SELF']?>' method='get'>
        <input type='text' class='customUrl' name='customUrl' value="" style='width:300px'>
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
echo "<center><b>You didn't enter a valid URL. Please Go back and <a href=dynwrappercheck.php>Run Another Site</a></center></b>";
exit;
}

//Using CURL to get the resulting URL - Including Redirects
$urlResult = getURL($rawUrl); // Use CURL to get any redirects and resulting URL
$url = strtolower($urlResult[url]); //Set the URL with what CURL returns in the array
$statusCheck = GetUrlStatus($url); //Gets the status of the URL

//Show them the site they entered - cause they prolly forgot
echo "<center><h2>Currently Viewing: <a href='".$url."' target='_new'>".$url."</a> <font size=2>(Request Status: ".$statusCheck[http_code].")</font></h2> <h4>(<a href=dynwrappercheck.php>Run Another Site</a>)</h4></center><p>&nbsp;<p>";
//echo "<div id='working'><center><font color=red><i>I'm crawling the site, this may take some time . . .</i></font></center><p></div>";



//------------------------------- 1. Requires 200 status code
echo "<div style='background-color:#E8E8E8;width:100%;padding:10px;margin-top:10px;margin-bottom:10px'><h3>1. Page Exists and Can Be Curled</div>";
if ($statusCheck[http_code] != 200){
  echo "<h2><font color=red>".$url."</font> is returning a status of <font color=red>".$statusCheck[http_code]."</font> <font size=3>(<a href=https://en.wikipedia.org/wiki/List_of_HTTP_status_codes target=_new>HTTP Status Codes</a>)</font> and cannot be reached for crawling.<br /> Please <a href=/crawler/dynwrappercheck.php>Run Another Site</a></h2>";
  exit;
}else{
  echo "<h3><font color=green>".$statusCheck[http_code]." Returned. This is a valid page</font></h3>";
}


$wrapperContents = GetUrlContents($url); //Get Contents of the Wrapper Page
preg_match_all("/<.+.idxstart.*|.+idxstop.*/i", $wrapperContents, $findIdxStopStart);

//------------------------------- LOOKING FOR IDX STOP/START TAGS
echo "<div style='background-color:#E8E8E8;width:100%;padding:10px;margin-top:10px;margin-bottom:10px'><h3>2. Proper IDX Start/Stop Tags</div>";

echo <<< EOL
<ul>
<li><a href='http://support.idxbroker.com/customer/en/portal/articles/1919274-automatically-create-wordpress-dynamic-wrapper?b_id=10433' target='_new'>Automatically Create WordPress Dynamic Wrapper</a></li>
<li><a href='http://support.idxbroker.com/customer/en/portal/articles/1917540-dynamic-wrapper-setup?b_id=10433' target='_new'>Dynamic Wrapper Setup</a></li>
</ul>
EOL;

echo "<b>Sample Proper Dynamic Wrapper Tags:</b>";
echo "<div style='background-color:#97C6AD;width:419px;padding:10px;margin-top:10px;margin-bottom:10px'>";
echo "<xmp>";
echo <<<EOL
<div id="idxStart" style="display: none;"></div>
<div id="idxStop" style="display: none;"></div>
EOL;
echo "</xmp>";
echo "</div>";

echo "<h2><u>Your Wrapper Page Results</u></h2>";

if ($findIdxStopStart[0][0] == NULL && $findIdxStopStart[0][1] == NULL){
  echo "<b><i><h3><font color=red>No IDX Tags on This Page!</font></h3></i></b>";
  $notOne = 1;
}

if ($findIdxStopStart[0][0] == NULL || $findIdxStopStart[0][1] == NULL){
  if ($notOne != 1){echo "<b><i><h3><font color=red>One of the Tags is missing!</font></h3></i></b>";}
  echo "<b><xmp>";
  if ($findIdxStopStart[0][0] != NULL){
    echo "Found: ".$findIdxStopStart[0][0];
  }
  echo "\n";
  if ($findIdxStopStart[0][1] != NULL){
    echo "Found: ".$findIdxStopStart[0][1];
  }
  echo "</xmp></b>";


}

if ($findIdxStopStart[0][0] != NULL && $findIdxStopStart[0][1] != NULL){
echo "<b>Here is what we found. Compare with The Proper tags to ensure they are the same.</b><p>&nbsp;<p>";

similar_text($findIdxStopStart[0][0], '<div id="idxStart" style="display: none;"></div>', $startPercent);
similar_text($findIdxStopStart[0][1], '<div id="idxStop" style="display: none;"></div>', $stopPercent);

if ($startPercent == 100){echo "<u>Start Tag Perfect Match</u>";}else{echo "<u>Start Tag Does Not Match: ".round($startPercent)."%</u>";}
echo "<xmp>".$findIdxStopStart[0][0]."</xmp>";

if ($stopPercent == 100){echo "<u>Stop Tag Perfect Match</u>";}else{echo "<u>Stop Tag Does Not Match: ".round($stopPercent)."%</u>";}
echo "<xmp>".$findIdxStopStart[0][1]."</xmp>";

}






if ($findIdxStopStart[0][2] != NULL){
echo "<b><i><h3><font color=red>Warning: We found extra IDX start/stop tags. Please remove the extra tags.</font></h3></i></b><p>";
}



}

include ("crawlFooter.html");
?>
