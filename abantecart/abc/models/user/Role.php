<?php

namespace abc\models\user;

use abc\models\BaseModel;
use Silber\Bouncer\Database\Concerns\IsRole;

class Role extends BaseModel
{
    use IsRole;

    protected $table = 'roles';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'title',
        'level',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id'        => 'int',
        'entity_id' => 'int',
        'level'     => 'int',
    ];

    public function getUsersRoles(array $params = [])
    {
        $arSelect = [$this->db->raw('SQL_CALC_FOUND_ROWS  *')];
        $roles = $this->select($arSelect);

        if ($params['start']) {
            $roles = $roles->offset($params['start']);
        }

        if ($params['limit']) {
            $roles = $roles->limit($params['limit']);
        }

        $roles = $roles->get();

        if (!$roles) {
            return false;
        }

        $roles = $roles->toArray();
        $totalNumRows = $this->db->sql_get_row_count();

        $rolesData = [];
        foreach ($roles as $role) {
            $role['total_num_rows'] = $totalNumRows;
            $rolesData[] = $role;
        }
        return $rolesData;
    }

}
