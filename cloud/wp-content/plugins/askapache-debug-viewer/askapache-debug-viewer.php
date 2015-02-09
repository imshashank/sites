<?php defined('WP_PLUGIN_DEBUG') && ( WP_PLUGIN_DEBUG ) && error_log("!! ".str_replace(dirname($_SERVER['DOCUMENT_ROOT']),'',__FILE__).' included' ); ?>
<?php
/**
 * Plugin Name: AskApache Debug Viewer
 * Short Name: AA Debug
 * Description: Displays Advanced Debugging Output
 * Author: askapache
 * Contributors: askapache
 * Version: 3.1
 * Updated: 09/10/2014
 * Requires at least: 3.1.0
 * Tested up to: 4.0
 * Tags: debug, debugging, error, errors, issue, help, warning, problem, bug, problems, support, admin, programmer, developer, plugin, development, information, stats, logs, queries, htaccess, password, error, support, askapache, apache, rewrites, server
 * WordPress URI: http://wordpress.org/extend/plugins/askapache-debug-viewer/
 * Author URI: http://www.askapache.com/
 * Donate URI: http://www.askapache.com/donate/
 * Plugin URI: http://www.askapache.com/wordpress/debug-viewer-plugin.html
 * Role: edit_users
 * Capability: askapache_debug_output
 *
 * AskApache Debug Viewer - Displays Advanced Debugging Output
 * Copyright (C) 2011 AskApache.com
 *
 * This program is free software - you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */




// don't load directly - exit if add_action or plugins_url functions do not exist
if ( ! defined( 'ABSPATH' ) || ! function_exists( 'add_action' ) || ! function_exists( 'plugins_url' ) )
	die( 'death by askapache firing squad' );



if ( ! class_exists( 'AA_DEBUG' ) ) :


/******************************************************************************************************************************************************************
 PHP DEFINES
 * __LINE__       The current line number of the file.
 * __FILE__       The full path and filename of the file. If used inside an include, the name of the included file is returned
 * __DIR__        The directory of the file. If used inside an include, the directory of the included file is returned. Does not have a trailing slash unless it is the root directory.
 * __FUNCTION__   The function name. As of PHP 5 this constant returns the function name as it was declared (case-sensitive). In PHP 4 its value is always lowercased.
 * __CLASS__      The class name. As of PHP 5 this constant returns the class name as it was declared (case-sensitive). In PHP 4 its value is always lowercased.
 * __METHOD__     The class method name. The method name is returned as it was declared (case-sensitive).
 * __NAMESPACE__  The name of the current namespace (case-sensitive). This constant is defined in compile-time
 ******************************************************************************************************************************************************************/
! defined( '__DIR__' ) && define('__DIR__', realpath(dirname(__FILE__)));

! defined( 'FILE_BINARY' ) && define('FILE_BINARY', 0);

if ( ! defined( 'PHP_VERSION_ID' ) ) {
	list( $major, $minor, $bug ) = explode( '.', phpversion(), 3 );
	$bug = ( (int) $bug < 10 ) ? '0' . (int) $bug : (int) $bug; // Many distros make up their own versions
	define( 'PHP_VERSION_ID', "{$major}0{$minor}$bug" );
	! defined( 'PHP_MAJOR_VERSION' ) && define( 'PHP_MAJOR_VERSION', $major );
}

if ( ! defined( 'PHP_EOL' ) ) {
	switch ( strtoupper( substr( PHP_OS, 0, 3 ) ) ) {
		case 'WIN':
			define( 'PHP_EOL', "\r\n" );
			break;
		case 'DAR':
			define( 'PHP_EOL', "\r" );
			break;
		default:
			define( 'PHP_EOL', "\n" );
			break;
	}
}

/******************************************************************************************************************************************************************
 WORDPRESS DEFINES
 ******************************************************************************************************************************************************************/
! defined( 'CRLF' ) && define( 'CRLF', chr( 13 ) . chr( 10 ) );


/******************************************************************************************************************************************************************
 FUNCTION DEFINES
 ******************************************************************************************************************************************************************/
/** aadv_error_log($msg='')
 *  since 2.3.2
 */
function aadv_error_log($msg='') {
	return error_log( $msg );
}


/** aadv_DEFINE_function()
 *  since 2.3.3
 */
function aadv_DEFINE_function($f='') {
	$funcs = array( 
		'absint',
		'array_walk_recursive',
		'curl_setopt_array',
		'get_include_path',
		'inet_ntop',
		'inet_pton',
		'ini_get_all',
		'is_a',
		'is_callable',
		'is_scalar',
		'md5_file',
		'mhash',
		'microtime',
		'mkdir',
		'ob_clean',
		'ob_flush',
		'ob_get_clean',
		'ob_get_flush',
		'pathinfo',
		'php_ini_loaded_file',
		'restore_include_path',
		'scandir',
		'set_include_path',
		'sys_get_temp_dir',
		'time_sleep_until',
		'var_export',
		'wp_die'
	);
	
	if ( empty( $f ) ) return $funcs;

	switch( $f ) {
		case 'absint':
			function absint( $maybeint )
			{
				return abs( intval( $maybeint ) );
			}
		break;
	
		case 'array_walk_recursive':
			function array_walk_recursive(&$input, $funcname)
			{
				if (!is_callable($funcname))
				{
					if (is_array($funcname)) $funcname = $funcname[0] . '::' . $funcname[1];
					user_error('array_walk_recursive() Not a valid callback ' . $funcname, E_USER_WARNING);
					return;
				}
	
				if (!is_array($input))
				{
					user_error('array_walk_recursive() The argument should be an array', E_USER_WARNING);
					return;
				}
	
				$args = func_get_args();
	
				foreach ($input as $key => $item)
				{
					$callArgs = $args;
					if (is_array($item)) {
						$thisCall = 'array_walk_recursive';
						$callArgs[1] = $funcname;
					} else {
						$thisCall = $funcname;
						$callArgs[1] = $key;
					}
					$callArgs[0] = &$input[$key];
					call_user_func_array($thisCall, $callArgs);
				}
			};
		break;
	
		case 'curl_setopt_array':
			function curl_setopt_array(&$ch, $curl_options)
			{
				$curl_info=array(
					'url'=>'Last effective URL',
					'content_type'=>'Content-type of downloaded object',
					'http_code'=>'Last received HTTP code',
					'header_size'=>'Total size of all headers received',
					'request_size'=>'Total size of issued requests, currently only for HTTP requests',
					'filetime'=>'Remote time of the retrieved document',
					'ssl_verify_result'=>'Result of SSL certification verification requested',
					'redirect_count'=>'Total number of redirects',
					'total_time'=>'Total transaction time in seconds for last transfer',
					'namelookup_time'=>'Time in seconds until name resolving was complete',
					'connect_time'=>'Time in seconds it took to establish the connection',
					'pretransfer_time'=>'Time in seconds from start until just before file transfer begins',
					'size_upload'=>'Total number of bytes uploaded',
					'size_download'=>'Total number of bytes downloaded',
					'speed_download'=>'Average download speed',
					'speed_upload'=>'Average upload speed',
					'download_content_length'=>'content-length of download, read from Content-Length: field',
					'upload_content_length'=>'Specified size of upload',
					'starttransfer_time'=>'Time in seconds until the first byte is about to be transferred',
					'redirect_time'=>'Time in seconds of all redirection steps before final transaction was started'
					);
	
				foreach ($curl_options as $option => $value) {
					if (!curl_setopt($ch, $option, $value)) return false;
				}
	
				return true;
			}
		break;
	
		case 'get_include_path':
			function get_include_path()
			{
				return ini_get('include_path');
			};
		break;
	
		case 'inet_ntop':
			function inet_ntop($in_addr)
			{
				switch (strlen($in_addr)) {
				case 4:
					list(,$r) = unpack('N', $in_addr);
					return long2ip($r);
				case 16:
					$r = substr(chunk_split(bin2hex($in_addr), 4, ':'), 0, -1);
					$r = preg_replace( array('/(?::?\b0+\b:?){2,}/', '/\b0+([^0])/e'), array('::', '(int)"$1"?"$1":"0$1"'), $r);
					return $r;
				}
	
				return false;
			};
		break;
	
		case 'inet_pton':
			function inet_pton($address)
			{
				$r = ip2long($address);
				if ($r !== false && $r != -1)  return pack('N', $r);
	
				$delim_count = substr_count($address, ':');
				if ($delim_count < 1 || $delim_count > 7)  return false;
	
				$r = explode(':', $address);
				$rcount = count($r);
				if (($doub = array_search('', $r, 1)) !== false) {
					$length = (!$doub || $doub == $rcount - 1 ? 2 : 1);
					array_splice($r, $doub, $length, array_fill(0, 8 + $length - $rcount, 0));
				}
	
				$r = array_map('hexdec', $r);
				array_unshift($r, 'n*');
				$r = call_user_func_array('pack', $r);
	
				return $r;
			};
		break;
	
		case 'ini_get_all':
			function ini_get_all($extension=null)
			{
				// Sanity check
				if ($extension !== null && !is_scalar($extension)) {
					user_error('ini_get_all() expects parameter 1 to be string, ' . gettype($extension) . ' given', E_USER_WARNING);
					return false;
				}
	
				// Get the location of php.ini
				ob_start();
				phpinfo(INFO_GENERAL);
				$info = ob_get_contents();
				ob_clean();
				$info = explode("\n", $info);
				$line = array_values(preg_grep('#php\.ini#', $info));
	
				// Plain vs HTML output
				if (substr($line[0], 0, 4) === '<tr>') {
					list (, $value) = explode('<td class="v">', $line[0], 2);
					$inifile = trim(strip_tags($value));
				} else {
					list (, $value) = explode(' => ', $line[0], 2);
					$inifile = trim($value);
				}
	
				// Check the file actually exists
				if (!file_exists($inifile)) {
					user_error('ini_get_all() Unable to find php.ini', E_USER_WARNING);
					return false;
				}
	
				// Check the file is readable
				if (!is_readable($inifile)) {
					user_error('ini_get_all() Unable to open php.ini', E_USER_WARNING);
					return false;
				}
	
				// Parse the ini
				if ($extension !== null)
				{
					$ini_all = parse_ini_file($inifile, true);
	
					// Lowercase extension keys
					foreach ($ini_all as $key => $value) $ini_arr[strtolower($key)] = $value;
	
					// Check the extension exists
					if (isset($ini_arr[$extension])) $ini = $ini_arr[$extension];
					else {
						user_error("ini_get_all() Unable to find extension '$extension'",E_USER_WARNING);
						return false;
					}
				} else {
					$ini = parse_ini_file($inifile);
				}
	
				// Order
				$ini_lc = array_map('strtolower', array_keys($ini));
				array_multisort($ini_lc, SORT_ASC, SORT_STRING, $ini);
	
				// Format
				$info = array();
				foreach ($ini as $key => $value) $info[$key] = array( 'global_value'=>$value, 'local_value'=>ini_get($key), 'access'=>-1);
				return $info;
			};
		break;
	
		case 'is_a':
			function is_a($object, $class)
			{
				if (!is_object($object)) return false;
				if (strtolower(get_class($object)) == strtolower($class))  return true;
				else return is_subclass_of($object, $class);
			};
		break;
	
		case 'is_callable':
			function is_callable($var, $syntax_only = false)
			{
				if (!is_string($var) && !(is_array($var) && count($var) == 2 && isset($var[0], $var[1]) && is_string($var[1]) && (is_string($var[0]) || is_object($var[0])))) return false;
				if ($syntax_only) return true;
				if (is_string($var)) return function_exists($var);
				else if (is_array($var))
				{
					if (is_string($var[0])) {
						$methods = get_class_methods($var[0]);
						$method = strtolower($var[1]);
						
						if ($methods) {
							foreach ($methods as $classMethod) {
								if (strtolower($classMethod) == $method) return true;
							}
						}
					} else {
						return method_exists($var[0], $var[1]);
					}
				}
				return false;
			};
		break;
	
		case 'is_scalar':
			function is_scalar($val)
			{
				return (is_bool($val) || is_int($val) || is_float($val) || is_string($val));
			};
		break;
	
		case 'md5_file':
			function md5_file($filename, $raw_output = false)
			{
				// Sanity check
				if (!is_scalar($filename)) {
					user_error('md5_file() expects parameter 1 to be string, ' . gettype($filename) . ' given', E_USER_WARNING);
					return;
				}
	
				if (!is_scalar($raw_output)) {
					user_error('md5_file() expects parameter 2 to be bool, ' . gettype($raw_output) . ' given', E_USER_WARNING);
					return;
				}
	
				if (!file_exists($filename)) {
					user_error('md5_file() Unable to open file', E_USER_WARNING);
					return false;
				}
	
				// Read the file
				if (false === $fh = fopen($filename, 'rb')) {
					user_error('md5_file() failed to open stream: No such file or directory', E_USER_WARNING);
					return false;
				}
	
				clearstatcache();
				if ($fsize = @filesize($filename)) {
					$data = fread($fh, $fsize);
				} else {
					$data = '';
					while (!feof($fh)) $data .= fread($fh, 8192);
				}
	
				fclose($fh);
	
				// Return
				$data = md5($data);
				if ($raw_output === true) $data = pack('H*', $data);
	
				return $data;
			};
		break;
	
		case 'mhash':
				! defined('MHASH_CRC32') && define('MHASH_CRC32', 0);
				! defined('MHASH_MD5') && define('MHASH_MD5', 1);
				! defined('MHASH_SHA1') && define('MHASH_SHA1', 2);
				! defined('MHASH_HAVAL256') && define('MHASH_HAVAL256', 3);
				! defined('MHASH_RIPEMD160') && define('MHASH_RIPEMD160', 5);
				! defined('MHASH_TIGER') && define('MHASH_TIGER', 7);
				! defined('MHASH_GOST') && define('MHASH_GOST', 8);
				! defined('MHASH_CRC32B') && define('MHASH_CRC32B', 9);
				! defined('MHASH_HAVAL192') && define('MHASH_HAVAL192', 11);
				! defined('MHASH_HAVAL160') && define('MHASH_HAVAL160', 12);
				! defined('MHASH_HAVAL128') && define('MHASH_HAVAL128', 13);
				! defined('MHASH_TIGER128') && define('MHASH_TIGER128', 14);
				! defined('MHASH_TIGER160') && define('MHASH_TIGER160', 15);
				! defined('MHASH_MD4') && define('MHASH_MD4', 16);
				! defined('MHASH_SHA256') && define('MHASH_SHA256', 17);
				! defined('MHASH_ADLER32') && define('MHASH_ADLER32', 18);
				function mhash($hashtype, $data, $key = '')
				{
					switch ($hashtype)
					{
						case MHASH_MD5:
							$key = str_pad((strlen($key) > 64 ? pack("H*", md5($key)) : $key), 64, chr(0x00));
							$k_opad = $key ^ (str_pad('', 64, chr(0x5c)));
							$k_ipad = $key ^ (str_pad('', 64, chr(0x36)));
							return pack("H*", md5($k_opad . pack("H*", md5($k_ipad .  $data))));
						default:
							return false;
						break;
					}
				};
		break;
	
		case 'microtime':
			function microtime($get_as_float = false)
			{
				if (!function_exists('gettimeofday'))
				{
					$time = time();
					return $get_as_float ? ($time * 1000000.0) : '0.00000000 ' . $time;
				}
	
				$gtod = gettimeofday();
				$usec = $gtod['usec'] / 1000000.0;
				return $get_as_float ? (float) ($gtod['sec'] + $usec) : (sprintf('%.8f ', $usec) . $gtod['sec']);
			};
		break;
	
		case 'mkdir':
			function mkdir($pathname, $mode = 0777, $recursive = true, $context = null)
			{
				if (version_compare(PHP_VERSION, '5.0.0', 'gte'))  return (func_num_args() > 3) ? mkdir($pathname, $mode, $recursive, $context) : mkdir($pathname, $mode, $recursive);
	
				if (!strlen($pathname)) {
					user_error('No such file or directory', E_USER_WARNING);
					return false;
				}
	
				if (is_dir($pathname))
				{
					if (func_num_args() == 5) return true;
					user_error('File exists', E_USER_WARNING);
					return false;
				}
	
				$parent_is_dir = mkdir(dirname($pathname), $mode, $recursive, null, 0);
				if ($parent_is_dir) return mkdir($pathname, $mode);
				user_error('No such file or directory', E_USER_WARNING);
				return false;
			};
		break;
	
		case 'ob_clean':
			function ob_clean()
			{
				if (@ob_end_clean()) return ob_start();
				user_error("ob_clean() failed to delete buffer. No buffer to delete.", E_USER_NOTICE);
				return false;
			};
		break;
	
		case 'ob_flush':
			function ob_flush()
			{
				if (@ob_end_flush()) return ob_start();
				user_error("ob_flush() Failed to flush buffer. No buffer to flush.", E_USER_NOTICE);
				return false;
			};
		break;
	
		case 'ob_get_clean':
			function ob_get_clean()
			{
				$contents = ob_get_contents();
				if ($contents !== false) ob_end_clean();
				return $contents;
			};
		break;
	
		case 'ob_get_flush':
			function ob_get_flush()
			{
				$contents = ob_get_contents();
				if ($contents !== false) ob_end_flush();
				return $contents;
			};
		break;
	
		case 'pathinfo':
			! defined('PATHINFO_FILENAME') && define('PATHINFO_FILENAME', 8);
			function pathinfo($path = false, $options = false)
			{
				// Sanity check
				if (!is_scalar($path)) {
					user_error('pathinfo() expects parameter 1 to be string, ' . gettype($path) . ' given', E_USER_WARNING);
					return;
				}
				if (version_compare(PHP_VERSION, '5.2.0', 'ge')) return pathinfo($path, $options);
				if ($options & PATHINFO_FILENAME) {
					if (strpos($path, '.') !== false) $filename = substr($path, 0, strrpos($path, '.'));
					if ($options === PATHINFO_FILENAME) return $filename;
					$pathinfo=pathinfo($path, $options);
					$pathinfo['filename']=$filename;
					return $pathinfo;
				}
				return pathinfo($path, $options);
			};
		break;
	
		case 'php_ini_loaded_file':
			function php_ini_loaded_file()
			{
				// Get the location of php.ini
				ob_start();
				phpinfo(INFO_GENERAL);
				$info = ob_get_contents();
				ob_clean();
				$info = explode("\n", $info);
				$line = array_values(preg_grep('#php\.ini#', $info));
	
				// Plain vs HTML output
				if (substr($line[0], 0, 4) === '<tr>') {
					list (, $value) = explode('<td class="v">', $line[0], 2);
					$inifile = trim(strip_tags($value));
				} else {
					list (, $value) = explode(' => ', $line[0], 2);
					$inifile = trim($value);
				}
	
				// Check the file actually exists
				if (!file_exists($inifile))  return false;
			};
		break;
	
		case 'restore_include_path':
			function restore_include_path()
			{
				return ini_restore('include_path');
			};
		break;
	
		case 'scandir':
			function scandir($directory, $sorting_order = 0)
			{
				if (!is_string($directory)) {
					user_error('scandir() expects parameter 1 to be string, ' . gettype($directory) . ' given', E_USER_WARNING);
					return;
				}
	
				if (!is_int($sorting_order) && !is_bool($sorting_order)) {
					user_error('scandir() expects parameter 2 to be long, ' . gettype($sorting_order) . ' given', E_USER_WARNING);
					return;
				}
	
				if (!is_dir($directory) || (false === $fh = @opendir($directory))) {
					user_error('scandir() failed to open dir: Invalid argument', E_USER_WARNING);
					return false;
				}
	
				$files = array ();
				while (false !== ($filename = readdir($fh))) $files[] = $filename;
				closedir($fh);
	
				if ($sorting_order == 1) rsort($files);
				else sort($files);
	
				return $files;
			};
		break;
	
		case 'set_include_path':
			function set_include_path($new_include_path)
			{
				return ini_set('include_path', $new_include_path);
			};
		break;
	
		case 'sys_get_temp_dir':
			function sys_get_temp_dir()
			{
				if (!empty($_ENV['TMP'])) return realpath($_ENV['TMP']);
				if (!empty($_ENV['TMPDIR']))  return realpath( $_ENV['TMPDIR']);
				if (!empty($_ENV['TEMP'])) return realpath( $_ENV['TEMP']);
	
				$tempfile = tempnam(uniqid(rand(),TRUE),'');
				if (file_exists($tempfile)) {
					unlink($tempfile);
					return realpath(dirname($tempfile));
				}
			};
		break;
	
		case 'time_sleep_until':
			function time_sleep_until($timestamp)
			{
				list($usec, $sec) = explode(' ', microtime());
				$now = $sec + $usec;
				if ($timestamp <= $now) {
					user_error('Specified timestamp is in the past', E_USER_WARNING);
					return false;
				}
	
				$diff = $timestamp - $now;
				usleep($diff * 1000000);
				return true;
			};
		break;
	
		case 'var_export':
			function var_export($var, $return = false, $level = 0, $inObject = false)
			{
				$indent      = '  ';
				$doublearrow = ' => ';
				$lineend     = ",\n";
				$stringdelim = '\'';
				$newline     = "\n";
				$find        = array(null, '\\', '\'');
				$replace     = array('NULL', '\\\\', '\\\'');
				$out         = '';
	
				// Indent
				$level++;
				for ($i = 1, $previndent = ''; $i < $level; $i++) $previndent .= $indent;
				$varType = gettype($var);
	
				// Handle object indentation oddity
				if ($inObject && $varType != 'object') $previndent = substr($previndent, 0, -1);
	
				// Handle each type
				switch ($varType)
				{
					case 'array':
						if ($inObject) $out .= $newline . $previndent;
						$out .= 'array (' . $newline;
	
						foreach ($var as $key => $value) {
							if (is_string($key)) {
								// Make key safe
								$key = str_replace($find, $replace, $key);
								$key = $stringdelim . $key . $stringdelim;
							}
	
							// Value
							if (is_array($value)) {
								$export = var_export($value, true, $level);
								$value = $newline . $previndent . $indent . $export;
							} else {
								$value = var_export($value, true, $level);
							}
	
							// Piece line together
							$out .= $previndent . $indent . $key . $doublearrow . $value . $lineend;
						}
	
						// End string
						$out .= $previndent . ')';
					break;
	
					case 'string':
						// Make the string safe
						for ($i = 0, $c = count($find); $i < $c; $i++) $var = str_replace($find[$i], $replace[$i], $var);
						$out = $stringdelim . $var . $stringdelim;
					break;
	
					case 'integer':
					case 'double':
						$out = (string) $var;
					break;
	
					case 'boolean':
						$out = $var ? 'true' : 'false';
					break;
	
					case 'NULL':
					case 'resource':
						$out = 'NULL';
					break;
	
					case 'object':
						// Start the object export
						$out = $newline . $previndent;
						$out .= get_class($var) . '::__set_state(array(' . $newline;
						// Export the object vars
						foreach(get_object_vars($var) as $key => $value) $out .= $previndent . $indent . ' ' . $stringdelim . $key . $stringdelim . $doublearrow . var_export($value, true, $level, true) . $lineend;
						$out .= $previndent . '))';
					break;
				}
	
				// Method of output
				if ($return === true) return $out;
				else echo $out;
			};
		break;
	
		case 'wp_die':
			function wp_die ($message = 'wp_die')
			{
				die($message);
			}
		break;
	
	}
}

// This is a cool workaround to defining functions already defined but throwing errors
foreach ( aadv_DEFINE_function() as $aafunky ) {
	if ( ! function_exists( $aafunky ) ) {
		aadv_DEFINE_function( $aafunky );
	}
}
unset( $aafunky );





/**
 * A class for displaying various tree-like structures.
 *
 * @package WordPress
 * @author askapache
 * @copyright Copyright (c) 2011
 * @version $Id$
 * @access public
 */
class AA_DEBUG {
	/**
	 * Quick Name used as prefix for plugin options and plugin info
	 *
	 * Stored in global wp_options under $_qn + '_options' and $_qn + '_plugin'
	 *
	 * @since 2.2.8
	 * @access private
	 * @var string
	 */
	var $_qn = 'askapache_debug';

	/**
	 * The debug level of this class
	 *
	 * Set to options['plugin_debug_level'] in __construct, turns logging and extra debugging on/off depending on its value.  0 is no debugging, 100 is max.
	 *
	 * @since 2.2.8
	 * @access private
	 * @var int
	 */
	var $_debug = 0;


	/**
	 * Contains Plugin Name and Settings from parsing this file
	 *
	 *  'plugin-name' => 'AskApache Debug Viewer', 
	 *  'short-name' => 'AA Debug', 
	 *  'description' => 'Displays Advanced Debugging Output', 
	 *  'author' => '<a href="http://www.askapache.com/" title="Visit author homepage">askapache,cduke250</a>', 
	 *  'version' => '2.4.1', 
	 *  'updated' => '12/20/2012 - 9:08 PM', 
	 *  'requires-at-least' => '3.1.0', 
	 *  'tested-up-to' => '3.5', 
	 *  'tags' => 'debug, debugging, error, errors, warning, problem, bug, problems, support, admin, programmer, developer, plugin, development, information, stats, logs, queries, htaccess, password, error, support, askapache, apache, rewrites', 
	 *  'contributors' => 'askapache,cduke250', 
	 *  'wordpress-uri' => 'http://wordpress.org/extend/plugins/askapache-debug-viewer/', 
	 *  'author-uri' => 'http://www.askapache.com/', 
	 *  'donate-uri' => 'http://www.askapache.com/donate/', 
	 *  'plugin-uri' => 'http://www.askapache.com/wordpress/debug-viewer-plugin.html', 
	 *  'role' => 'administrator', 
	 *  'capability' => 'askapache_debug_output', 
	 *  'qn' => 'askapache_debug', 
	 *  'file' => '~/wp-content/plugins/askapache-debug-viewer/askapache-debug-viewer.php', 
	 *  'title' => '<a href="http://www.askapache.com/wordpress/debug-viewer-plugin.html" title="Visit plugin homepage">AskApache Debug Viewer</a>', 
	 *  'pb' => 'askapache-debug-viewer/askapache-debug-viewer.php', 
	 *  'page' => 'askapache-debug-viewer.php', 
	 *  'pagenice' => 'askapache-debug-viewer', 
	 *  'nonce' => 'form_askapache-debug-viewer', 
	 *  'hook' => 'settings_page_askapache-debug-viewer', 
	 *  'action' => 'options-general.php?page=askapache-debug-viewer.php', 
	 *  'op' => 'adv7', 

	 * @since 2.2.8
	 * @var array
	 */
	var $plugin = array();	// array to hold plugin information
	var $_plugin = array();	// array to hold plugin information

	/**
	 * The Options for this plugin
	 * -------------------------------
	 *
	 * [options] => Array
	 * 		[page] => home
	 * 		[logfile] => php_error.log
	 * 		[dirtoexplore] => /tmp
	 * 		[log_errors] => 1
	 *		[verbose_modules] => 0
	 * 		[debug_live] => 0
	 * 		[admin_footer] => 1
	 * 		[wp_footer] => 1
	 * 		[error_reporting] => 4983
	 * 		[plugin_debug_level] => 5
	 * 		[debug_mods_v] => 5
	 * 		[debug_mods] => 5
	 * 		[admin_bar] => 1
	 *
	 * @since 2.2.8
	 * @var array
	 */
	var $options = array(
		'page' => 'home',
		'logfile' => '',
		'dirtoexplore' => ABSPATH,
		'key' => '',
		'log_errors' => '0',
		'verbose_modules' => '1',
		'debug_live' => '0',
		'display_height' => 200,
		'admin_footer' => '0',
		'wp_footer' => '0',
		'admin_bar' => '1',
		'error_reporting' => 2147483647,
		'plugin_debug_level' => 0,
		'debug_mods_v' => 0,
		'debug_mods' => 2148540849
	);

	/**
	 * The Pages for Navigating to
	 *
	 * @since 2.2.8
	 * @access private
	 * @var bool
	 * @var array
	 */
	 var $pages = array(
		'home' 			=> array( 'name' => 'Settings', 				'title' => 'Setup Debugging Options', 'nonce' => '' ),
		'phpinfo' 		=> array( 'name' => 'PHPINFO', 					'title' => 'phpinfo', 'nonce' => '' ),
		'server-status' => array( 'name' => 'Server Status', 			'title' => 'Server Status', 'nonce' => '' ),
		'server-info' 	=> array( 'name' => 'Server Info', 				'title' => 'Server Info', 'nonce' => '' ),
		'server-env' 	=> array( 'name' => 'Server Env', 				'title' => 'Printenv Output', 'nonce' => '' ),
		'server-parsed' => array( 'name' => 'Server Parsed',			'title' => 'SHTML Printenv', 'nonce' => '' ),
		'files' 		=> array( 'name' => 'Directory File Browser', 	'title' => 'Browse files and directories', 'nonce' => '' ),
		'wpconfig' 		=> array( 'name' => 'wp-config File', 			'title' => 'wp-config.php file', 'nonce' => '' ),
	 );

	/**
	 * The Actions for immediate effect
	 *
	 * @since 2.2.8
	 * @var array
	 */
	 var $actions = array(
		//'adminbaroff'	=>array( 'title' => 'Disable Front Admin Bar', 'nonce' => ''),
		'disable'		=> array( 'title' => 'Front Disable', 	'alt' => 'Instantly Disable viewing in Front End', 	'nonce' => '' ),
		'enable'		=> array( 'title' => 'Front Enable', 	'alt' => 'Instantly Enable viewing in Front End', 	'nonce' => '' ),
		'disableadmin'	=> array( 'title' => 'Admin Disable', 	'alt' => 'Instantly Disable viewing in Admin Area', 'nonce' => '' ),
		'enableadmin'	=> array( 'title' => 'Admin Enable', 	'alt' => 'Instantly Enable viewing in Admin Area', 	'nonce' => '' ),
		'disablebar'	=> array( 'title' => 'Admin Disable', 	'alt' => 'Instantly Disable viewing in Admin Area', 'nonce' => '' ),
		'enablebar'		=> array( 'title' => 'Admin Enable', 	'alt' => 'Instantly Enable viewing in Admin Area', 	'nonce' => '' ),
	 );
	 
	/**
	 * The debug mods
	 *
	 * @since 2.2.8
	 * @var array
	 */
	var $debug_mods = array();

	/**
	 * The ini_overwrites
	 *
	 * @since 2.2.8
	 * @var array
	 */
	var $ini_overwrites = array(
		//'output_handler' => '',
		//'session.auto_start' => '0',
		//'zlib.output_compression' => 0,
		//'output_buffering' => 0,
		//'precision' => '14',
		//'report_zend_debug' => 0,
		'open_basedir' => '',
		'tidy.clean_output' => 0,
		'xdebug.default_enable' => 0,
		'mbstring.func_overload' => 0,
		'error_prepend_string' => '',
		'error_append_string' => '',
		'auto_prepend_file' => '',
		'auto_append_file' => '',
		'disable_functions' => '',
		'safe_mode' => 0,
		'request_order' => 'GPCES',
		'register_globals' => 1,
		'register_long_arrays' => 1,
		'register_argc_argv' => 1,
		'always_populate_raw_post_data' => 1,
		'error_reporting' => 0,
		'display_errors' => 0,
		'display_startup_errors' => 0,
		'log_errors' => 1,
		'html_errors' => 0,// Turn off HTML tags in error messages. The new format for HTML errors produces clickable messages that direct the user to a page describing the error or function in causing the error. These references are affected by docref_root and docref_ext.
		'track_errors' => 1,
		'report_memleaks' => 1,
		'magic_quotes_runtime' => 0,
		'magic_quotes_gpc' => 0,
		'ignore_repeated_errors' => 1,
		'ignore_repeated_source' => 1,
		'log_errors_max_len' => '0'
	);

	/**
	 * The Old Ini Settings for storage
	 *
	 * @since 2.2.8
	 * @var array
	 */
	var $old_inis = array();




	/** AA_DEBUG::AA_DEBUG()
	 */
	function AA_DEBUG() {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );
		if ( version_compare( PHP_VERSION, '5.0.0', 'lt' ) ) {
			$this->__construct();
			register_shutdown_function( array( $this, '__destruct' ) );
		}
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );
	}

	/** AA_DEBUG::__construct()
	 */
	function __construct() {
		/* PRINT ERROR_GET_LAST ON SHUTDOWN
		 * register_shutdown_function(create_function('',
		 *		'error_log("print error_get_last '.error_log("print error_get_last").'");$l=error_get_last();if ( isset( $l["type"])&&$l["type"]===E_ERROR)echo "fatal error";echo (isset( $php_errormsg)?PHP_EOL.$php_errormsg:"").PHP_EOL.print_r( $l,1).PHP_EOL;' ) );
		 */
		$this->options = get_option( $this->_qn . '_options' );
		$this->_debug = $this->options['plugin_debug_level'];

		if ( $this->_debug > 0 ) {
			error_log( str_repeat( "\n", 5 ) . str_repeat( '=', 235 ) );
		}
		
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 50 );
		
		$this->_plugin = $this->get_plugin_data();
		
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 50 );
	}

	/** AA_DEBUG::LoadOptions()
	 */
	function LoadOptions() {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );

		$this->options = get_option( $this->_qn . '_options' );
		$this->_plugin = $this->get_plugin_data();
		$this->_debug = absint( $this->options['plugin_debug_level'] );

		$D = array();
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_footerhelper', 		'Footer Helper' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_templates', 		'Templates' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_options', 			'WP Options' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_aa_plugin', 		'Debug Plugin' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_request', 			'Request' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_wordpress', 		'WordPress Globals' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_globalprint', 		'GLOBALS Print' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_globalvars', 		'Variables in Scope' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_rewrites', 			'Rewrites' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_included', 			'Included Files' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_extensions', 		'Extensions' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_classes', 			'Classes' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_functions', 		'Functions' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_defined', 			'Constants' );		
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_posix', 			'Posix Info' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_inis', 				'PHP ini settings' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_permissions',		'User/File/Process permissions' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_interfaces', 		'Interfaces' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_sockets', 			'Sockets' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_queries', 			'DataBase Queries' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_crons', 			'WordPress Crons' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_plugins', 			'WordPress Plugins' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_filters', 			'WordPress Actions/Filters' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_scripts', 			'WordPress JS Scripts' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_styles', 			'WordPress CSS Styles' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_tax', 		    	'Taxonomies' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_post_types',    	'Custom Post Types' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_nav_menus',     	'Nav Menus' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_admin_menus',   	'Admin Menus' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_sidebars',			'SideBars' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_widgets',			'Widgets' );
		$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_mem_hogs',			'Memory Hogs' );
		
		if ( method_exists( 'RGFormsModel', 'get_forms' ) )
			$D[ ( 1 << sizeof( $D ) ) ] = array( 'get_debug_gforms',        'GForms' );


		$this->debug_mods = $D;
		unset( $D );

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );
	}

	/** AA_DEBUG::SaveOptions()
	 */
	function SaveOptions() {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );
		
		if ( ! $this->check_auth() ) wp_die( '<strong>ERROR</strong>: User does not have permission to "edit_users". ' . __FUNCTION__ . ':' . __LINE__ );

		update_option( $this->_qn . '_options', $this->options );
		update_option( $this->_qn . '_plugin', $this->_plugin );

		$this->LoadOptions();
		
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );
	}

	/** AA_DEBUG::DefaultOptions()
	 *
	 * @uses wp_hash_password() Used to encrypt the user's password before passing to the database
	 *
	 *
	 * @since 2.2.1
	 * @version 1.1
	 *
	 * @param array $a The array
	 * @param string $s The text
	 * @param int $i The int 
	 * @param string|array $query_args Passed to {@link parse_request()}
	 * @param WP_Error|null $a WP_Error object or null.
	 
	 * @return bool True on success. False on failure.
	 * @return int The int.
	 * @return array The array.
	 * @return string The string.
	 * @return null Null on failure.
	 */
	function DefaultOptions($save=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );

		// get all the plugin array data
		$this->_plugin = $this->get_plugin_data( true );

		// save the $this->_plugin to $this->_qn_plugin
		$this->SaveOptions();

		$key = wp_generate_password( 24, false, false );
		
		// default array of options
		$ret = array(
			              'page' => 'home',
			            'logfile' => $this->get_error_log(),
			       'dirtoexplore' => __DIR__,
			         'log_errors' => '0',
			    'verbose_modules' => '1',
			                'key' => $key,
			         'debug_live' => '0',
			       'admin_footer' => '0',
			          'wp_footer' => '0',
			          'admin_bar' => '1', //_get_admin_bar_pref( 'backend' ),
			    'error_reporting' => 2147483647,
			 'plugin_debug_level' => 0,
			     'display_height' => 200,
			       'debug_mods_v' => 0,
			         'debug_mods' => 2148540849
		);

		// if $save is true
		if ( $save === true ) {
			//save $ret to $this->options
			$this->options = $ret;

			// save both $this->options and $this->_plugin
			$this->SaveOptions();

			// reset $ret to equal true for return;
			$ret = true;
		}

		// Save all these variables to database
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );

		return $ret;
	}

	/** AA_DEBUG::Activate()
	 */
	function Activate() {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );

		$old_options = $old_plugin = $default_options = false;

		// load the default options without saving ::options
		$new_options = $this->DefaultOptions( false );

		// get old options
		$old_options = get_option( $this->_qn . '_options' );

		// if old_options exist, merge the old settings into the new
		if ( $old_options !== false && is_array( $old_options ) && array_key_exists( 'plugin_debug_level', $old_options ) && ! array_key_exists( 'admin_bar_fix', $old_options ) ) {
			
			aadv_error_log(  print_r( array_diff( $old_options, $new_options ), 1 ) );
			$this->options = wp_parse_args( $old_options, $new_options );
		}

		// delete the existing options
		delete_option( $this->_qn . '_options' );
		delete_option( $this->_qn . '_plugin' );

		// add the new options
		//add_option( $option, $value = '', $deprecated = '', $autoload = 'yes' )
		add_option( $this->_qn . '_options', $this->options, '', 'yes' );
		add_option( $this->_qn . '_plugin', $this->_plugin, '', 'yes' );

		$this->SaveOptions();

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );
	}

	/** AA_DEBUG::DeActivate()
	 */
	function DeActivate() {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );
		
		if ( ! $this->check_auth() )
			wp_die(__FUNCTION__ . ':' . __LINE__);

		$this->deactivate_ff_htaccess();

		delete_option( $this->_qn . '_plugin' );

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );
	}

	/** AA_DEBUG::Uninstall()
	 */
	function Uninstall() {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );

		delete_option( $this->_qn . '_options' );
		delete_option( $this->_qn . '_plugin' );

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );
	}

	/** AA_DEBUG::RegisterPluginSettings()
	 */
	function RegisterPluginSettings($l=array()) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );
		
		$ret = array_merge( array( '<a href="' . admin_url( $this->_plugin['action'] ) . '">Settings</a>' ), $l );
		
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );
		
		return $ret;
	}

	/** AA_DEBUG::Init()
	 */
	function Init() {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );

		// quit now or forever hold your peace ( and avoid processing any further )
		if ( ! is_user_logged_in() )
			return;

		// Load options
		$this->LoadOptions();


		// adminbar
		add_action( 'admin_bar_menu', array( &$this, 'AdminBar' ), 9983 );


		// add admin-specific stuff
		if ( is_admin() ) {
			add_action( "admin_head-{$this->_plugin['hook']}", array( &$this, 'AddHelp' ) );
			add_filter( "plugin_action_links_{$this->_plugin['pb']}", array( &$this, 'RegisterPluginSettings' ) );

			add_action( "load-{$this->_plugin['hook']}", array( &$this, 'Load' ) );
			add_action( 'admin_menu', array( &$this, 'AdminMenu' ) );

			// enqueue styles
			wp_enqueue_style( $this->_plugin['pagenice'], plugins_url( 'f/admin.css', __FILE__ ), false, $this->_plugin['version'], 'all' );
			wp_enqueue_style( $this->_plugin['pagenice'] . '1', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css', false, $this->_plugin['version'], 'all' );
			
			if ( $this->options['admin_footer'] == '1' ) {
				// enqueue script
				wp_enqueue_script( $this->_plugin['pagenice'], plugins_url( 'f/admin.js', __FILE__ ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-resizable', 'jquery-ui-tabs', 'jquery-ui-draggable' ), $this->_plugin['version'] );
			}


			register_uninstall_hook( __FILE__, array( &$this, 'Uninstall' ) );
			register_activation_hook( __FILE__, array( &$this, 'Activate' ) );
			register_deactivation_hook( __FILE__, array( &$this, 'DeActivate' ) );
			
		} elseif ( $this->options['wp_footer'] == '1' ) {
		
			// enqueue styles
			wp_enqueue_style( $this->_plugin['pagenice'], plugins_url( 'f/admin.css', __FILE__ ), false, $this->_plugin['version'], 'all' );
			wp_enqueue_style( $this->_plugin['pagenice'] . '1', '//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css', false, $this->_plugin['version'], 'all' );
	
			// enqueue script
			wp_enqueue_script( $this->_plugin['pagenice'], plugins_url( 'f/admin.js', __FILE__ ), array( 'jquery', 'jquery-ui-core', 'jquery-ui-resizable', 'jquery-ui-tabs', 'jquery-ui-draggable' ), $this->_plugin['version'] );

		}







		// add old inis to class var and create shutdown function to reset
		foreach ( $this->ini_overwrites as $k => $v )
			$this->old_inis[ $k ] = ini_get( $k );


		$this->old_inis['error_reporting'] = error_reporting();
		register_shutdown_function( create_function( '', '$oe=' . $this->old_inis['error_reporting'] . ';$ne=' . error_reporting( $this->options['error_reporting'] ) . ';error_reporting($oe);' ) );





		foreach ( $this->pages as $id => $idv ) {
			$this->pages[ $id ]['nonce'] = wp_nonce_url( admin_url( "{$this->_plugin['action']}&amp;{$this->_plugin['op']}_page={$id}" ), "{$this->_plugin['op']}_page_{$id}" );
		}

		foreach ( $this->actions as $id => $idv ) {
			$this->actions[ $id ]['nonce'] = wp_nonce_url( admin_url( "{$this->_plugin['action']}&amp;{$this->_plugin['op']}_action={$id}" ), "{$this->_plugin['op']}_action_{$id}" );
		}




		// add to admin footer
		if ( $this->options['admin_footer'] == '1' )
			add_action( 'admin_footer', array( &$this, 'footer_output' ), 999999 );


		// add to wp footer
		if ( $this->options['wp_footer'] == '1' )
			add_action( 'wp_footer', array( &$this, 'footer_output' ), 999999 );

		

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );
	}

	/** AA_DEBUG::AddHelp($text, $screen)
	 */
	function AddHelp() {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 20 );
		
		$current_screen = get_current_screen();

		add_contextual_help( $current_screen,
			'<h4>Fixing Status Headers</h4>'
			.'<p>For super-advanced users, or those with access and knowledge of Apache <a href="http://www.askapache.com/htaccess/htaccess.html">.htaccess/httpd.conf files</a>'
			.' you should check that your error pages are correctly returning a <a href="http://www.askapache.com/htaccess/apache-status-code-headers-errordocument.html"><code>404 Not Found</code>'
			.' HTTP Header</a> and not a <code>200 OK</code> Header which appears to be the default for many WP installs, this plugin attempts to fix this using PHP, but the best way I have found'
			.' is to add the following to your <a href="http://www.askapache.com/htaccess/htaccess.html">.htaccess</a> file.</p>'
			. '<pre>ErrorDocument 404 /index.php?error=404'."\n".'Redirect 404 /index.php?error=404</pre>'
			.'<h5>Comments/Questions</h5><p><strong>Please visit <a href="http://www.askapache.com/">AskApache.com</a> or send me an email at <code>webmaster@askapache.com</code></strong></p>'
			);
			
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 20 );
	}

	/** AA_DEBUG::AdminMenu()
	 */
	function AdminMenu() {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 20 );
		
		$p = $this->_plugin;
		add_options_page( $p['plugin-name'], $p['short-name'], $p['role'], $p['page'], array( &$this, 'AdminPage' ) );
			
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 20 );
	}

	/** AA_DEBUG::AdminBar()
	 */
	function AdminBar($wp_admin_bar) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );

		if ( ! is_object( $wp_admin_bar ) )
			return aadv_error_log( ' wp_admin_bar is not an object' );

		//if ($this->options['wp_footer']=='1' && $this->options['admin_bar']=='1' ) {
			//$pref = get_user_option( "show_admin_bar_front", $user->ID );
			//update_user_option( $user->ID, "show_admin_bar_front", '' );

		//}
		//isset( $_POST['admin_bar_front'] ) ? 'true' : 'false'
		//

		$wp_admin_bar->add_menu( array(
			'id' 	=> $this->_plugin['op'] . 'menu',
			'title' => strip_tags( $this->_plugin['short-name'] ),
			'href'  => admin_url( $this->_plugin['action'] )
		) );



		$pages = $subpages = array();
		
		$pages = array(
					   'home' => $this->pages['home'],
					   'info' => array( 'name' => 'Info', 'title' => 'Info', 'nonce' => wp_nonce_url( admin_url( "{$this->_plugin['action']}&amp;{$this->_plugin['op']}_page=info" ), "{$this->_plugin['op']}_page_info" ) ),
					'phpinfo' => $this->pages['phpinfo']
		);

		foreach ( $pages as $id => $idv ) {
			$wp_admin_bar->add_menu( array(
				'parent'=> $this->_plugin['op'] . 'menu',
				'id' 	=> $this->_plugin['op'] . $id,
				'title' => $idv['title'],
				'href'  => $idv['nonce']
			) );
		}
		
		
		
		$subpages = array(
				'wpconfig' 		=> $this->pages['wpconfig'],
				'phpinfo' 		=> $this->pages['phpinfo'],
				'server-status' => $this->pages['server-status'],
				'server-info' 	=> $this->pages['server-info'],
				'server-env' 	=> $this->pages['server-env'],
				'server-parsed' => $this->pages['server-parsed']
		);
		
		foreach ( $subpages as $id => $idv ) {
			$wp_admin_bar->add_menu( array(
				'parent'=> $this->_plugin['op'] . 'info',
				'id' 	=> $this->_plugin['op'] . $id,
				'title' => $idv['title'],
				'href'  => $idv['nonce']
			) );
		}
		

		$actions = array();
		$action = $this->actions['enable'];
		if ( $this->options['wp_footer'] == '1' ) {
			$actions['disable'] = $this->actions['disable'];
		} else {
			$actions['enable'] = $this->actions['enable'];
		}

		$action = $this->actions['enableadmin'];
		if ( $this->options['admin_footer'] == '1' ) {
			$actions['disableadmin'] = $this->actions['disableadmin'];
		} else {
			$actions['enableadmin'] = $this->actions['enableadmin'];
		}

		foreach ( $actions as $id => $idv ) {
			$wp_admin_bar->add_menu( array(
				'parent'=> $this->_plugin['op'] . 'menu',
				'id' 	=> $this->_plugin['op'] . $id,
				'title' => $idv['title'],
				'href'  => $idv['nonce'],
				'meta'  => array( 'title' => $idv['alt'] ),
			) );
		}


		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );
	}

	/** AA_DEBUG::Load()
	 */
	function Load() {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );

		//global $show_admin_bar;
		if ( ! $this->check_auth() ) wp_die( '<strong>ERROR</strong>: User does not have permission to "edit_users". ' . __FUNCTION__ . ':' . __LINE__ );


		// Handle page
		foreach ( array_keys( $this->pages ) as $w ) {
			if ( isset( $_GET["{$this->_plugin['op']}_page"] ) && $_GET["{$this->_plugin['op']}_page"] == $w ) {
				check_admin_referer( "{$this->_plugin['op']}_page_" . $w );
				$this->options['page'] = $w;
				break;
			}
		}
		
		
		// Handle actions
		foreach ( array_keys( $this->actions ) as $w ) {
			if ( isset( $_GET["{$this->_plugin['op']}_action"] ) && $_GET["{$this->_plugin['op']}_action"] == $w ) {
				check_admin_referer( "{$this->_plugin['op']}_action_" . $w );
				
				if ( $w == 'disable' )  {
					$this->options['wp_footer'] = $this->options['log_errors'] = '0';

				} elseif ( $w == 'disableadmin' ) {
					$this->options['admin_footer'] = $this->options['log_errors'] = '0';

				} elseif ( $w == 'enable' ) {
					$this->options['wp_footer'] = '1';

				} elseif ( $w == 'enableadmin' ) {
					$this->options['admin_footer'] = '1';

				} elseif ( $w == 'adminbaroff' )  {
					$this->options['admin_bar'] = '0';
				}

				wp_redirect( $_SERVER['HTTP_REFERER'] );
				break;
			}
		}


		// parse and handle post requests to plugin
		if ( 'GET'== $_SERVER['REQUEST_METHOD'] )
			$this->HandleGet();

		if ( 'POST' == $_SERVER['REQUEST_METHOD'] )
			$this->HandlePost();


		$this->SaveOptions();

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );
  	}

	/** AA_DEBUG::HandleGet()
	 */
	function HandleGet() {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );
		
		if ( ! $this->check_auth() ) wp_die( '<strong>ERROR</strong>: User does not have permission to "edit_users". ' . __FUNCTION__ . ':' . __LINE__ );
		
		// verify nonce, if not verified, then DIE
		if ( isset( $_GET["{$this->_plugin['op']}_action"], $_GET['_wpnonce'] ) && $_GET["{$this->_plugin['op']}_action"] == 'files' && isset( $_GET['file'] ) ) {
			wp_verify_nonce( $_GET['_wpnonce'], 'file_nonce' ) || wp_die( '<strong>ERROR</strong>: Incorrect Form Submission, please try again.' );
			
			$f = $_GET['file'];
			$f = rtrim( $this->base64url_decode( $f ), '/' );
			
			if ( is_dir( $f ) ) {
				$this->options['dirtoexplore'] = rtrim( $f, '/' );
			} elseif ( is_file( $f ) ) {
				$this->options['dirtoexplore'] = rtrim( $f, '/' );
			}

		} else {
			return;
		}
		



		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );
	}


	/** AA_DEBUG::HandlePost()
	 */
	function HandlePost() {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );
		
		if ( ! $this->check_auth() ) wp_die( '<strong>ERROR</strong>: User does not have permission to "edit_users". ' . __FUNCTION__ . ':' . __LINE__ );
		
		// verify nonce, if not verified, then DIE
		if ( isset( $_POST["{$this->_plugin['op']}_{$this->_plugin['nonce']}"] ) ) {
			wp_verify_nonce( $_POST["{$this->_plugin['op']}_{$this->_plugin['nonce']}"], $this->_plugin['nonce'] ) || wp_die( '<strong>ERROR</strong>: Incorrect Form Submission, please try again.' );
		
		} elseif ( isset( $_POST["{$this->_plugin['op']}_{$this->_plugin['nonce']}_reset"] ) ) {
			wp_verify_nonce( $_POST["{$this->_plugin['op']}_{$this->_plugin['nonce']}_reset"], $_POST["{$this->_plugin['op']}_{$this->_plugin['nonce']}_reset"] ) || wp_die( '<strong>ERROR</strong>: Incorrect Form Submission, please try again.' );

		} else {
			return;
		}
		
		
		$op = $this->_plugin['op'];


		// resets options to default values
		if ( isset( $_POST["{$op}_action_reset"] ) )
			return $this->DefaultOptions( true );


		// save options
		if ( isset( $_POST["{$op}_save_debug_options"] ) ) {

			
			//------------------- simple on/off
			foreach ( array( 'log_errors','debug_live','admin_footer','wp_footer', 'admin_bar','verbose_modules' ) as $k ) 
				$this->options["{$k}"] = ( isset( $_POST["{$op}_{$k}"] ) ? '1' : '0' );
			//------------------- simple on/off
			


			
			
			//------------------- absint
			foreach ( array( 'plugin_debug_level','display_height', 'error_reporting' ) as $k ) 
				if ( isset( $_POST["{$op}_{$k}"] ) ) $this->options["{$k}"] = absint( $_POST["{$op}_{$k}"] );
			//------------------- absint
			


			
			
			//------------------- strings
			foreach ( array( 'logfile','dirtoexplore' ) as $k ) 
				$this->options["{$k}"] = ( ( isset( $_POST["{$op}_{$k}"] ) && ! empty( $_POST["{$op}_{$k}"] ) ) ? rtrim( trim( $_POST["{$op}_{$k}"] ), '/' ) : $this->options["{$k}"] );
			//------------------- strings
			


			
			
			//------------------- specials
			if ( isset( $_POST["{$op}_plugin_debug_level"] ) )
				$this->_debug = absint( $_POST["{$op}_plugin_debug_level"] );


			if ( isset( $_POST["{$op}_log_errors"] ) || empty( $this->options['logfile'] ) )
				$this->options['logfile'] = $this->get_error_log();
			//------------------- specials
			


			
			
			//------------------- bits
			if ( isset( $_POST["{$op}_error_reporting"] ) ) {
				if ( strpos( $_POST["{$op}_error_reporting"], 'E' ) !== false ) {
					$this->options['error_reporting'] = $this->get_error_levels( trim( $_POST["{$op}_error_reporting"], '|' ), 'string2error' );
				
				} elseif ( strpos( $_POST["{$op}_error_reporting"], '|' ) !== false ) {
					$this->options['error_reporting'] = $this->get_error_levels( $this->get_error_levels( trim( $_POST["{$op}_error_reporting"], '|' ), 'error2string' ), 'string2error' );
				}


				if ( ( $this->options['error_reporting'] = 0) == 0 ) {
					foreach ( array_map( 'intval', (array) $_POST["{$op}_error_reporting"] ) as $bit ) {
						$this->options['error_reporting'] |= $bit;
					}
				}

			}


			if ( isset( $_POST["{$op}_debug_mods"] ) && ( $this->options['debug_mods'] = 0 ) == 0 ) {
				foreach ( array_map( 'intval', (array) $_POST["{$op}_debug_mods"] ) as $bit ) {
					$this->options['debug_mods'] |= $bit;
				}
			}
			//------------------- bits



		}
		
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );
	}

	/** AA_DEBUG::AdminPage()
	 * @version 1.2
	 */
	function AdminPage() {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		if ( ! $this->check_auth() ) wp_die(__FUNCTION__.':'.__LINE__);

		global $screen, $current_screen, $wp_meta_boxes, $_wp_contextual_help;


		echo '<div class="wrap" id="' . $this->_plugin['op'] . '">' . ( $this->_cf( 'screen_icon' ) ? screen_icon() : '' ) . '<h2>' . $this->_plugin['plugin-name'] . ' - ' . $this->pages[ $this->options['page'] ]['title'] . '</h2>';

		$this->display_navigation_menu();


		switch ( $this->options['page'] ) {
			case 'server-info':
				$r = $this->get_socket_request( array( 'n' => 'server-info', 'pre' => 0 ) );
				if ( strpos( $r, '<body>' ) !== false ) {
					$r = substr( $r, strpos( $r, '<body>' ) + 6, -15 );
				}
				echo "\n\n\n\n\n" . str_replace( 'href="?', 'href="#', $r ) . "\n\n\n\n";
				unset( $r );
			break;

			case 'server-status':
				$r = $this->get_socket_request( array( 'n' => 'server-status', 'pre' => 0  ) );
				if ( strpos( $r, '<body>' ) !== false ) {
					$r = substr( $r, strpos( $r, '<body>' ) + 6, -15 );
				}
				echo "\n\n\n\n\n" . $r . "\n\n\n\n";
				unset( $r );
			break;

			case 'server-env':
				echo $this->get_socket_request( array( 'n' => 'server-env.cgi', 'p' => '0744', 'pcheck' => true ) );
			break;
			
			case 'server-parsed':
				echo $this->get_socket_request( array( 'n' => 'server-parsed.shtml' ) );
			break;
			

			case 'phpinfo':
				echo '<div id="' . $this->_plugin['op'] . '_phpinfo">';
				echo $this->get_debug_phpinfo( 0 );
				echo '</div>';
			break;

			case 'files':
				echo '<h2>AskApache Directory File Browser</h2>';
				printf( "<p>UMASK: %04o | DIR: %04o | FILE: %04o ", umask(), ( 0755 & ~ umask() ), ( 0644 & ~ umask() ) . '</p>' );
				echo '<form action="' . admin_url( $this->_plugin['action'] ) . '" method="post" id="' . $this->_plugin['op'] . '_form">';
				echo '<hr />';
				
				$this->ff( array(
					'form' => 6,
					'type' => 'hidden',
					'id' => $this->_plugin['op'] . '_' . $this->_plugin['nonce'],
					'name' => $this->_plugin['op'] . '_' . $this->_plugin['nonce'],
					'value' => wp_create_nonce( $this->_plugin['nonce'] ),
					'pre' => '<p style="display:none;">',
					'post' => ''
				) );


				$this->ff( array( 
					'form' => 6,
					'type' => 'hidden',
					'id' => '_wp_http_referer',
					'name' => '_wp_http_referer',
					'value'=> ( esc_attr( $_SERVER['REQUEST_URI'] ) ),
					'pre' => '',
					'post' => '</p>'
				) );
				
				$this->ff( array(
					'form' => 6,
					'type' => 'hidden',
					'id' => $this->_plugin['op'] . '_save_debug_options',
					'name' => $this->_plugin['op'] . '_save_debug_options',
					'value' => 'save_debug_options',
					'pre' => '',
					'post' => '</p>'
				) );
				
				echo '<div id="' . $this->_plugin['op'] . '">';
				
				$this->ff( array(
					'form' => 2,
					'type' => 'text',
					'class' => 'aa_wide',
					'title' => 'Dir/File to Explore: ',
					'id' => 'dirtoexplore',
					'value' => $this->options['dirtoexplore']
				) );
				
				echo '</div></form>';
				
				$file_nonce = wp_create_nonce( 'file_nonce' ); 
				echo $this->_pls( $this->options['dirtoexplore'], admin_url( "{$this->_plugin['action']}&amp;{$this->_plugin['op']}_action=files&_wpnonce=" . $file_nonce ), 1 );

			break;

			case 'wpconfig':

				$wp_config = ( file_exists( ABSPATH . 'wp-config.php' ) ) ? ABSPATH . 'wp-config.php' : ( file_exists( dirname( ABSPATH ) . '/wp-config.php' ) ? dirname( ABSPATH ) . '/wp-config.php' : '' );

				echo '<p>This is just a recommendation, this is not editable. Add this to your wp-config.php at the bottom BEFORE the wp-settings is included.';
				echo '  This is unneccessary if you can modify your php.ini - See my <a href="http://www.askapache.com/wordpress/advanced-wp-config-php-tweaks.html">wp-config.php tutorial</a>.</p>';
				echo '<textarea class="code" rows="6" cols="70" style="width:90%;">';
				ob_start();
				echo "\n<?php\n\n! defined('WP_DEBUG') && define('WP_DEBUG', false);\n";
				echo "! defined('SAVEQUERIES') && define('SAVEQUERIES', false);\n";
				echo "! defined('ACTION_DEBUG') && define('ACTION_DEBUG', false);\n";
				echo "! defined('SCRIPT_DEBUG') && define('SCRIPT_DEBUG', false);\n";
				echo "! defined('WP_DEBUG_DISPLAY') && define('WP_DEBUG_DISPLAY', false);\n\n";

				echo "ini_set('display_errors', 'Off' );\n";
				echo "ini_set('display_startup_errors', 'Off' );\n";
				echo "ini_set('log_errors', 'Off' ); //turn on if you use logs (i do)\n";
				echo "ini_set('error_log', dirname(ABSPATH).'/php_error.log' ); // set to a non-web-accessible location (above docroot)\n\n?>";
				$rec = ob_get_clean();
				echo $rec;
				echo '</textarea>';



				echo "<p>Current Contents of <code>{$wp_config}</code></p>";
				echo "<div style='border:1px solid #000;padding:5px;width:95%;overflow:hidden;'>";
				echo preg_replace( '#color="(.*?)"#', 'style="color:\\1"', str_replace( array( '<' . 'font ', '</font>' ), array( '<' . 'span ', '</span>' ), highlight_string( stripslashes( file_get_contents( $wp_config ) ), true ) ) );
				echo '</div>';
			break;



			default:
			case 'home':

				echo '<form action="' . admin_url( $this->_plugin['action'] ) . '" method="post" id="' . $this->_plugin['op'] . '_form"><hr />';
				
				$this->ff( array(
					'form' => 6,
					'type' => 'hidden',
					'id' => $this->_plugin['op'] . '_' . $this->_plugin['nonce'],
					'name' => $this->_plugin['op'] . '_' . $this->_plugin['nonce'],
					'value' => wp_create_nonce( $this->_plugin['nonce'] ),
					'pre' => '<p style="display:none;">',
					'post' => ''
				) );

				$this->ff( array(
					'form' => 6,
					'type' => 'hidden',
					'id' => '_wp_http_referer',
					'name' => '_wp_http_referer',
					'value'=> ( esc_attr( $_SERVER['REQUEST_URI'] ) ),
					'pre' => '',
					'post' => '</p>'
				) );


				echo '<div id="' . $this->_plugin['op'] . '">';
				$this->ff( array(
					'form' => 1,
					'type' => 'checkbox',
					'title' => '<strong>View in admin_footer</strong>',
					'id' => 'admin_footer',
					'checked'=> ( $this->options['admin_footer'] == '1' ),
					'value' => $this->options['admin_footer']
				) );

				$this->ff( array(
					'form' => 1,
					'type' => 'checkbox',
					'title' => '<strong>View in wp_footer</strong>',
					'id' => 'wp_footer',
					'checked'=> ( $this->options['wp_footer'] == '1' ),
					'value' => $this->options['wp_footer']
				) );

				$this->ff( array(
					'form' => 1,
					'type' => 'checkbox',
					'title' => '<strong>Log Errors to File</strong>',
					'id' => 'log_errors',
					'checked'=> ( $this->options['log_errors'] == '1' ),
					'value' => $this->options['log_errors']
				) );
				//$this->ff( array( 'form' => 1,'type' => 'checkbox', 'title' => '<strong>Enable Live Debugging</strong>', 'id' => 'debug_live', 'checked'=>($this->options['debug_live']=='1'),'value' => $this->options['debug_live']) );
				echo '<hr />';
				
				$this->ff( array(
					'form' => 2,
					'type' => 'text',
					'class' => 'aa_mid',
					'title' => 'Error Log File',
					'id' => 'logfile',
					'value' => $this->options['logfile']
				) );
				echo '<hr />';
				
				
				//$this->ff( array( 'form' => 1,'type' => 'checkbox', 'title' => '<strong>Show Admin Bar</strong>', 'id' => 'admin_bar', 'checked'=>($this->options['admin_bar']=='1'),'value' => $this->options['admin_bar']) );
				$this->ff( array(
					'form' => 2,
					'type' => 'text',
					'class' => 'aa_small',
					'title' => 'Plugin Debug Level (0-100)',
					'id' => 'plugin_debug_level',
					'value' => $this->options['plugin_debug_level']
				) );

				$this->ff( array(
					'form' => 2,
					'type' => 'text',
					'class' => 'aa_small',
					'title' => 'Output Display Height (0-5000)',
					'id' => 'display_height',
					'value' => $this->options['display_height']
				) );
				echo '<hr />';


				//$this->ff( array( 'form' => 2,'type' => 'text', 'class' => 'aa_wide', 'title' => 'Dir to Explore: ', 'id' => 'dirtoexplore', 'value' => $this->options['dirtoexplore']) );



				echo '<div style="padding-left:10px">';
				echo '<h3>Output Modules</h3>';
				$this->ff( array(
					'form' => 1,
					'type' => 'checkbox',
					'title' => '<strong>Verbose Module Output</strong>',
					'id' => 'verbose_modules',
					'checked'=> ( $this->options['verbose_modules'] == '1' ),
					'value' => $this->options['verbose_modules']
				) );
				echo '<hr />';

				//$this->ff( array( 'form' => 5,'type' => 'hidden', 'id' => 'debug_mods_v', 'name' => 'debug_mods_v', 'value' => $this->options['debug_mods_v'],'pre' => '', 'post' => '') );
				$this->ff( array(
					'form' => 5,
					'type' => 'hidden',
					'id' => 'debug_mods',
					'name' => 'debug_mods',
					'value' => $this->options['debug_mods'],
					'pre' => '',
					'post' => ''
				) );

				foreach ( $this->debug_mods as $id => $info ) {
					$this->ff( array(
						'form' => 3,
						'type' => 'checkbox',
						'title' => $info[1],
						'id' => 'debug_mods',
						'name' => 'debug_mods[]',
						'value' => $id,
						'checked'=> ( ( $id & $this->options['debug_mods'] ) == $id ),
						'pre'=> "<p class='c4r'>",
						'post'=> '</p>'
					)) ;
					//$this->ff( array( 'form' => 4,'type' => 'checkbox', 'title' => 'Verbose', 'id' => 'debug_mods_v', 'name' => 'debug_mods_v[]', 'value' => $id,'checked'=>(($id & $this->options['debug_mods_v'])==$id),'pre' => '', 'post'=>"<br style='clear:both;' /></p>" ) );
				}
				echo '</div><hr />';

				$this->ff( array(
					'form' => 2,
					'type' => 'text',
					'class' => 'aa_small',
					'title' => 'PHP Error Reporting Level (try 2147483647)',
					'id' => 'error_reporting',
					'value' => $this->options['error_reporting']
				) );
		
				echo '<div style="padding-left:10px; width:90%; margin:0 auto;">';
				printf( '<p>Current: <strong>%1$s</strong> - %2$s<br /><code>error_reporting(%2$s)</code></p>', error_reporting(), $this->get_error_levels( error_reporting(), 'error2string' ) );
				printf( '<p>Previous: <strong>%1$s</strong> - %2$s<br /><code>error_reporting(%2$s)</code></p>', intval( $this->old_inis['error_reporting'] ), $this->get_error_levels( intval( $this->old_inis['error_reporting'] ), 'error2string' ) );
				echo '</div>';

				$this->display_ini_error_configs();
				echo '<hr />';


				$this->ff( array(
					'form' => 5,
					'type' => 'submit',
					'class' => 'button-primary',
					'id' => 'save_debug_options',
					'name' => 'save_debug_options',
					'value' => 'Save Changes &raquo;',
					'pre' => '<p class="submit">',
					'post' => ' ' . str_repeat( '&nbsp;', 11 ) . ' '
				) );

				$this->ff( array(
					'form' => 5,
					'type' => 'submit',
					'class' => 'button-secondary',
					'id' => 'action_reset',
					'name' => 'action_reset',
					'value' => 'Revert to Defaults &raquo;',
					'pre' => '',
					'post' => '</p>'
				) );
				


				echo '</div></form>';

			break;

		} //endswitch page

		echo '</div><!--wrap-->';


		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
	}




	/** AA_DEBUG::display_navigation_menu()
	*/
	function display_navigation_menu() {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 25 );
		
		echo "<div id=\"{$this->_plugin['op']}_css_menu\"><ul>";
		
		foreach ( $this->pages as $id => $idv ) {
			$c = '';
			if ( isset( $_GET[ "{$this->_plugin['op']}_page" ] ) && $_GET[ "{$this->_plugin['op']}_page" ] == $id )
				$c = 'macti';

			printf( '<li class="%1$s"><a href="%2$s" title="%4$s" class="aa_css_menu_btn">%3$s</a></li>', $c, $this->pages[ $id ]['nonce'], $this->pages[ $id ]['name'], $this->pages[ $id ]['title'] );
		}
		
		echo '<li><a class="aa_css_menu_btn" title="WP Plugin Home" target="_blank" href="http://wordpress.org/extend/plugins/askapache-debug-viewer/">WP PLUGIN HOME</a></li></ul></div>';
		
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 25 );
	}

	/** AA_DEBUG::display_ini_error_configs()
	*/
	function display_ini_error_configs() {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 25 );
		

		echo '<div style="padding-left:10px; width:90%; margin:0 auto;">';
		
		// SHOW ERROR INFO
		$e = array();
		$eany = 0;
		foreach ( $this->get_error_levels( 0, 'defined' ) as $k => $v ) {
			$eany |= $e["$k"] = constant( $k );
		}
		/*
		echo '<ul style="margin-left:4em;">';
		foreach ( $this->get_error_levels(error_reporting(),'enabled') as $k => $v ) echo "<li><strong>{$v[1]} ({$v[0]})</strong>: {$v[2]}</li>";
		echo '</ul>';
		*/

		
		echo '<p><strong>PHP Error Handling Configuration (php.ini) Settings (current|original)</strong></p><ul style="margin-left:1em;">';
		
		$directives = array(
			'display_errors',
			'display_startup_errors',
			'log_errors',
			'log_errors_max_len',
			'ignore_repeated_errors',
			'ignore_repeated_source',
			'report_memleaks',
			'track_errors',
			'html_errors',
			'xmlrpc_errors',
			'xmlrpc_error_number',
			'docref_root',
			'docref_ext',
			'error_prepend_string',
			'error_append_string',
			'error_log'
		);
		
		foreach ( $directives as $k ) {
			$v1 = ini_get( $k );
			$v2 = get_cfg_var( $k );

			if ( empty( $v1 ) && empty( $v2 ) )
				continue;

			if ( $v1 != $v2 && ! empty( $v2 ) ) {
				$v2 = ' | <tt>' . $v2 . '</tt>';
			} else {
				$v2 = '';
			}
			printf( '<li><a href="http://php.net/manual/en/errorfunc.configuration.php#%1$s">%1$s</a>: <strong><tt>%2$s</tt></strong>%3$s</li>', $k, $v1, $v2 );
		}
		
		echo '</ul></div>';
		
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 25 );
	}

	/** AA_DEBUG::footer_output()
	 */
	function footer_output() {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		if ( is_admin() && $this->options['admin_footer'] != '1' ) {
			aadv_error_log( 'is_admin and admin_footer option is off, ending footer_output' );
			return;
		}

		if ( ! is_admin() && $this->options['wp_footer'] != '1' ) {
			aadv_error_log( '!is_admin and wp_footer option is off, ending footer_output' );
			return;
		}

		if ( ! is_admin() && ! $this->check_auth() )
			return;
		
		if ( is_admin() && ! $this->check_auth() )
			wp_die(__FUNCTION__ . ':' . __LINE__);


		
		echo "<div><br style=\"clear:both\" /></div><hr id='aahidehr' />";
		echo '<div id="aan" style="height:' . absint( $this->options['display_height'] ) . 'px;"><div id="aao">';


		
		foreach ( array_keys( $this->debug_mods ) as $k => $id ) {
			ob_start();
			if ( ( $this->options['debug_mods'] & $id ) == $id ) {
				$ar = $this->debug_mods[ $id ][1];
				$this->pptlinks( $ar );
				$st = sanitize_title_with_dashes( $ar );
				echo '<div id="tabs-' . $st . '"><a name="' . $st . '" id="' . $st . '"></a>';
				echo $this->{$this->debug_mods[ $id ][0]}( (bool) $this->options['verbose_modules'] ); //echo $this->{$this->debug_mods[$id][0]}( ($this->options['debug_mods_v'] & $id)==$id );
				echo "<p><br />\n\n\n\n</p></div><!--tabs-{$st}-->\n";
			}
			echo ob_get_clean();
		}

		echo $this->pptlinks( '', true );
		

		echo '</div><!--aao-->  ';

		
		echo '<a id="aatoggle" href=""></a><a id="aatoggle2" href=""></a></div><!--aan-->';

		echo '</div><!--wpwrap-->';

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
	}

	/** AA_DEBUG::live_debug()
	 */
	function live_debug() {
		if ( $this->options['debug_live'] !== '1' ) return;
		
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		static $started = false;
		
		


		/*
			register_shutdown_function(
				create_function('',
					'error_log("overwrite_inis '.error_log("overwrite_inis").'");
					'.(ob_start() && array_walk($this->old_inis,create_function('$a,$k', '$vv=strval(@ini_get($k ) ); if ($a!=$vv){@ini_set($k,$a);error_log("$a:@ini_set(\"$k\",\"$vv\");");};'))?ob_get_clean():ob_get_clean())
					)
				);
		*/
		
		if ( $started === true ) {
			// using ini_restore for now until i figure out how to make it more secure (from malicious ini settings another plugin might try)
			//error_log('restoring old_inis' );
			foreach ( (array) $this->old_inis as $key => $val )
				ini_restore( $key );

			return error_reporting( $this->old_inis['error_reporting'] );
		}


		/*
		static $inis=array(
			"session.auto_start" => "0",
			"safe_mode" => "0",
			"tidy.clean_output" => "0",
			"output_buffering" => "1",
			"xdebug.default_enable" => "0",
			"mbstring.func_overload" => "0",
			"html_errors" => "0", // Turn off HTML tags in error messages. The new format for HTML errors produces clickable messages that direct the user to a page describing the error or function in causing the error. These references are affected by docref_root and docref_ext.
			"magic_quotes_runtime" => "0",
			"magic_quotes_gpc" => "0",
			"ignore_repeated_errors" => "0", // Do not log repeated messages. Repeated errors must occur in the same file on the same line unless ignore_repeated_source is set true.
			"ignore_repeated_source" => "0", // Ignore source of message when ignoring repeated messages. When this setting is On you will not log errors with repeated messages from different files or sourcelines.
			"display_errors" => "0", //This determines whether errors should be printed to the screen as part of the output or if they should be hidden from the user.  Value "stderr" sends the errors to stderr instead of stdout. The value is available as of PHP 5.2.4. In earlier versions, this directive was of type boolean.   Although display_errors may be set at runtime (with ini_set()), it won't have any affect if the script has fatal errors. This is because the desired runtime action does not get executed.
			"display_startup_errors" => "0", //Even when display_errors is on, errors that occur during PHP's startup sequence are not displayed. It's strongly recommended to keep display_startup_errors off, except for debugging.
			"xmlrpc_errors" => "0",

			"file_uploads" => "1",
			"register_globals" => "1",
			"register_long_arrays" => "1",
			"register_argc_argv" => "1",
			"always_populate_raw_post_data" => "1",
			"report_memleaks" => "1", // If this parameter is set to Off, then memory leaks will not be shown (on stdout or in the log). This has only effect in a debug compile, and if error_reporting includes E_WARNING in the allowed list
			"log_errors" => "1", //Tells whether script error messages should be logged to the server's error log or error_log. This option is thus server-specific.
			"track_errors" => "1", // If enabled, the last error message will always be present in the variable $php_errormsg.

			"output_handler" => "",
			"open_basedir" => "",
			"disable_functions" => "",
			"error_prepend_string" => "", //String to output before an error message.
			"error_append_string" => "", //String to output after an error message.
			"auto_prepend_file" => "",
			"auto_append_file" => "",

			//"docref_root" => "", //The new error format contains a reference to a page describing the error or function causing the error. In case of manual pages you can download the manual in your language and set this ini directive to the URL of your local copy. If your local copy of the manual can be reached by "/manual/" you can simply use docref_root=/manual/. Additional you have to set docref_ext to match the fileextensions of your copy docref_ext=.html. It is possible to use external references. For example you can use docref_root=http://manual/en/ or docref_root="http://landonize.it/?how=url&theme=classic&filter=Landon &url=http%3A%2F%2Fwww.php.net%2F"  Most of the time you want the docref_root value to end with a slash "/". But see the second example above which does not have nor need it.
			//"docref_ext" => "", //The value of docref_ext must begin with a dot ".".
			"log_errors_max_len"=>"0", //    Set the maximum length of log_errors in bytes. In error_log information about the source is added. The default is 1024 and 0 allows to not apply any maximum length at all. This length is applied to logged errors, displayed errors and also to $php_errormsg.   When an integer is used, the value is measured in bytes. Shorthand notation may also be used.
			"xmlrpc_error_number" => "0", //Used as the value of the XML-RPC faultCode element.
			//"error_log" => "", // Name of the file where script errors should be logged. The file should be writable by the web server's user. If the special value syslog is used, the errors are sent to the system logger instead. On Unix, this means syslog(3) and on Windows NT it means the event log. The system logger is not supported on Windows 95. See also: syslog(). If this directive is not set, errors are sent to the SAPI error logger. For example, it is an error log in Apache or stderr in CLI.
			"request_order" => "GPCES"
			);
		*/



		

		//error_log('overwriting inis: BEGIN' );
		$ini_overwrites = array(
			//'precision' => '14',
			//'report_zend_debug' => 0,
			//'output_handler' => '',
			//'session.auto_start' => '0',
			//'zlib.output_compression' => 0,
			//'output_buffering' => 0,
			'open_basedir' => '',
			'tidy.clean_output' => 0,
			'xdebug.default_enable' => 0,
			'mbstring.func_overload' => 0,
			'error_prepend_string' => '',
			'error_append_string' => '',
			'auto_prepend_file' => '',
			'auto_append_file' => '',
			'disable_functions' => '',
			'safe_mode' => 0,
			'request_order' => 'GPCES',
			'register_globals' => 1,
			'register_long_arrays' => 1,
			'register_argc_argv' => 1,
			'always_populate_raw_post_data' => 1,
			'error_reporting' => $this->options['error_reporting'],
			'display_errors' => 0,
			'display_startup_errors' => 0,
			'log_errors' => $this->options['log_errors'],
			'html_errors' => 0,
			'track_errors' => 1,
			'report_memleaks' => 1,
			'magic_quotes_runtime' => 0,
			'magic_quotes_gpc' => 0,
			'ignore_repeated_errors' => 1,
			'ignore_repeated_source' => 1,
			'log_errors_max_len' => '0'
		);



				
		foreach ( $ini_overwrites as $key => $newval ) {
			$this->old_inis[ $key ] = ( $key == 'error_reporting' ? error_reporting() : strval( ini_get( $key ) ) );
			ini_set( $key, $newval );
		}
		//$this->SaveOptions();

		$started = true;
		return true;
	}

	/** AA_DEBUG::get_plugin_data()
	 *
	 * @param string $type
	 *
	 *  'plugin-name' => 'AskApache Debug Viewer', 
	 *  'short-name' => 'AA Debug', 
	 *  'description' => 'Displays Advanced Debugging Output', 
	 *  'author' => '<a href="http://www.askapache.com/" title="Visit author homepage">askapache,cduke250</a>', 
	 *  'version' => '2.4.1', 
	 *  'updated' => '12/20/2012 - 9:08 PM', 
	 *  'requires-at-least' => '3.1.0', 
	 *  'tested-up-to' => '3.5', 
	 *  'tags' => 'debug, debugging, error, errors, warning, problem, bug, problems, support, admin, programmer, developer, plugin, development, information, stats, logs, queries, htaccess, password, error, support, askapache, apache, rewrites', 
	 *  'contributors' => 'askapache,cduke250', 
	 *  'wordpress-uri' => 'http://wordpress.org/extend/plugins/askapache-debug-viewer/', 
	 *  'author-uri' => 'http://www.askapache.com/', 
	 *  'donate-uri' => 'http://www.askapache.com/donate/', 
	 *  'plugin-uri' => 'http://www.askapache.com/wordpress/debug-viewer-plugin.html', 
	 *  'role' => 'administrator', 
	 *  'capability' => 'askapache_debug_output', 
	 *  'file' => '~/htdocs/wp-content/plugins/askapache-debug-viewer/askapache-debug-viewer.php', 
	 *  'title' => '<a href="http://www.askapache.com/wordpress/debug-viewer-plugin.html" title="Visit plugin homepage">AskApache Debug Viewer</a>', 
	 *  'pb' => 'askapache-debug-viewer/askapache-debug-viewer.php', 
	 *  'page' => 'askapache-debug-viewer.php', 
	 *  'pagenice' => 'askapache-debug-viewer', 
	 *  'nonce' => 'form_askapache-debug-viewer', 
	 *  'hook' => 'settings_page_askapache-debug-viewer', 
	 *  'action' => 'options-general.php?page=askapache-debug-viewer.php', 
	 *  'op' => 'adv7', 
	 */
	function get_plugin_data($force=false,$type='settings') {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );

		$plugin = get_option( $this->_qn . '_plugin' );
		if ( $force === true || ! is_array( $plugin ) || !!! $plugin || ! array_key_exists( 'file', $plugin ) || "{$plugin['file']}" != __FILE__ ) {
			$data = $this->_readfile( __FILE__, 1450 );
			$mtx = $plugin = array();
			preg_match_all( '/[^a-z0-9]+((?:[a-z0-9]{2,25})(?:\ ?[a-z0-9]{2,25})?(?:\ ?[a-z0-9]{2,25})?)\:[\s\t]*(.+)/i', $data, $mtx, PREG_SET_ORDER );
			foreach ( $mtx as $m ) {
				$mm = trim( str_replace( ' ', '-', strtolower( $m[1] ) ) );
				$plugin[ $mm ] = str_replace( array( "\r", "\n", "\t" ), '', trim( $m[2] ) );
			}

			$plugin['file'] = __FILE__;
			$plugin['title'] = '<a href="' . $plugin['plugin-uri'] . '" title="Visit plugin homepage">' . $plugin['plugin-name'] . '</a>';
			$plugin['author'] = '<a href="' . $plugin['author-uri'] . '" title="Visit author homepage">' . $plugin['author'] . '</a>';
			$plugin['pb'] = preg_replace( '|^' . preg_quote( WP_PLUGIN_DIR, '|' ) . '/|', '', __FILE__ );
			$plugin['page'] = basename( __FILE__ );
			$plugin['pagenice'] = rtrim( $plugin['page'], '.php' );
			$plugin['nonce'] = 'form_' . $plugin['pagenice'];
			$plugin['hook'] = $type . '_page_' . $plugin['pagenice'];
			$plugin['action'] = ( ( $type == 'settings' ) ? 'options-general' : $type ) . '.php?page=' . $plugin['page'];
			$plugin['op'] = 'adv7';
		}
		
		if ( strpos( $plugin['short-name'], '<' . 'img' ) === false )
			$plugin['short-name'] = '<img src="' . plugins_url( 'f/icon-menu.png', __FILE__ ) . '" alt="" style="margin-top:3px;" />&nbsp;' . $plugin['short-name'];

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 10 );
		
		return $plugin;
	}
	
	/** AA_DEBUG::ff()
	 */
	function ff($args) {
		$defaults = array(
			'form'		=> 1,
			'type'		=> '',
			'id'		=> '',
			'name'		=> '',
			'value'		=> '',
			'title'		=> '',
			'pre'		=> '<p class="c4r">',
			'post'		=> '<br style="clear:both;" /></p>',
			'desc'		=> false,
			'class'		=> false,
			'checked' => false
		);
		$args = $this->_parse_args( $args, $defaults );
		
		if ( empty( $args['name'] ) )
			$args['name'] = $args['id'];
		
		$id = $this->_plugin['op'] . '_' . $args['id'];
		$name = $this->_plugin['op'] . '_' . $args['name'];
		$checked = ( $args['checked'] === true ? ' checked="checked"' : '' );
		$desc = strip_tags( $args['title'] );

		if ( $args['form'] == 6 ){
			$id = $args['id'];
			$name = $args['name'];
		}

		switch ( $args['form'] ) {
			case 1:
				echo $args['pre'];
				echo '<input title="' . $desc . '" name="' . $name . '" size="10" type="' . $args['type'] . '" id="' . $id 	. '" value="' . $args['value'] . '"' . $checked . ' />';
				echo '<label title="' . $desc . '" for="' . $id . '"> ' . $args['title'] . '</label>';
				echo $args['post'];
			break;
			
			case 2:
				echo $args['pre'];
				echo '<label title="' . $desc . '" for="' . $id . '"> ' . $args['title'] . ':</label>';
				echo '<input title="' . $desc . '" name="' . $id . '" type="' . $args['type'] . '" class="' . $args['class'] . '" id="' . $id . '" value="' . $args['value'] . '" />';
				echo $args['post'];
			break;
			
			case 3:
				echo '<p class="c4r">';
				echo '<label class="aa_label2" for="' . $id . '"><' . 'input type="checkbox" value="' . $args['value'] . '" id="' . $id . '" name="' . $name . '"' . $checked . ' /> ' . $args['title'] . '</label>';
			break;

			case 4:
				echo '<label class="aa_label1" for="' . $id . '"><' . 'input type="checkbox" value="' . $args['value'] . '" id="' . $id . '" name="' . $name . '"' . $checked . ' /> ' . $args['title'] . '</label>';
				echo '<br style="clear:both;" /></p>';
			break;

			case 5:
				echo $args['pre'] . '<input name="' . $name . '" class="' . $args['class'] . '" type="' . $args['type'] . '" id="' . $id . '" value="' . $args['value'] . '" />' . $args['post'];
			break;

			case 6:
				echo $args['pre'] . '<input name="' . $name . '" class="' . $args['class'] . '" type="' . $args['type'] . '" id="' . $id . '" value="' . $args['value'] . '" />' . $args['post'];
			break;

		} //switch
	}




	// GET DEBUG FUNCTIONS ----------------------------------------------------------------------------------------------------------------------------------------------------------------
	/** AA_DEBUG::get_debug_footerhelper()
	 */
	function get_debug_footerhelper($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		global $wp_query, $wp_object_cache, $wp, $wp_the_query, $user_email, $wp_admin_bar, $wpdb, $post, $post_ID, $user, $current_user, $_wp_theme_features, $template, $current_screen, $hook_suffix, $wp_importers;
		global $page_hook, $pagenow, $hook_suffix, $parent_file, $admin_body_class, $plugin_page, $self, $blog_id;


		/** WordPress Post Administration API */
		include_once( ABSPATH . 'wp-admin/includes/post.php' );
		include_once( ABSPATH . 'wp-admin/includes/theme.php' );

		$out = array();
		$out['DB'] = $out['wp_object_cache'] = $out['q'] = array();
		
		if ( is_object( $wp_the_query ) && is_array( $wp_the_query->query ) ) {
			foreach ( $wp_the_query->query as $k => $v ) {
				if ( ! empty( $v ) && strlen( $v ) > 0 ) {
					$out['q']['wp_the_query->query'][ $k ] = $v;
				}
			}
		}
		
		if ( is_object( $wp ) && is_array( $wp->query_vars ) ) {
			foreach ( $wp->query_vars as $k => $v ) {
				if ( ! empty( $v ) && strlen( $v ) > 0 ) {
					$out['q']['wp->query_vars'][ $k ] = $v;
				}
			}
		}
		
		$out['DB']['num_queries'] = $wpdb->num_queries;
		$out['DB']['num_rows'] = $wpdb->num_rows;
		$out['DB']['func_call'] = $wpdb->func_call;
		$out['DB']['last_query'] = $wpdb->last_query;
		
		// The amount of times the cache data was already stored in the cache.
		$out['wp_object_cache']['cache_hits'] = $wp_object_cache->cache_hits;
		
		// Amount of times the cache did not have the request in cache
		$out['wp_object_cache']['cache_misses'] = $wp_object_cache->cache_misses;
		
		$out['q']['wp->query_string'] = $wp->query_string;
		$out['q']['wp->request'] = $wp->request;
		$out['q']['wp->matched_rule'] = $wp->matched_rule;
		$out['q']['wp->matched_query'] = $wp->matched_query;

		
			
		if ( is_admin() ) {
			if ( $hook_suffix == 'post.php' ) {
				$d = $post;
				$out['POST'] = $d;
				
				$d = get_the_category( $post->ID);
				$out['get_the_category-id'] = $d;
			}

			$out['page_hook'] = $page_hook;
			$out['pagenow'] = $pagenow;
			$out['hook_suffix'] = $hook_suffix;
			$out['parent_file'] = $parent_file;
			$out['admin_body_class'] = $admin_body_class;
			$out['plugin_page'] = $plugin_page;
			$out['self'] = $self;
			if ( $blog_id > 1 ) $out['blog_id'] = $blog_id;

			$out['get_current_screen'] = get_current_screen();
			
			if ( is_singular() || $hook_suffix == 'post.php' ) {
				$d = has_meta( get_queried_object_id() );
				$out['has_meta'] = $d;
				
				$d = get_post_custom( get_queried_object_id() );
				$out['get_post_custom'] = $d;
				
				$d = get_meta_keys();
				$out['get_meta_keys'] = $d;
			}
	
	

		} else {
			
			$out['get_queried_object_id'] = $object_id = (int) get_queried_object_id(); 
			$out['get_queried_object'] = $object = get_queried_object(); 
			
			// clean post_content
			if ( isset( $out['object']->post_content ) && ! $vb ) {
				$out['object']->post_content = 'cleared by aa_debug for clean output';
			}
			
			
				
			if ( is_singular() ) {				
				$object_status = get_post_status( $object_id );
				if ( $object_status !== false && ! empty( $object_status ) && ! is_null( $object_status ) ) 
					$out['status'] = $object_status;
				
				$object_type = get_post_type( $object_id );
				if ( $object_type !== false && ! empty( $object_type ) && ! is_null( $object_type ) )
					$out['type'] = $object_type;
				
				$object_mime_type = get_post_mime_type( $object_id );
				if ( $object_mime_type !== false && ! empty( $object_mime_type ) && ! is_null( $object_mime_type ) )
					$out['mime_type'] = $object_mime_type;

				$object_format = get_post_format( $object );
				if ( $object_format !== false && ! empty( $object_format ) && ! is_null( $object_format ) )
					$out['format'] = $object_format;
			}
			
	
			
			/*
			$d=get_post_format_slugs(); $out['get_post_format_slugs'] = $d;
			$themes = get_themes();$theme = get_current_theme();$templates = $themes[$theme]['Template Files'];
			$d=$themes[$theme];$out['themes-theme'] = $d;			
			$d=get_theme_mods(); $out['THEME MODS'] = $d;
			*/
			
			
			if ( is_singular() ) {
				$d = has_meta( get_queried_object_id() );
				$out['has_meta'] = $d;
				
				$d = get_post_custom( get_queried_object_id() );
				$out['get_post_custom'] = $d;
				
				$d = get_meta_keys();
				$out['get_meta_keys'] = $d;
			}
	
		}

		
		
		//$out['get_all_user_settings']=get_all_user_settings();
		
		//$out["get_metadata#user#{$current_user->ID}"]=get_metadata("user",$current_user->ID);

		//echo $this->ed($out,'out' );

		//$this->ppt("current_theme_info()",current_theme_info( ) );
		//$this->ppt("THEME FEATURES",$_wp_theme_features);


		//error_reporting($olde);
		//$ret='<pre class="aa_pre2">'.htmlspecialchars(ob_get_clean())."\n\n\n\n\n".'</pre>';
		
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );	

		return $this->pp( $this->ed( $out, 'out' ) . "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n" );
		//return $ret;
	}

	/** AA_DEBUG::get_debug_wordpress()
	*/
	function get_debug_wordpress($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		global $wp_query, $wp_the_query, $wp_actions, $merged_filters, $wp, $wpdb;

		$oa=array();
		$oa['wp_query'] = $wp_query;
		$oa['wp_the_query'] = $wp_the_query;
		$oa['wp_actions'] = $wp_actions;
		$oa['merged_filters'] = $merged_filters;
		$oa['wp'] = $wp;
		$oa['wpdb'] = $wpdb;
		
		//$oa['current_user'] = $current_user;
		//$oa['user'] = $user;
		//$d=get_userdata( $user_ID );
		//$oa['userdata']= $d;
		//$oa['wp_roles'] = $wp_roles;
		
		//$oa['wp_rewrite'] = $wp_rewrite;
		//$this->pp($wp_taxonomies), 				//$this->ppt('current_screen',$current_screen),
		//$this->ppt('wp_user_roles',$wp_user_roles);
		//$this->pp($wp_filter);

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		$oa[] = "\n\n\n\n\n\n\n";
		return $this->pp( $oa, true );
	}

	/** AA_DEBUG::get_debug_globalprint()
	*/
	function get_debug_globalprint($vb=false) {
		return $this->pp($this->ed($GLOBALS,'GLOBALS')."\n\n\n\n\n");
	}

	/** AA_DEBUG::get_debug_globalvars()
	*/
	function get_debug_globalvars($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		global $authordata;
		$globalkeys = array_keys( $GLOBALS );
		sort( $globalkeys );
		$gkeys = array();

		ob_start();
		foreach ( $globalkeys as $k => $v ) {
			$val = $GLOBALS[ $v ];
			$gtype = gettype( $val );

			if ( $gtype == 'NULL' || ( in_array( $gtype, array( 'string', 'float', 'double', 'integer', 'array' ) ) && empty( $val ) ) )
				continue;

			$out = '$' . $v . ' (' . $gtype . ( in_array( $gtype, array( 'string', 'array' ) ) ? '' : ') ' );
			
			switch ( $gtype ) {
				case 'float':
				case 'double':
				case 'integer':
					$out .= '= ' . htmlspecialchars( $val );
					break;

				case 'boolean':
					$out .= '= ' . ( $val === true ? 'true' : 'false' );
					break;

				case 'resource':
					$out .= '= ' . get_resource_type( $val );
					break;

				case 'string':
					$out .= '#' . strlen( $val ) . ') = ' . htmlspecialchars( $val );
					break;

				case 'array':
					$out .= '#' . sizeof( $val ) . ') => ';
				break;

				case 'object':
					$cn = get_class( $val );
					$cpn = get_parent_class( $val );
					$out .= ' (' . ( ! empty( $cn ) ? "class:{$cn}" : '' ) . ( ! empty( $cpn ) ? " | parent:{$cpn}" : '' ) . ')';
				break;
			}
			
			echo  $out . "\n";
		}
		$out = ob_get_clean();

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		return '<pre class="aa_pre2" style="height:' . absint( $this->options['display_height'] ) . 'px;">'  .$out . "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n" . '</pre>';
	}

	/** AA_DEBUG::get_debug_queries()
	*/
	function get_debug_queries($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		global $wpdb;

		$mesg = ( ! defined( 'SAVEQUERIES' ) || ! SAVEQUERIES ) ? '<p><code>define(\'SAVEQUERIES\', true);</code>in your <code>wp-config.php</code></p>' : '';
		
		$oa = '';
		if ( $wpdb->queries ) {
			$total_time = ( timer_stop( false, 22 ) + 1 );
			$total_query_time = 0;

			foreach ( $wpdb->queries as $q ) {
				$q[0] = trim( ereg_replace( '[[:space:]]+', ' ', $q[0] ) );
				if ( isset( $q[1]) ) {
					$oa .= $q[0] . "\n";
					$total_query_time += $q[1];
				}
				if ( $vb && isset( $q[2] ) )
					$oa .= "\t" . $q[2] . "\n";
			}

		}
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		$oa .= "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n";
		return $this->pp( $oa, true );
	}

	/** AA_DEBUG::get_debug_sockets()
	*/
	function get_debug_sockets($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		$fp = null;
		$response = $errstr = '';
		$errno = 0;

		ob_start();
		// resource fsockopen ( string $hostname [, int $port = -1 [, int &$errno [, string &$errstr [, float $timeout = ini_get("default_socket_timeout") ]]]] )
		if ( false === ( $fp = fsockopen( $_SERVER['HTTP_HOST'], $_SERVER['SERVER_PORT'], $errno, $errstr, 5 ) ) || ! is_resource( $fp ) ) {
			echo $this->socket_error( $fp, (int) $errno, $errstr );
		} else {
			$request = join( CRLF, array(
								 'GET / HTTP/1.0',
								 'Host: ' . $_SERVER['HTTP_HOST'],
								 'User-Agent: Mozilla/5.0 (AskApache/; +http://www.askapache.com/)',
								 'Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,*/*;q=0.5',
								 'Accept-Encoding: none',
								 'Referer: http://www.askapache.com/'
								 ) ) . CRLF . CRLF;
			fwrite( $fp, $request, strlen( $request ) );

			if ( is_resource( $fp ) )
				echo $this->_sockdebug( $fp );


			while ( ! feof( $fp ) )
				$response .= fread( $fp, 1 );


			echo "\n===================================================================\n" . $request . "\n\n";
			if ( $vb ) echo $response  ."\n===================================================================\n";

			if ( is_resource( $fp ) )
				fclose( $fp );

	 	}

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		return $this->pp( ob_get_clean() . "\n\n\n", true );
	}

	/** AA_DEBUG::get_debug_rewrites()
	 *
	 * @param mixed $vb
	 */
	function get_debug_rewrites($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		global $is_apache, $wp_rewrite, $wp_query,$wp,$wp_the_query;

		function handle_results($incoming) {
			global $wp_rewrite;
			$oa=array();
			static $rewrite_methods=false;
			if (!$rewrite_methods) $rewrite_methods=get_class_methods( 'WP_Rewrite' );
			foreach ((array)$incoming as $k) :
				if ( in_array($k, $rewrite_methods) )$v=$wp_rewrite->{$k}();
				elseif ( function_exists($k) ) $v=$k();
				else continue;

				if ($k=='mod_rewrite_rules')$v=explode("\n",$v);
				if (is_bool($v))	$oa[$k]=(($v===true) ? 'TRUE' : 'FALSE' );
				elseif (is_string($v))$oa[$k] = $v;
				elseif (is_array($v) && sizeof($v)>0)
				{
					$vo='';
					foreach ( $v as $vv) {
						if (is_array($vv)) foreach ( $vv as $vvv) $vo.="{$vvv}\n";
						else $vo.="{$vv}\n";
					}
					$oa[$k] = $vo;
				}

			endforeach;

			return $oa;
		}

		
		flush_rewrite_rules();

		$vars=array(
			'use_trailing_slashes' => 'Whether to add trailing slashes.',
			'use_verbose_rules' => 'Whether to write every mod_rewrite rule for WP. This is off by default',
			'use_verbose_page_rules' => 'Whether to write every mod_rewrite rule for WP pages.',
			'permalink_structure' => 'Default permalink structure for WP.',
			'category_base' => 'Customized or default category permalink base ( askapache.com/xx/tagname ).',
			'tag_base' => 'Customized or default tag permalink base ( askapache.com/xx/tagname ).',
			'category_structure' => 'Permalink request structure for categories.',
			'tag_structure' => 'Permalink request structure for tags.',
			'author_base' => 'Permalink author request base ( askapache.com/author/authorname ).',
			'author_structure' => 'Permalink request structure for author pages.',
			'date_structure' => 'Permalink request structure for dates.',
			'page_structure' => 'Permalink request structure for pages.',
			'search_base' => 'Search permalink base ( askapache.com/search/query ).',
			'search_structure' => 'Permalink request structure for searches.',
			'comments_base' => 'Comments permalink base.',
			'feed_base' => 'Feed permalink base.',
			'comments_feed_structure' => 'Comments feed request structure permalink.',
			'feed_structure' => 'Feed request structure permalink.',
			'front' => 'Front URL path. If permalinks are turned off. The WP/index.php will be the front portion. If permalinks are turned on',
			'root' => 'Root URL path to WP (without domain). The difference between front property is that WP might be located at askapache.com/WP/. The root is the WP/ portion.',
			'index' => 'Permalink to the home page.',
			'matches' => 'Request match string.',
			'rules' => 'Rewrite rules to match against the request to find the redirect or query.',
			'extra_rules' => 'Additional rules added external to the rewrite class.',
			'extra_rules_top' => 'Additional rules that belong at the beginning to match first.',
			'non_wp_rules' => 'Rules that don\'t redirect to WP\'s index.php. These rules are written to the mod_rewrite portion of the .htaccess.',
			'extra_permastructs' => 'Extra permalink structures.',
			'endpoints' => 'Endpoints permalinks',
			'rewritecode' => 'Permalink structure search for preg_replace.',
			'rewritereplace' => 'Preg_replace values for the search.',
			'queryreplace' => 'Search for the query to look for replacing.',
			'feeds' => 'Supported default feeds.'
		);

		$keys = array_keys( get_object_vars( $wp_rewrite ) );
		foreach ( $keys as $k ) {
			if ( ! isset( $wp_rewrite->$k ) ) continue;
			
			$d = ( array_key_exists( $k, $vars ) ? $vars[ $k ] : '' );
			$v = $wp_rewrite->$k;
			$vv = '';
				
			
			if ( is_bool( $v ) ) {
				$vv = ( $v ? 'TRUE' : 'FALSE' ) . "   // {$d}";
			} elseif ( is_string( $v ) ) {
				if ( strlen( trim( $v ) ) > 0 ) $vv = "'" . $v . "'" . "   // {$d}";
			} elseif ( is_array( $v ) ) {
				if ( count( $v ) > 0 ) $vv = "// {$d}\n" . $this->rvar_export( $v );
			} elseif ( is_object( $v ) ) {
				$vv = "// {$d}\n" . $this->rvar_export( $v );
			} elseif ( is_numeric( $v ) ) {
				$vv = "'" . $v . "'" . "   // {$d}";
			} else {
				$vv = gettype( $v ) . "   // {$d}";
			}
			
			if ( ! empty( $vv ) ) $oa['rewrite_vars'][$k] = $vv;
	
		}
		

		// robots.txt
		$robots_rewrite = array( 'robots\.txt$' => $wp_rewrite->index . '?robots=1' );

		//Default Feed rules - These are require to allow for the direct access files to work with permalink structure starting with %category%
		$default_feeds = array(
			'.*wp-atom.php$'			=>	$wp_rewrite->index . '?feed=atom',
			'.*wp-rdf.php$'				=>	$wp_rewrite->index . '?feed=rdf',
			'.*wp-rss.php$'				=>	$wp_rewrite->index . '?feed=rss',
			'.*wp-rss2.php$'			=>	$wp_rewrite->index . '?feed=rss2',
			'.*wp-feed.php$'			=>	$wp_rewrite->index . '?feed=feed',
			'.*wp-commentsrss2.php$'	=>	$wp_rewrite->index . '?feed=rss2&withcomments=1'
		);

		// Post
		$post_rewrite = $wp_rewrite->generate_rewrite_rules($wp_rewrite->permalink_structure, EP_PERMALINK);
		$post_rewrite = apply_filters('post_rewrite_rules', $post_rewrite);

		// Date
		$date_rewrite = $wp_rewrite->generate_rewrite_rules($wp_rewrite->get_date_permastruct(), EP_DATE);
		$date_rewrite = apply_filters('date_rewrite_rules', $date_rewrite);

		// Root
		$root_rewrite = $wp_rewrite->generate_rewrite_rules($wp_rewrite->root . '/', EP_ROOT);
		$root_rewrite = apply_filters('root_rewrite_rules', $root_rewrite);

		// Comments
		$comments_rewrite = $wp_rewrite->generate_rewrite_rules($wp_rewrite->root . $wp_rewrite->comments_base, EP_COMMENTS, true, true, true, false);
		$comments_rewrite = apply_filters('comments_rewrite_rules', $comments_rewrite);

		// Search
		$search_structure = $wp_rewrite->get_search_permastruct();
		$search_rewrite = $wp_rewrite->generate_rewrite_rules($search_structure, EP_SEARCH);
		$search_rewrite = apply_filters('search_rewrite_rules', $search_rewrite);

		// Categories
		$category_rewrite = $wp_rewrite->generate_rewrite_rules($wp_rewrite->get_category_permastruct(), EP_CATEGORIES);
		$category_rewrite = apply_filters('category_rewrite_rules', $category_rewrite);

		// Tags
		$tag_rewrite = $wp_rewrite->generate_rewrite_rules($wp_rewrite->get_tag_permastruct(), EP_TAGS);
		$tag_rewrite = apply_filters('tag_rewrite_rules', $tag_rewrite);

		// Authors
		$author_rewrite = $wp_rewrite->generate_rewrite_rules($wp_rewrite->get_author_permastruct(), EP_AUTHORS);
		$author_rewrite = apply_filters('author_rewrite_rules', $author_rewrite);

		// Pages
		$page_rewrite = $wp_rewrite->page_rewrite_rules();
		$page_rewrite = apply_filters('page_rewrite_rules', $page_rewrite);

		$oa['extra_rules_top'] = $wp_rewrite->extra_rules_top;
		$oa['robots_rewrite'] = $robots_rewrite;
		$oa['default_feeds'] = $default_feeds;
		$oa['page_rewrite'] = $page_rewrite;
		$oa['root_rewrite'] = $root_rewrite;
		$oa['comments_rewrite'] = $comments_rewrite;
		$oa['search_rewrite'] = $search_rewrite;
		$oa['category_rewrite'] = $category_rewrite;
		$oa['tag_rewrite'] = $tag_rewrite;
		$oa['author_rewrite'] = $author_rewrite;
		$oa['date_rewrite'] = $date_rewrite;
		$oa['post_rewrite'] = $post_rewrite;
		$oa['extra_rules'] = $wp_rewrite->extra_rules;
		$oa['Permastructs'] = handle_results( array( 'get_date_permastruct', 'get_year_permastruct', 'get_month_permastruct', 'get_day_permastruct', 'get_category_permastruct', 'get_tag_permastruct', 'get_author_permastruct', 'get_search_permastruct', 'get_page_permastruct', 'get_feed_permastruct', 'get_comment_feed_permastruct' ) );
		$oa['Rewrite Rules'] = handle_results( array( 'wp_rewrite_rules', 'mod_rewrite_rules' ) );

		if ( $vb ) $oa['page_generated'] = handle_results( array( 'page_uri_index', 'page_rewrite_rules' ) );
		
		
	
		ob_start();

		$rewrite='(';
		foreach (get_terms( 'category', array( 'get' => 'all')) as $k){
			$rewrite.=$k->slug."|";
		}
		$rewrite.=')';
		$rewrite=str_replace('|)', ')',$rewrite);
		//echo $rewrite;

		print_r( $oa);

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		return $this->pp(ob_get_clean()."\n\n\n",true);
	}

	/** AA_DEBUG::get_debug_interfaces()
	 *
	 * @param mixed $vb
	 */
	function get_debug_interfaces($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		$ret = @get_declared_interfaces();

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		$ret[] = "\n\n\n\n\n";
		return $this->pp( $ret, true );
	}

	/** AA_DEBUG::get_debug_extensions()
	 *
	 * @param mixed $vb
	 */
	function get_debug_extensions($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		$ret = array();
		$ret[] = @get_loaded_extensions();
		if ( $vb ) {
			foreach ( (array) @get_loaded_extensions() as $v ) {
				$ret[ $v ] = array();
				$ext = new ReflectionExtension( $v );
				
				foreach ( (array) $ext->getINIEntries() as $kk => $vv ) {
					$ret[ $v ]['inis'][ $kk ] = $vv;
				}
	
				$ret[ $v ]['functions'] = array_keys( $ext->getFunctions() );
				$ret[ $v ]['constants'] = $ext->getConstants();
				//$ret[$v]['info'] = $ext->info();
			}
		}

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		$ret[] = "\n\n\n\n\n";
		return $this->pp( $ret, true );
	}

	/** AA_DEBUG::get_debug_functions()
	 *
	 * @version 1.5
	 * @param mixed $vb
	 */
	function get_debug_functions($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		$defined_funcs = @get_defined_functions();
		if ( ! $vb ) {
			$out = $defined_funcs['user'];
		} else {
			$out = array();
			//$out[] = $defined_funcs['user'];
			foreach ( $defined_funcs['user'] as $v ) {
				$ext = new ReflectionFunction( $v );
				$out[ $v ] = $ext->getFileName() . ':' . $ext->getStartLine() . '  Params:' . $ext->getNumberOfParameters();
			}
		}
		
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		$out[] = "\n\n\n\n\n";
		return $this->pp( $out, true );
	}

	/** AA_DEBUG::get_debug_posix()
	 *
	 * @param mixed $vb
	 */
	function get_debug_posix($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		$oa = array();
		
		$posix_vars = array(
			'PHP script Process ID' => 'getmypid',
			'PHP script owners UID' => 'getmyuid',
			'php_sapi_name' => 'php_sapi_name',
			'PHP Uname' => 'php_uname',
			'Zend Version' => 'zend_version',
			'PHP INI Loaded' => 'php_ini_loaded_file',
			'Current Working Directory' => 'getcwd',
			'Last Mod' => 'getlastmod',
			'Script Inode' => 'getmyinode',
			'Script GID' => 'getmygid',
			'Script Owner' => 'get_current_user',
			'Get Rusage' => 'getrusage',
			'Error Reporting' => 'error_reporting',
			'Path name of controlling terminal' => 'posix_ctermid',
			'Error number set by the last posix function that failed' => 'posix_get_last_error',
			'Pathname of current directory' => 'posix_getcwd',
			'posix_getpid' => 'posix_getpid',
			'posix_uname' => 'posix_uname',
			'posix_times' => 'posix_times',
			'posix_errno' => 'posix_errno',
			'Effective group ID of the current process' => 'posix_getegid',
			'Effective user ID of the current process' => 'posix_geteuid',
			'Real group ID of the current process' => 'posix_getgid',
			'Group set of the current process' => 'posix_getgroups',
			'Login name' => 'posix_getlogin',
			'Current process group identifier' => 'posix_getpgrp',
			'Current process identifier' => 'posix_getpid',
			'Parent process identifier' => 'posix_getppid',
			'System Resource limits' => 'posix_getrlimit',
			'Return the real user ID of the current process' => 'posix_getuid',
			'Magic Quotes GPC' => 'get_magic_quotes_gpc',
			'Magic Quotes Runtime' => 'get_magic_quotes_runtime'
		);
		
		foreach ( $posix_vars as $t => $fn ) {
			if ( $this->_cf( $fn ) ) {
				$oa[ $t ] = $fn();
			}
		}
		

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		$oa[] = "\n\n\n\n\n\n\n";
		return $this->pp( $oa, true );
	}

	/** AA_DEBUG::get_debug_included()
	 *
	 * @param mixed $vb
	 */
	function get_debug_included($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		$oa = array();
		$oa[] = $included = ( $this->_cf( 'get_included_files' ) ? get_included_files() : array() );
		
		foreach ( $included as $k => $v ) {
			$oa[ $v ] = ( $vb === false ) ? '' : $this->_statls( $v );
		}

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		$oa[] = "\n\n\n\n\n\n\n";
		return $this->pp( $oa, true );
	}

	/** AA_DEBUG::get_debug_permissions()
	 *
	 * @param mixed $vb
	 */
	function get_debug_permissions($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		$oa = array();

		$perm_stuff = array(
			'Real Group ID' => ( $this->_cf( 'posix_getgid' ) ) ? posix_getgid() : '',
			'Effective Group ID' => ( $this->_cf( 'posix_getegid' ) ) ? posix_getegid() : '',
			'Parent Process ID' => ( $this->_cf( 'posix_getppid' ) ) ? posix_getppid() : '',
			'Parent Process Group ID' => ( $this->_cf( 'posix_getpgid' ) && $this->_cf( 'posix_getppid' ) ) ? posix_getpgid( posix_getppid() ) : '',
			'Real Process ID' => ( $this->_cf( 'posix_getpid' ) ) ? posix_getpid() : '',
			'Real Process Group ID' => ( $this->_cf( 'posix_getpgid' ) && $this->_cf( 'posix_getpid' ) ) ? posix_getpgid( posix_getpid() ) : '',
			'Process Effective User ID' => ( $this->_cf( 'posix_geteuid' ) ) ? posix_geteuid() : '',
			'Process Owner Username' => $this->get_posix_info( 'user', '', 'name' ),
			'File Owner Username' => ( $this->_cf( 'get_current_user' ) ) ? get_current_user() : '',
			'User Info' => print_r( $this->get_posix_info( 'user' ), 1 ),
			'Group Info' => print_r( $this->get_posix_info( 'group' ), 1 ),
			'RealPath' => realpath( __FILE__ ),
			'SAPI Name' => ( $this->_cf( 'php_sapi_name' ) ) ? print_r( php_sapi_name(), 1 ) : '',
			'Posix Process Owner' => ( $this->_cf( 'posix_getpwuid' ) && $this->_cf( 'posix_geteuid' ) ) ? print_r( posix_getpwuid( posix_geteuid() ), 1 ) : '',
			'Scanned Ini' => ( $this->_cf( 'php_ini_scanned_files' ) ) ? str_replace( "\n", '', php_ini_scanned_files() ) : '',
			'PHP.ini Path' => ( $this->_cf( 'get_cfg_var' ) ) ? get_cfg_var( 'cfg_file_path' ) : '',
			'Sendmail Path' => ( $this->_cf( 'get_cfg_var' ) ) ? get_cfg_var( 'sendmail_path' ) : '',
			'Info about a group by group id' => ( $this->_cf( 'posix_getgrgid' ) && $this->_cf( 'posix_getegid' ) ) ? posix_getgrgid( posix_getegid() ) : '',
			'Process group id for Current process' => ( $this->_cf( 'posix_getgrgid' ) && $this->_cf( 'posix_getpid' ) ) ? posix_getpgid( posix_getpid() ) : '',
			'Process group id for Parent process' => ( $this->_cf( 'posix_getpgid' ) && $this->_cf( 'posix_getppid' ) ) ? posix_getpgid( posix_getppid() ) : '',
			'Process group id of the session leader.' => ( $this->_cf( 'posix_getsid' ) && $this->_cf( 'posix_getpid' ) ) ? posix_getsid( posix_getpid() ) : '',
			'Info about a user by username' => ( $this->_cf( 'posix_getpwnam' ) && $this->_cf( 'get_current_user' ) ) ? posix_getpwnam( get_current_user() ) : '',
			'Info about a user by user id' => ( $this->_cf( 'posix_getpwuid' ) && $this->_cf( 'posix_geteuid' ) ) ? posix_getpwuid( posix_geteuid() ) : '',
			'Apache Version' => ( $this->_cf( 'apache_get_version' ) ) ? print_r( apache_get_version(), 1 ) : '',
			'Apache Modules' => ( $this->_cf( 'apache_get_modules' ) ) ? print_r( apache_get_modules(), 1 ) : '',
			'PHP_LOGO_GUI' => ( $this->_cf( 'php_logo_guid' ) ) ? php_logo_guid() : '',
			'ZEND_LOGO_GUI' => ( $this->_cf( 'zend_logo_guid' ) ) ? zend_logo_guid() : ''
		);

		foreach ( $perm_stuff as $t => $v ) {
			$oa[ $t ] = $v;
		}

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		$oa[] = "\n\n\n\n\n\n\n";
		return $this->pp( array( $oa ), true );
	}

	/** AA_DEBUG::get_debug_classes()
	 *
	 * @version 1.1
	 * @param mixed $vb
	 */
	function get_debug_classes($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		$classes = $oa = array();

		$oa[] = $classes = (array) ( $this->_cf( 'get_declared_classes' ) ? get_declared_classes() : array() );


		foreach ( $classes as $k ) {
			if ( $vb === false ) $oa[] = $k;
			else {
				$out = array( 'methods' => array(), 'vars' => array() );
				$methods = get_class_methods( "{$k}" );
				$vars = get_class_vars( "{$k}" );

				if ( sizeof( $methods ) > 0 ) $out['methods'] = $methods;
				else unset( $out['methods'] );

				if ( sizeof( $vars ) > 0 ) $out['vars'] = $vars;
				else unset( $out['vars'] );

				if ( isset( $out['methods'] ) || isset( $out['vars'] ) ) $oa[ $k ] = $out;
				else $oa[ $k ]='';
			}
		}


		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		$oa[] = "\n\n\n\n\n\n\n";
		return $this->pp( $oa, true );
	}

	/** AA_DEBUG::get_debug_request()
	 *
	 * @param mixed $vb
	 */
	function get_debug_request($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		global $_GET, $_POST, $_COOKIE, $_SESSION, $_ENV, $_FILES, $_SERVER, $_REQUEST, $HTTP_POST_FILES, $HTTP_POST_VARS;
		global $HTTP_SERVER_VARS, $HTTP_RAW_POST_DATA, $HTTP_GET_VARS, $HTTP_COOKIE_VARS, $HTTP_ENV_VARS;

		$oa = array();		
		$req_vars = array( 
			'GET' => $_GET,
			'POST' => $_POST,
			'COOKIE' => $_COOKIE,
			'SESSION' => $_SESSION,
			'ENV' => $_ENV,
			'FILES' => $_FILES,
			'SERVER' => $_SERVER,
			'REQUEST' => $_REQUEST,
			'HTTP_POST_FILES' => $HTTP_POST_FILES,
			'HTTP_POST_VARS' => $HTTP_POST_VARS,
			'HTTP_SERVER_VARS' => $HTTP_SERVER_VARS,
			'HTTP_RAW_POST_DATA' => $HTTP_RAW_POST_DATA,
			'HTTP_GET_VARS' => $HTTP_GET_VARS,
			'HTTP_COOKIE_VARS' => $HTTP_COOKIE_VARS,
			'HTTP_ENV_VARS' => $HTTP_ENV_VARS,
		);

		foreach ( $req_vars as $k => $v ) {
			if ( is_array( $v ) && sizeof( $v ) > 0 ) {
				$oa[ $k ] = $v;
			}
		}

		foreach ( array_keys( $_SERVER ) as $k ) {
			$v = strval( $_SERVER[ $k ] );
			if ( ! empty( $v ) ) {
				$oa[ ( substr( $k, 0, 5 ) == 'HTTP_' ? 'HTTP' : 'SERVER' ) ][ $k ] = $_SERVER[ $k ];
			}
		}

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		$oa[] = "\n\n\n\n\n\n\n";
		return $this->pp( $oa, true );
	}

	/** AA_DEBUG::get_debug_defined()
	 *
	 * @param mixed $vb
	 */
	function get_debug_defined($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		$oa = array();

		$constants = (array) ( $this->_cf( 'get_defined_constants' ) ? ( version_compare( PHP_VERSION, '5.0.0', 'gte' ) ? get_defined_constants( true ) : get_defined_constants() ) : array() );
		$pos1 = array_search( 'ABSPATH', array_keys( $constants ) );

		if ( ! $vb ) {
			$constants = array_slice( $constants, ( $pos1 - 10 ) );
		}
		
		foreach ( $constants as $k => $v ) {
			$oa[ $k ] = $v;
		}

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		$oa[] = "\n\n\n\n\n\n\n";
		return $this->pp( $oa, true );
	}

	/** AA_DEBUG::get_debug_inis()
	 *
	 * @param mixed $vb
	 *  On, Off, True, False, Yes, No and None
	 *  Expressions in the INI file are limited to bitwise operators and parentheses:
	 *  |  bitwise OR
	 *  ^  bitwise XOR
	 *  &  bitwise AND
	 *  ~  bitwise NOT
	 *  !  boolean NOT
	 *  Boolean flags can be turned on using the values 1, On, True or Yes.
	 *  They can be turned off using the values 0, Off, False or No.
	 *  An empty string can be denoted by simply not writing anything after the equal sign, or by using the None keyword:
	 */
	function get_debug_inis($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		$oa = array();

		$debug_inis = array(
			'Error Log' => 'error_log',
			'Session Data Path' => 'session.save_path',
			'Upload Tmp Dir' => 'upload_tmp_dir',
			'Include Path' => 'include_path',
			'Memory Limit' => 'memory_limit',
			'Max Execution Time' => 'max_execution_time',
			'Display Errors' => 'display_errors',
			'Allow url fopen' => 'allow_url_fopen',
			'Disabled Functions' => 'disable_functions',
			'Safe Mode' => 'safe_mode',
			'Open Basedir' => 'open_basedir',
			'File Uploads' => 'file_uploads',
			'Max Upload Filesize' => 'upload_max_filesize',
			'Max POST Size' => 'post_max_size',
			'Open Basedir' => 'open_basedir'
		);

		if ( $this->_cf( 'ini_get' ) ) {
			foreach ( $debug_inis as $t => $n ) {
				$oa[ $n ] = strval( ini_get( $n ) );
			}
		}


		if ( $vb && $this->_cf( 'ini_get_all' ) ) {
			foreach ( (array) ini_get_all() as $k => $v ) {
				$oa[ $k ] = ( ( $v['global_value'] == $v['local_value'] ) ? $v['global_value'] : $v );
			}
		}

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		$oa[] = "\n\n\n\n\n\n\n";
		return $this->pp( $oa, true );
	}

	/** AA_DEBUG::get_debug_phpinfo()
	 */
	function get_debug_phpinfo($type=1) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		$oa = array();
		$sr = array(
			'#^.*<body>(.*)</body>.*$#ms' => '$1',
			'#<h2>PHP License</h2>.*$#ms' => '',
			'#<h1>Configuration</h1>#' => '',
			"#\r?\n#" => '',
			"#</(h1|h2|h3|tr)>#" => '<$1>' . "\n",
			'# +<#' => '<',
			"#[ \t]+#" => ' ',
			'#&nbsp;#' => ' ',
			'#  +#' => ' ',
			'# class=".*?"#' => '',
			'%&#039;%' => ' ',
			'#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a><h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#' => '<h2>PHP Configuration</h2>' . "\n" . '<tr><td>PHP Version</td><td>$2</td></tr>' . "\n" . '<tr><td>PHP Egg</td><td>$1</td></tr>',
			'#<h1><a href="(?:.*?)?=(.*?)">PHP Credits</a></h1>#' => '<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
			'#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#' => '<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" . '<tr><td>Zend Egg</td><td>$1</td></tr>',
			"#	+#",'#<tr>#' => '%S%',
			'#</tr>#' =>'%E%' 
		);

		if ( $this->_cf( 'phpinfo' ) ) {
			ob_start();
			phpinfo( -1 );


			if ( $type != 0 ) {
				$oa = preg_replace( array_keys( $sr ), array_values( $sr ), ob_get_clean() );
				$sections = explode( '<h2>', strip_tags( $oa, '<h2><th><td>' ) );
				unset( $sections[0] );

				$oa = array();
				foreach ( $sections as $s ) {
					preg_match_all( '#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#', $s, $askapache, PREG_SET_ORDER );
					foreach ( $askapache as $m ) {
						$oa[ ( substr( $s, 0, strpos( $s, '</h2>' ) ) ) ][ $m[1] ] = ( ! isset( $m[3] ) || $m[2] == $m[3] ) ? ( isset( $m[2] ) ? $m[2] : '' ) : array_slice( $m, 2 );
					}
				}
			} else {
				$oa = preg_replace( array( '#^.*<body>(.*)</body>.*$#ms', '#width="600"#' ), array( '$1', 'width="95%"' ), ob_get_clean() );
			}

		}

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		return $oa;
	}

	/** AA_DEBUG::get_debug_options()
	 */
	function get_debug_options($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		$o = wp_cache_get( 'alloptions', 'options' );

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		return $this->pp( $this->ed( $o, 'o' ) . "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n" );
	}

	/** AA_DEBUG::get_debug_aa_plugin( $vb = false )
	 *
	 * @version 1.5
	 * @param mixed $vb
	 */
	function get_debug_aa_plugin($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		$oa=array(
			'options' => $this->options,
			'plugin' => $this->_plugin,
			'actions' => $this->actions,
			'pages' => $this->pages,
			'debug_mods' => $this->debug_mods,
			'old_inis' => $this->old_inis
		);
		
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		$oa[] = "\n\n\n\n\n\n\n";
		return $this->pp( $oa, true );
	}

	/** AA_DEBUG::get_debug_templates()
	 */
	function get_debug_templates($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		/** WordPress Post Administration API */
		require_once( ABSPATH . 'wp-admin/includes/post.php' );
		require_once( ABSPATH . 'wp-admin/includes/theme.php' );

		
		$o = array();
		$o['INDEX_TEMPLATE'] = get_index_template();
		$o['AUTHOR_TEMPLATE'] = get_author_template();
		$o['404_TEMPLATE'] = get_404_template();
		$o['ARCHIVE_TEMPLATE'] = get_archive_template();
		$o['CATEGORY_TEMPLATE'] = get_category_template();
		$o['TAG_TEMPLATE'] = get_tag_template();
		$o['TAXONOMY_TEMPLATE'] = get_taxonomy_template();
		$o['DATE_TEMPLATE'] = get_date_template();
		$o['HOME_TEMPLATE'] = get_home_template();
		$o['FRONT_PAGE_TEMPLATE'] = get_front_page_template();
		$o['PAGE_TEMPLATE'] = get_page_template();
		$o['PAGED_TEMPLATE'] = get_paged_template();
		$o['SEARCH_TEMPLATE'] = get_search_template();
		$o['SINGLE_TEMPLATE'] = get_single_template();
		$o['PAGE_TEMPLATE'] = get_page_template();
		$o['ATTACHMENT_TEMPLATE'] = get_attachment_template();
		
		foreach ( $o as $k => $v ) {
			if ( empty( $v ) ) {
				unset( $o[ $k ] );
			}
		}

		$o['wp_upload_dir'] = wp_upload_dir();


		$gpt = get_page_templates();
		$o['templates'] = array_flip( $gpt );
		
		$o['get_theme_data'] = get_theme_data( TEMPLATEPATH . '/style.css' );
		$o['get_template'] = get_template();
		//$o[' '] = "\n\n\n\n\n\n\n";


			//$d = get_page_templates();
			//$out['get_page_templates'] = $d;

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		
		return $this->pp( $this->ed( $o, 'o' ) . "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n" );
	}

	/** AA_DEBUG::get_debug_crons( $vb = false )
	 *
	 * @version 2.3.3
	 * @param mixed $vb
	 */
	function get_debug_crons($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		$oa = array(
			'schedules' => wp_get_schedules(),
			'crons' => _get_cron_array(),
			' ' => "\n\n\n\n\n"
		);

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		$oa[] = "\n\n\n\n\n\n\n";
		return $this->pp( $oa, true );
	}

	/** AA_DEBUG::get_debug_plugins()
	 *
	 * @since 2.6
	 * @version 1.0
	 *
	 * @param mixed $vb
	 */
	function get_debug_plugins($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		$all_plugins = get_plugins();

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		$all_plugins[]="\n\n\n\n\n";
		return $this->pp( $all_plugins, true );
	}

	/** AA_DEBUG::get_debug_filters()
	 *
	 * @since 2.6
	 * @version 1.0
	 *
	 * @param mixed $vb
	 */
	function get_debug_filters($vb=false) {
		global $wp_filter, $wp_actions;
		
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		$ret = array(
			'wp_filters' => array_keys( $wp_filter ),
			'wp_actions' => $wp_actions,
			 'wp_filter' => $wp_filter,
			' ' => "\n\n\n\n\n\n\n\n"
		);
		
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		$ret[] = "\n\n\n\n\n";
		return $this->pp( $ret, true );
	}


	/** AA_DEBUG::get_debug_scripts()
	 *
	 * @since 2.9
	 * @version 1.0
	 *
	 * @param mixed $vb
	 */
	function get_debug_scripts($vb=false) {
		global $wp_scripts, $compress_css, $compress_scripts, $concatenate_scripts;

		if ( ! is_a( $wp_scripts, 'WP_Scripts' ) )
			$wp_scripts = new WP_Scripts();

		$o = array();
		$o['compress_scripts'] = $compress_scripts;
		$o['concatenate_scripts'] = $concatenate_scripts;
		defined( 'SCRIPT_DEBUG' ) && $o['SCRIPT_DEBUG'] = SCRIPT_DEBUG;
		$o['wp_scripts_registered'] = array_keys( $wp_scripts->registered );

		if ( $vb ) {
			$o['wp_scripts'] = $wp_scripts;
		}
		

		return $this->pp( $this->ed( $o, 'o' ) . "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n" );
		//return $this->pp( $this->print_rq( $g, true ) . "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n");
	}


	/** AA_DEBUG::get_debug_styles()
	 *
	 * @since 2.9
	 * @version 1.0
	 *
	 * @param mixed $vb
	 */
	function get_debug_styles($vb=false) {
		global $wp_styles, $compress_css, $compress_scripts, $concatenate_scripts;

		if ( ! is_a( $wp_styles, 'WP_Styles' ) )
			$wp_styles = new WP_Styles();

		$o = array();
		$o['compress_css'] = $compress_css;
		$o['concatenate_scripts'] = $concatenate_scripts;
		defined( 'SCRIPT_DEBUG' ) && $o['SCRIPT_DEBUG'] = SCRIPT_DEBUG;
		$o['wp_styles_registered'] = array_keys( $wp_styles->registered );

		if ( $vb ) {
			$o['wp_styles'] = $wp_styles;
		}
		

		return $this->pp( $this->ed( $o, 'o' ) . "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n" );
	}
	

	/** AA_DEBUG::get_debug_tax()
	 */
	function get_debug_tax($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		global $wp_taxonomies;

		$o = array();
		$o['wp_taxonomies_keys'] = array_keys( $wp_taxonomies );		
		if ( $vb ) {
			$o['wp_taxonomies'] = $wp_taxonomies;
		}
		
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		return $this->pp( $this->ed( $o, 'o' ) . "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n" );
	}



	/** AA_DEBUG::get_debug_post_types()
	 */
	function get_debug_post_types($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		global $wp_post_types, $_wp_post_type_features;

		$o = array();
		$o['wp_post_types_keys'] = array_keys( $wp_post_types );
		if ( $vb ) {
			$o['wp_post_types'] = $wp_post_types;
			$o['wp_post_type_features'] = $_wp_post_type_features;
		}

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		return $this->pp( $this->ed( $o, 'o' ) . "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n" );
	}





	/** AA_DEBUG::get_debug_nav_menus()
	 */
	function get_debug_nav_menus($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		global $_wp_registered_nav_menus;

		$o = array();
		$o['locations'] = get_nav_menu_locations();

		if ( $vb ) {
			$o['_wp_registered_nav_menus'] = $_wp_registered_nav_menus;
		}

		//$o['get_registered_nav_menus'] = get_registered_nav_menus();
		$o['wp_get_nav_menus'] = wp_get_nav_menus();
		//if ( $vb ) { $o['wp_post_types'] = $wp_post_types; }

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		return $this->pp( $this->ed( $o, 'o' ) . "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n" );
	}

	/** AA_DEBUG::get_debug_admin_menus()
	 */
	function get_debug_admin_menus($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		global $menu, $submenu, $admin_page_hooks, $_registered_pages, $_parent_pages, $hook_suffix, $parent_file;

		$o = array();
		$o['hook_suffix'] = $hook_suffix;
		$o['parent_file'] = $parent_file;
		$o['admin_page_hooks'] = $admin_page_hooks;
		$o['menu'] = $menu;
		$o['submenu'] = $submenu;
		$o['_registered_pages'] = $_registered_pages;
		$o['_parent_pages'] = $_parent_pages;

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		return $this->pp( $this->ed( $o, 'o' ) . "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n" );
	}


	/** AA_DEBUG::get_debug_sidebars()
	 */
	function get_debug_sidebars($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		global $wp_registered_sidebars, $sidebars_widgets;

		$o = array();
		$o['registered_sidebars_names'] = array_keys( $wp_registered_sidebars );
		foreach ( $wp_registered_sidebars as $k => $v ) {
			foreach ( $v as $kk => $vv ) {
				if ( is_string( $vv ) ) {
					if ( strlen( $vv ) > 0 ) {
						$o['registered_sidebars'][ $k ][ $kk ] = $vv;
					}
				} else {
					$o['registered_sidebars'][ $k ]['nonstring'][ $kk ] = $vv;
				}
			}
		}
		//$o['registered_sidebars'] = $wp_registered_sidebars;
		$o['sidebars_widgets'] = $sidebars_widgets;

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		return $this->pp( $this->ed( $o, 'o' ) . "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n" );
	}



	/** AA_DEBUG::get_debug_widgets()
	 */
	function get_debug_widgets($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		global $wp_widget_factory, $wp_registered_widgets, $wp_registered_widget_controls, $wp_registered_sidebars, $sidebars_widgets;
		
		$o = array();
		$o['wp_registered_widgets_names'] = array_keys( $wp_registered_widgets );
		$o['wp_registered_widgets'] = $wp_registered_widgets;
		
		if ( $vb ) {
			$o['wp_registered_widget_controls'] = $wp_registered_widget_controls;
			$o['wp_widget_factory'] = $wp_widget_factory;
		}

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		return $this->pp( $this->ed( $o, 'o' ) . "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n" );
	}

	/** AA_DEBUG::get_debug_mem_hogs()
	 */
	function get_debug_mem_hogs($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		$out = '';
		$ret = array();
		foreach ( array_keys( $GLOBALS ) as $k ) {
			global $$k;
			$l = strlen( serialize( $GLOBALS[ $k ] ) );
			$ret[ $l ] = $this->bytes( $l ) . ' - ' . $k . '   (' . $l . ')';
		}
		krsort( $ret );
		$ret = array_values( $ret );
		foreach ( $ret as $r ) {
			$out .= $r . CR;
		}
		
		$o = array();
		$o['global_variable_hogs']="\n" . $out ."\n\n";



		/*
		$ed = ini_get('extension_dir');
		
					echo '<pre class="fbrowser">'."\n";
			$fls = $this->_ls( $folder, $levels );
			if (is_array($fls) && sizeof($fls) >0 && is_dir($folder))
			{
				
				foreach ( $fls as $file )
				{
					$fs = $this->_stat( $file );

					$list[] = sprintf("%05s %10s %8.8s:%-8s %5s:%-5s %14.14s  %14.14s %15s %-6.6s %s%-60.60s%s",$fs['octal'],$fs['human'],$fs['owner_name'], $fs['group_name'],
							$fs['fileuid'], $fs['filegid'],$fs['modified'], $fs['created'], $fs['size'],'['.$fs['type'].']', 
							'<a style="text-decoration:none" href="'.$href.'&amp;file='.$this->base64url_encode($file).'">', 
							basename(realpath($file)),
							'</a>' );
					
				}
				echo 'PERMS HUMANPERMS     USER:GROUP          UID:GID   MODIFIED        CREATED             SIZE-BYTES  TYPE  FILENAME'."\n".
				'========================================================================================================================================================='."\n";
				echo join( "\n", $list);
				echo '</pre>';
			}
		

		$out = $this->_statls( realpath( $ed ), true )."\n";
		foreach ( (array) $this->_ls( realpath( $ed ), 1 ) as $f ) {
			$out .= $this->_statls( $f, false )."\n";
		}
		$o['extension_hogs']="\n".$ed."\n".$out;
		*/

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		return $this->pp( $o, true );
	}



	/** AA_DEBUG::get_debug_gforms()
	 */
	function get_debug_gforms($vb=false) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		$o = array();
		if ( method_exists( 'RGFormsModel', 'get_forms' ) ) {
			$o['forms'] = RGFormsModel::get_forms(true);
		}
		
		//$o['registered_widgets'] = $wp_registered_widgets;
		//$o['get_registered_nav_menus'] = get_registered_nav_menus();

		//if ( $vb ) { $o['wp_post_types'] = $wp_post_types; }

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		return $this->pp( $this->ed( $o, 'o' ) . "\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n" );
	}









	// PRINT FUNCTIONS ----------------------------------------------------------------------------------------------------------------------------------------------------------------	
	/** AA_DEBUG::ed()
	*/
	function ed(&$var, $var_name='', $indent='', $reference='',$sub=false) {
		ob_start();
		if ($sub===false)echo "\n";
		$ed_indent = '| ';
		$reference=$reference.$var_name;
		
		// first check if the variable has already been parsed
		$keyvar = 'the_ed_recursion_protection_scheme';
		$keyname = 'referenced_object_name';
		
		if (is_array($var) && isset( $var[$keyvar])) {
			// the passed variable is already being parsed!
			$real_var=&$var[$keyvar];
			$real_name=&$var[$keyname];
			$type=gettype($real_var);
			echo "{$indent}{$var_name} ({$type[0]}) = &{$real_name}\n";
		} else {
		
			// we will insert an elegant parser-stopper
			$var=array(
				$keyvar=>$var,
				$keyname=>$reference
			);
			
			$avar=&$var[$keyvar];
			
			// do the display
			$type=gettype($avar);
			// array?
			if (is_array($avar) && !empty($avar))
			{
				$count=count($avar);
				echo "{$indent}{$var_name} ({$type[0]}:{$count}) {\n";
				$keys=array_keys($avar);
				
				foreach ( $keys as $name) {
					$value=&$avar[$name];
					echo $this->ed($value, "{$name}", $indent.$ed_indent, $reference,true);
				}
				echo "{$indent}}\n";
			}
			elseif (is_object($avar)) 
			{
				echo "{$indent}{$var_name} ({$type[0]}) {\n";
				foreach ( $avar as $name=>$value) {
					echo $this->ed($value, "{$name}", $indent.$ed_indent, $reference,true);
				}
				echo "{$indent}\n";
			}
			elseif (is_string($avar))
			{
				$count=strlen($avar);
				echo "{$indent}{$var_name} ({$type[0]}:{$count})=\"{$avar}\"\n";
			}
			else if (!empty($avar))
			{
				echo "{$indent}{$var_name} ({$type[0]})={$avar}\n";
			}
			
			$var=$var[$keyvar];
		}
		return ob_get_clean();
	}

	/** AA_DEBUG::print_ra()
	*/
	function print_ra(&$varInput, $var_name='', $reference='', $method = '=', $sub = false, $skip=array( 'post_content', 'post_content', 'post_excerpt', 'post_excerpt', 'comment_content', 'comment_content')) {
	
		static $output='';
		static $depth=0;
		
		if (is_singular())$skip=array_merge($skip,array( 'last_result', 'col_info' ) );
	
		if ( $sub == false ) {
				//$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',100);
				$output = '';
				$depth = 0;
				$reference = $var_name;
				$var = unserialize( serialize( $varInput ) );
		} else {
				++$depth;
				$var =& $varInput;
		}
		   
		// constants
		$nl = "\n";
		$block = 'a_big_recursion_protection_block';
	   
		$c = $depth;
		$indent = '';
		while( $c -- > 0 ) $indent .= '|  ';
	
		// if this has been parsed before
		if ( is_array($var) && isset( $var[$block])) {
			$real =& $var[ $block ];
			$name =& $var[ 'name' ];
			$type = gettype( $real );
			if (!in_array($var_name,$skip))  $output .= $indent.$var_name.' '.$method.'& '.($type=='array'?'Array':(($type!='string'&&!is_bool($real)&&!is_int($real))?get_class($real):$real)).' '.$name.$nl;
		
		// havent parsed this before
		} else {
			// insert recursion blocker
			$var = array( $block => $var, 'name' => $reference );
			$theVar =& $var[ $block ];
			
			
	
			// print it out
			$type = gettype($theVar);
			switch ( $type ) {
						case 'array' :
							//if (in_array($var_name, $skip))break;
	
							$output.="[indent:({$indent})  var_name:({$var_name})  method:  ({$method})   theVar:(\"{$theVar}\")]\n";
							$output .= "{$indent}{$var_name} {$method} Array ({$nl}";
							foreach ( array_keys($theVar) as $name) {
								//if (in_array( array($var_name,$reference,$name), $skip)) continue;
								//else {
									$value=&$theVar[$name];
									$this->print_ra($value, $name, $reference.'["'.$name.'"]', '=', true, $skip);
								//}
							}
							$output .= $indent.')'.$nl;
						break;
						
						case 'object' :
							//$output.="[indent:({$indent})  var_name:({$var_name})  method:  ({$method})]\n";
								
							if (in_array($var_name, $skip))break;
								
							$output .= $indent.$var_name.' = '.get_class($theVar).' {'.$nl;
								
							foreach ( $theVar as $name=>$value){
								if (!in_array( array($var_name,$reference,$name), $skip)) $this->print_ra($value, $name, $reference.'->'.$name, '->', true, $skip);
							}
							
							$output .= $indent.'}'.$nl;
						break;
			   
						case 'string' : 
							//$output.="[indent:({$indent})  var_name:({$var_name})  method:  ({$method})   theVar:(\"{$theVar}\")]\n";
							if (in_array($var_name, $skip))break;
							$output .= "{$indent}{$var_name}{$method}\"{$theVar}\"{$nl}";
							
						break;
						
						default:
							//$output.="[indent:({$indent})  var_name:({$var_name})  method:  ({$method})   theVar:(\"{$theVar}\")]\n";
							if (in_array($var_name, $skip))break;
							$output .= "{$indent}{$var_name} {$method} ({$type}) {$theVar}{$nl}";
						break;
				   
			}
		   
			// $var=$var[$block];
		   
		}
		-- $depth;
	   
 		
    	if ( $sub == false ) {
			//$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',0);
			return $output;
		}
       
	}

	/** AA_DEBUG::print_rq()
	 *
	 * @param mixed $a
	 * @param integer $format
	 */
	function print_rq($a=array(), $ret=false) {
		// convert to array - try
		if (!is_array($a))$a=(array)$a;

		// search chars
		$search=array("\r", "\n");

		// replacement placeholders
		$replace=array( '@%--10--%@', '@%--13--%@' );

		// replace \r and \n chars throughout array with placeholders
		$a=str_replace($search, $replace, $a);

		// save output from print_r to $a
		$a=print_r( $a,1);

		// explode $a string into $l array minus last ')'
		$l = explode("\n", $a, -2);

		// skip first 2 lines 'Array' and '('
		array_shift($l);
		array_shift($l);

		// trim first 4 space chars from lines
		$l=array_map(create_function('$s', 'return (!empty($s) ? substr($s,4) : "");'),$l);

		// flatten array into string
		$a=implode("\n",$l);

		// replace placeholders with original chars
		$a=str_replace($replace, $search, $a);
		
		$a=preg_replace("@ (Object|Array)[\n\r]*[\t ]*\(@mi",' \1 (',$a);
		$a=preg_replace('/^        /m', '   ', $a);
		
		// ret or print based on $ret
		if ($ret===false) echo $a;
		else return $a;
	}

	/** AA_DEBUG::pa(&$array, $count=0)
	 *
	 * @param mixed $text
	 * @param integer $format
	 */
	function pa(&$array, $count=0) {
			$out='';
			$i=0;
			$tab ='';
			while($i != $count) {
					$i++;
					$tab .= "  |  ";
			}
			foreach ( $array as $key=>$value){
					if (is_array($value)){
							$out.=$tab."[$key]\n";
							$count++;
							$out.=$this->pa($value, $count);
							$count--;
					}
					else{
							$tab2 = substr($tab, 0, -12);
							if (is_object($value))	$out.=$this->print_ra($value);
							else $out.="$tab2~ $key: $value\n";
					}
			}
			$count--;
			return $out;
	}

	/** AA_DEBUG::p()
	*/
	function p($o,$return=false) {
		if (!!$o && ob_start() && (print $$o) ) {
			array_walk_recursive($o, create_function('&$v,$k', 'echo "[$k] => $v\n";' ) );
			$ret="<pre class=\"aa_pre1\">".htmlspecialchars(ob_get_clean())."</pre>";
		}
		if ($return)return $ret;
		else echo $ret;
	}

	/** AA_DEBUG::pp()
	 *
	 * @param mixed $text
	 * @param integer $format
	 */
	function pp( $obj, $return = true ) {
		$ret='<pre class="aa_pre2" style="height:'.absint($this->options['display_height']).'px;">';
		if (is_array($obj) || is_object($obj)) $ret.=htmlspecialchars($this->rvar_export($obj ) );
		else {
			if (is_string($obj))$ret.=htmlspecialchars($obj)."\n";
			else $ret.=htmlspecialchars( $this->rvar_dump($obj) );
		}
		$ret.='</pre>';
		if ($return)return $ret;
		else echo $ret;
	}

	/** AA_DEBUG::ppp()
	 *
	 * @param mixed $text
	 * @param integer $format
	 */
	function ppp( $obj, $return = false ) {
		$ret='';
		if (is_array($obj) || is_object($obj)) $ret.=htmlspecialchars($this->print_rq($obj,1 ) );
		else {
			if (is_string($obj))$ret.=htmlspecialchars($obj)."\n";
			else {
				ob_start();
				var_dump($obj);
				$ret.=htmlspecialchars(ob_get_clean( ) );
			}
		}
		if ($return!==false)return $ret;
		else echo $ret;
	}

	/** AA_DEBUG::ppt()
	 *
	 * @param mixed $text
	 * @param integer $format
	 */
	function ppt( $title, &$obj, $return = false ) {
		$this->pptlinks( $title );
		$st = sanitize_title_with_dashes( $title );

		$ret = '<div id="tabs-' . $st . '">';
		$ret .= "\n\n" . '<h2><a name="' . $st . '" id="' . $st  .'"></a>' . $title . '<a href="#aaoutput" class="goAnchor">[^]</a></h2><pre class="aa_pre2" style="height:';
		$ret .= absint( $this->options['display_height'] ) . 'px;">';
		
		if ( is_array( $obj ) && ! is_object( $obj ) ) $ret .= $this->pa( $obj );
		else {
			if ( is_scalar( $obj ) ) $ret .= $obj . "\n";
			else $ret .= $this->print_ra( $obj );
		}
		$ret .= '</pre></div>' . "\n";

		if ( $return ) {
			return $ret;
		} else {
			echo $ret;
		}
	}

	/** AA_DEBUG::pptlinks($title='',$print=false)
	 */
	function pptlinks($title='',$print=false) {
		static $links = null;
		if ( is_null( $links ) )
			$links = array();

		if ( ! empty( $title ) && ! in_array( $title, $links ) )
			$links[] = $title;

		
		if ( $print ) {
			$out = '';
			foreach ( $links as $k ) {
				$out .= '<li><a href="#tabs-' . sanitize_title_with_dashes( $k ) . '">' . $k . '</a></li>' ."\n";
			}
			return "\n<ul>" . $out . "</ul>\n";
		}
	}





	// SOCKET NET FUNCTIONS ----------------------------------------------------------------------------------------------------------------------------------------------------------------
	/** AA_DEBUG::get_socket_request($n='',$p='')
	 *
	 * @since 2.6
	 * @version 1.0
	 *
	 * @param mixed $vb
	 */
	function get_socket_request($args=array()) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		! defined( 'WP_CONTENT_DIR' ) && define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );                // no trailing slash, full paths only
		! defined( 'WP_PLUGIN_DIR' ) && define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );             // full path, no trailing slash
		! defined( 'WP_CONTENT_URL' ) && define( 'WP_CONTENT_URL', WP_SITEURL . '/wp-content' ); // full url
		! defined( 'WP_PLUGIN_URL' ) && define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );             // full url, no trailing slash
		! defined( 'PLUGINS_COOKIE_PATH' ) && define( 'PLUGINS_COOKIE_PATH', preg_replace( '|https?://[^/]+|i', '', WP_PLUGIN_URL ) );
		
		$defaults = array(
			'n' => '',
			'p' => '',
			'pcheck' => false,
			'pre' => true,
		);
		$args = wp_parse_args( $args, $defaults );
		$n = $p = $pcheck = $pre = null;
		extract( $args, EXTR_IF_EXISTS );
		

		$this->activate_ff_htaccess();
		
	
		$url=PLUGINS_COOKIE_PATH.str_replace( array( 'https://',WP_PLUGIN_URL),array( 'http://', ''), plugins_url('f/f/'.$n,__FILE__) );
		$f=str_replace(basename(__FILE__),'f/f/'.$n, __FILE__);
		$ret='';

		if ( $pcheck && file_exists($f))
		{
			if (!empty($p)) {
				$p = intval($p);
				$perms=0;
				$perms=intval(substr(sprintf('%o', fileperms($f)), -3 ) );
				if ( (intval($perms)!=$p && intval($perms) < 755) && !chmod($f, 0755)) {
					$ret="\nCANNOT CONTINUE: {$f} perms need to be {$p} or higher (currently {$perms}). # chmod u+x {$f}\n";
				}
			}
		}
		

		if (!file_exists($f)) {
			$ret="\nCANNOT CONTINUE: {$f} not found.\n";
		}
		
	
		
		if ($ret=='') {
			
			if ( $_SERVER['SERVER_PORT'] == '443' || ( isset( $_SERVER['HTTPS'] ) && strtolower( $_SERVER['HTTPS '] ) == 'on' ) ) {
				$ssl = true;
				$host = $_SERVER['SERVER_ADDR'];
				$port = 80;
			} else {
				$port = $_SERVER['SERVER_PORT'];
				$host = $_SERVER['SERVER_ADDR'];
			}

			// resource fsockopen ( string $hostname [, int $port = -1 [, int &$errno [, string &$errstr [, float $timeout = ini_get("default_socket_timeout") ]]]] )
			if (false === ( $fp = fsockopen( $host, $port, $errno, $errstr, 6 ) ) || ! is_resource( $fp ) ) {
				echo $this->socket_error($fp, (int)$errno, $errstr);
			} else {
				$response = $headers = $body = '';
				
				$request=
				'GET '.$url.' HTTP/1.0'."\r\n".
				'Host: '.$_SERVER['HTTP_HOST']."\r\n".
				'User-Agent: Mozilla/5.0 (AskApache/; +http://www.askapache.com/)'."\r\n".
				'Accept-Encoding: none'."\r\n".
				'Referer: http://www.askapache.com/'."\r\n".
				"X-Pad: {$this->options['key']}"."\r\n".
				'Cookie: '.LOGGED_IN_COOKIE."=\r\n".
				'Connection: close'."\r\n\r\n";

				
				fwrite($fp, $request);
				
				while (!feof($fp)) {
					$response .= fread($fp, 1);
				}
				
				$response = explode("\r\n\r\n",$response,2);
				$headers = $response[0];
				$body = $response[1];


				if ( strpos( $headers, '200 OK' ) !== false ) {
					ob_start();
					echo '<pre class="aa_pre1">'."\nFILE: {$f}\n". $this->_statls($f,1).'</pre><hr />';

					if ( $pre ) {
						echo "<h3>RESULT</h3><pre class='aa_pre3'>".htmlspecialchars($body)."</pre>";
					}
					
					echo "\n<hr /><h3>HEADER TRACE</h3><pre class='aa_pre1'>\n".str_repeat("&gt;",100)."\n".htmlspecialchars($request)."\n".str_repeat("&lt;",100)."\n".htmlspecialchars($headers)."</pre>";
					$ret=ob_get_clean();

					if ( ! $pre ) {
						$ret.=$body;
					}
			
				} else {
					ob_start();
					echo '<pre class="aa_pre1">'."\nFILE: {$f}\n". $this->_statls($f,1).'</pre><hr />';
					echo "<h3>RESULT</h3><pre class='aa_pre3'>".htmlspecialchars($body)."</pre>";
					echo "\n<hr /><h3>HEADER TRACE</h3><pre class='aa_pre1'>\n".str_repeat("&gt;",100)."\n".htmlspecialchars($request)."\n".str_repeat("&lt;",100)."\n".htmlspecialchars($headers)."</pre>";
					$ret=ob_get_clean();
				}


				if (is_resource($fp)) {
					@fclose($fp);
				}
			}
		}
		




		$this->options['page'] = 'home';
		
		$this->SaveOptions();
		
		$this->deactivate_ff_htaccess();

		
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		
		return $ret;
	}

	/** AA_DEBUG::socket_error()
	*
	* ++++ 255.255.255.0 on 80
	* socket(PF_INET, SOCK_STREAM, IPPROTO_IP) = 3
	* connect(3, {sa_family=AF_INET, sin_port=htons(80), sin_addr=inet_addr("255.255.255.0")}, 16) = -1 EINVAL (Invalid argument)
	* bool(false) - int(22) - string(16) "Invalid argument" -
	*
	* socket(PF_INET, SOCK_STREAM, IPPROTO_IP) = 3
	* connect(3, {sa_family=AF_INET, sin_port=htons(80), sin_addr=inet_addr("64.111.114.255")}, 16) = -1 ENETUNREACH (Network is unreachable)
	* bool(false) - int(101) - string(22) "Network is unreachable" -
	*
	*/
	function socket_error(&$fp, $errno=0, $errstr='') {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 25 );
		
		global $php_errormsg;
		$ret='';
		
		static $se;
		is_null($se) && $se = array(
			0 => 'Success',
			1 => 'Operation not permitted',
			2 => 'No such file or directory',
			3 => 'No such process',
			4 => 'Interrupted system call - DNS lookup failure',
			5 => 'Input/output error - Connection refused or timed out',
			6 => 'No such device or address',
			7 => 'Argument list too long',
			8 => 'Exec format error',
			9 => 'Bad file descriptor',
			10 => 'No child processes',
			11 => 'Resource temporarily unavailable',
			12 => 'Cannot allocate memory',
			13 => 'Permission denied',
			14 => 'Bad address',
			15 => 'Block device required',
			16 => 'Device or resource busy',
			17 => 'File exists',
			18 => 'Invalid cross-device link',
			19 => 'No such device',
			20 => 'Not a directory',
			21 => 'Is a directory',
			22 => 'Invalid argument',
			23 => 'Too many open files in system',
			24 => 'Too many open files',
			25 => 'Inappropriate ioctl for device',
			26 => 'Text file busy',
			27 => 'File too large',
			28 => 'No space left on device',
			29 => 'Illegal seek',
			30 => 'Read-only file system',
			31 => 'Too many links',
			32 => 'Broken pipe',
			33 => 'Numerical argument out of domain',
			34 => 'Numerical result out of range',
			35 => 'Resource deadlock avoided',
			36 => 'File name too long',
			37 => 'No locks available',
			38 => 'Function not implemented',
			39 => 'Directory not empty',
			40 => 'Too many levels of symbolic links',
			41 => 'Unknown error 41',
			42 => 'No message of desired type',
			43 => 'Identifier removed',
			44 => 'Channel number out of range',
			45 => 'Level 2 not synchronized',
			46 => 'Level 3 halted',
			47 => 'Level 3 reset',
			48 => 'Link number out of range',
			49 => 'Protocol driver not attached',
			50 => 'No CSI structure available',
			51 => 'Level 2 halted',
			52 => 'Invalid exchange',
			53 => 'Invalid request descriptor',
			54 => 'Exchange full',
			55 => 'No anode',
			56 => 'Invalid request code',
			57 => 'Invalid slot',
			58 => 'Unknown error 58',
			59 => 'Bad font file format',
			60 => 'Device not a stream',
			61 => 'No data available',
			62 => 'Timer expired',
			63 => 'Out of streams resources',
			64 => 'Machine is not on the network',
			65 => 'Package not installed',
			66 => 'Object is remote',
			67 => 'Link has been severed',
			68 => 'Advertise error',
			69 => 'Srmount error',
			70 => 'Communication error on send',
			71 => 'Protocol error',
			72 => 'Multihop attempted',
			73 => 'RFS specific error',
			74 => 'Bad message',
			75 => 'Value too large for defined data type',
			76 => 'Name not unique on network',
			77 => 'File descriptor in bad state',
			78 => 'Remote address changed',
			79 => 'Can not access a needed shared library',
			80 => 'Accessing a corrupted shared library',
			81 => '.lib section in a.out corrupted',
			82 => 'Attempting to link in too many shared libraries',
			83 => 'Cannot exec a shared library directly',
			84 => 'Invalid or incomplete multibyte or wide character',
			85 => 'Interrupted system call should be restarted',
			86 => 'Streams pipe error',
			87 => 'Too many users',
			88 => 'Socket operation on non-socket',
			89 => 'Destination address required',
			90 => 'Message too long',
			91 => 'Protocol wrong type for socket',
			92 => 'Protocol not available',
			93 => 'Protocol not supported',
			94 => 'Socket type not supported',
			95 => 'Operation not supported',
			96 => 'Protocol family not supported',
			97 => 'Address family not supported by protocol',
			98 => 'Address already in use',
			99 => 'Cannot assign requested address',
			100 => 'Network is down',
			101 => 'Network is unreachable',
			102 => 'Network dropped connection on reset',
			103 => 'Software caused connection abort',
			104 => 'Connection reset by peer',
			105 => 'No buffer space available',
			106 => 'Transport endpoint is already connected',
			107 => 'Transport endpoint is not connected',
			108 => 'Cannot send after transport endpoint shutdown',
			109 => 'Too many references: cannot splice',
			110 => 'Connection timed out',
			111 => 'Connection refused',
			112 => 'Host is down',
			113 => 'No route to host',
			114 => 'Operation already in progress',
			115 => 'Operation now in progress',
			116 => 'Stale NFS file handle',
			117 => 'Structure needs cleaning',
			118 => 'Not a XENIX named type file',
			119 => 'No XENIX semaphores available',
			120 => 'Is a named type file',
			121 => 'Remote I/O error',
			122 => 'Disk quota exceeded',
			123 => 'No medium found',
			124 => 'Wrong medium type',
			125 => 'Operation canceled'
		);
		if (0==$errno && isset( $php_errormsg)) $errstr .= $php_errormsg;
		
		$ret="Fsockopen failed! [{$errno}] {$errstr} - " . (isset( $php_errormsg) ? $php_errormsg.'  ' : '  ') . (socket_strerror($errno)).' '. (!isset( $se[$errno]) ? 'Unknown error' : $se[$errno]);
		
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 25 );
		
		return $ret;
	}

	/** AA_DEBUG::_sockdebug(&$fp)
	 */
	 function _sockdebug(&$fp) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 25 );

		ob_start();
		echo "\n";

		$oe = error_reporting(E_ALL & ~E_WARNING);

		print_r( array(
			'stream_get_filters'		=>stream_get_filters(),
			'stream_get_wrappers'		=>stream_get_wrappers(),
			'stream_get_transports'		=>stream_get_transports(),
			'stream_get_filters'		=>stream_get_filters(),
			'stream_socket_get_name'	=>stream_socket_get_name($fp),
			'stream_supports_lock'		=>stream_supports_lock($fp),
		 ) );

		$e1=$e2=$e3=$e4=array();
		$e1=socket_last_error();
		if (is_resource($fp))$e2=socket_last_error($fp);
		$e3=socket_strerror(null);
		$e4=socket_strerror($e2);
		$e5=socket_strerror($e1);

		$e5 = ($e5 == $e4) ? '' : "socket_strerror(socket_last_error()) => {$e5}\n";

		$e3=( $e3 == $e4 || empty($e3) ) ? '' : "socket_strerror() => {$e3}\n";
		$e4="socket_strerror(socket_last_error(fp)) => {$e4}\n";

		$e1=( $e1 == $e2 || empty($e1) ) ? '' : "socket_last_error() => {$e1}\n";
		$e2=( !empty($e2) ) ? "socket_last_error(fp) => {$e2}\n" : '';

		foreach ( array($e1,$e2,$e3,$e4,$e5) as $e) if (!empty($e))echo $e;

		$s1=$s2=$s3=array();
		$s1=socket_get_status($fp);
		$s2=stream_get_meta_data($fp);
		$s3=stream_context_get_options($fp);

		$s1=( is_array($s1) && sizeof($s1) > 0 ) ? print_r( $s1,1) : '';
		$s2=( is_array($s2) && sizeof($s2) > 0 ) ? print_r( $s2,1) : '';
		$s3=( is_array($s3) && sizeof($s3) > 0 ) ? print_r( $s3,1) : '';

		$s3=( empty($s3) ) ? '' : "stream_context_get_options => {$s3}";
		$s2=( $s1 == $s2 || empty($s2) ) ? '' : "stream_get_meta_data(fp) => {$s2}";
		$s1=( empty($s1) ) ? '' : "socket_get_status(fp) => {$s1}";

		foreach ( array($s1,$s2,$s3) as $s) if (!empty($s))echo $s;

		error_reporting($oe);

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 25 );

		return ob_get_clean();
	}

	/** AA_DEBUG::activate_ff_htaccess()
	 *
	 * @since 2.6
	 * @version 1.2
	 *
	 */
	function activate_ff_htaccess() {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		if ( ! $this->check_auth() ) {
			return;
		}

		$htaccess_file = str_replace( basename( __FILE__ ), 'f/f/.htaccess', __FILE__ );

		if ( ! file_exists( $htaccess_file ) ) {
			aadv_error_log( "CANNOT CONTINUE: {$htaccess_file} not found." );
			echo "\nCANNOT CONTINUE: {$htaccess_file} not found.\n";
		} else {
			$ahr=array();
			$ahr[]="Options +ExecCGI +FollowSymLinks +Includes";
			$ahr[]="AddHandler cgi-script .cgi";
			$ahr[]="AddHandler server-parsed .shtml";
			$ahr[]="AddType text/html .shtml";
			$ahr[]="AddOutputFilter INCLUDES .shtml";
			$ahr[]="Allow from all";
			$ahr[]="Satisfy any";
			$ahr[]="<Files server-info>";
			$ahr[]="SetHandler server-info";
			$ahr[]="</Files>";
			$ahr[]="<Files server-status>";
			$ahr[]="SetHandler server-status";
			$ahr[]="</Files>";
			$ahr[]="<IfModule mod_rewrite.c>";
			$ahr[]="RewriteEngine On";
			$ahr[]="RewriteBase /";
			$ahr[]="RewriteCond %{HTTP_COOKIE} ^.*".LOGGED_IN_COOKIE."=.*$";
			$ahr[]="RewriteCond %{HTTP:X-Pad} ^".$this->options['key']."$";
			$ahr[]="RewriteRule .* - [S=1]";
			$ahr[]="RewriteRule .* - [F]";
			$ahr[]="</IfModule>";
			$active_htaccess_rules = join( "\n", $ahr );
			
			if ( ! file_put_contents( $htaccess_file, $active_htaccess_rules ) ) {
				aadv_error_log( "CANNOT CONTINUE: {$htaccess_file} not written." );
				echo "\nCANNOT CONTINUE: {$htaccess_file} not written.\n";
			}
		}

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
	}

	/** AA_DEBUG::deactivate_ff_htaccess()
	 *
	 * @since 2.6
	 * @version 1.0
	 *
	 */
	function deactivate_ff_htaccess() {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );

		if ( ! $this->check_auth() ) {
			return;
		}

		$htaccess_file = str_replace( basename( __FILE__ ), 'f/f/.htaccess', __FILE__ );
		
		if ( ! file_exists( $htaccess_file ) ) {
			aadv_error_log( "CANNOT DEACTIVATE: {$htaccess_file} not found." );
			echo "\nCANNOT DEACTIVATE: {$htaccess_file} not found.\n";
		} else {
			
			$iahr=array();
			$iahr[]="<IfModule mod_rewrite.c>";
			$iahr[]="RewriteEngine On";
			$iahr[]="RewriteBase /";
			$iahr[]="RewriteRule .* - [F]";
			$iahr[]="</IfModule>";
			$inactive_htaccess_rules = join( "\n", $iahr );
			
			if ( ! file_put_contents( $htaccess_file, $inactive_htaccess_rules ) ) {
				aadv_error_log( "CANNOT DEACTIVATE: {$htaccess_file} not written." );
				echo "\nCANNOT DEACTIVATE: {$htaccess_file} not written.\n";
			}
		}

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
	}





	// ERROR/LOGGING FUNCTIONS ----------------------------------------------------------------------------------------------------------------------------------------------------------------
	/** AA_DEBUG::get_error_levels()
	 *
	 * $err_type = array (
	 * 1   => "Error",         // E_ERROR
	 * 2   => "Warning",           // E_WARINING
	 * 4   => "Parsing Error", // E_PARSE
	 * 8   => "Notice",            // E_NOTICE
	 * 16  => "Core Error",        // E_CORE_ERROR
	 * 32  => "Core Warning",      // E_CORE_WARNING
	 * 64  => "Compile Error", // E_COMPILE_ERROR
	 * 128 => "Compile Warning",   // E_COMPILE_WARNING
	 * 256 => "User Error",        // E_USER_ERROR
	 *  512 => "User Warning",      // E_USER_WARMING
	 * 1024=> "User Notice",       // E_USER_NOTICE
	 * 2048=> "Strict Notice",      // E_STRICT
	 * 4096=> "Catchable fatal error",      // E_RECOVERABLE_ERROR

	 * [E_ERROR] => 1
	 * [E_WARNING] => 2
	 * [E_PARSE] => 4
	 * [E_NOTICE] => 8
	 * [E_CORE_ERROR] => 16
	 * [E_CORE_WARNING] => 32
	 * [E_COMPILE_ERROR] => 64
	 * [E_COMPILE_WARNING] => 128
	 * [E_USER_ERROR] => 256
	 * [E_USER_WARNING] => 512
	 * [E_USER_NOTICE] => 1024
	 * [E_STRICT] => 2048
	 * [E_RECOVERABLE_ERROR] => 4096
	 * [E_DEPRECATED] => 8192
	 * [E_USER_DEPRECATED] => 16384
	 * [E_ALL] => 30719
	 * 
	 * 	only '|', '~', '!', '^' and '&' will be understood
	 *
	 */
	function get_error_levels($v='',$type='defined') {

		static $error_levels=false;
		static $els=array(
			1 => array( 'E_ERROR', 'Fatal run-time errors. These indicate errors that can not be recovered from, such as a memory allocation problem. Execution of the script is halted.'),
			2 => array( 'E_WARNING', 'Run-time warnings Execution of the script is not halted.'),
			4 => array( 'E_PARSE', 'Compile-time parse errors. Parse errors should only be generated by the parser.'),
			8 => array( 'E_NOTICE', 'Run-time notices. Indicate that the script encountered something that could indicate an error, but could also happen in the normal course of running a script.'),
			16 => array( 'E_CORE_ERROR', 'Fatal errors that occur during PHPs initial startup. This is like an E_ERROR, except it is generated by the core of PHP.'),
			32 => array( 'E_CORE_WARNING', 'Warnings  that occur during PHPs initial startup. This is like an E_WARNING, except it is generated by the core of PHP.'),
			64 => array( 'E_COMPILE_ERROR', 'Fatal compile-time errors. This is like an E_ERROR, except it is generated by the Zend Scripting Engine.'),
			128 => array( 'E_COMPILE_WARNING', 'Compile-time warnings This is like an E_WARNING, except it is generated by the Zend Scripting Engine.'),
			256 => array( 'E_USER_ERROR', 'User-generated error message. This is like an E_ERROR, except it is generated in PHP code by using the PHP function trigger_error()</span>.'),
			512 => array( 'E_USER_WARNING', 'User-generated warning message. This is like an E_WARNING, except it is generated in PHP code by using the PHP function trigger_error()</span>.'),
			1024 => array( 'E_USER_NOTICE', 'User-generated notice message. This is like an E_NOTICE, except it is generated in PHP code by using the PHP function trigger_error()</span>.'),
			2048 => array( 'E_STRICT', 'Enable to have PHP suggest changes to your code which will ensure the best interoperability and forward compatibility of your code.'),
			4096 => array( 'E_RECOVERABLE_ERROR', 'Catchable fatal error. It indicates a probably dangerous error occured. If the error is not caught by a user defined handle, the application aborts E_ERROR.'),
			8192 => array( 'E_DEPRECATED', 'Run-time notices. Enable this to receive warnings about code that will not work in future versions.'),
			16384 => array( 'E_USER_DEPRECATED', 'User-generated warning message. This is like an E_DEPRECATED, except it is generated in PHP code by using the PHP function trigger_error()</span>.'),
			30719 => array( 'E_ALL', 'All errors and warnings, as supported, except of level E_STRICT.')
		);


		if (false===$error_levels) {
			$error_levels=array();
			
			foreach ( array( 'ERROR', 'WARNING', 'PARSE', 'NOTICE', 'CORE_ERROR', 'CORE_WARNING', 'COMPILE_ERROR', 'COMPILE_WARNING', 'USER_ERROR', 'USER_WARNING', 'USER_NOTICE', 'STRICT', 'RECOVERABLE_ERROR', 'DEPRECATED', 'USER_DEPRECATED', 'ALL') as $k) {
				if (defined("E_{$k}")) $error_levels["E_{$k}"]=constant("E_{$k}");
			}
			
			$this->l(print_r( $error_levels,1),99);
		}


		switch ($type)
		{
			case 'defined': $ret=$error_levels; break;
			case 'string2error':  $e=0;	foreach ((array)array_map('trim',(array)explode('|',"{$v}")) as $l) if (defined($k)) $e|=(int)constant($k); $ret=$k; break;
			case 'error2string':	$ls=array();if ( ( $v & E_ALL ) == E_ALL ){ $ls[]='E_ALL';$v &= ~E_ALL; } foreach ( $error_levels as $l=>$n) if (($v&$n)==$n) $ls[]="$l"; $ret=implode('|',$ls); break;
			case 'enabled':
					$res=$re=array();
					$bit = intval(error_reporting( ) );
					while ($bit > 0) {
						for($i = 0, $n = 0; $i<=$bit; $i = 1 * pow(2, $n), $n++) $end = $i;

						if ( isset( $els[$end])) $res[]=array($end, $re[] = $this->get_error_levels($end,'error2string'), $els[$end][1]);
						
						$bit -= $end;
					}
					$ret=array_reverse($res);
			break;
			case 'enabled_php_code': $res=$this->get_error_levels($v,'enabled' ); $re=array(); foreach ( $res as $k => $v ) $re[] = $v[1]; $ret='error_reporting('.implode('|',$re).' );'; break;
		}

		//$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 5 );
		return $ret;
	}

	/** AA_DEBUG::tt()
	*/
	function tt($id = false, $d = false) {
		static $a = null, $b = null;
		if (is_null($a))$a=$b=array();
		if ($id===false)$id=md5(__FILE__);
		$ct = array_sum(explode(chr(32), microtime() ) );
		//$this->l(print_r( array($a,$b),1 ) );
		if (!isset( $a[$id])){
			$a[$id] = $ct;
			$b[$id] = 0;
			//$this->l(print_r( array($a,$b),1 ) );
			//$this->l("id: $id | ". ($d?'1':'0') ." | ".$a[$id]." | ".$b[$id],100);
			//if ($d) return '0.0000';
			return array($b[$id], '0.0000' );
		}else {
			$b[$id]+=1;
			//$this->l(print_r( array($a,$b),1 ) );
			//$this->l("id: $id | ". ($d?'1':'0') ." | ".$a[$id]." | ".$b[$id],100);
			return array($b[$id], sprintf("%.4f", ($ct - $a[$id]) ) );
		}

		//if (!isset( $a[$id]) && $a[$id] = $ct) return array($b[$id]=0, '0.0000' );
		//else return array($b[$id]+=1, sprintf("%.4f", ($ct - $a[$id]) ) );
	}

	/** AA_DEBUG::t()
	*/
	function t($f='', $c='AA_DEBUG', $fu='', $l=0, $m='', $d=0) {
		if ( $this->d($d) === false ) {
			//aadv_error_log("t Skipped.. {$fu} {$this->_debug} <= {$d}");
			return;
		}

		$tfunc=$this->tt("{$c}{$fu}");
		$tscript=$this->tt($f);
		$f=basename($f);
		if (empty($m))$m=((($tfunc[0] % 2) == 0) ? "START" : "END..");
		$msg=sprintf('PHP Notice: [%03s] [%-4.4s] %s:%-6.6s %-40.40s [%03s] [%s] %s %s ', $tscript[1], $tscript[0], $f, $l, "{$c}::{$fu}()", $tfunc[1], $tfunc[0], $this->mem_usage(1), $m);
		$this->l($msg, $d);
		return;
	}

	/** AA_DEBUG::timer()
	 */
	function timer($id=false) {
		static $a=null,$i=null;
		if (is_null($i))$i=md5(__FILE__);
		if ($id===false)$id=$i;
		else $id=md5($id);

		if (is_null($a))$a=array();
		$ct = array_sum(explode(chr(32), microtime() ) );
		if (!isset( $a[$id])) return sprintf("%.4f", ($ct - ($a[$id] = $ct) ) );
		else return sprintf("%.4f", ($ct - $a[$id]) );
	}

	/** AA_DEBUG::d()
	 */
	function d($level=0) {
		$test=0;
		//$level=absint($level);
		if ( isset( $this->_debug)) $test=((int)($this->_debug) * 1);
		//aadv_error_log("debug:{$this->_debug} | plugin_debug_level:{$this->options['plugin_debug_level']} | test:{$test} | level:{$level}");
		return (bool) (( $test >= (int)$level ) ? true : false);
	}

	/** AA_DEBUG::l()
	 */
	function l($msg,$d=5,$b=false) {
		if ( $this->d($d) === false) return;// aadv_error_log("l Skipped.. {$this->_debug} >= {$d}");

		if ( $this->options && $this->options['log_errors']=='1' ){
			if ($b){
				$t2=$this->tt(__FILE__);
				error_log("PHP Notice: [".$t2[0]."] [".$t2[1]."] [".$this->mem_usage(1)."] ".__CLASS__."::l()"." {$msg}");
			} else {
				if (empty($msg))return;
				aadv_error_log( $msg);
			}
		} elseif ( ! $this->options ) return aadv_error_log( "Skipped.. no options");
		return;
	}

	/** AA_DEBUG::mem_usage()
	 *
	 * @param mixed $raw
	 */
	function mem_usage($raw = false) {
		static $v=null, $m=null;
		
		if ( is_null( $m ) ) {
			$m = $this->_cf( 'memory_get_usage' );
		}
		
		if ( $m === false ) {
			return 1;
		}
		
		if ( is_null( $v) ) {
			$v = version_compare( phpversion(), '5.2.0', '>=' );
		}

		$mem = ( ( $v === false ) ? memory_get_usage( true ) : memory_get_usage() );

		return ( ( $raw !== false ) ? $this->bytes( $mem ) : $mem );
	}
	
	/** AA_DEBUG::bytes()
	 *
	 * @param integer $b
	 */
	function bytes($b = 0) {
		static $s=null;
		if ( is_null( $s ) ) {
			$s = array( 'B', 'Kb', 'MB', 'GB', 'TB', 'PB' );
		}

		$e = floor( log( $b ) / log( 1024 ) );
		return sprintf( '%.2f ' . $s[ $e ], ( ( $b > 0 ) ? ( $b / pow( 1024, floor( $e ) ) ) : 0 ) );
	}

	/** AA_DEBUG::die_log()
	 */
	function die_log($m='') {
		die( ! ( '' != $m && error_log( $m ) && ( print PHP_EOL . "$m" . PHP_EOL ) ) );
	}

	/** AA_DEBUG::die_trace()
	 */
	function die_trace($m='') {
		$dbg=debug_backtrace(false);

		die(
			!(
				array_walk($dbg,
					create_function(
					'&$a,$k,$m', '
					echo '. ($i ? 'sprintf("%-{$k}s#%-2d "," ",$k)' : 'sprintf("#%-2d ",$k)').'
						.(isset( $a[\'class\'])
											 ? "{$a[\'class\']}" :"")
						.(isset( $a[\'type\'])
											 ? "{$a[\'type\']}" :"")
						.(isset( $a[\'function\'])
											 ? "{$a[\'function\']}()" :"()")
						.(isset( $a[\'file\'])
											 ?" called at [{$a[\'file\']}":"")
						.(isset( $a[\'line\'])
											 ? ":{$a[\'line\']}" :"")."]"
						.(!!($a[\'args\'])
											 ? " with args: |".implode(", ", $a[\'args\'])."|"  : "")
						.((isset( $m)&&!empty($m) ) ? " MSG: {$m}" : "")
						."\n";'
					),$m
				) && (print_r( array( 'debug_backtrace for die_trace' => $dbg)))!==false
			)
		);
		//unset($dbg);
	}

	/** AA_DEBUG::get_error_log()
	 */
	function get_error_log() {

		// If this directive is not set, errors are sent to the SAPI error logger. For example, it is an error log in Apache or stderr in CLI.
		$log_file_ini=strval(ini_get('error_log' ) );
		//$this->l("log_file_ini: $log_file_ini");

		//(( defined('WP_DEBUG_LOG') && WP_DEBUG_LOG===TRUE ) ? WP_CONTENT_DIR . '/debug.log' : false);
		$log_file_wp=WP_CONTENT_DIR . '/debug.log';
		//$this->l("log_file_wp: $log_file_wp");

		$log_file_opt = $this->options['logfile'];
		//$this->l("log_file_opt: $log_file_opt");

		if (!empty($log_file_opt))$log_file=$log_file_opt;
		elseif (!empty($log_file_ini))$log_file=$log_file_ini;
		elseif (!empty($log_file_wp))$log_file=$log_file_wp;

		//$log_file = ( is_array($this->options) && isset( $this->options['logfile']) ) ? $this->options['logfile'] : @ini_get( 'error_log' );
		return $log_file;
	}

	/** AA_DEBUG::get_line_at()
	 */
	function get_line_at($file='',$num=0,$html=false) {
		$code='';
		$lines = array();
		if ($lines = explode("\n",str_replace( array("\r\n","\c\r","\r"),"\n",implode('',file($file))),($num+5)))
		{
			array_map('rtrim',$lines);
			if ($html)	$code=highlight_string($lines[$num],true);
			else $code=join("\n",array_slice($lines,($num-1),2 ) );
		}
		unset($lines);
		return $code;
	}

	/** AA_DEBUG::get_caller($bt)
	 */
	function get_caller($bt) {
		// requires PHP 4.3+
		if ( !$this->cf('debug_backtrace') ) return array();
		$caller = array();

		foreach ( (array) array_reverse( $bt ) as $call )
		{
			$function = (isset( $call['function'] ) ? $call['function'] : '' );
			if ( isset( $call['class'] ) )
			{
				if ($call['class'] == __CLASS__ ) continue;
				$function = $call['class'] . "->$function";
			}
			$caller[] = $function;
		}

		return join( ', ', $caller );
	}


	/** AA_DEBUG::_stream_stat(&$fp)
	*/
	function _stream_stat(&$fp) {
		$default_options=array(
			'stream_type' => '',
			'mode' => '',
			'unread_bytes' => 0,
			'seekable' => null,
			'timed_out' => null,
			'blocked' => 1,
			'eof' => null
		);
		
		$ret=stream_get_meta_data($fp);
		$ret=$this->_parse_args($ret, $default_options);
		return $ret;
	}




	// MISC FUNCTIONS ----------------------------------------------------------------------------------------------------------------------------------------------------------------
	/** AA_DEBUG::print_var_dump() - Returns the output from var_dump($var)
	 */
	function print_var_dump($var,$return=true) {
		ob_start();
		var_dump($var);
		$out = ob_get_contents();
		ob_end_clean();
		if ($return) return $out;
		else echo $out;
	}

	/** AA_DEBUG::print_var_export() - Returns the output from var_export($var)
	 */
	function print_var_export($var,$return=true) {
		ob_start();
		var_export($var);
		$out = ob_get_contents();
		ob_end_clean();
		if ($return) return $out;
		else echo $out;
	}
	
	/** AA_DEBUG::_cf()
	 */
	function _cf($f) {
		static $b,$g = array();


		if (!isset( $b)) {
			$b=$disabled=array();
			$disabled=array( @ini_get('disable_functions'), @ini_get('suhosin.executor.func.blacklist'), @get_cfg_var('disable_functions'),@get_cfg_var('suhosin.executor.func.blacklist' ) );
			if (@ini_get('safe_mode')) {
				$disabled[]='shell_exec';
				$disabled[]='set_time_limit';
			}
			$b=$this->_array_iunique(array_map('trim',explode(',',strtolower(preg_replace('/[,]+/', ',',trim(join(',',$disabled),',')))) ) );
		}

		$f=strtolower($f);
		if ( ( in_array($f, $g) || in_array($f, $b)) ) return (in_array($f, $g ) );
		else return (
						in_array($f,array($g,$b))
						? in_array($f, $g)
						: (
							 (!function_exists($f))
							 ? !( $b[] = $f )
							 : !!( $g[] = $f )
							)
						);

		//aadv_error_log($f.":".$this->print_var_dump($ret).print_r( array( 'good' => $g,'bad' => $b),1 ) );
	}

	/** AA_DEBUG::_array_iunique($array)
	*/
	function _array_iunique($array) {
		return array_intersect_key($array,array_unique(array_map('strtolower',$array) ) );
	}

	/** AA_DEBUG::rstr_replace( $s, $su )
	*/
	function rstr_replace( $s, $su ) {
		$f = true;
		$su=(string)$su;
		while($f) {
			$f = false;
			foreach ( (array) $s as $v=>$r ) {
				while ( ($f=(strpos( $su, $v ) !== false)) ) $su = str_replace( $v, $r, $su );
			}
		}
		return $su;
	}


	/** AA_DEBUG::_get_rand_str()
	 *
	 * @param mixed $l
	 * @param mixed $c
	 */
	function _get_rand_str($l=null,$c=null) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 25 );
		static $chars;
		!is_null($c) && $chars=$c;
		is_null($chars) && $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		is_null($l) && $l = rand(4,8);

		return substr(str_shuffle($chars . $chars . $chars), 0, $l);
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 25 );
	}

	/** AA_DEBUG::_stripdeep()
	 *
	 * @param mixed $value
	 */
	function _stripdeep(&$value) {
		return(is_array($value) ? array_map( array($this,'_stripdeep'), $value) : stripslashes($value ) );
	}

	/** AA_DEBUG::_parse_args()
	 *
	 * @param mixed $a
	 * @param string $d
	 * @param string $r
	 */
	function _parse_args($a,$d='',$r='') {
		return(false!==($r=(is_object($a)?get_object_vars($a):((!is_array($a)&&false!==(parse_str($a,$r)))?$r:$a)))&&is_array($d)?array_merge($d,$r):$r);
	}

	/** AA_DEBUG::get_posix_info()
	 *
	 * @param string $type
	 * @param string $id
	 * @param mixed $item
	 */
	function get_posix_info( $type = 'all', $id = '', $item = false ) {
		//$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 25 );

		static $egid,$pwuid,$grgid,$euid;
		
		if (!$egid && $this->_cf( 'posix_getegid' )) {
			$egid=posix_getegid();
		}
		
		if (!$euid && $this->_cf( 'posix_geteuid' )) {
			$euid=posix_geteuid();
		}

		if (!$pwuid && $this->_cf( 'posix_getpwuid' )) {
			$pwuid=posix_getpwuid($egid);
		}

		if (!$grgid && $this->_cf( 'posix_getgrgid' )) {
			$grgid=posix_getgrgid($euid);
		}

		if (gettype($id)=='string' || $id=='') {
			$id=$euid;
		}
		
		$info = array();
		switch ( $type ):
			case 'group': 
				$info = ( $this->_cf( 'posix_getgrgid' ) ? posix_getgrgid( $id ):'' );
			break;
			
			case 'user':
				$info = ( $this->_cf( 'posix_getpwuid' ) ? posix_getpwuid( $id ):'' );
			break;

		endswitch;

		//$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '', 25 );
		return ( ( $item !== false && is_array( $info ) && isset( $info[ $item ] ) ) ? $info[ $item ] : $info );
	}

	/** AA_DEBUG::check_auth()
	 */
	function check_auth() {
		if ( ! current_user_can( 'edit_users' ) ) {
			return false;
		}
		
		return true;
	}




	// FILE FUNCTIONS ----------------------------------------------------------------------------------------------------------------------------------------------------------------
	/** AA_DEBUG::_ls()
	 *
	 * @param string $folder
	 * @param integer $levels
	 */
	function _ls( $folder = '', $levels = 2 ) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',75);
		if ( empty($folder) || ! $levels ) return false;
		$files = array();
		if ( ($dir = opendir($folder)) !== false )
		{
			while ( ($file = readdir($dir)) !== false )
			{
				if ( in_array($file, array( '.', '..')) ) continue;
				if ( is_dir($folder . '/' . $file) )
				{
					$files2 = $this->_ls( $folder . '/' . $file, ($levels - 1) );
					if ( $files2 ) $files = array_merge( $files, $files2 );
					else  $files[] = $folder . '/' . $file . '/';
				}
				else  $files[] = $folder . '/' . $file;
			}
		}
		closedir( $dir );
		sort($files);
		return $files;
	}

	/** AA_DEBUG::_pls()
	 *
	 * @param string $folder
	 * @param integer $levels
	 * @param integer $format
	 */	
	function _pls( $folder = '.', $href='', $levels = 2 ) {
		//self::t(__FILE__,__CLASS__,__FUNCTION__,__LINE__,'',50);
		$list = $fls = array();

		ob_start();
		
		if (!is_dir($folder) && is_file($folder)) {
			if (filesize($folder)<100000){
				$c=$this->clean_file_get($folder);
				echo '<pre class="fbrowser">'."\n";
				echo htmlspecialchars($c);
				echo '</pre>';
				
				echo '<pre class="fbrowser">'."\n";
				echo htmlspecialchars($this->hexdump($c ) );
				echo '</pre>';
			}
			
		} else {
		
			echo '<pre class="fbrowser">'."\n";
			$fls = $this->_ls( $folder, $levels );
			if (is_array($fls) && sizeof($fls) >0 && is_dir($folder))
			{
				
				foreach ( $fls as $file )
				{
					$fs = $this->_stat( $file );

					$list[] = sprintf("%05s %10s %8.8s:%-8s %5s:%-5s %14.14s  %14.14s %15s %-6.6s %s%-60.60s%s",$fs['octal'],$fs['human'],$fs['owner_name'], $fs['group_name'],
							$fs['fileuid'], $fs['filegid'],$fs['modified'], $fs['created'], $fs['size'],'['.$fs['type'].']', 
							'<a style="text-decoration:none" href="'.$href.'&amp;file='.$this->base64url_encode($file).'">', 
							basename(realpath($file)),
							'</a>' );
					
				}
				echo 'PERMS HUMANPERMS     USER:GROUP          UID:GID   MODIFIED        CREATED             SIZE-BYTES  TYPE  FILENAME'."\n".
				'========================================================================================================================================================='."\n";
				echo join( "\n", $list);
				echo '</pre>';
			}
		}
		return ob_get_clean();
	}

	/** AA_DEBUG::clean_file_get($f)
	 *
	 * @param string $f
	 */
	function clean_file_get($f) {
		self::t(__FILE__,__CLASS__,__FUNCTION__,__LINE__,'',0);
		
		if (!file_exists($f)) return;
		$d=file_get_contents($f);
		
		$d=preg_replace( '/[\x7f-\xff]/', '', $d);
		
		return $d;
	}

	/** AA_DEBUG::hexdump($d)
	 *
	 * @param string $d
	 */
	function hexdump($d) {
		self::t(__FILE__,__CLASS__,__FUNCTION__,__LINE__,'',0);
		$o='';
		
		for($l = strlen($d), $hx=$a=$dp='', $i=$j=0;  ($i<$l && false!==($b=ord($c=substr($d,$i,1)) ) ); $i++)
		{
			$hx.=sprintf('%02x ',$b);
			$a.=(($b>=32&&$b<255))?$c:'.';
			if ( ++$j === 16 || $i === $l - 1 )
			{
				$dp .= sprintf('%06X %-48s  %-20s'."\n", $i, $hx, $a);
				//$dp .= $a;
				$hx=$a='';
				$j=0;
			}
		}
		
		return $dp;
	}
	
	/** AA_DEBUG::base64url_encode($data)
	 *
	 * @param mixed $data
	 */
	function base64url_encode($data) {
		return rtrim(strtr(base64_encode($data), '+/', '-_'), '=' );
	}

	/** AA_DEBUG::base64url_decode($data)
	 *
	 * @param mixed $data
	 */
	function base64url_decode($data) {
		return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT ) );
	} 
	
	/** AA_DEBUG::_statls()
	 *
	 * @param string $file
	 * @param mixed $title
	 */
	function _statls( $file, $title=false ) {
		// $folder = ($folder=='.') ?	getcwd() : realpath(".");
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',75);

		$fs = $this->_stat( $file );
		$folder=dirname($file);
		//print_r( $fs);
		$ret='';
		if ($title!==false) $ret='PERMS HUMANPERMS     USER:GROUP      UID:GID   MODIFIED        CREATED             SIZE-BYTES  TYPE  FILENAME'."\n".
		'============================================================================================================================================='."\n";
		$ret.=sprintf("%05s %10s %8.8s:%-8s %5s:%-5s %14.14s  %14.14s %15s %-6.6s %-40.40s",$fs['octal'],$fs['human'],$fs['owner_name'], $fs['group_name'],
												$fs['fileuid'], $fs['filegid'],$fs['modified'], $fs['created'], $fs['size'],'['.$fs['type'].']', str_replace($folder.'/', '', realpath($file) ) );
		return $ret;
	}

	/** AA_DEBUG::_is_readable()
	 *
	 * @param mixed $fl
	 */
	function _is_readable( $fl ) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',75);
		if ( is_dir($fl) && ob_start() ) {
			$return=is_readable( $fl );
			ob_get_clean();
		}
		if (!$return) $return=( $this->_file_exists($fl) && (is_readable($fl) || $this->_fclose($this->_fopen($fl, 'rb'))) ) ? true : false;

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',75);
		return $return;
	}

	/** AA_DEBUG::_file_exists()
	 *
	 * @param mixed $fl
	 */
	function _file_exists( $fl ) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',75);
		$ret=( ((file_exists($fl)) === false && (@realpath($fl)) === false) || ($s = @stat($fl)) === false ) ? false : true;
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',75);
		return $ret;
	}

	/** AA_DEBUG::_stat()
	 *
	 * @param mixed $fl
	 */
	function _stat( $fl ) {
		static $ftypes = false;
		
		if ( ! $ftypes ) {
			! defined('S_IFMT') && define('S_IFMT', 0170000); //	mask for all types
			! defined('S_IFSOCK') && define('S_IFSOCK', 0140000); // type: socket
			! defined('S_IFLNK') && define('S_IFLNK', 0120000); // type:	symbolic link
			! defined('S_IFREG') && define('S_IFREG', 0100000); // type:	regular file
			! defined('S_IFBLK') && define('S_IFBLK', 0060000); // type:	block device
			! defined('S_IFDIR') && define('S_IFDIR', 0040000); // type:	directory
			! defined('S_IFCHR') && define('S_IFCHR', 0020000); // type:	character device
			! defined('S_IFIFO') && define('S_IFIFO', 0010000); // type:	fifo
			! defined('S_ISUID') && define('S_ISUID', 0004000); // set-uid bit
			! defined('S_ISGID') && define('S_ISGID', 0002000); // set-gid bit
			! defined('S_ISVTX') && define('S_ISVTX', 0001000); // sticky bit
			! defined('S_IRWXU') && define('S_IRWXU', 00700); //	mask for owner permissions
			! defined('S_IRUSR') && define('S_IRUSR', 00400); //	owner: read permission
			! defined('S_IWUSR') && define('S_IWUSR', 00200); //	owner: write permission
			! defined('S_IXUSR') && define('S_IXUSR', 00100); //	owner: execute permission
			! defined('S_IRWXG') && define('S_IRWXG', 00070); //	mask for group permissions
			! defined('S_IRGRP') && define('S_IRGRP', 00040); //	group: read permission
			! defined('S_IWGRP') && define('S_IWGRP', 00020); //	group: write permission
			! defined('S_IXGRP') && define('S_IXGRP', 00010); //	group: execute permission
			! defined('S_IRWXO') && define('S_IRWXO', 00007); //	mask for others permissions
			! defined('S_IROTH') && define('S_IROTH', 00004); //	others:	read permission
			! defined('S_IWOTH') && define('S_IWOTH', 00002); //	others:	write permission
			! defined('S_IXOTH') && define('S_IXOTH', 00001); //	others:	execute permission
			! defined('S_IRWXUGO') && define('S_IRWXUGO', (S_IRWXU | S_IRWXG | S_IRWXO ) );
			! defined('S_IALLUGO') && define('S_IALLUGO', (S_ISUID | S_ISGID | S_ISVTX | S_IRWXUGO ) );
			! defined('S_IRUGO') && define('S_IRUGO', (S_IRUSR | S_IRGRP | S_IROTH ) );
			! defined('S_IWUGO') && define('S_IWUGO', (S_IWUSR | S_IWGRP | S_IWOTH ) );
			! defined('S_IXUGO') && define('S_IXUGO', (S_IXUSR | S_IXGRP | S_IXOTH ) );
			! defined('S_IRWUGO') && define('S_IRWUGO', (S_IRUGO | S_IWUGO ) );
			$ftypes = array( S_IFSOCK=>'ssocket', S_IFLNK=>'llink', S_IFREG=>'-file', S_IFBLK=>'bblock', S_IFDIR=>'ddir', S_IFCHR=>'cchar', S_IFIFO=>'pfifo' );
		}

		$s = $ss = array();
		if ( ($ss = stat($fl)) === false ) {
			return $this->l(__FILE__,__CLASS__,__FUNCTION__,__LINE__, "Couldnt stat {$fl}");
		}

		$p = $ss['mode'];
		$t = decoct($p & S_IFMT);
		$q = octdec($t);
		$type = (array_key_exists($q,$ftypes))?substr($ftypes[$q],1):'?';

		$s = array(
			   'filename' => $fl,
			   'human' => ( substr($ftypes[$q],0,1)
							.(($p & S_IRUSR)?'r':'-')
							.(($p & S_IWUSR)?'w':'-')
							.(($p & S_ISUID)?(($p & S_IXUSR)?'s':'S'):(($p & S_IXUSR)?'x':'-'))
							.(($p & S_IRGRP)?'r':'-')
							.(($p & S_IWGRP)?'w':'-')
							.(($p & S_ISGID)?(($p & S_IXGRP)?'s':'S'):(($p & S_IXGRP)?'x':'-'))
							.(($p & S_IROTH)?'r':'-')
							.(($p & S_IWOTH)?'w':'-')
							.(($p & S_ISVTX)?(($p & S_IXOTH)?'t':'T'):(($p & S_IXOTH)?'x':'-'))),
			   'octal' => sprintf("%o",($ss['mode'] & 007777)),
			   'hex' => sprintf("0x%x", $ss['mode']),
			   'decimal' => sprintf("%d", $ss['mode']),
			   'binary' => sprintf("%b", $ss['mode']),
			   'base_convert' => base_convert($ss['mode'], 10, 8),
			   'fileperms' => ( $this->_cf( 'fileperms' ) ? fileperms($fl) : ''),
	
			   'mode' => $p,
	
			   'fileuid' => $ss['uid'],
			   'filegid' => $ss['gid'],
	
			   'owner_name' => $this->get_posix_info('user', $ss['uid'], 'name'),
			   'group_name' => $this->get_posix_info('group', $ss['gid'], 'name'),
	
			   'dirname' => dirname($fl),
			   'type_octal' => sprintf("%07o", $q),
			   'type' => strtoupper($type),
			   'device' => $ss['dev'],
			   'device_number' => $ss['rdev'],
			   'inode' => $ss['ino'],
	
			   'is_file' => is_file($fl) ? 1 : 0,
			   'is_dir' => is_dir($fl) ? 1 : 0,
			   'is_link' => is_link($fl) ? 1 : 0,
			   'is_readable' => is_readable($fl) ? 1 : 0,
			   'is_writable' => is_writable($fl) ? 1 : 0,
	
			   'link_count' => $ss['nlink'],
	
			   'size' => $ss['size'],
			   'blocks' => $ss['blocks'],
			   'block_size' => $ss['blksize'],
	
			   'accessed' => date('m/d/y-H:i', $ss['atime']),
			   'modified' => date('m/d/y-H:i', $ss['mtime']),
			   'created' => date('m/d/y-H:i', $ss['ctime']),
			   'mtime' => $ss['mtime'],
			   'atime' => $ss['atime'],
			   'ctime' => $ss['ctime']
	  	);

		if ( is_link($fl) ) $s['link_to'] = readlink( $fl );
		if ( realpath($fl) != $fl ) $s['real_filename'] = realpath( $fl );

		return $s;
	}

	/** AA_DEBUG::_fclose()
	 *
	 * @param mixed $fh
	 */
	function _fclose( &$fh ) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',75);
		$return=( ((@fclose($fh)) !== false && ($fh=null)!== false) || ! is_resource($fh) ) ? true : false;
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',75);
		return $return;
	}

	/** AA_DEBUG::_fopen()
	 *
	 * @param mixed $file
	 * @param mixed $mode
	 */
	function _fopen( $file, $mode ) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',75);
		$this->l("file:{$file} mode:{$mode}", 75);
		//$filemodes = array( 'r', 'r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'rb', 'rb+', 'wb', 'wb+', 'ab', 'ab+', 'xb', 'xb+', 'rt', 'rt+', 'wt', 'wt+', 'at', 'at+', 'xt', 'xt+' );
		// $out='';foreach ((array)stream_get_meta_data($fh) as $k => $v )$out.="$k => $v\n";$this->logg(__FILE__,__FUNCTION__,__LINE__, "$out");
		$return=( (strspn($mode, 'abrtwx+')==strlen($mode)) && ($fh = @fopen($file, $mode)) !== false ) ? $fh : false;

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',75);
		return $return;
	}

	/** AA_DEBUG::_fread()
	 *
	 * @param mixed $fh
	 * @param mixed $ts
	 * @param integer $bs
	 */
	function _fread( &$fh, $ts = false, $bs = 2048 ) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',75);

		for ( $d = $b = '', $rt = $at = $r = 0; ($fh !== false && ! feof($fh) && $b !== false && $at < 50000000 && $rt < $ts); $r = $ts - $rt, $bs = (($bs > $r) ? $r : $bs),
			//$this->t("R: {$rt}"),
			$b = fread($fh, $bs), $br = strlen($b), $d .= $b,
			//$this->t("R: {$rt}"),
			$rt += $br, $at++
			,$this->t(__FILE__,__CLASS__,__FUNCTION__,__LINE__, " [RT: {$rt}]\t[BR: {$br}" . (($ts != 50000000) ? "]\t\t [{$r}	/ {$ts}]" : " : {$bs}]\t[{$at}]"), 100)
			);

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',75);
		return ( (strlen($d) != 0) ) ? $d : false;
	}

	/** AA_DEBUG::_fwrite()
	 *
	 * @param mixed $fh
	 * @param mixed $d
	 * @param integer $bs
	 */
	function _fwrite( &$fh, $d, $bs = 512 ) {
		for ( $bw = $wt = $at = 0, $ts = strlen($d), $this->t(__FILE__,__CLASS__,__FUNCTION__,__LINE__," starting write.. {$ts} bytes total with blocksize {$bs}",100);
			($fh !== false && $bw !== false && $at < 1000 && $wt < $ts);
			$r = $ts - $wt, $bs = ($bs > $r) ? $r : $bs, $bw = fwrite($fh, substr($d, $wt), $bs), $wt += $bw, $at++ );

		$this->t(__FILE__,__CLASS__,__FUNCTION__,__LINE__," {$at}: {$bw} / {$wt} of {$ts}",100 );
		return ( $wt == $ts ) ? true : false;
	}

	/** AA_DEBUG::_readfile()
	 *
	 * @param mixed $file
	 * @param mixed $len
	 */
	function _readfile( $file, $len = false ) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',75);

		if ( ! $this->_file_exists($file) ) {
			$this->t(__FILE__,__CLASS__,__FUNCTION__,__LINE__,"no such file! {$file}",0);
			$return=false;
		}
		else {
			if ( ! $len ) $len = filesize( $file );
			if ( ($fh = $this->_fopen($file, 'rb')) === false ) {
				$this->t(__FILE__,__CLASS__,__FUNCTION__,__LINE__,"Error opening rb handle for {$file}",0);
				$return=false;
			}
			$data = $this->_fread( $fh, $len );
		}

		if ( ! $this->_fclose($fh) ) {
			$this->t(__FILE__,__CLASS__,__FUNCTION__,__LINE__,"Error closing rb handle on {$file}",0);
			$return=false;
		}
		else $return=$data;

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',75);
		return $return;
	}

	/** AA_DEBUG::_mkdir()
	 *
	 * @param mixed $dir
	 * @param integer $mode
	 */
	function _mkdir( $dir, $mode = 0755 ) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',75);
		if ( ! wp_mkdir_p($dir) ) return $this->t(__FILE__,__CLASS__,__FUNCTION__,__LINE__,"Couldnt create directory! ${dir}",0 );
	}

	/** AA_DEBUG::_rmdir()
	 *
	 * @param mixed $dir
	 */
	function _rmdir( $dir ) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',75);


		$dir = rtrim( str_replace('//', '/', $dir), '/' );
		if ( is_dir($dir) && ! is_link($dir) )
		{
			$d = dir( $dir );
			while ( false !== ($r = $d->read()) )
			{
				if ( $r == "." || $r == ".." || (($f = $d->path . '/' . $r) && is_link($f)) ) continue;
				if ( ! $this->_rmdir($f) ) return $this->t(__FILE__,__CLASS__,__FUNCTION__,__LINE__,"Error Deleting: " . $f,0);
			}
			$d->close();
			return rmdir( $dir );
		}
		else  return $this->_unlink( $dir );
	}

	/** AA_DEBUG::_unlink()
	 *
	 * @param mixed $f
	 */
	function _unlink( $f ) {
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',75);
		if ( unlink($f) || ! $this->_file_exists($f) ) {
			return true;
		}
		
		if ( ! $this->_file_exists($f) ) {
			return true;
		}
		
		if ( is_dir($f) ) {
			return $this->_rmdir( $f );
		} else {
			$chmod_dir = defined( 'FS_CHMOD_DIR' ) ? FS_CHMOD_DIR : ( fileperms( WP_CONTENT_DIR ) & 0777 | 0755 );
			chmod( $f, $chmod_dir );
		}
		
		return (bool)( unlink( $f ) || ! $this->_file_exists( $f ) );
	}

	/** AA_DEBUG::_is_writable()
	 *
	 * @param mixed $fl
	 */
	function _is_writable( $fl ) {
		// if ( is_dir( $fl ) || $fl{strlen( $fl ) - 1} == '/' ) $fl = $this->tslashit($fl).microtime().'.tmp';
		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',75);

		if ( is_writable($fl) || touch($fl) ) $return=true;
		else {
			$exists = ( bool )$this->_file_exists( $fl );
			$dir = ( bool )is_dir( $fl );
	
			$chmod_dir = defined( 'FS_CHMOD_DIR' ) ? FS_CHMOD_DIR : ( fileperms( WP_CONTENT_DIR ) & 0777 | 0755 );
			$chmod_file = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : 0644;

			if ( $exists && ! @chmod($fl, $chmod_file) && ! @chmod(dirname($file), $chmod_dir) && ! @chmod($file, $chmod_file) && ! @touch($fl) ) {
				$return = false;
			} else {
				if ( $dir === true ) {
					$tfl = $fl . '/' . $this->_get_rand_str( 8 ) . '.tmp';
				}
				
				$w = ( bool )( $this->_fclose($this->_fopen($fl, 'a')) || $this->_fclose($this->_fopen($fl, 'x')) || $this->_fclose($this->_fopen($fl, 'w')) ) ? true : false;
				
				if ( $d === true || $e === false ) {
					$this->_unlink( $fl );
				}

				$return = $w;
			}
		}

		$this->t( __FILE__, __CLASS__, __FUNCTION__, __LINE__, '',75);
		return $return;
	}


	/** AA_DEBUG::rvar_dump($ss=false)
	 *
	 * @return string|output of var_dump
	 */
	function rvar_dump($ss=false) {
		ob_start();
		var_dump( $ss );
		return ob_get_clean();
	}


	/** AA_DEBUG::rvar_export($ss=false)
	 *
	 * @return string|output of var_export
	 */
	function rvar_export($ss=false) {
		ob_start();
		var_export( $ss );
		return ob_get_clean();
	}


}
endif;














function init_aa_debug_object() {
	static $aa_debug_object=null;
	if ( null === $aa_debug_object )  {
		$aa_debug_object = new AA_DEBUG();
		$GLOBALS['aa_debug_object'] =& $aa_debug_object;
		$aa_debug_object->Init();
	}
	//return $aa_debug_object;
}


add_action( 'init', 'init_aa_debug_object', 0 );


//add_action( 'init', array(&$AA_DEBUG, 'live_debug') );
//add_action( 'shutdown', array(&$AA_DEBUG, 'live_debug') );
//if (ob_start() && (print("\n+-- ".__LINE__." ------------------------------[ ".__FILE__." ]  [END]\n")) && !!error_log(ob_get_clean()))true;



// EOF