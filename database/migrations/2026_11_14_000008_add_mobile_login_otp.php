<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sign-in with a mobile number and a one-time code (OTP).
 *
 * - users.mobile: the number a Super Admin / Admin registers for the user (optional; existing
 *   users keep working without it).
 * - login_otps: one row per code sent. Only a hash of the code is kept, never the code itself.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'mobile')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('mobile', 20)->nullable()->after('email');
                $table->index('mobile');
            });
        }

        if (! Schema::hasTable('login_otps')) {
            Schema::create('login_otps', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('mobile', 20)->index();
                $table->string('code_hash');
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->timestamp('expires_at');
                $table->timestamp('used_at')->nullable();
                $table->string('ip', 45)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('login_otps');

        if (Schema::hasColumn('users', 'mobile')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['mobile']);
                $table->dropColumn('mobile');
            });
        }
    }
};
