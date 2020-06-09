<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public const TABLE_NAME = 'users';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(static::TABLE_NAME, function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('email_verification_token', 32)->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->string('password');

            $table->unsignedBigInteger('primary_role_id')->nullable();
            $table->foreign('primary_role_id')->references('id')->on('roles')->onDelete('set null');

            $table->unsignedBigInteger('manager_id')->nullable();
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');

            $table->unsignedBigInteger('business_id')->nullable();
            $table->foreign('business_id')->references('id')->on('users')->onDelete('set null');
            $table->jsonb('properties')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(static::TABLE_NAME);
    }
}
