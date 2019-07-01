<?php
/**
 * AbanteCart auto-generated migration file
 */

use abc\models\customer\Customer;
use abc\models\user\User;
use Phinx\Migration\AbstractMigration;

class CreateBouncerTables extends AbstractMigration
{
    /**
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     */

    public function up()
    {
        $prefix = $this->getAdapter()->getOption('table_prefix');

        $table = $this->table('abilities');
        if (!$table->exists()) {
            $table->addColumn('name', 'string')
                ->addColumn('parent_id', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('title', 'string', ['null' => true])
                ->addColumn('entity_id', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('entity_type', 'string', ['null' => true])
                ->addColumn('only_owned', 'boolean', ['default' => false])
                ->addColumn('user_model', 'string', ['null' => true])
                ->addColumn('options', 'text', ['null' => true])
                ->addColumn('scope', 'integer', ['null' => true])
                ->addTimestamps('date_added', 'date_modified')
                ->addColumn('date_deleted', 'timestamp', ['default' => null, 'null' => true])
                ->addIndex(['scope'])
                ->create();

        }

        $table = $this->table('roles');
        if (!$table->exists()) {
            $table->addColumn('name', 'string')
                ->addColumn('title', 'string', ['null' => true])
                ->addColumn('user_model', 'string', ['null' => true])
                ->addColumn('level', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('scope', 'integer', ['null' => true])
                ->addTimestamps('date_added', 'date_modified')
                ->addColumn('date_deleted', 'timestamp', ['default' => null, 'null' => true])
                ->addIndex(['scope'])
                ->addIndex(['name', 'scope'], [
                    'unique' => true,
                    'name'   => 'roles_name_unique',
                ])
                ->create();
        }

        $table = $this->table('assigned_roles');
        if (!$table->exists()) {
            $table->addColumn('role_id', 'integer')
                ->addColumn('entity_id', 'integer', ['signed' => false])
                ->addColumn('entity_type', 'string')
                ->addColumn('restricted_to_id', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('restricted_to_type', 'string', ['null' => true])
                ->addColumn('scope', 'integer', ['null' => true])
                ->addIndex(['role_id'])
                ->addIndex(['scope'])
                ->addIndex(['entity_id', 'entity_type', 'scope'], [
                    'unique' => true,
                    'name'   => 'assigned_roles_entity_index',
                ])
                ->create();

            $table->addForeignKey('role_id', 'roles', 'id',
                [
                    'delete' => 'CASCADE',
                    'update' => 'CASCADE',
                ])->save();

        }

        $table = $this->table('permissions');
        if (!$table->exists()) {
            $table->addColumn('ability_id', 'integer')
                ->addColumn('entity_id', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('entity_type', 'string', ['null' => true])
                ->addColumn('forbidden', 'boolean', ['default' => false])
                ->addColumn('scope', 'integer', ['null' => true])
                ->addIndex(['ability_id'])
                ->addIndex(['scope'])
                ->addIndex(['ability_id', 'entity_id', 'entity_type', 'scope'], [
                    'unique' => true,
                    'name'   => 'permissions_entity_index',
                ])
                ->create();
            $table->addForeignKey('ability_id', 'abilities', 'id',
                [
                    'delete' => 'CASCADE',
                    'update' => 'CASCADE',
                ])->save();
        }

        $dafaultAbilities = [
            'read' => [
                'name'        => 'read',
                'title'       => 'Read Dashboard',
                'entity_type' => 'Dashboard',
                'user_model' => User::class,
                'parent' => null,
                'scope' => 0,
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Sales',
                'entity_type' => 'Sales',
                'user_model' => User::class,
                'parent'      => null,
                'scope' => 0,
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Sales',
                'entity_type' => 'Sales',
                'user_model' => User::class,
                'parent'      => null,
                'scope' => 0,
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Sales',
                'entity_type' => 'Sales',
                'user_model' => User::class,
                'parent'      => null,
                'scope' => 0,
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Orders',
                'entity_type' => 'Order',
                'user_model' => User::class,
                'parent'      => 'Sales',
                'scope' => 0,
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Orders',
                'entity_type' => 'Order',
                'user_model' => User::class,
                'parent'      => 'Sales',
                'scope' => 0,
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Orders',
                'entity_type' => 'Order',
                'user_model' => User::class,
                'parent'      => 'Sales',
                'scope' => 0,
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Customers',
                'entity_type' => 'Customer',
                'user_model' => User::class,
                'parent'      => 'Sales',
                'scope' => 0,
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Customers',
                'entity_type' => 'Customer',
                'user_model' => User::class,
                'parent'      => 'Sales',
                'scope' => 0,
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Customers',
                'entity_type' => 'Customer',
                'user_model' => User::class,
                'parent'      => 'Sales',
                'scope' => 0,
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Customer Groups',
                'entity_type' => 'Customer Groups',
                'user_model' => User::class,
                'parent'      => 'Sales',
                'scope' => 0,
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Customer Groups',
                'entity_type' => 'Customer Groups',
                'user_model' => User::class,
                'parent'      => 'Sales',
                'scope' => 0,
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Customer Groups',
                'entity_type' => 'Customer Groups',
                'user_model' => User::class,
                'parent'      => 'Sales',
                'scope' => 0,
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Coupons',
                'entity_type' => 'Coupons',
                'user_model' => User::class,
                'parent'      => 'Sales',
                'scope' => 0,
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Coupons',
                'entity_type' => 'Coupons',
                'user_model' => User::class,
                'parent'      => 'Sales',
                'scope' => 0,
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Coupons',
                'entity_type' => 'Coupons',
                'user_model' => User::class,
                'parent'      => 'Sales',
                'scope' => 0,
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Send Mail',
                'entity_type' => 'Send Mail',
                'user_model' => User::class,
                'parent'      => 'Sales',
                'scope' => 0,
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Catalog',
                'entity_type' => 'Catalog',
                'user_model' => User::class,
                'parent'      => null,
                'scope' => 0,
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Catalog',
                'entity_type' => 'Catalog',
                'user_model' => User::class,
                'parent'      => null,
                'scope' => 0,
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Catalog',
                'entity_type' => 'Catalog',
                'user_model' => User::class,
                'parent'      => null,
                'scope' => 0,
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Categories',
                'entity_type' => 'Categories',
                'user_model' => User::class,
                'parent'      => 'Catalog',
                'scope' => 0,
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Categories',
                'entity_type' => 'Categories',
                'user_model' => User::class,
                'parent'      => 'Catalog',
                'scope' => 0,
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Categories',
                'entity_type' => 'Categories',
                'user_model' => User::class,
                'parent'      => 'Catalog',
                'scope' => 0,
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Products',
                'entity_type' => 'Products',
                'user_model' => User::class,
                'parent'      => 'Catalog',
                'scope' => 0,
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Products',
                'entity_type' => 'Products',
                'user_model' => User::class,
                'parent'      => 'Catalog',
                'scope' => 0,
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Products',
                'entity_type' => 'Products',
                'user_model' => User::class,
                'parent'      => 'Catalog',
                'scope' => 0,
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Manufacturers',
                'entity_type' => 'Manufacturers',
                'user_model' => User::class,
                'parent'      => 'Catalog',
                'scope' => 0,
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Manufacturers',
                'entity_type' => 'Manufacturers',
                'user_model' => User::class,
                'parent'      => 'Catalog',
                'scope' => 0,
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Manufacturers',
                'entity_type' => 'Manufacturers',
                'user_model' => User::class,
                'parent'      => 'Catalog',
                'scope' => 0,
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Downloads',
                'entity_type' => 'Downloads',
                'user_model' => User::class,
                'parent'      => 'Catalog',
                'scope' => 0,
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Downloads',
                'entity_type' => 'Downloads',
                'user_model' => User::class,
                'parent'      => 'Catalog',
                'scope' => 0,
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Downloads',
                'entity_type' => 'Downloads',
                'user_model' => User::class,
                'parent'      => 'Catalog',
                'scope' => 0,
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Reviews',
                'entity_type' => 'Reviews',
                'user_model' => User::class,
                'parent'      => 'Catalog',
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Reviews',
                'entity_type' => 'Reviews',
                'user_model' => User::class,
                'parent'      => 'Catalog',
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Reviews',
                'entity_type' => 'Reviews',
                'user_model' => User::class,
                'parent'      => 'Catalog',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Attributes',
                'entity_type' => 'Attributes',
                'user_model' => User::class,
                'parent'      => 'Catalog',
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Attributes',
                'entity_type' => 'Attributes',
                'user_model' => User::class,
                'parent'      => 'Catalog',
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Attributes',
                'entity_type' => 'Attributes',
                'user_model' => User::class,
                'parent'      => 'Catalog',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Object Types',
                'entity_type' => 'Object Types',
                'user_model' => User::class,
                'parent'      => 'Catalog',
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Object Types',
                'entity_type' => 'Object Types',
                'user_model' => User::class,
                'parent'      => 'Catalog',
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Object Types',
                'entity_type' => 'Object Types',
                'user_model' => User::class,
                'parent'      => 'Catalog',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Media Manager',
                'entity_type' => 'Media Manager',
                'user_model' => User::class,
                'parent'      => 'Catalog',
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Media Manager',
                'entity_type' => 'Media Manager',
                'user_model' => User::class,
                'parent'      => 'Catalog',
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Media Manager',
                'entity_type' => 'Media Manager',
                'user_model' => User::class,
                'parent'      => 'Catalog',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Design',
                'entity_type' => 'Design',
                'user_model' => User::class,
                'parent'      => null,
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Design',
                'entity_type' => 'Design',
                'user_model' => User::class,
                'parent'      => null,
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Design',
                'entity_type' => 'Design',
                'user_model' => User::class,
                'parent'      => null,
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Templates',
                'entity_type' => 'Templates',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Templates',
                'entity_type' => 'Templates',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Templates',
                'entity_type' => 'Templates',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Layouts',
                'entity_type' => 'Layouts',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Layouts',
                'entity_type' => 'Layouts',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Layouts',
                'entity_type' => 'Layouts',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Blocks',
                'entity_type' => 'Blocks',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Blocks',
                'entity_type' => 'Blocks',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Blocks',
                'entity_type' => 'Blocks',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Menu',
                'entity_type' => 'Menu',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Menu',
                'entity_type' => 'Menu',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Menu',
                'entity_type' => 'Menu',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Content',
                'entity_type' => 'Content',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Content',
                'entity_type' => 'Content',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Content',
                'entity_type' => 'Content',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Banner Manager',
                'entity_type' => 'Banner Manager',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Banner Manager',
                'entity_type' => 'Banner Manager',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Banner Manager',
                'entity_type' => 'Banner Manager',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Forms Manager',
                'entity_type' => 'Forms Manager',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Forms Manager',
                'entity_type' => 'Forms Manager',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Forms Manager',
                'entity_type' => 'Forms Manager',
                'user_model' => User::class,
                'parent'      => 'Design',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Extensions',
                'entity_type' => 'Extensions',
                'user_model' => User::class,
                'parent'      => null,
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Extensions',
                'entity_type' => 'Extensions',
                'user_model' => User::class,
                'parent'      => null,
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Extensions',
                'entity_type' => 'Extensions',
                'user_model' => User::class,
                'parent'      => null,
            ],
            [
                'name'        => 'read',
                'title'       => 'Read System',
                'entity_type' => 'System',
                'user_model' => User::class,
                'parent'      => null,
            ],
            [
                'name'        => 'write',
                'title'       => 'Write System',
                'entity_type' => 'System',
                'user_model' => User::class,
                'parent'      => null,
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete System',
                'entity_type' => 'System',
                'user_model' => User::class,
                'parent'      => null,
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Settings',
                'entity_type' => 'Settings',
                'user_model' => User::class,
                'parent'      => 'System',
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Settings',
                'entity_type' => 'Settings',
                'user_model' => User::class,
                'parent'      => 'System',
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Settings',
                'entity_type' => 'Settings',
                'user_model' => User::class,
                'parent'      => 'System',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Users',
                'entity_type' => 'Users',
                'user_model' => User::class,
                'parent'      => 'System',
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Users',
                'entity_type' => 'Users',
                'user_model' => User::class,
                'parent'      => 'System',
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Users',
                'entity_type' => 'Users',
                'user_model' => User::class,
                'parent'      => 'System',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Localization',
                'entity_type' => 'Localization',
                'user_model' => User::class,
                'parent'      => 'System',
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Localization',
                'entity_type' => 'Localization',
                'user_model' => User::class,
                'parent'      => 'System',
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Localization',
                'entity_type' => 'Localization',
                'user_model' => User::class,
                'parent'      => 'System',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Data',
                'entity_type' => 'Data',
                'user_model' => User::class,
                'parent'      => 'System',
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Data',
                'entity_type' => 'Data',
                'user_model' => User::class,
                'parent'      => 'System',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Updater',
                'entity_type' => 'Updater',
                'user_model' => User::class,
                'parent'      => 'System',
            ],
            [
                'name'        => 'delete',
                'title'       => 'Read Cache',
                'entity_type' => 'Cache',
                'user_model' => User::class,
                'parent'      => 'System',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Messages',
                'entity_type' => 'Messages',
                'user_model' => User::class,
                'parent'      => 'System',
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Messages',
                'entity_type' => 'Messages',
                'user_model' => User::class,
                'parent'      => 'System',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Logs',
                'entity_type' => 'Logs',
                'user_model' => User::class,
                'parent'      => 'System',
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Logs',
                'entity_type' => 'Logs',
                'user_model' => User::class,
                'parent'      => 'System',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Reports',
                'entity_type' => 'Reports',
                'user_model' => User::class,
                'parent'      => null,
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Sales',
                'entity_type' => 'Sales Reports',
                'user_model' => User::class,
                'parent'      => 'Reports',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Customers',
                'entity_type' => 'Customers Reports',
                'user_model' => User::class,
                'parent'      => 'Reports',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Products',
                'entity_type' => 'Products Reports',
                'user_model' => User::class,
                'parent'      => 'Reports',
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Banner Statistic',
                'entity_type' => 'Banner Statistic',
                'user_model' => User::class,
                'parent'      => 'Reports',
            ],

            //Customer Abilities

            [
                'name'        => 'read',
                'title'       => 'Read Orders',
                'entity_type' => 'Orders',
                'user_model' => Customer::class,
                'parent'      => null,
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Orders',
                'entity_type' => 'Orders',
                'user_model' => Customer::class,
                'parent'      => null,
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Orders',
                'entity_type' => 'Orders',
                'user_model' => Customer::class,
                'parent'      => null,
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Transactions',
                'entity_type' => 'Transactions',
                'user_model' => Customer::class,
                'parent'      => null,
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Transactions',
                'entity_type' => 'Transactions',
                'user_model' => Customer::class,
                'parent'      => null,
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Transactions',
                'entity_type' => 'Transactions',
                'user_model' => Customer::class,
                'parent'      => null,
            ],
            [
                'name'        => 'read',
                'title'       => 'Read Products',
                'entity_type' => 'Products',
                'user_model' => Customer::class,
                'parent'      => null,
            ],
            [
                'name'        => 'write',
                'title'       => 'Write Products',
                'entity_type' => 'Products',
                'user_model' => Customer::class,
                'parent'      => null,
            ],
            [
                'name'        => 'delete',
                'title'       => 'Delete Products',
                'entity_type' => 'Products',
                'user_model' => Customer::class,
                'parent'      => null,
            ],
        ];

        $connection = $this->getAdapter()->getConnection();


        foreach ($dafaultAbilities as $ability) {
            $ability['scope'] = 0;
            if ($ability['parent']) {
                \H::df('SELECT * FROM '.$prefix.'abilities'.
                    ' WHERE entity_type=\''.$ability['parent'].'\' AND name=\''.$ability['name'].'\' AND user_model='.$connection->quote($ability['user_model']).' limit 1');

                $rows = $this->fetchAll('SELECT * FROM '.$prefix.'abilities'.
                    ' WHERE entity_type=\''.$ability['parent'].'\' AND name=\''.$ability['name'].'\' AND user_model='.$connection->quote($ability['user_model']).' limit 1');
                $parent_id = current($rows)['id'];
            }
            if ($parent_id) {
                $ability['parent_id'] = $parent_id;
            }
            unset($ability['parent'], $parent_id);

            $this->table('abilities')->insert($ability)->save();
        }
    }

    public function down()
    {
        $tableNames = [
            'permissions',
            'assigned_roles',
            'roles',
            'abilities',
        ];
        foreach ($tableNames as $tableName) {
            $table = $this->table($tableName);
            if ($table->exists()) {
                $table->drop();
                $table->save();
            }
        }
    }
}
