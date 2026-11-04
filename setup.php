<?php
/**
 * Jalankan SEKALI dari root project Laravel 11, setelah isi ZIP ini disalin ke sana:
 *   php setup.php
 * Skrip ini mem-patch User.php, config/app.php, config/database.php,
 * config/filesystems.php dan .env, lalu melaporkan hasil tiap langkah.
 */

$root = __DIR__;
$ok   = fn (string $m) => print("  [OK]   $m\n");
$skip = fn (string $m) => print("  [SKIP] $m\n");
$warn = fn (string $m) => print("  [PERLU MANUAL] $m\n");

echo "Menyiapkan Humas Content Hub...\n";

/* 1. User model: tambahkan 'username' ke $fillable */
$f = "$root/app/Models/User.php";
if (is_file($f)) {
    $c = file_get_contents($f);
    if (str_contains($c, "'username'")) {
        $skip('User.php sudah punya username');
    } elseif (preg_match('/(\$fillable\s*=\s*\[)(.*?)(\];)/s', $c)) {
        $c = preg_replace('/(\$fillable\s*=\s*\[)/', "$1\n        'username',", $c, 1);
        file_put_contents($f, $c);
        $ok("User.php: 'username' ditambahkan ke \$fillable");
    } else {
        $warn("User.php: tambahkan 'username' ke \$fillable secara manual");
    }
} else {
    $warn('app/Models/User.php tidak ditemukan - jalankan dari root project Laravel');
}

/* 2. config/app.php: timezone */
$f = "$root/config/app.php";
if (is_file($f)) {
    $c = file_get_contents($f);
    if (str_contains($c, "'timezone' => 'Asia/Jakarta'")) {
        $skip('timezone sudah Asia/Jakarta');
    } elseif (preg_match("/'timezone'\s*=>\s*[^,]+,/", $c)) {
        $c = preg_replace("/'timezone'\s*=>\s*[^,]+,/", "'timezone' => 'Asia/Jakarta',", $c, 1);
        file_put_contents($f, $c);
        $ok('config/app.php: timezone = Asia/Jakarta');
    } else {
        $warn("config/app.php: set 'timezone' => 'Asia/Jakarta' secara manual");
    }
}

/* 3. config/database.php: sslmode pgsql */
$f = "$root/config/database.php";
if (is_file($f)) {
    $c = file_get_contents($f);
    if (preg_match("/'sslmode'\s*=>\s*[^,]+,/", $c)) {
        $c = preg_replace("/'sslmode'\s*=>\s*[^,]+,/", "'sslmode' => env('DB_SSLMODE', 'require'),", $c, 1);
        file_put_contents($f, $c);
        $ok("config/database.php: sslmode pgsql = require");
    } else {
        $warn("config/database.php: tambahkan 'sslmode' => 'require' pada koneksi pgsql");
    }
}

/* 4. config/filesystems.php: disk supabase */
$f = "$root/config/filesystems.php";
if (is_file($f)) {
    $c = file_get_contents($f);
    if (str_contains($c, "'supabase'")) {
        $skip('disk supabase sudah ada');
    } elseif (preg_match("/'disks'\s*=>\s*\[/", $c)) {
        $disk = <<<'PHPDISK'
$0

        'supabase' => [
            'driver'                  => 's3',
            'key'                     => env('SUPABASE_S3_KEY'),
            'secret'                  => env('SUPABASE_S3_SECRET'),
            'region'                  => env('SUPABASE_S3_REGION', 'ap-southeast-1'),
            'bucket'                  => env('SUPABASE_BUCKET', 'humas-hub'),
            'endpoint'                => env('SUPABASE_S3_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'throw'                   => true,
        ],
PHPDISK;
        $c = preg_replace("/'disks'\s*=>\s*\[/", $disk, $c, 1);
        file_put_contents($f, $c);
        $ok('config/filesystems.php: disk supabase ditambahkan');
    } else {
        $warn("config/filesystems.php: tambahkan disk 'supabase' (lihat README.md)");
    }
}

/* 5. .env */
$envFile = "$root/.env";
if (!is_file($envFile) && is_file("$root/.env.example")) {
    copy("$root/.env.example", $envFile);
}
if (is_file($envFile)) {
    $env = file_get_contents($envFile);
    $set = [
        'APP_LOCALE'          => 'id',
        'APP_FALLBACK_LOCALE' => 'id',
        'DB_CONNECTION'       => 'pgsql',
        'DB_HOST'             => 'aws-0-REGION.pooler.supabase.com',
        'DB_PORT'             => '5432',
        'DB_DATABASE'         => 'postgres',
        'DB_USERNAME'         => 'postgres.PROJECTREF',
        'DB_PASSWORD'         => 'PASSWORD_DB_SUPABASE',
        'SESSION_DRIVER'      => 'file',
        'CACHE_STORE'         => 'file',
        'QUEUE_CONNECTION'    => 'sync',
        'HUB_DISK'            => 'supabase',
        'HUB_MAX_UPLOAD_KB'   => '102400',
        'HUB_ADMIN_PASSWORD'  => 'GANTI_PASSWORD_KUAT',
        'SUPABASE_S3_ENDPOINT'=> 'https://PROJECTREF.supabase.co/storage/v1/s3',
        'SUPABASE_S3_REGION'  => 'ap-southeast-1',
        'SUPABASE_S3_KEY'     => 'ISI_ACCESS_KEY',
        'SUPABASE_S3_SECRET'  => 'ISI_SECRET_KEY',
        'SUPABASE_BUCKET'     => 'humas-hub',
    ];
    $added = [];
    foreach ($set as $k => $v) {
        $pattern = '/^[ \t]*#?[ \t]*' . preg_quote($k, '/') . '=.*$/m';
        if (preg_match('/^[ \t]*' . preg_quote($k, '/') . '=(.*)$/m', $env, $m)) {
            // sudah aktif: jangan timpa nilai yang sudah diisi untuk kunci rahasia
            if (in_array($k, ['DB_PASSWORD','HUB_ADMIN_PASSWORD','SUPABASE_S3_KEY','SUPABASE_S3_SECRET'], true) && trim($m[1]) !== '' ) {
                continue;
            }
            $env = preg_replace('/^[ \t]*' . preg_quote($k, '/') . '=.*$/m', "$k=$v", $env, 1);
        } elseif (preg_match($pattern, $env)) {
            $env = preg_replace($pattern, "$k=$v", $env, 1);   // baris yang di-comment
        } else {
            $added[] = "$k=$v";
        }
    }
    if ($added) {
        $env = rtrim($env) . "\n\n# --- Humas Content Hub ---\n" . implode("\n", $added) . "\n";
    }
    file_put_contents($envFile, $env);
    $ok('.env diperbarui - ISI nilai placeholder (Supabase & password admin) sebelum migrate');
} else {
    $warn('.env tidak ditemukan; salin dari .env.hub.example secara manual');
}

echo "\nSelanjutnya:\n";
echo "  1. composer require league/flysystem-aws-s3-v3 \"^3.0\"\n";
echo "  2. Edit .env (nilai yang masih placeholder)\n";
echo "  3. php artisan migrate --seed\n";
echo "  4. php artisan serve\n";
