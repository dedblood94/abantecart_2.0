<?php

namespace abc\models\system;

use abc\models\BaseModel;

/**
 * Class Dataset
 *
 * @property int $dataset_id
 * @property string $dataset_name
 * @property string $dataset_key
 *
 * @property \Illuminate\Database\Eloquent\Collection $dataset_definitions
 * @property \Illuminate\Database\Eloquent\Collection $dataset_properties
 *
 * @package abc\models
 */
class Dataset extends BaseModel
{
    protected $primaryKey = 'dataset_id';
    public $timestamps = false;

    protected $fillable = [
        'dataset_name',
        'dataset_key',
    ];

    public function dataset_definitions()
    {
        return $this->hasMany(DatasetDefinition::class, 'dataset_id');
    }

    public function dataset_properties()
    {
        return $this->hasMany(DatasetProperty::class, 'dataset_id');
    }
}
