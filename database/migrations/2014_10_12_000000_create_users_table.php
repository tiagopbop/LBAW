<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('authenticated_user', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->date('user_creation_date')->default(DB::raw('CURRENT_DATE'));
            $table->boolean('suspended_status')->default(false);
            $table->string('pfp')->nullable();
            $table->string('pronouns', 50)->nullable();
            $table->text('bio')->nullable();
            $table->string('country', 100)->nullable();
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('authenticated_user');
    }
    
};
