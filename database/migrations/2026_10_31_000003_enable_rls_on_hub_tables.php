<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Tabel di schema public Supabase terbuka lewat Data API (anon key) bila RLS mati.
    // Laravel memakai role postgres yang bypass RLS, jadi aman diaktifkan.
    private array $tables = ['users', 'contents', 'content_assets', 'drafts', 'draft_comments'];

    public function up(): void
    {
        foreach ($this->tables as $t) {
            DB::statement("ALTER TABLE \"{$t}\" ENABLE ROW LEVEL SECURITY");
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $t) {
            DB::statement("ALTER TABLE \"{$t}\" DISABLE ROW LEVEL SECURITY");
        }
    }
};
