<?php

namespace abc\models\user;

use abc\models\BaseModel;

class Permission extends BaseModel
{

    protected $table = 'permissions';


    /**
     * @var bool
     */
    public $timestamps = false;


}
