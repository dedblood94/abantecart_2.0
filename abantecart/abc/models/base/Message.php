<?php

namespace abc\models\base;

use abc\models\AModelBase;

/**
 * Class Message
 *
 * @property int $msg_id
 * @property string $title
 * @property string $message
 * @property string $status
 * @property int $viewed
 * @property int $repeated
 * @property \Carbon\Carbon $date_added
 * @property \Carbon\Carbon $date_modified
 *
 * @package abc\models
 */
class Message extends AModelBase
{
    protected $primaryKey = 'msg_id';
    public $timestamps = false;

    protected $casts = [
        'viewed'   => 'int',
        'repeated' => 'int',
    ];

    protected $dates = [
        'date_added',
        'date_modified',
    ];

    protected $fillable = [
        'title',
        'message',
        'status',
        'viewed',
        'repeated',
        'date_added',
        'date_modified',
    ];
}
