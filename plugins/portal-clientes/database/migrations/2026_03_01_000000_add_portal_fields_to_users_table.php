<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email_pending')->nullable()->after('email');
            $table->string('email_pending_token')->nullable()->after('email_pending');
            $table->timestamp('email_pending_expires_at')->nullable()->after('email_pending_token');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['email_pending', 'email_pending_token', 'email_pending_expires_at']);
        });
    }
};
