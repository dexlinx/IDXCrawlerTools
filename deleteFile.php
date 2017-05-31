<?php

$fileName = $_GET["file"];
$filePath = '/home/idxcrawler/public_html/tmp/'.$fileName;

if (file_exists($filePath)) {
    unlink($filePath);
} else {
    echo "The file <b><font color=red> $filePath </font></b>does not exist";
}




?>
