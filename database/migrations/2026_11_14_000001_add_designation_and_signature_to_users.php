<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * An officer's designation and signature image, printed on the requisition slip.
 * Both are optional; existing users keep working without them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'designation')) {
                $table->string('designation', 191)->nullable()->after('name_bn');
            }
            if (! Schema::hasColumn('users', 'signature_path')) {
                $table->string('signature_path', 191)->nullable()->after('profile_photo_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['designation', 'signature_path']);
        });
    }
};
