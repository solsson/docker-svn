<?php
// unittest prepare scripts
// should NOT require repos.properties.php, because we want to be able to mock those functions
// if the scripts under test use repos.properties.php, they should be imported before this script in the unit test files

// see http://simpletest.sourceforge.net/ for documentation

// This setup was designed for simpletest 1.0.1beta1 to get a minimum of boilerplate code in test cases.
// Newer feature such as autorun.php have not been evaluated.

error_reporting(E_ALL);
// simpletest uses deprecated syntax
if (defined('E_DEPRECATED')) {
	error_reporting(error_reporting() ^ E_DEPRECATED);
}

// allow other scripts to detect that they are running from a test case
define('REPOSTEST',$_SERVER['SCRIPT_FILENAME']);

// ----- string helper functions that should have been in php, now copied from repos.properties -----
if (!function_exists('strBegins')) { function strBegins($str, $sub) { return (substr($str, 0, strlen($sub)) === $sub); } }
if (!function_exists('strEnds')) { function strEnds($str, $sub) { return (substr($str, strlen($str) - strlen($sub)) === $sub); } }
if (!function_exists('strContains')) { function strContains($str, $sub) { return (strpos($str, $sub) !== false); } }
if (!function_exists('strAfter')) { function strAfter($str, $sub) { return (substr($str, strpos($str, $sub) + strlen($sub))); } }

require_once(dirname(__FILE__).'/simpletest/unit_tester.php');
//require_once(dirname(__FILE__).'/simpletest/reporter.php');
// using our own custom HtmlReporter, TestReporter and SelectiveReporter
require_once(dirname(__FILE__).'/reporter.php');

// don't force test cases to cooperate with repos.properties.php
if (!function_exists('reportErrorText')) {
	function reportErrorText($n, $message, $trace) {
		if ($n == 2048) { // E_STRICT not defined in php 4
			// ignore
		} else if ($n == 8192) { // E_DEPRECATED
			// ignore
		} else {
			echo("Unexpected error (type $n): $message\n<pre>\n$trace</pre>");
			exit;
		}
	}
}

/**
 * Temporarily sets the internal username and password,
 * for authentication towards svn and services.
 *
 * @param String $username the client's username
 * @param String $password the client's password
 */
function setTestUser($username='test', $password='test') {
	//$_SERVER['PHP_AUTH_USER'] = $username;
	//$_SERVER['PHP_AUTH_PW'] = $password;
	getReposUser($username);
	_getReposPass($password);
}

/**
 * Clears the internal credentials for outgoing requests.
 */
function setTestUserNotLoggedIn() {
	//unset($_SERVER['PHP_AUTH_USER']);
	//unset($_SERVER['PHP_AUTH_PW']);
	getReposUser(false);
	_getReposPass(false);
}

$reporter = new ReposHtmlReporter();
function testrun(&$testcase) {
	global $reporter;
	$testcase->run($reporter);
}

// custom error handling for unit tests, overres the error handling in repos.properties.php

function reportErrorInTest($n, $message, $file, $line) {
	global $reporter;
	$level = $n;
	$trace = _getStackTrace();
	if (defined('E_STRICT') && $n == E_STRICT) return; // simpletest produces syntactic error messages
	if (!isset($reporter->report)) reportErrorText($level, $message, $trace); // report not started yet
	if ($level == E_USER_ERROR) {
		$reporter->paintError("Error:   ".$message);
	} else if ($level == E_USER_WARNING) {
		$reporter->paintError("Warning: ".$message);
	} else if ($level == E_USER_NOTICE) {
		$reporter->paintError("Notice:  ".$message);
	} else {
		if ($level==2048) { // E_STRICT not defined in PHP 4
			$t = explode("\n",$trace);
			if (strContains($t[1], 'simpletest')) return; // simpletest has many code check errors
		  	$reporter->paintMessage("PHP code check warning: ".$message);  		
		  	$reporter->paintFormattedMessage("$message\n$trace");
		} else {
		   	$reporter->paintMessage("Error of unknown type: ".$message);
		   	$reporter->paintFormattedMessage("$message\n$trace");
		}
	}
	// TODO need to force a "return;" from the function that did the 
	// trigger_error call to the test case in the call stack
}

if (!function_exists('_getStackTrace')) {
	function _getStackTrace() {
		$o = '';
		$stack=debug_backtrace();
		$o .= "file\tline\tfunction\n";
		for($i=1; $i<count($stack); $i++) { // skip this method call
			if (isset($stack[$i]["file"]) && $stack[$i]["line"]) {
		    	$o .= "{$stack[$i]["file"]}\t{$stack[$i]["line"]}\t{$stack[$i]["function"]}\n";
			}
		}
		return $o;
	}
}

set_error_handler('reportErrorInTest');

?>
