<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectDetail extends Model
{
    protected $table = 'project_detail';
	
	protected $primaryKey = 'id';
	
	public $timestamps = false;

	public $fillable = ['id','project_id','user_id','technology'];
	
	protected $hidden = [];
}
