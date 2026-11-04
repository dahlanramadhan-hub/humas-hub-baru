
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('contents')) {
            Schema::create('contents', function (Blueprint $t) {
                $t->id();
                $t->string('title');
                $t->string('type');
                $t->jsonb('channels');
                $t->string('status')->default('Belum Dikerjakan')->index();
                $t->string('requester_name');
                $t->string('responsible_name');
                $t->text('brief')->nullable();
                $t->date('deadline');
                $t->string('deadline_note')->default('Same Day');
                $t->text('caption')->nullable();
                $t->timestamp('publish_at')->nullable();
                $t->unsignedBigInteger('final_draft_id')->nullable();
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('content_assets')) {
            Schema::create('content_assets', function (Blueprint $t) {
                $t->id();
                $t->foreignId('content_id')->constrained()->cascadeOnDelete();
                $t->string('category');
                $t->string('kind');
                $t->string('name');
                $t->text('text')->nullable();
                $t->string('path')->nullable();
                $t->string('size_label')->nullable();
                $t->string('file_type')->nullable();
                $t->timestamps();
                $t->index(['content_id', 'category']);
            });
        }

        if (!Schema::hasTable('drafts')) {
            Schema::create('drafts', function (Blueprint $t) {
                $t->id();
                $t->foreignId('content_id')->constrained()->cascadeOnDelete();
                $t->string('kind');
                $t->string('name');
                $t->text('text')->nullable();
                $t->string('path')->nullable();
                $t->string('size_label')->nullable();
                $t->string('file_type')->nullable();
                $t->timestamps();
            });
        }

        if (!Schema::hasTable('draft_comments')) {
            Schema::create('draft_comments', function (Blueprint $t) {
                $t->id();
                $t->foreignId('draft_id')->constrained()->cascadeOnDelete();
                $t->string('user_name');
                $t->text('body');
                $t->timestamps();
            });
        }

        // Tambahkan foreign key hanya jika belum tersedia.
        $foreignKeyExists = DB::selectOne(
            "SELECT 1 FROM pg_constraint
             WHERE conname = 'contents_final_draft_id_foreign'
             AND conrelid = 'public.contents'::regclass"
        );

        if (!$foreignKeyExists) {
            Schema::table('contents', function (Blueprint $t) {
                $t->foreign('final_draft_id')
                    ->references('id')
                    ->on('drafts')
                    ->nullOnDelete();
            });
        }
    }

    // Tidak menghapus tabel atau data yang sudah tersedia.
    public function down(): void
    {
        // Sengaja dikosongkan.
    }
};
