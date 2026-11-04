<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Draft;
use App\Support\Uploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DraftController extends Controller
{
    /** Editor upload draft (file dan/atau teks/link) -> status "Menunggu Review". */
    public function store(Request $request, Content $content)
    {
        Gate::authorize('uploadDraft', $content);

        $request->validate([
            'files'   => ['nullable', 'array'],
            'files.*' => ['file', 'max:' . config('hub.max_upload_kb')],
            'text'    => ['nullable', 'string', 'max:20000'],
        ]);

        $files = $request->file('files', []);
        $text  = trim((string) $request->input('text'));

        if (empty($files) && $text === '') {
            return back()->with('err', 'Pilih file atau isi teks / link draft terlebih dahulu.');
        }

        foreach ($files as $file) {
            $content->drafts()->create(
                ['kind' => 'file'] + Uploads::put($file, "contents/{$content->id}/drafts")
            );
        }

        if ($text !== '') {
            $content->drafts()->create(
                Uploads::isUrl($text)
                    ? ['kind' => 'link', 'name' => $text, 'text' => $text, 'size_label' => 'Link']
                    : ['kind' => 'text', 'name' => 'Draft Teks', 'text' => $text, 'size_label' => 'Teks']
            );
        }

        $content->update(['status' => 'Menunggu Review']);

        return redirect()->route('contents.index')->with('ok', 'Draft terkirim. Menunggu review dari pengaju.');
    }

    /** Pengaju memberi revisi -> status "Revisi" (giliran editor). */
    public function comment(Request $request, Content $content)
    {
        Gate::authorize('review', $content);

        $body = $request->validate(['body' => ['required', 'string', 'max:2000']])['body'];

        $last = $content->lastDraft();
        if (! $last) {
            return back()->with('err', 'Belum ada draft untuk dikomentari.');
        }

        $last->comments()->create(['user_name' => $request->user()->name, 'body' => $body]);
        $content->update(['status' => 'Revisi']);

        return redirect()->route('contents.index')->with('ok', 'Revisi dikirim ke editor.');
    }

    public function download(Request $request, Draft $draft)
    {
        Gate::authorize('downloadDraft', [$draft->content, $draft]);

        return Uploads::response($draft, $request->boolean('inline'));
    }

    /** Hapus draft yang salah unggah (belum dikomentari). */
    public function destroy(Draft $draft)
    {
        $content = $draft->content;
        Gate::authorize('deleteDraft', [$content, $draft]);

        if ($draft->kind === 'file' && $draft->path) {
            try {
                Uploads::disk()->delete($draft->path);
            } catch (\Throwable $e) {
                report($e);
            }
        }
        $draft->delete();

        // status mengikuti draft yang tersisa
        $last   = $content->drafts()->reorder('id', 'desc')->first();
        $status = ! $last ? 'Belum Dikerjakan' : ($last->comments()->exists() ? 'Revisi' : 'Menunggu Review');
        $content->update(['status' => $status]);

        return back()->with('ok', 'Draft dihapus. Silakan unggah draft yang benar.');
    }
}