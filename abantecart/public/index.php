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
namespace abc;
use abc\core\ABC;

require dirname(__DIR__).DIRECTORY_SEPARATOR.'abc'.DIRECTORY_SEPARATOR.'core'.DIRECTORY_SEPARATOR.'abc.php';
// Windows IIS Compatibility
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	ABC::env('IS_WINDOWS', true);
}
ABC::env('INDEX_FILE', basename(__FILE__));
$app = new ABC();
$app->run();
