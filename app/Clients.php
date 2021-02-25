<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Clients extends Model {

	protected $table = 'clients';

	protected $fillable = [
		'id',
		'firstname',
		'lastname',
		'email',
		'mobile',
		'address',
		'status',
		'color',
		'creation_date',
		'image',
		'created_at',
		'updated_at'
	];

	public function mission(){
		return $this->hasMany('App\Mission', 'client_id', 'id');
	}

	public function getNameAttribute(){
		return ucfirst($this->firstname) . ' ' . ucfirst($this->lastname);
	}
}