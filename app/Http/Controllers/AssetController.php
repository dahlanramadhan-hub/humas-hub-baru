<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\ContentAsset;
use App\Support\Uploads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AssetController extends Controller
{
    /** Tambah file dan/atau teks/link (otomatis dikenali). */
    public function store(Request $request, Content $content)
    {
        Gate::authorize('manageAssets', $content);

        $data = $request->validate([
            'category' => ['required', Rule::in(['material', 'reference'])],
            'text'     => ['nullable', 'string', 'max:5000'],
            'files'    => ['nullable', 'array'],
            'files.*'  => ['file', 'max:' . config('hub.max_upload_kb')],
        ]);

        $cat   = $data['category'];
        $text  = trim((string) ($data['text'] ?? ''));
        $files = $request->file('files', []);

        if ($text === '' && empty($files)) {
            return back()->with('err', 'Isi teks / link atau pilih file terlebih dahulu.');
        }

        foreach ($files as $file) {
            $content->addFile($cat, $file);
        }
        if ($text !== '') {
            Uploads::isUrl($text) ? $content->addLink($cat, $text) : $content->addText($cat, $text);
        }

        return back()->with('ok', 'Item berhasil ditambahkan.');
    }

    public function destroy(ContentAsset $asset)
    {
        Gate::authorize('manageAssets', $asset->content);

        if ($asset->kind === 'file' && $asset->path) {
            Uploads::disk()->delete($asset->path);
        }
        $asset->delete();

        return back()->with('ok', 'Item dihapus.');
    }

    /** Hapus beberapa item yang dicentang. */
    public function bulkDestroy(Request $request, Content $content)
    {
        Gate::authorize('manageAssets', $content);

        $ids = $request->validate(
            ['ids' => ['required', 'array', 'min:1'], 'ids.*' => ['integer']],
            ['ids.required' => 'Centang item yang ingin dihapus terlebih dahulu.']
        )['ids'];

        $assets = $content->assets()->whereIn('id', $ids)->get();
        foreach ($assets as $asset) {
            if ($asset->kind === 'file' && $asset->path) {
                Uploads::disk()->delete($asset->path);
            }
            $asset->delete();
        }

        return back()->with('ok', $assets->count() . ' item dihapus.');
    }

    public function download(Request $request, ContentAsset $asset)
    {
        Gate::authorize('downloadAsset', [$asset->content, $asset]);

        return Uploads::response($asset, $request->boolean('inline'));
    }
}