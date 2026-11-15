<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Login codes (OTP) now go to the user's e-mail address instead of a mobile number.
 * login_otps keeps its mobile column for older rows; new rows record the e-mail.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('login_otps', function (Blueprint $table) {
            if (! Schema::hasColumn('login_otps', 'email')) {
                $table->string('email')->nullable()->index()->after('user_id');
            }
            $table->string('mobile', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('login_otps', function (Blueprint $table) {
            if (Schema::hasColumn('login_otps', 'email')) {
                $table->dropIndex(['email']);
                $table->dropColumn('email');
            }
        });
    }
};
