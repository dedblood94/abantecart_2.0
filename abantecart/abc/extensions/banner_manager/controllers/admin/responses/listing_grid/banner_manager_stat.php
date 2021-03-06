<?php
/*------------------------------------------------------------------------------
  $Id$

  AbanteCart, Ideal OpenSource Ecommerce Solution
  http://www.AbanteCart.com

  Copyright © 2011-2018 Belavier Commerce LLC

  This source file is subject to Open Software License (OSL 3.0)
  License details is bundled with this package in the file LICENSE.txt.
  It is also available at this URL:
  <http://www.opensource.org/licenses/OSL-3.0>

 UPGRADE NOTE:
   Do not edit or add to this file if you wish to upgrade AbanteCart to newer
   versions in the future. If you wish to customize AbanteCart for your
   needs please refer to http://www.AbanteCart.com for more information.
------------------------------------------------------------------------------*/

namespace abc\controllers\admin;

use abc\core\engine\AController;
use abc\core\lib\AFilter;
use abc\core\lib\AJson;
use abc\extensions\banner_manager\models\admin\extension\ModelExtensionBannerManager;
use stdClass;

/**
 * Class ControllerResponsesListingGridBannerManagerStat
 *
 * @package abc\controllers\admin
 * @property ModelExtensionBannerManager $model_extension_banner_manager
 */
class ControllerResponsesListingGridBannerManagerStat extends AController
{
    public function main()
    {
        //init controller data
        $this->extensions->hk_InitData( $this, __FUNCTION__ );

        $this->loadLanguage( 'banner_manager/banner_manager' );
        $this->loadModel( 'extension/banner_manager' );

        $filter_params = ['name', 'banner_group_name', 'type', 'cnt'];
        $filter_grid = new AFilter( [
            'method'                   => 'post',
            'grid_filter_params'       => $filter_params,
            'additional_filter_string' => '',
        ]);

        $total = $this->model_extension_banner_manager->getBannersStat( $filter_grid->getFilterData(), 'total_only' );
        if ( $total > 0 ) {
            $total_pages = ceil( $total / 10 );
        } else {
            $total_pages = 0;
        }

        $response = new stdClass();
        $response->page = $this->request->post['page'];
        $response->total = $total_pages;
        $response->records = $total;

        $results = $this->model_extension_banner_manager->getBannersStat( $filter_grid->getFilterData() );

        $i = 0;
        foreach ( $results as $result ) {

            $response->rows[$i]['id'] = $result['banner_id'];
            $response->rows[$i]['cell'] = [
                $result['name'],
                $result['banner_group_name'],
                $result['clicked'],
                $result['viewed'],
                $result['percent'],
            ];
            $i++;
        }

        //update controller data
        $this->extensions->hk_UpdateData( $this, __FUNCTION__ );

        $this->load->library( 'json' );
        $this->response->setOutput( AJson::encode( $response ) );
    }

}