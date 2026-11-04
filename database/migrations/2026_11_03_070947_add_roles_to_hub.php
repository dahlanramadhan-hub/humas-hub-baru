<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'role')) {
            Schema::table('users', fn (Blueprint $t) => $t->string('role', 30)->default('pengaju'));
        }
        if (! Schema::hasColumn('users', 'is_active')) {
            Schema::table('users', fn (Blueprint $t) => $t->boolean('is_active')->default(true));
        }
        if (! Schema::hasColumn('contents', 'requester_id')) {
            Schema::table('contents', fn (Blueprint $t) => $t->foreignId('requester_id')->nullable()->constrained('users')->nullOnDelete());
        }
        if (! Schema::hasColumn('contents', 'editor_id')) {
            Schema::table('contents', fn (Blueprint $t) => $t->foreignId('editor_id')->nullable()->constrained('users')->nullOnDelete());
        }
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $t) {
            $t->dropConstrainedForeignId('requester_id');
            $t->dropConstrainedForeignId('editor_id');
        });
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['role', 'is_active']));
    }
};