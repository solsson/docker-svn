<?php
/**
 * Incremental admin reports (c) 2006-2007 Staffan Olsson www.repos.se
 *
 * A common interface for scripts that do tests, configuration or sysadmin tasks.
 * Does not do output buffering, because for slow operations we want to report progress.
 *
 * PHP scripts should require this class or the Report class, depending on the type of output they produce.
 *
 * @package conf
 * @see Presentation, for user contents.
 */
// TODO count fatal() as exception
// TODO convert to HTML-entities where needed (without ending up in some kind of wiki syntax). see test reporter.php

// do not force the use of shared functions //require_once(dirname(__FILE__).'/repos.properties.php');
if (!defined('WEBSERVICE_KEY')) define('WEBSERVICE_KEY', 'serv');

// same function as in Presentation
if (!function_exists('setupResponse')) {
	function setupResponse() {
		if (isOffline()) {
			// can not set headers in CLI mode
		} elseif (isTextmode()) {
			header('Content-Type: text/plain');
		}
		// online headers are set in meta tag
	}
}

// reports may be long running
set_time_limit(60*5);

// time zone as in repos.properties.php
date_default_timezone_set('UTC');

$reportDate = date("Y-m-d\TH:i:sP");

$reportStartTime = time();

/**
 * @return true if this is PHP running from a command line instead of a web server
 */
function isOffline() {
	// maybe there is some clever CLI detection, but this works too
	return !isset($_SERVER['REMOTE_ADDR']);
}

/**
 * @return true if output should be plain text
 */
function isTextmode() {
	return isOffline() || (isset($_REQUEST[WEBSERVICE_KEY]) && $_REQUEST[WEBSERVICE_KEY] != 'html');
}

/**
 * The Report can quite easily print everything as TAP comments and the summary as ok/not ok
 * @flag expected TAP (Test Anything Protocol) output
 */
function isTAP() {
	// Currently all text output implies TAP format
	return isTextmode() && true;
}

/**
 * @return newline character for this OS, or always \n if output is web
 */
function getNewline() {
	if (isOffline() && substr(PHP_OS, 0, 3)=='WIN') return "\n\r";
	else return "\n";
}

function getReportTime() {
	global $reportDate;
	return $reportDate;
}

/**
 * Represents the output of the operation, either for web or as text.
 * All output should go through this class.
 * Passing array as message means print as block (with <pre> tag in html).
 * Output should end with a call to the display() method.
 */
class Report {

	var $offline;
	// counters
	var $nd = 0; //debug
	var $ni = 0; //info
	var $nw = 0; //warn
	var $ne = 0; //error
	var $no = 0; //ok
	var $nf = 0; //fail
	var $nt = 0; //test cases
	var $test = false; // true inside a test case

	/**
	 * Report must be able to run without repos.properties.php
	 * so it should be able to resolve webapp url on its own.
	 * @static
	 */
	function getWebapp() {
		//if (function_exists('getWebapp')) return getWebapp();
		if (isset($_SERVER['REPOS_WEBAPP'])) return $_SERVER['REPOS_WEBAPP'];
		return Report::getWebappDefault();
	}

	/**
	 * @return String /repos-web/
	 * @static
	 */
	function getWebappDefault() {
		return '/repos-web/';
	}

	/**
	 * Creates a new report, which is a new page.
	 * @param boolean $plaintext Overrides the default detection of offline/online output: true to get plaintext output, false to get html.
	 */
	function Report($title='Repos system report', $category='', $plaintext=null) {
		if (is_null($plaintext)) {
			$this->offline = isTextmode();
		} else {
			$this->offline = $plaintext;
		}
		setupResponse();
		$this->_pageStart($title);
	}

	/**
	 * Completes the report and saves it as a file at the default reports location.
	 */
	function publish() {
		trigger_error("publish() not implemented", E_USER_ERROR);
		$this->display();
	}

	/**
	 * Ends output and writes it to output stream.
	 */
	function display() {
		$this->_summary();
		if ($this->nd > 0) $this->_toggleDebug();
		if ($this->hasErrors()) $this->_toggleError();
		$this->_pageEnd();
	}

	function teststart($name) {
		$this->_linestart('row test n'.$this->nt%4);
		$this->test = 1 + $this->ne + $this->nf;
		$this->nt++;

		$this->_linestart('testname');
		$this->_output($name);
		$this->_lineend();
		$this->_linestart('testoutput');
	}

	function testend() {
		$this->_lineend();
		if ($this->ne + $this->nf >= $this->test) {
			$this->_linestart('testresult failed');
			$this->_output("failed");
		} else {
			$this->_linestart('testresult passed');
			$this->_output("passed");
		}
		$this->_lineend();
		$this->test = false;
		$this->_lineend();
	}

	// I'm not sure right now what the difference between this and outputline is supposed to be
	function _testoutput($class, $message) {
		$s='i';
		if ($class=='passed') $s='=';
		if ($class=='failed') $s='X';
		if ($class=='debug') $s='.';
		if ($class=='warning') $s='?';
		if ($class=='error') $s='!';
		if ($this->offline) {
			$this->_output(isTAP() ? "# $s $message" : " $s $message");
			$this->_lineend();
		} else if ($this->test) {
			$message = str_replace('"','&quot;', $message);
			$this->_output("<acronym class=\"$class\" title=\"");
			$this->_output($message);
			$this->_output("\">$s</acronym>");
			$this->_print(getNewline());//multiple results in same div//$this->_lineend();
		} else {
			$this->_outputline($class, " $s $message");
		}
	}

	/**
	 * Call when a test or validation has completed successfuly
	 * (opposite to fail)
	 */
	function ok($message='') {
		$this->no++;
		$this->_testoutput('passed', $message);
	}

	/**
	 * Call when a check has failed.
	 * Not same as error($message), which is called for unexpected conditions.
	 */
	function fail($message) {
		$this->nf++;
		$this->_testoutput('failed', $message);
	}

	/**
	 * Debug lines are hidden by default
	 */
	function debug($message) {
		$this->nd++;
		$this->_outputline('debug', $message);
	}

	/**
	 * Prints a normal paragraph
	 * @param String $message line contents, String array to make a block
	 */
	function info($message) {
		$this->ni++;
		$this->_outputline(null, $message);
	}

	/**
	 * Prints a warning, calls for administrator's attention but is not considered an error
	 * @param String $message line contents, String array to make a block
	 */
	function warn($message) {
		$this->nw++;
		$this->_outputline('warning', $message);
	}

	/**
	 * Prints an error message.
	 * @param String $message line contents, String array to make a block
	 */
	function error($message) {
		$this->ne++;
		if ($this->test) { $this->_testoutput('error', $message); return; }
		$this->_outputline('error', $message);
	}

	/**
	 * Fatal error causes output to end and script to exit.
	 * It is assumed that fatal errors are handled manually by the administrator.
	 */
	function fatal($message, $code = 1) {
		$this->error( $message );
		$this->display();
		exit($code);
	}

	function hasErrors() {
		return $this->ne + $this->nf > 0;
	}

	// prepare for line contents
	function _linestart($class='normal') {
		if ($this->offline) {
			if (isTAP()) $this->_print("# ");
			if ($class=='ok') $this->_print("== ");
			if ($class=='warning') $this->_print("?? ");
			if ($class=='error') $this->_print("!! ");
		} else {
			$this->_print("<div class=\"$class\">");
		}
	}
	// line complete, does flush()
	function _lineend() {
		if ($this->offline) {
			$this->_print(getNewline());
		} else {
			$this->_print("</div>".getNewline());
		}
		flush();
	}
	// text block start (printed inside a line)
	function _blockstart() {
		if (!$this->offline) $this->_print("<pre>");
		$this->_print(getNewline());
	}
	// text block end (before line end)
	function _blockend() {
		if (!$this->offline) $this->_print("</pre>");
		$this->_print(getNewline());
	}
	// writes a line of output
	function _outputline($class, $message) {
		if ($this->test) { $this->_testoutput($class, $message); return; }
		$this->_linestart($class);
		$this->_output($message);
		$this->_lineend();
	}
	// writes a message to output no HTML here because it is used both online and offline
	function _output($message) {
		// simple and greedy filter in offline mode
		if ($this->offline) {
			$message = preg_replace('/<\w.*\/\w*>/', '...', $message);
		}
		if (is_array($message)) {
			$this->_blockstart();
			$this->_print($this->_formatArray($message));
			$this->_blockend();
		} else {
			$this->_print($message);
		}
	}
	// replacement for echo, to customize output buffering and such things
	function _print($string) {
		echo($string);
	}

	function _formatArray($message) {
		$msg = '';
		$linebreak = getNewline();
		foreach ( $message as $key=>$val ) {
			if ( $val===false )
				$val = 0;
			if ( is_string($key) )
				$msg .= "$key: ";
			$msg .= "$val$linebreak";
		}
		// remove last linebreak
		$last = strlen($msg)-strlen($linebreak);
		if ( $last>=0 )
			$msg = substr( $msg, 0, $last);
		return $msg;
	}

	function _pageStart($title) {
		$webapp = Report::getWebapp();
		if (!$this->offline) {
		$this->_print('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"');
		$this->_print(' "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">'."\n");
		$this->_print('<html xmlns="http://www.w3.org/1999/xhtml">'."\n");
		$this->_print('<head>');
		$this->_print('<meta http-equiv="Content-Type" content="text/html; charset=utf-8"></meta>'."\n");
		$this->_print('<title>Repos administration: ' . $title . '</title>'."\n");
		if (function_exists('getService')) {
			$this->_print('<meta name="repos-service" content="' . getService() . '" />');
		}
		$this->_print('<link href="'.$webapp.'style/global.css" rel="stylesheet" type="text/css"></link>'."\n");
		$this->_print('<link href="'.$webapp.'style/docs.css" rel="stylesheet" type="text/css"></link>'."\n");
		$this->_print('<script src="'.$webapp.'scripts/head.js" type="text/javascript"></script>');
		$this->_print("</head>\n");
		$this->_print("<body>\n");

		$this->_print('<div id="commandbar">'."\n");
		// all pages with reports are somewhere in the repos hierarchy
		$this->_print('<a id="parent" href="../">up</a>'."\n");
		// webapp home
		$this->_print('<a id="reposroot" href="'.$webapp.'">repos&nbsp;web</a>'."\n");
		// hardcoded paths to repos-admin and repos-backup, without checking if they are installed
		$this->_print('<a id="reposadmin" href="/repos-admin/">admin</a>'."\n");
		$this->_print('<a id="reposconf" href="/repos-backup/">backup</a>'."\n");
		$this->_print('</div>');

		$this->_print("<h1>$title</h1>\n");
		$this->_print('<p><span class="datetime">'.getReportTime().'</span></p>');
		} else {
			$this->_linestart();
			$this->_output("---- $title ----");
			$this->_lineend();
		}
	}

	function _pageEnd($code = 0) {
		$this->_pageEndScript();
		if (!$this->offline) $this->_print("</body></html>\n\n");
		exit( $code );
	}

	function _summary() {
		global $reportStartTime;
		$time = time() - $reportStartTime;
		$class = $this->hasErrors() ? "failed" : "passed";
		if ($this->offline) {
			$this->info("-----------------------------");
			if (isTAP()) $this->_print($this->hasErrors() ? "not ok " : "ok ");
			$this->info("$this->no passed, $this->nf failed, $this->ne exceptions in $time seconds");
		} else {
			$this->_output("<div class=\"testsummary $class\">");
			$this->_output(" <strong>" . $this->no . "</strong> passed,");
			$this->_output(" <strong>" . $this->nf . "</strong> failed,");
			$this->_output(" <strong>" . $this->ne . "</strong> exceptions");
			$this->_output(" in $time seconds.");
			$this->_output("</div>\n");
		}
	}

	function _pageEndScript() {
		if ($this->offline) return;
		?>
		<script type="text/javascript">
			/* testwalk notation for marking test pages */
			if ($('.testsummary').filter('.passed').size()>0) {
				$('body').addClass('passed');
			} else {
				$('body').addClass('failed');
			}
		</script>
		<?php
	}

	function _toggleDebug() {
		if ($this->offline) return;
		?>
		<script type="text/javascript">$('.debug').hide();</script>
		<p><a href="#" onclick="$('.debug').show()" accesskey="d">show <?php echo($this->nd); ?> <u>d</u>ebug messages</a></p>
		<?php
	}

	function _toggleError() {
		if ($this->offline) return;
		?>
		<p><a href="#" onclick="showErrors()" accesskey="e">show <?php echo($this->ne + $this->nf); ?> <u>e</u>rror messages</a></p>
		<script type="text/javascript">
		function showErrors() {
			var i = 0;
			$('acronym.failed, acronym.error').each( function() {
				i++;
				this.innerHTML=''+i;
				var error = this.getAttribute('title');
				var span = document.createElement('span');
				$('<small/>').text('['+i+'] '+error).appendTo(span); // TODO real jQuery for this function
				span.style.display = 'block';
				this.parentNode.parentNode.appendChild(span);
			});
		}
		</script>
		<?php
	}

}
?>
