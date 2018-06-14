<?php

namespace abc\models\base;

use abc\models\AModelBase;

/**
 * Class TaxClassDescription
 *
 * @property int $tax_class_id
 * @property int $language_id
 * @property string $title
 * @property string $description
 *
 * @property TaxClass $tax_class
 * @property Language $language
 *
 * @package abc\models
 */
class TaxClassDescription extends AModelBase
{
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'tax_class_id' => 'int',
        'language_id'  => 'int',
    ];

    protected $fillable = [
        'title',
        'description',
    ];

    public function tax_class()
    {
        return $this->belongsTo(TaxClass::class, 'tax_class_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }
}
