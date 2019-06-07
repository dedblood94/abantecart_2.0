<?php
/**
 * AbanteCart auto-generated migration file
 */

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
        $table = $this->table('abilities');
        if (!$table->exists()) {
            $table->addColumn('name', 'string')
                ->addColumn('title', 'string', ['null' => true])
                ->addColumn('entity_id', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('entity_type', 'string', ['null' => true])
                ->addColumn('only_owned', 'boolean', ['default' => false])
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
            $table->addColumn('role_id', 'integer', ['signed' => false])
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
            $table->addColumn('ability_id', 'integer', ['signed' => false])
                ->addColumn('entity_id', 'integer', ['null' => true, 'signed' => false])
                ->addColumn('entity_type', 'string', ['null' => true])
                ->addColumn('forbidden', 'boolean', ['default' => false])
                ->addColumn('scope', 'integer', ['null' => true])
                ->addIndex(['ability_id'])
                ->addIndex(['scope'])
                ->addIndex(['entity_id', 'entity_type', 'scope'], [
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
            }
        }
    }
}
