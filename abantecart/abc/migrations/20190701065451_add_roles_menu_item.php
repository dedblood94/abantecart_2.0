<?php
/**
* AbanteCart auto-generated migration file
*/

use abc\core\lib\AMenu;
use abc\core\lib\AResourceManager;
use Phinx\Migration\AbstractMigration;

class AddRolesMenuItem extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        // add new menu item
        $rm = new AResourceManager();
        $rm->setType( 'image' );

        $language_id = 1;
        $data = array();
        $data['resource_code'] = '<i class="fa fa-user-shield"></i>&nbsp;';
        $data['name'] = array( $language_id => 'Menu Icon User Roles' );
        $data['title'] = array( $language_id => '' );
        $data['description'] = array( $language_id => '' );
        $resource_id = $rm->addResource( $data );

        $menu = new AMenu ( "admin" );
        $menu->insertMenuItem( array(
                "item_id"         => "user_roles",
                "parent_id"       => "users",
                "item_text"       => "text_user_role",
                "item_url"        => "user/roles",
                "item_icon_rl_id" => $resource_id,
                "item_type"       => "system",
                "sort_order"      => "7",
            )
        );

        $menu->insertMenuItem( array(
                "item_id"         => "customer_roles",
                "parent_id"       => "sale",
                "item_text"       => "text_customer_role",
                "item_url"        => "user/roles&customer=1",
                "item_icon_rl_id" => $resource_id,
                "item_type"       => "system",
                "sort_order"      => "7",
            )
        );

    }

    public function down()
    {
        $menu = new AMenu ( "admin" );
        $menu->deleteMenuItem( "user_roles" );
        $menu->deleteMenuItem( "customer_roles" );
    }
}
