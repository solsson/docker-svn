<?php

require dirname(dirname(dirname(__FILE__))).'/conf/Report.class.php';
require dirname(dirname(__FILE__)).'/uncompress.php';

$report = new Report('Install Simpletest');
$report->info("Simpletest is installing...");

$repos_package = "simpletest";
$home = "simpletest.sourceforge.net";

$version = "1.0.1";
$archive = "http://downloads.sourceforge.net/project/simpletest/simpletest/simpletest_$version/simpletest_$version.tar.gz";

$basedir = dirname(__FILE__);
//$dir_backslash = rtrim($basedir, DIRECTORY_SEPARATOR);
//$dir = str_replace('\\', '/', $dir_backslash);
$dir = strtr($basedir, "\\", '/');
$tmp = $dir.'/downloaded.tmp';
$extracted_folder = "$dir/$repos_package/";
$tarfile = "$dir/$repos_package.tar";


if (file_exists($extracted_folder)) {
	$report->ok("$repos_package is already installed, done.");
	$report->display();
	exit;
}

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
if(uncompressTAR( $tarfile, null, null )) {
	$report->ok("Archive extracted.");
}

// delete the docs and test folder
System::deleteFile($tmp);
System::deleteFile($tarfile);
System::deleteFolder($dir.'/simpletest/docs/');
System::deleteFolder($dir.'/simpletest/test/');

// As long as we want to be compatible with PHP 4, exceptions are syntax errors. remove the code from simpletest.
$exceptionsfile = $dir.'/simpletest/exceptions.php';
if (!file_exists($exceptionsfile)) $report->fatal("Could not locate $exceptionsfile, download must have failed.");
if (substr(phpversion(),0,1)=='4') {
	$fh = fopen($exceptionsfile, 'w');
	if (!$fh) $report->fatal("Could not write to $exceptionsfile. Simpletest installation not complete.");
	fwrite($fh, "<?php /* removed by repos because it was not PHP4 compatible */ ?>");
	fclose($fh);
}
$report->ok("Installation successful, done.");
$report->display();
?>
