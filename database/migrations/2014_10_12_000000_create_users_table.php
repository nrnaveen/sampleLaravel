<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up(){
		Schema::create('users', function(Blueprint $table){
			$table->increments('id');
			$table->string('firstname')->nullable();
			$table->string('lastname')->nullable();
			$table->enum('role', ['consultant', 'manager', 'collaborator']);
			$table->string('email')->unique();
			$table->string('password');
			$table->string('mobile')->nullable();
			$table->string('address')->nullable();
			$table->dateTime('creation_date')->nullable();
			$table->string('image')->nullable();
			$table->string('api_token', 60)->unique()->nullable();
			$table->rememberToken();
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down(){
		Schema::dropIfExists('users');
	}
}