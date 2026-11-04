<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\User;
use App\Support\Uploads;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ContentController extends Controller
{
    /** Daftar editor untuk dropdown (editor aktif + editor yang sedang ditugaskan). */
    private function editors(?int $include = null)
    {
        return User::where(fn ($q) => $q->where('role', 'editor')->where('is_active', true))
            ->when($include, fn ($q) => $q->orWhere('id', $include))
            ->orderBy('name')->get();
    }

    /** Satu aksi selesai -> kembali ke daftar konten (popup tertutup). */
    private function backToList(string $message)
    {
        return redirect()->route('contents.index')->with('ok', $message);
    }

    private function listData(Request $request): array
    {
        $user      = $request->user();
        $publisher = $user->isPublisher();

        $cards = $publisher
            ? ['Approved' => 'Siap Publish', 'Publish Terjadwal' => 'Terjadwal', 'Publish' => 'Sudah Publish']
            : ['Belum Dikerjakan' => 'Belum Dikerjakan', 'Revisi' => 'Perlu Revisi', 'Approved' => 'Approval / Final'];

        $filters = $publisher
            ? ['Semua' => 'Semua', 'Approved' => 'Siap Publish', 'Publish Terjadwal' => 'Terjadwal', 'Publish' => 'Publish']
            : ['Semua' => 'Semua', 'Belum Dikerjakan' => 'Belum Dikerjakan', 'Menunggu Review' => 'Menunggu Review',
               'Revisi' => 'Revisi', 'Approved' => 'Final / Approval', 'Publish Terjadwal' => 'Terjadwal', 'Publish' => 'Publish'];

        $filter = $request->query('status', 'Semua');
        if (! array_key_exists($filter, $filters)) {
            $filter = 'Semua';
        }

        $counts = Content::visibleTo($user)
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $contents = Content::visibleTo($user)->withCount(['materials', 'drafts'])
            ->when($filter !== 'Semua', fn ($q) => $q->where('status', $filter))
            ->latest('id')->get();

        return compact('contents', 'counts', 'filter', 'cards', 'filters');
    }

    public function index(Request $request)
    {
        return view('contents.index', $this->listData($request));
    }

    public function create(Request $request)
    {
        Gate::authorize('create', Content::class);

        return view('contents.index', $this->listData($request) + [
            'openCreate' => true,
            'editors'    => $this->editors(),
        ]);
    }

    public function destroy(Content $content)
    {
        Gate::authorize('delete', $content);

        $id    = $content->id;
        $title = $content->title;

        DB::transaction(function () use ($content) {
            $content->update(['final_draft_id' => null]);
            $content->delete();   // bahan, draft, dan komentar ikut terhapus (cascade)
        });

        try {
            Uploads::disk()->deleteDirectory("contents/{$id}");   // hapus semua file konten ini
        } catch (\Throwable $e) {
            report($e);
        }

        return $this->backToList('Konten "' . $title . '" dihapus.');
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Content::class);

        $user  = $request->user();
        $maxKb = config('hub.max_upload_kb');

        $data = $request->validate([
            'title'             => ['required', 'string', 'max:255'],
            'editor_id'         => ['required', Rule::exists('users', 'id')->where('role', 'editor')],
            'type'              => ['required', Rule::in(Content::TYPES)],
            'channels'          => ['required', 'array', 'min:1'],
            'channels.*'        => [Rule::in(Content::SOCIALS)],
            'brief'             => ['nullable', 'string'],
            'deadline'          => ['required', 'date'],
            'deadline_note'     => ['nullable', Rule::in(Content::DEADLINE_NOTES)],
            'material_files.*'  => ['file', "max:{$maxKb}"],
            'material_text'     => ['nullable', 'string', 'max:5000'],
            'reference_files.*' => ['file', "max:{$maxKb}"],
            'reference_text'    => ['nullable', 'string', 'max:5000'],
        ], [
            'channels.required'  => 'Pilih minimal satu media sosial.',
            'editor_id.required' => 'Pilih editor penanggung jawab.',
        ]);

        $editor = User::findOrFail($data['editor_id']);
        $data['deadline_note'] = $data['deadline_note'] ?? 'Tanggal Tertentu';

        $content = DB::transaction(function () use ($data, $request, $user, $editor) {
            $content = Content::create(Arr::only($data, [
                'title', 'type', 'channels', 'editor_id', 'brief', 'deadline', 'deadline_note',
            ]) + [
                'status'           => 'Belum Dikerjakan',
                'requester_id'     => $user->id,
                'requester_name'   => $user->name,
                'responsible_name' => $editor->name,
            ]);

            foreach (['material', 'reference'] as $cat) {
                foreach ($request->file("{$cat}_files", []) as $file) {
                    $content->addFile($cat, $file);
                }
                $text = trim((string) $request->input("{$cat}_text"));
                if ($text !== '') {
                    Uploads::isUrl($text) ? $content->addLink($cat, $text) : $content->addText($cat, $text);
                }
            }

            return $content;
        });

        return $this->backToList('Konten "' . $content->title . '" berhasil diajukan ke editor.');
    }

    /** Detail konten tampil sebagai popup di atas daftar. */
    public function show(Request $request, Content $content)
    {
        Gate::authorize('view', $content);

        $content->load(['materials', 'references', 'drafts.comments', 'finalDraft']);

        return view('contents.index', $this->listData($request) + [
            'openContent' => $content,
            'editors'     => $this->editors($content->editor_id),
        ]);
    }

    public function update(Request $request, Content $content)
    {
        Gate::authorize('update', $content);

        $data = $request->validate([
            'title'         => ['required', 'string', 'max:255'],
            'editor_id'     => ['required', Rule::exists('users', 'id')->where('role', 'editor')],
            'type'          => ['sometimes', Rule::in(Content::TYPES)],
            'channels'      => ['required', 'array', 'min:1'],
            'channels.*'    => [Rule::in(Content::SOCIALS)],
            'brief'         => ['nullable', 'string'],
            'deadline'      => ['required', 'date'],
            'deadline_note' => ['sometimes', Rule::in(Content::DEADLINE_NOTES)],
        ], ['channels.required' => 'Pilih minimal satu media sosial.']);

        $data['responsible_name'] = User::findOrFail($data['editor_id'])->name;
        $content->update($data);

        return $this->backToList('Informasi konten berhasil disimpan.');
    }

    public function caption(Request $request, Content $content)
    {
        Gate::authorize('editCaption', $content);

        $content->update($request->validate(['caption' => ['nullable', 'string', 'max:10000']]));

        return $this->backToList('Caption berhasil disimpan.');
    }

    /** Pengaju menyatakan draft terakhir sudah benar -> lanjut ke publisher. */
    public function approve(Content $content)
    {
        Gate::authorize('review', $content);

        $last = $content->lastDraft();
        if (! $last) {
            return back()->with('err', 'Belum ada draft dari editor untuk disetujui.');
        }

        DB::transaction(function () use ($content, $last) {
            $content->update(['final_draft_id' => $last->id, 'status' => 'Approved']);

            $last->comments()->create([
                'user_name' => auth()->user()->name,
                'body'      => 'ACC — disetujui sebagai FINAL.',
            ]);
        });

        return $this->backToList('Draft disetujui. Konten diteruskan ke Publisher.');
    }

    public function reopen(Content $content)
    {
        Gate::authorize('reopen', $content);

        // final dicabut -> publisher otomatis kehilangan akses
        $content->update(['status' => 'Revisi', 'final_draft_id' => null]);

        return $this->backToList('Konten dibuka kembali untuk revisi. Menunggu draft baru dari editor.');
    }

    public function schedule(Request $request, Content $content)
    {
        Gate::authorize('publish', $content);

        $data = $request->validate([
            'publish_date' => ['required', 'date'],
            'publish_time' => ['required', 'date_format:H:i'],
        ]);

        $content->update([
            'publish_at' => Carbon::parse("{$data['publish_date']} {$data['publish_time']}", config('app.timezone')),
            'status'     => 'Publish Terjadwal',
        ]);

        return $this->backToList('Jadwal publish disimpan.');
    }

    public function publish(Content $content)
    {
        Gate::authorize('publish', $content);

        $content->update(['status' => 'Publish', 'publish_at' => $content->publish_at ?? now()]);

        return $this->backToList('Konten ditandai sudah publish.');
    }

    public function downloadFinal(Request $request, Content $content)
    {
        Gate::authorize('downloadFinal', $content);

        return Uploads::response($content->finalDraft, $request->boolean('inline'));
    }
}