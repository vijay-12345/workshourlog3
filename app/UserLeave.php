<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLeave extends Model
{
    protected $table = 'user_leave';
	
	protected $primaryKey = 'id';
	
	public $timestamps = false;

	public $fillable = ['id','user_id','leave_days','start_date','end_date'];
	
	protected $hidden = [];

	public function task()
	{
		return $this->hasMany('App\Task','user_id','user_id');
	}

	public function users()
	{
		return $this->belongsTo('App\User','id','user_id');
	}

	
	
	// public static function getCityById($id)
	// {
	// 	return $city	= City::where('id',$id)->first();	
	// }
	// public static function insertGetId($data){
	// 	$data = array_merge(['lang_id'=>\Config::get('app.locale_prefix'),'status'=>1],$data);
	// 	self::create($data);
	// 	return \DB::getPdo()->lastInsertId();
	// }
}