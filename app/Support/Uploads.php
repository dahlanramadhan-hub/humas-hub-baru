<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Uploads
{
    /** Hanya tipe ini yang boleh ditampilkan langsung di browser (View). */
    private const INLINE_SAFE = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'mp4', 'webm'];

    public static function disk()
    {
        return Storage::disk(config('hub.disk'));
    }

    public static function isUrl(string $value): bool
    {
        return (bool) preg_match('~^https?://~i', $value) && filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /** Simpan file ke disk & kembalikan atribut siap-insert. */
    public static function put(UploadedFile $file, string $dir): array
    {
        $ext  = strtolower($file->getClientOriginalExtension());
        $size = (int) $file->getSize();
        $path = $file->storeAs($dir, Str::uuid() . ($ext ? ".{$ext}" : ''), ['disk' => config('hub.disk')]);

        return [
            'name'       => $file->getClientOriginalName(),
            'path'       => $path,
            'size_label' => self::size($size),
            'file_type'  => self::type($ext),
        ];
    }

    /** Respons download (atau tampil langsung bila $inline dan tipenya aman). */
    public static function response($item, bool $inline = false)
    {
        return match ($item->kind) {
            'link'  => redirect()->away($item->text),
            'text'  => response($item->text, 200, [
                'Content-Type'        => 'text/plain; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . Str::slug($item->name ?: 'teks') . '.txt"',
            ]),
            default => self::file($item, $inline),
        };
    }

    private static function file($item, bool $inline)
    {
        $ext = strtolower(pathinfo((string) $item->name, PATHINFO_EXTENSION));

        if ($inline && in_array($ext, self::INLINE_SAFE, true)) {
            return self::disk()->response($item->path, $item->name, ['X-Content-Type-Options' => 'nosniff']);
        }

        return self::disk()->download($item->path, $item->name);
    }

    public static function size(int $n): string
    {
        if ($n <= 0) {
            return '0 B';
        }
        $u = ['B', 'KB', 'MB', 'GB'];
        $i = min((int) floor(log($n, 1024)), 3);
        return number_format($n / (1024 ** $i), $i ? 1 : 0) . ' ' . $u[$i];
    }

    public static function type(string $ext): string
    {
        return match (true) {
            in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])                       => 'image',
            in_array($ext, ['mp4', 'mov', 'avi', 'mkv'])                                => 'video',
            in_array($ext, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt']) => 'document',
            in_array($ext, ['zip', 'rar', '7z'])                                        => 'archive',
            default                                                                     => 'file',
        };
    }
}