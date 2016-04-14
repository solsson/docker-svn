<?php

require dirname(dirname(dirname(__FILE__))).'/conf/Report.class.php';
require dirname(dirname(__FILE__)).'/uncompress.php';

$report = new Report('Install SyntaxHighlighter');

// todo check for file inside sh/ so that it is not detected as installed if unzip has failed
if (file_exists('sh/')) {
	$report->ok("SyntaxHighlighter is already installed, done.");
	$report->display();
	exit;
}


// syntax highlighting framework

$repos_package = "SyntaxHighlighter";
$home = "http://code.google.com/p/syntaxhighlighter/";

$version = "2.1.382";
$archive = "http://alexgorbatchev.com/SyntaxHighlighter/download/download.php?sh_$version";
// from v3 this lib is MIT+GPL but the 2.1 distributable contains the LGPLv3.txt
$license = "http://www.gnu.org/licenses/lgpl-3.0.txt";

$basedir = dirname(__FILE__);
$dir = strtr($basedir, "\\", '/');
$tmp = $dir.'/downloaded.tmp';
$extracted_folder = "$dir/sh/";


if(download($archive, $tmp)) {
	$report->info("Download complete.");
} else {
	$report->fatal("Download failed.");
}

// SyntaxHighlighter 2 is not packed in a parent folder
//decompressZip($tmp, $dir);
mkdir($extracted_folder);
decompressZip($tmp, $extracted_folder);

System::deleteFile($tmp);

$report->ok("Done.");
$report->display();
?>

