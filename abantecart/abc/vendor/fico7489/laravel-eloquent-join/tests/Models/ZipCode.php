<?php

namespace Fico7489\Laravel\EloquentJoin\Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class ZipCode extends BaseModel
{
    use SoftDeletes;

    protected $table = 'zip_codes';

    protected $fillable = ['name', 'is_primary'];
}
