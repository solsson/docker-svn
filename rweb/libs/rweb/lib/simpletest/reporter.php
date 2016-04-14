<?php
/**
 * Repos PHP unittest report class for the Simpletest library.
 */

/**#@+
 * This class delegates to Report
 */
require_once(dirname(dirname(dirname(__FILE__))).'/conf/Report.class.php');
/**#@-*/

/**#@+
 * This class extends SimpleReporter
 */
require_once(dirname(__FILE__) . '/simpletest/scorer.php');
/**#@-*/

// see http://www.lastcraft.com/reporter_documentation.php

/**
   *    Sample minimal test displayer. Generates only
   *    failure messages and a pass count.
*	  @package SimpleTest
*	  @subpackage UnitTester
   */
class ReposHtmlReporter extends SimpleReporter {
    var $_character_set;
    
    var $report;

    /**
     *    Does nothing yet. The first output will
     *    be sent on the first test start. For use
     *    by a web browser.
     *    @access public
     */
    function HtmlReporter($character_set = 'ISO-8859-1') {
        $this->SimpleReporter();
        $this->_character_set = $character_set;
    }

    /**
     *    Paints the top of the web page setting the
     *    title to the name of the starting test.
     *    @param string $test_name      Name class of test.
     *    @access public
     */
    function paintHeader($test_name) {
        $this->sendNoCacheHeaders();
        $this->report = new Report($test_name);
    }

    /**
     *    Send the headers necessary to ensure the page is
     *    reloaded on every request. Otherwise you could be
     *    scratching your head over out of date test data.
     *    @access public
     *    @static
     */
    function sendNoCacheHeaders() {
        if (! headers_sent()) {
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
        }
    }

    /**
     *    Paints the end of the test with a summary of
     *    the passes and failures.
     *    @param string $test_name        Name class of test.
     *    @access public
     */
    function paintFooter($test_name) {
        $this->report->display();
    }

    function paintMethodStart($test_name) {
    	parent::paintMethodStart($test_name);
    	$this->report->teststart($this->unCamelCase($test_name));
    }
    
    function paintMethodEnd($test_name) {
    	parent::paintMethodEnd($test_name);
    	$this->report->testend();
    }
    
    function paintPass($message) {
    	parent::paintPass($message);
    	$this->report->ok($message);
    }
    
    /**
     *    Paints the test failure with a breadcrumbs
     *    trail of the nesting test suites below the
     *    top level test.
     *    @param string $message    Failure message displayed in
     *                              the context of the other tests.
     *    @access public
     */
    function paintFail($message) {
        parent::paintFail($message);
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        $p = implode(" -&gt; ", $breadcrumb);
        $p .= " -&gt; " . $this->_htmlEntities($message) . "<br />\n";
        $this->report->fail($p);
    }

    /**
     *    Paints a PHP error or exception.
     *    @param string $message        Message is ignored.
     *    @access public
     *    @abstract
     */
    function paintError($message) {
        parent::paintError($message);
        $breadcrumb = $this->getTestList();
        array_shift($breadcrumb);
        $p = implode(":", $breadcrumb);
        $p .= ": " . $this->_htmlEntities($message) . "";
        $this->report->error($p);
    }
    
    function paintMessage($message) {
    	parent::paintMessage($message);
    	$escaped = array();
    	if (is_array($message)) {
    		foreach ($message as $k => $m) {
    			$escaped[] = '['.$this->_htmlEntities($k).'] '.$this->_htmlEntities($m);
    		}
    	} else {
    		$escaped = $this->_htmlEntities($message);
    	}
    	// debug method accepts html, but in test results we always want it escapes so that all strings are visible
    	$this->report->debug($escaped);
    }
    
    /**
     *    Paints formatted text such as dumped variables.
     *    @param string $message        Text to show.
     *    @access public
     */
    function paintFormattedMessage($message) {
    	parent::paintFormattedMessage($message);
        $this->report->info( array($this->_htmlEntities($message)) );
    }

    /**
     *    Character set adjusted entity conversion.
     *    @param string $message    Plain text or Unicode message.
     *    @return string            Browser readable message.
     *    @access protected
     */
    function _htmlEntities($message) {
        //return htmlentities($message, ENT_COMPAT, $this->_character_set);
        return htmlspecialchars($message);
    }
    
	function unCamelCase($str){
		return $str; // disabled
		$bits = preg_split('/([A-Z])/',$str,false,PREG_SPLIT_DELIM_CAPTURE);
		$a = array();
		array_shift($bits);
		for($i = 0; $i < count($bits); ++$i) {
		    if($i%2) $a[] = $bits[$i - 1].$bits[$i];
		}
		return implode($a, ' ');
	}
}
?>
