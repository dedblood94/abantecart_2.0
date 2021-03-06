<?php

namespace abc\models\system;

use abc\models\BaseModel;

/**
 * Class DatasetDefinition
 *
 * @property int $dataset_column_id
 * @property int $dataset_id
 * @property string $dataset_column_name
 * @property string $dataset_column_type
 * @property int $dataset_column_sort_order
 *
 * @property Dataset $dataset
 * @property \Illuminate\Database\Eloquent\Collection $dataset_column_properties
 * @property \Illuminate\Database\Eloquent\Collection $dataset_values
 *
 * @package abc\models
 */
class DatasetDefinition extends BaseModel
{
    protected $table = 'dataset_definition';
    protected $primaryKey = 'dataset_column_id';
    public $timestamps = false;

    protected $casts = [
        'dataset_id'                => 'int',
        'dataset_column_sort_order' => 'int',
    ];

    protected $fillable = [
        'dataset_id',
        'dataset_column_name',
        'dataset_column_type',
        'dataset_column_sort_order',
    ];

    public function dataset()
    {
        return $this->belongsTo(Dataset::class, 'dataset_id');
    }

    public function column_properties()
    {
        return $this->hasMany(DatasetColumnProperty::class, 'dataset_column_id');
    }

    public function values()
    {
        return $this->hasMany(DatasetValue::class, 'dataset_column_id');
    }
}
