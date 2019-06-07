<?php

namespace abc\models\user;

use abc\models\BaseModel;
use Silber\Bouncer\Database\Concerns\IsAbility;

class Ability extends BaseModel
{
    use IsAbility;

    protected $table = 'abilities';

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'title'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'int',
        'only_owned' => 'boolean',
    ];


}
