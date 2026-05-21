<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('address');
            $table->date('date_of_birth')->nullable()->after('avatar');
            $table->enum('gender', ['male', 'female'])->nullable()->after('date_of_birth');
            $table->string('parent_name')->nullable()->after('gender');
            $table->string('parent_phone')->nullable()->after('parent_name');
            $table->enum('status', ['active', 'inactive', 'locked'])->default('active')->after('parent_phone');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'avatar',
                'date_of_birth',
                'gender',
                'parent_name',
                'parent_phone',
                'status',
            ]);
            $table->dropSoftDeletes();
        });
    }
};
