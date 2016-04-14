<?php
/**
 * System utitlity functions (c) 2006-2007 Staffan Olsson www.repos.se
 * Controls access to the filesystem and server environment.
 * Unlike repos.properties.php, these functions do not depend on configuration.
 *
 * Some standard PHP functions should not be used in repos, except through this class.
 * - tempnam
 * - unlink
 * - exec/passthru (use Command class instead)
 *
 * @package conf
 */

// ----- string helper functions that should have been in php -----
function strBegins($str, $sub) { return (substr($str, 0, strlen($sub)) === $sub); }
function strEnds($str, $sub) { return (substr($str, strlen($str) - strlen($sub)) === $sub); }
function strContains($str, $sub) { return (strpos($str, $sub) !== false); }
function strAfter($str, $sub) { return (substr($str, strpos($str, $sub) + strlen($sub))); }


// ----- global functions, logic for the repos naming conventions for path -----

/**
 * A path is a String of any length, not containing '\'.
 * Windows paths must be normalized using toPath.
 * @package conf
 */
function isPath($path) {
	if (!is_string($path)) {
		trigger_error("Path $path is not a string.", E_USER_ERROR);
		return false;
	}
	if (strContains($path, '\\')) {
		trigger_error("Path $path contains backslash. Use toPath(path) to convert to generic path.", E_USER_ERROR);
		return false;
	}
	if (strContains(str_replace('://','',$path), '//')) {
		trigger_error("Path $path contains double slashes.", E_USER_ERROR);
		return false;
	}
	return true;
}

/**
 * Converts filesystem path to path that works on all OSes, with same encoding as command line.
 * Windows paths are converted from backslashes to forward slashes, and from UTF-8 to ISO-8859-1 (see toShellEncoding).
 * If a path does not use the OS encoding, functions like file_exists will only work with ASCII file names.
 * @param String $path path that might contain backslashes
 * @return String the same path, but with forward slashes
 * @package conf
 */
function toPath($path) {
	return System::toShellEncoding(strtr($path, '\\', '/'));
}

/**
 * Absolute paths start with '/' or 'protocol://', on Windows only, 'X:/'.
 * @param String $path the file system path or URL to check
 * @return boolean true if path is absolute, false if not
 * @package conf
 */
function isAbsolute($path) {
	if (!isPath($path)) trigger_error("'$path' is not a valid path", E_USER_ERROR);
	if (strBegins($path, '/')) return true;
	if (System::isWindows() && preg_match('/^[a-zA-Z]:\//', $path)) return true;
	return ereg('^[a-z]+://', $path)!=false;
}

/**
 * Relative paths are those that are not absolute, including empty strings.
 * @param String $path the file system path or URL to check
 * @return boolean true if path is relative, false if not
 * @package conf
 */
function isRelative($path) {
	return !isAbsolute($path);
}

/**
 * Files are relative or absolute paths that do not end with '/'.
 * The actual filename can be retreived using getParent($path).
 * @param String $path the file system path or URL to check
 * @return boolean true if path is a file, false if not
 * @package conf
 */
function isFile($path) {
	if (!isPath($path)) trigger_error("'$path' is not a valid path");
	return !strEnds($path, '/');
}

/**
 * Folders are relative or absolute paths that _do_ end with '/'.
 * To check if a URL with no tailing slash is a folder, use HTTP.
 * @param String $path the file system path or URL to check
 * @return boolean true if path is a folder, false if not
 * @package conf
 */
function isFolder($path) {
	return !isFile($path);
}

/**
 * @param String $path the file system path or URL to check
 * @return The parent folder if isFolder($path), the folder if isFile($path), false if there is no parent.
 * 	With trailing slash.
 * @package conf
 */
function getParent($path) {
	if (strlen($path)<1) return false;
	$c = substr_count($path, '/');
	if ($c < 1) return false;
	if ($c == 1 && strBegins($path, '/')) return '/';
	if ($c < 4 && strContains($path, '://') && !($c==3 && !strEnds($path, '/'))) return false;
	$f = substr($path, 0, strrpos(rtrim($path,'/'), '/'));
	if (strlen($f)==0 && isRelative($path)) return $f;
	return $f.'/';
}

/**
 * Returns the file or folder name from a path.
 * Use the PHP 'basename' function only for paths that are always ASCII
 * and that might contain backslashes.
 * @param String $path valid path or URL, use toPath if it contains backslashes.
 * @return String the filename if path is a file, the folder name if it is a folder.
 */
function getPathName($path) {
	if (!isPath($path)) trigger_error("'$path' is not a valid path");
	$p = rtrim($path, '/');
	if (!strContains($p,'/')) return $p;
	return substr($p, strrpos($p, '/')+1);
}

/**
 * Non configurable global functions.
 *
 * It is not allowed for code outside this file to do
 * any of: unlink(x), rmdir(x), touch(x), fopen(x, 'a' or 'w')
 *
 * @static
 * @package conf
 */
class System {

	/**
	 * @return true if the web server is running windows
	 */
	function isWindows() {
		return ( substr(PHP_OS, 0, 3) == 'WIN' );
	}

	/**
	 * @return newline character for this OS, the one used by subversion with "svn:eol-style native".
	 */
	function getNewline() {
		if (System::isWindows()) return "\r\n";
		else return "\n";
	}

	/**
	 * Manages the common temp dir for repos-php. Temp is organized in subfolders per operation.
	 * This method returns an existing temp folder; to get a new empty folder use {@link getTempFolder}.
	 * The method uses the internal System::_getSystemTemp() to get the starting point
	 * @param String $subfolder optional name of a subfolder in the application temp folder, no slashes
	 * @return absolute path to temp, or the subfolder of temp, with trailing slash
	 */
	function getApplicationTemp($subfolder=null) {
		// Get temporary directory
		$systemp = System::_getSystemTemp();
		if (is_writable($systemp) == false) die ('Error. Can not write to temp foloder "'.$systemp.'"');
		// Create a repos subfolder, allow multiple repos installations on the same server
		$appname = 'r'.trim(base64_encode(dirname(dirname(__FILE__))),'=');
		$tmpdir = $systemp . $appname;
		if (!file_exists($tmpdir)) {
			mkdir($tmpdir);
		}
		if ($subfolder) {
			$tmpdir .= '/' . $subfolder;
			if (!file_exists($tmpdir)) {
				mkdir($tmpdir);
			}
		}
		return toPath($tmpdir) . '/';
	}

	/**
	 * Return a new empty temporary file on the application temp area.
	 * @param String $subfolder (optional) category (like 'upload'), subfolder to temp where the file will be created.
	 * @param String $suffix (optional) end of file name, like .txt
	 */
	function getTempFile($subfolder=null, $suffix='') {
		$f = System::getTempPath($subfolder, $suffix);
		if (!touch($f)) trigger_error('Failed to create temp file '.$f, E_USER_ERROR);
		return $f;
	}

	/**
	 * @return a non-existing absolute path in the temp area, no trailing slash
	 * @param $suffix may not contain /
	 */
	function getTempPath($subfolder=null, $suffix='') {
		return System::getApplicationTemp($subfolder).uniqid().$suffix;
	}

	/**
	 * Return a new, empty, temporary folder in the application temp area.
	 * @param String $subfolder optional category (like 'upload')
	 * @see getApplicationTemp
	 */
	function getTempFolder($subfolder=null) {
		$f = System::getTempPath($subfolder).'/';
		// Create the temporary directory and returns its name.
		if (mkdir($f)) return $f;
		trigger_error('Failed to create temp folder '.$f, E_USER_ERROR);
	}

	// ------ functions to keep scripts portable -----

	/**
	 * Converts a string from internal encoding to the encoding used for php file functions that don't support multibyte
	 * For Command arguments, see the encoding in that class.
	 * @param String $string the value with internal encoding (same as no encoding)
	 * @return String the same value encoded as the OS expects it in php filesystem functions like unlink and file_exists
	 */
	function toShellEncoding($string) {
		$to = false; // default: no special encoding
		if (System::isWindows()) $to = 'ISO-8859-1'; // assume something
		if (isset($_SERVER['REPOS_SHELL_ENCODING'])) $to = $_SERVER['REPOS_SHELL_ENCODING'];
		if ($to) $string = mb_convert_encoding($string, $to, 'UTF-8');
		return $string;
	}

	/**
	 * Get the execute path of command line operations supported by repos.
	 * @param String $commandName Command name, i.e. 'svnadmin'.
	 * @return String Full command with path, false if the command shouldn't be needed in current OS.
	 * Error message starting with 'Error:' if command name is not supported.
	 * Get the execute path of the subversion command line tools used for repository administration
	 * @param Command name, i.e. 'svnadmin'.
	 * @return Command line command, false if the command shouldn't be needed in current OS. Error message starting with 'Error:' if command name is not supported.
	 */
	function getCommand($command) {
		$key = 'REPOS_EXECUTABLE_'.strtoupper($command);
		// customized
		if (isset($_SERVER[$key])) {
			return $_SERVER[$key];
		}
		// defaults
		if ($c = System::_getSpecialCommand($command)) {
			return $c;
		}
		$w = System::isWindows();
		switch($command) {
			case 'svn':
				return ( 'svn' );
			case 'svnlook':
				return ( getParent(System::getCommand('svn')) . 'svnlook' );
			case 'svnadmin':
				return ( getParent(System::getCommand('svn')) . 'svnadmin' );
			case 'gzip':
				return ( 'gzip' );
			case 'gunzip':
				return ( 'gunzip' );
			case 'whoami':
				return 'whoami';
			case 'env':
				return ( $w ? 'set' : 'env' );
			case 'du':
				return ( $w ? false : 'du' );
			case 'curl':
				return ( 'curl' );
			case 'wget':
				return ( 'wget' );
		}
		return false;
	}

	// ----- file system helper functions ------

	/**
	 * Deletes a folder and all contents. No other scripts in repos may call the 'unlink' method.
	 * Removes the folder recursively if it is in one of the allowed locations,
	 * such as the temp dir and the repos folder.
	 * Note that the path should be encoded with the local shell encoding, see toPath.
	 * @param String $folder absolute path, with tailing slash like all folders.
	 *  Valid path is either inside the repos folder or in the repos temp location
	 */
	function deleteFolder($folder) {
		System::_authorizeFilesystemModify($folder);
		if (!isFolder($folder)) {
			trigger_error("Path \"$folder\" is not a folder.", E_USER_ERROR); return false;
		}

		if (!file_exists($folder) || !is_dir($folder)) {
			trigger_error("Path \"$folder\" does not exist.", E_USER_ERROR); return false;
		}
		if (!is_readable($folder)) {
			trigger_error("Path \"$folder\" is not readable.", E_USER_ERROR); return false;
		}
		if (!is_writable($folder) && !System::_chmodWritable($folder)) {
			trigger_error("Path \"$folder\" is not writable.", E_USER_ERROR); return false;
		}
		else {
			$handle = opendir($folder);
			while (false !== ($item = readdir($handle))) {
				if ($item != '.' && $item != '..') {
					$path = $folder.$item;
					if(is_dir($path)) {
						System::deleteFolder($path.'/');
					} else {
						System::deleteFile($path);
					}
				}
			}
			closedir($handle);
			if(!rmdir($folder)) {
				trigger_error("Could not remove folder \"$folder\".", E_USER_ERROR); return false;
			}
			return true;
		}
	}

	/**
	 * replaces touch().
	 * @deprecated use System::createFile
	 */
	function createFile($absolutePath) {
		System::_authorizeFilesystemModify($absolutePath);
		if (!isFile($absolutePath)) {
			trigger_error("Path \" $absolutePath\" is not a valid file name.", E_USER_ERROR); return false;
		}
		return touch($absolutePath);
	}

	/**
	 * replaces mkdir().
	 */
	function createFolder($absolutePath) {
		System::_authorizeFilesystemModify($absolutePath);
		if (!isFolder($absolutePath)) {
			trigger_error("Path \" $absolutePath\" is not a valid folder name.", E_USER_ERROR); return false;
		}
		if (!is_writable(getParent($absolutePath))) {
			trigger_error("Can not create \"$absolutePath\" because parent is not writable", E_USER_ERROR); return false;
		}
		return mkdir($absolutePath);
	}

	/**
	 * replaces unlink().
	 * @param String $file absolute path to file
	 * @param boolean $makeWritableIfNeeded set to false to disable the is_writable check.
	 * 	Sometimes useful in windows where chomd and is_writable do not always reflect reality.
	 */
	function deleteFile($file, $makeWritableIfNeeded=true) {
		System::_authorizeFilesystemModify($file);
		if (!isFile($file)) {
			trigger_error("Path \" $file\" is not a file.", E_USER_ERROR); return false;
		}
		if (!file_exists($file)) {
			trigger_error("Path \" $file\" does not exist.", E_USER_ERROR); return false;
		}
		if (!is_readable($file)) {
			trigger_error("Path \" $file\" is not readable.", E_USER_ERROR); return false;
		}
		if ($makeWritableIfNeeded && !is_writable($file) && !System::_chmodWritable($file)) {
			trigger_error("Path \" $file\" is not writable.", E_USER_ERROR); return false;
		}
		return unlink($file);
	}

	/**
	 * Instead of createFile() and fopen+fwrite+fclose.
	 */
	function createFileWithContents($absolutePath, $contents, $convertToWindowsNewlineOnWindows=false, $overwrite=false) {
		if (!isFile($absolutePath)) {
			trigger_error("Path $absolutePath is not a file."); return false;
		}
		if (file_exists($absolutePath) && !$overwrite) {
			trigger_error("Path $absolutePath already exists. Delete it first."); return false;
		}
		if ($convertToWindowsNewlineOnWindows) {
			$file = fopen($absolutePath, 'wt');
		} else {
			$file = fopen($absolutePath, 'w');
		}
		$b = fwrite($file, $contents);
		fclose($file);
		return $b;
	}

	/**
	 * Replaces chmod 0777, and is used internally before deleting files or folders.
	 * Only allowes chmodding of folders that are expected to be write protected, like .svn.
	 * @return false if it is not allowed to chmod the path writable, or if 'chmod' function fails
	 */
	function _chmodWritable($absolutePath) {
		if (strContains($absolutePath, '/.svn')) return chmod($absolutePath, 0777);
		if (strBegins($absolutePath, System::_getSystemTemp())) return chmod($absolutePath, 0777);
		return false;
	}

	/**
	 * @todo
	 *
	 * @param unknown_type $absolutePath
	 */
	function chmodWebRuntimeWritable($absolutePath) {
		// what if we are running in CLI mode? we probably are not.
	}

	/**
	 * @todo
	 *
	 * @param unknown_type $absolutePath
	 */
	function chmodWebGroupWritable($absolutePath) {

	}

	/**
	 * It is considered a serious system error if a modify path is invalid according to the internal rules.
	 * Therefore we throw an error and do exit.
	 */
	function _authorizeFilesystemModify($path) {
		if (!isAbsolute($path)) {
			trigger_error("Security error: local write not allowed in \"$path\". It is not absolute.", E_USER_ERROR);
		}
		$tmp = System::_getSystemTemp(); // segfault if inside strBegins
		if (stristr($path, $tmp)==$path) {
			return true;
		}
		if (strContains($path, 'repos')) {
			return true;
		}
		// assume that the web server host is in some server folder
		if (strBegins($path, toPath(dirname(dirname(dirname(dirname(__FILE__))))))) {
			return true;
		}
		trigger_error("Security error: local write not allowed in \"$path\". It is not a temp or repos dir.", E_USER_ERROR);
	}

	/**
	 * Platform independen way of getting the server's temp folder.
	 * @return String absolute path, folder, existing
	 */
	function _getSystemTemp() {
		static $tempfolder = null;
		if (!is_null($tempfolder)) return $tempfolder;
		$type = '';
		if (getenv('TMP')) {
			$type = 'TMP';
			$tempdir = getenv('TMP');
		} elseif (getenv('TMPDIR')) {
			$type = 'TMPDIR';
			$tempdir = getenv('TMPDIR');
		} elseif (getenv('TEMP')) {
			$type = 'TEMP';
			$tempdir = getenv('TEMP');
		} elseif (function_exists('isReposJava') && isReposJava()) {
			$type = 'java';
			$tempdir = '/tmp'; // Shouldn't be too hard to use Quercus env or java env here instead
			if (!is_dir($tempdir)) trigger_error("Unsupported platform for java php, no folder at $tempdir", E_USER_ERROR);
		} else {
			$type = 'tempnam';
			// suggest a directory that does not exist, so that tempnam uses system temp dir
			$doesnotexist = 'dontexist'.rand();
			$tmpfile = tempnam($doesnotexist, 'emptytempfile');
			if (strpos($tmpfile, $doesnotexist)!==false) trigger_error("Could not get system temp, got: ".$tmpfile, E_USER_ERROR);
			if (!file_exists($tmpfile)) trigger_error("Failed to create temp file using $type", E_USER_ERROR);
			$tempdir = dirname($tmpfile);
			unlink($tmpfile);
			if (strlen($tempdir)<4) trigger_error("Attempted to use tempnam() to get system temp dir, but the result is: $tempdir", E_USER_ERROR);
		}

		if (empty($tempdir)) { trigger_error('Can not get the system temp dir', E_USER_ERROR); }

		$tempdir = rtrim(toPath($tempdir),'/').'/';
		if (strlen($tempdir) < 4) { trigger_error('Can not get the system temp dir, "'.$tempdir.'" is too short. Method: '.$type, E_USER_ERROR); }

		$tempfolder = $tempdir;
		return $tempfolder;
	}
}

?>
