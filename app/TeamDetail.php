<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TeamDetail extends Model
{
    protected $table = 'team_detail';
	
	protected $primaryKey = 'id';
	
	public $timestamps = false;

	public $fillable = ['id','team_id','user_id'];
	
	protected $hidden = [];	

	
}
