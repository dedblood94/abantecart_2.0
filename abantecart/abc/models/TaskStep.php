<?php

/**
 * Created by Reliese Model.
 * Date: Sun, 13 May 2018 01:25:45 +0000.
 */

namespace App\Models;

use Reliese\Database\Eloquent\Model as Eloquent;

/**
 * Class AcTaskStep
 * 
 * @property int $step_id
 * @property int $task_id
 * @property int $sort_order
 * @property int $status
 * @property \Carbon\Carbon $last_time_run
 * @property int $last_result
 * @property int $max_execution_time
 * @property string $controller
 * @property string $settings
 * @property \Carbon\Carbon $date_added
 * @property \Carbon\Carbon $date_modified
 *
 * @package App\Models
 */
class AcTaskStep extends Eloquent
{
	protected $primaryKey = 'step_id';
	public $timestamps = false;

	protected $casts = [
		'task_id' => 'int',
		'sort_order' => 'int',
		'status' => 'int',
		'last_result' => 'int',
		'max_execution_time' => 'int'
	];

	protected $dates = [
		'last_time_run',
		'date_added',
		'date_modified'
	];

	protected $fillable = [
		'task_id',
		'sort_order',
		'status',
		'last_time_run',
		'last_result',
		'max_execution_time',
		'controller',
		'settings',
		'date_added',
		'date_modified'
	];
}
