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
            $table->string('resume_path')->nullable()->after('email');
            $table->boolean('can_upload')->default(false)->after('resume_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'resume_path')) {
                $table->dropColumn('resume_path');
            }
            if (Schema::hasColumn('users', 'can_upload')) {
                $table->dropColumn('can_upload');
            }
        });
    }
};
