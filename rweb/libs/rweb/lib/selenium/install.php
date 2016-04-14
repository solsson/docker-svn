<?php

require dirname(dirname(dirname(__FILE__))).'/conf/Report.class.php';
require dirname(dirname(__FILE__)).'/uncompress.php';

$repos_package = "selenium";
$home = "selenium.openqa.org";

$report = new Report("Install $repos_package");
$report->info("$repos_package is installing...");

$version = "1.0.1";
$archive = "http://release.seleniumhq.org/selenium-core/$version/selenium-core-$version.zip";

$basedir = dirname(__FILE__);
$dir = strtr($basedir, "\\", '/');
$tmp = $dir.'/downloaded.tmp';
$extracted_folder = "$dir/selenium-core-$version/";
$core_folder = "$dir/core/";

if (file_exists($core_folder)) {
	$report->ok("$repos_package is already installed, done.");
	$report->display();
	exit;
}

if(download($archive, $tmp)) {
	$report->info("Download complete.");
} else {
	$report->fatal("Download failed.");
}

//decompressZip($tmp, $dir);
// 0.8.3 is not packed in a folder
mkdir($extracted_folder);
decompressZip($tmp, $extracted_folder);

rename($extracted_folder."core/", $core_folder);

System::deleteFile($tmp);
System::deleteFolder($extracted_folder);

$report->ok("Done.");
$report->display();
?>
