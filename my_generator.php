#!/usr/bin/env php
<?php
/**
* Manage the software
*
* @package  PHP_CSS_Generator
* @version  1.0.0b
* @author   Mickael PERNIN <mickael.pernin@epitech.eu>
* 
*/
require_once("lib/CssGenerator.php");
require_once("lib/MSGLogs.php");

if (!isset($argv)) {
  echo "File error!\n";
  return true;
}

try {

  $CssGenerator = new CssGenerator($argv);

} catch (Exception $e) {

  MSGLogs::showError_($e->getCode(), $e->getMessage());

}
