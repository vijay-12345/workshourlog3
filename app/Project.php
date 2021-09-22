<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $table = 'project';
	
	protected $primaryKey = 'id';
	
	public $timestamps = false;

	public $fillable = ['id','team_id','name','tl_userid','technology'];
	
	protected $hidden = [];	
}
