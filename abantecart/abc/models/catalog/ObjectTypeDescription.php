<?php
/**
 * AbanteCart, Ideal Open Source Ecommerce Solution
 * http://www.abantecart.com
 *
 * Copyright 2011-2018 Belavier Commerce LLC
 *
 * This source file is subject to Open Software License (OSL 3.0)
 * License details is bundled with this package in the file LICENSE.txt.
 * It is also available at this URL:
 * <http://www.opensource.org/licenses/OSL-3.0>
 *
 * UPGRADE NOTE:
 * Do not edit or add to this file if you wish to upgrade AbanteCart to newer
 * versions in the future. If you wish to customize AbanteCart for your
 * needs please refer to http://www.abantecart.com for more information.
 */

namespace abc\models\catalog;

use abc\models\BaseModel;
use abc\models\locale\Language;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ProductTypeDescription
 *
 * @property string name
 * @property string description
 * @property int stage_id
 *
 * @package abc\models\base
 */
class ObjectTypeDescription extends BaseModel
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    protected $primaryKeySet = [
        'object_type_id',
        'language_id',
    ];

    public $timestamps = false;

    protected $casts = [
        'object_type_id'   => 'int',
        'language_id' => 'int',
    ];

    protected $fillable = [
        'object_type_id',
        'language_id',
        'name',
        'description',
        'stage_id',
    ];

    public function product_type()
    {
        return $this->belongsTo(ObjectType::class, 'object_type_id');
    }

    public function language()
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

}