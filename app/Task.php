<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table = 'task';
	
	protected $primaryKey = 'id';
	
	public $timestamps = false;

	public $fillable = ['id','user_id','project_id','date','completed','task_name','time_spent','estimated_time','time_spent_value','estimated_time_value'];
	
	protected $hidden = [];	

	public function user_leave() {
		return $this->belongsTo('App\UserLeave','user_id','user_id');
	}

	public function users()
	{
		return $this->belongsToMany('App\User','id','user_id');
	}


}
