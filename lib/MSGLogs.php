<?php
/**
* Manage all software's error
*
* @package  PHP_CSS_Generator
* @version  1.0.0b
* @author   Mickael PERNIN <mickael.pernin@epitech.eu>
* 
*/
class MSGLogs {

/**
* @var const int INTERNAL_ERROR  An error code
*/
const INTERNAL_ERROR = 503;

/**
* @var const int NO_ARGUMENT  An error code
*/
const NO_ARGUMENT = 1;

/**
* @var const int NO_DIRECTORY  An error code
*/
const NO_DIRECTORY = 2;

/**
* @var const int FILE_NO_EXISTS  An error code
*/
const FILE_NO_EXISTS = 3;

/**
* @var const int FILE_NOT_FOLDER  An error code
*/
const FILE_NOT_FOLDER = 4;

/**
* @var const int EMPTY_OR_NO_PNG  An error code
*/
const EMPTY_OR_NO_PNG = 5;

/**
* @var const int CANNOT_ACCESS_FOLDER  An error code
*/
const CANNOT_ACCESS_FOLDER = 6;

/**
* @var const int NOT_IMG  An error code
*/
const NOT_IMG = 7;

/**
* @var const int INVALID_FLAG  An error code
*/
const INVALID_FLAG = 8;

/**
* @var const int INVALID_ARGUMENT  An error code
*/
const INVALID_ARGUMENT = 9;

/**
* @var const int EMPTY_ARGUMENT  An error code
*/
const EMPTY_ARGUMENT = 10;

/**
* @var const int BAD_ARGUMENT_TYPE  An error code
*/
const BAD_ARGUMENT_TYPE = 11;

/**
* @var const int FLAG_NOT_EXISTS  An error code
*/
const FLAG_NOT_EXISTS = 12;

/**
* @var const int NOT_PNG  An error code
*/
const NOT_PNG = 13;

/**
* @var const[] ERRORS_MSGS  Content all error message
*/
const ERRORS_MSGS = [
  503 => "Internal software error",
  1 => "Can't find argument",
  2 => "No directory selected",
  3 => "Can't find directory %s",
  4 => "%s isn't a directory",
  5 => "%s is empty or no png file founded in this directory",
  6 => "Can't read %s directory",
  7 => "%s isn't a real image file",
  8 => "invald flag %s",
  9 => "invalid argument %s for %s",
  10 => "empty argument for %s",
  11 => "bad argument type for %s",
  12 => "%s isn't a flag",
  13 => "%s isn't a png file",
];

/**
* 
* Echo the error message
*
* @param int $code Content the error code
* @param array[] $_msg Content the param msg to replace %s
*
* @return int
* @static
* @access public
*/
public static function showError_($_code = 503, $_msg = []) {
  if (!array_key_exists($_code, self::ERRORS_MSGS)) {
    $_code = self::INTERNAL_ERROR;
    echo "\nError : " . self::ERRORS_MSGS[$_code] . "\n\n";
    return $_code;
  }

  $str = self::ERRORS_MSGS[$_code];
  if (!empty($_msg)) {
    $_msg = explode(",", $_msg);
    foreach ($_msg as $_msg) {
      $str = preg_replace("/%s/", $_msg, $str, 1);
    }
  }

  echo "\nError : " . $str . "\n\n";
  return $_code;
}

}