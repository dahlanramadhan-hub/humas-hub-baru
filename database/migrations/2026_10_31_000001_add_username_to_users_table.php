
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'username')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('username')->nullable()->unique();
            });
        }
    }

    // Tidak menghapus username yang mungkin sudah digunakan.
    public function down(): void
    {
        // Sengaja dikosongkan.
    }
};
