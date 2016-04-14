<?php

require dirname(dirname(dirname(__FILE__))).'/conf/Report.class.php';
require dirname(dirname(__FILE__)).'/uncompress.php';

$report = new Report('Install Smarty');
$report->info("Smarty is installing...");

if (file_exists('libs/')) {
	$report->ok("Smarty libs already installed, done.");
	$report->display();
	exit;
}

define('CACHE_DIR', dirname(__FILE__).'/cache/');
if (!file_exists(CACHE_DIR)) {
	mkdir( CACHE_DIR );
	mkdir( CACHE_DIR.'templates/' );
	mkdir( CACHE_DIR.'templates_c/' );
	mkdir( CACHE_DIR.'configs/' );
	mkdir( CACHE_DIR.'cache/' );
	$report->ok("Created empty cache folder ".CACHE_DIR.". Should be writable by webserver.");
}

$home = "http://www.smarty.net";
$version = "2.6.28";
$archive = "$home/files/Smarty-$version.tar.gz";
$repos_package = "Smarty";

$basedir = dirname(__FILE__);
$dir = strtr($basedir, "\\", '/');
$tmp = $dir.'/downloaded.tmp';
$tarfile = "$dir/$repos_package.tar";

if(download($archive, $tmp)) {
	$report->info("Download complete.");
} else {
	$report->fatal("Download failed.");
}

/*
	extract GZ archive
	arg 1 is an absolute path to a gz archive
	arg 2 is the extracted file's name
	arg 3 is optional. default value is 1 000 000 000. it has to be larger than the extracted file
*/
$report->info("Extract archive...");
if(!uncompressGZ($tmp, $tarfile, 2000000 )) {
	$report->fatal("Not allowed to write to destination $tarfile");
}

/*
	extract TAR archive
	arg 1 is an absolute path to a gz archive
	arg 2 is the extracted file's name. it is optional. default value is the same path as the tar file
	arg 3 is optional. it should be used only if a special directory from the tar file is needed.
*/
if(uncompressTAR( $tarfile, null, "libs" )) {
	$report->ok("Archive extracted.");
}
System::deleteFile($tmp);
System::deleteFile($tarfile);

// apply fix to allow resources cached relative to webapp
$smartyClass = $dir.'/libs/Smarty.class.php';
if (!file_exists($smartyClass)) $report->fatal("Install error. '$smartyClass' not found.");
$fixFrom = '/function\s+_get_compile_path\(\$resource_name\)[^{]*\{/ms';
$fixTo = 'function _get_compile_path($resource_name)
    {
		// --- preserve cached templates when moving web application ---
		if (defined("TEMPLATE_BASE")) $resource_name = substr($resource_name, strlen(TEMPLATE_BASE));
		// ---
	';
// smarty is encoded with single newline
$fixTo = str_replace("\r\n", "\n", $fixTo);

$fh = fopen($smartyClass, 'r');
$contents = fread($fh, 131072);
fclose($fh);
$contentsFixed = preg_replace($fixFrom, $fixTo, $contents);
if ($contents == $contentsFixed) $report->error('Coult not apply fix to Smarty class');
$fh = fopen($smartyClass, 'w');
fwrite($fh, $contentsFixed);
$report->ok('Fix for template caching applied');

$report->ok("Done.");
$report->display();
?>
