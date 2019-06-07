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

namespace abc\core\engine;

use Silber\Bouncer\Bouncer;

if (!class_exists('abc\core\ABC')) {
    header('Location: static_pages/?forbidden='.basename(__FILE__));
}

final class Registry
{
    private $data = array();
    static private $instance = null;

    /**
     * @return Registry
     */
    static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Registry();
        }
        return self::$instance;
    }

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * @param $key string
     *
     * @return \abc\core\lib\CSRFToken|\abc\core\lib\ARequest|ALoader|\abc\core\lib\ADocument|\abc\core\lib\ADB|\abc\core\lib\AConfig|AHtml|ExtensionsApi|\abc\core\lib\AExtensionManager|\abc\core\lib\ALanguageManager|\abc\core\lib\ASession|\abc\core\cache\ACache|\abc\core\lib\AMessage|\abc\core\lib\ALog|\abc\core\lib\AResponse|\abc\core\lib\AUser|ARouter|\abc\core\lib\ACurrency|\abc\models\admin\ModelLocalisationLanguageDefinitions|\abc\models\admin\ModelLocalisationCountry|\abc\models\admin\ModelSettingSetting|\abc\models\admin\ModelToolOnlineNow|\abc\core\lib\ADataEncryption|\abc\core\lib\ADownload|\abc\core\lib\AOrderStatus|\abc\core\lib\AIMManager|\abc\core\lib\ACustomer|Bouncer
     */
    public function get($key)
    {
        return (isset($this->data[$key]) ? $this->data[$key] : null);
    }

    /**
     * @param $key string
     * @param $value mixed
     */
    public function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @param $key string
     *
     * @return bool
     */
    public function has($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Return objects by static call
     * @param $name
     *
     * @return mixed - object or null
     */
    public static function __callStatic($name, $arguments)
    {
        return self::getInstance()->get($name);
    }
}
