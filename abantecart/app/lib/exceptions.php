<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2017 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/
namespace abc\lib;
use abc\core\Registry;

if (!defined('DIR_CORE')){
	header('Location: static_pages/');
}

require_once(DIR_LIB . 'exceptions/exception_codes.php');
require_once(DIR_LIB . 'exceptions/exception.php');

/**
 * called for php errors
 *
 * @param int $errno
 * @param string $errstr
 * @param string $err_file
 * @param string $err_line
 * @return null
 */
function ac_error_handler($errno, $errstr, $err_file, $err_line){

	if (class_exists('\abc\core\Registry')){
		$registry = Registry::getInstance();
		if ($registry->get('force_skip_errors')){
			return null;
		}
	}

	//skip notice
	if ($errno == E_NOTICE) {
		return null;
	}

	try{
		throw new AException($errno, $errstr, $err_file, $err_line);
	} catch(AException $e){
		//var_Dump($e); exit;
		ac_exception_handler($e);
	}
	return true;
}

/**
 * called for caught exceptions
 * @param  AException $e
 * @return null
 */
function ac_exception_handler($e){
	if (class_exists('\abc\core\Registry')){
		$registry = Registry::getInstance();
		if ($registry->get('force_skip_errors')){
			return null;
		}
	}

	//fix for default PHP handler call in third party PHP libraries
	if (!method_exists($e, 'logError')){
		$e = new AException($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
	}

	if (class_exists('\abc\core\Registry')){
		$registry = Registry::getInstance();
		$config = $registry->get('config');
		if (!$config || (!$config->has('config_error_log') && !$config->has('config_error_display'))){
			//we have no config or both settings are missing. 
			$e->logError();
			$e->displayError();
		} else{
			if ($config->has('config_error_log') && $config->get('config_error_log')){
				$e->logError();
			}
			if ($config->has('config_error_display') && $config->get('config_error_display')){
				$e->displayError();
			}
		}
		//do we have fatal error and need to end?
		if ($e->errorCode() >= 10000 && !defined('INSTALL')){
			$e->showErrorPage();
		} else{
			//nothing critical
			return null;
		}
	}

	//no registry, something totally wrong
	$e->logError();
	$e->displayError();
	$e->showErrorPage();
}

/**
 * called on application shutdown
 * check if shutdown was caused by error and write it to log
 */
function ac_shutdown_handler(){
	$error = error_get_last();
	if (!is_array($error) || !in_array($error['type'], array (E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR))){
		return null;
	}
	$exception = new AException($error['type'], $error['message'], $error['file'], $error['line']);
	$exception->logError();
}

set_error_handler("\abc\lib\ac_error_handler");
register_shutdown_function("\abc\lib\ac_shutdown_handler");
set_exception_handler("\abc\lib\ac_exception_handler");
