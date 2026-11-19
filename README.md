# Template 02

Template aplikasi TALL Stack berasaskan Laravel 13, Livewire 4, Alpine.js dan Tailwind CSS 4. Ia menyertakan shell responsif, tema cerah/gelap, carian, kawalan akses, dashboard monitoring dan contoh borang serta dialog yang boleh digunakan semula.

## Keperluan

- PHP 8.5 dengan extension yang disahkan oleh `composer check-platform-reqs`. SQLite memerlukan `pdo_sqlite`.
- Composer 2.
- Node.js 22.12 atau lebih baharu dan npm. Projek ini diuji menggunakan Node.js 24.
- SQLite untuk pembangunan tempatan. Konfigurasi Laravel turut menyediakan sambungan MySQL dan PostgreSQL.

Versi dependency dipin melalui `composer.lock` dan `package-lock.json`.

## Setup tempatan

```sh
git clone https://github.com/emirulizzat01/template-02.git my-project
cd my-project
composer setup
php artisan template:user
composer dev
```

`composer setup` memasang dependencies, menyalin `.env.example` jika diperlukan, menjana APP_KEY hanya apabila kosong, menyediakan fail SQLite, menjalankan migrasi dan membina aset. Setup berulang mengekalkan kunci dan rekod sedia ada. Akaun baharu dicipta melalui arahan interaktif dengan input kata laluan tersembunyi.

`composer dev` menjalankan Laravel pada `http://localhost:8000` dan Vite serentak. Kekalkan terminal itu terbuka. Tekan Ctrl+C untuk menghentikan kedua-duanya. Untuk menggunakan aset yang sudah dibina, jalankan `php artisan serve --host=127.0.0.1 --port=8765` dan buka `http://127.0.0.1:8765`.

Jika menyambung ke pangkalan data lain, salin `.env.example` kepada `.env`, ubah tetapan DB terlebih dahulu, kemudian jalankan `composer setup`.

### Cuba data demo

Dalam `.env` tempatan, tetapkan `SIGNAL_DEMO=true` dan pastikan `APP_ENV=local`. Kemudian jalankan arahan berikut.

```sh
php artisan config:clear
php artisan db:seed --class=MonitoringDemoSeeder
```

Seeder menjana 32 perkhidmatan, 72 jam request buckets, snapshot KPI, deployment dan insiden contoh dalam satu transaksi. Ia melangkau seeding apabila rekod persekitaran sudah wujud. Mod demo menyediakan akses tanpa log masuk pada local/testing. Dalam production, log masuk sentiasa diperlukan walaupun `SIGNAL_DEMO=true`.

## Konfigurasi untuk projek baharu

| Lokasi | Perkara yang boleh diubah |
| --- | --- |
| `.env` | Nama aplikasi, penerangan, subtitle/logo ikon, kunci simpanan pelayar, module flags, polling, saiz jadual, pangkalan data dan perkhidmatan Laravel. |
| `config/template.php` | Branding, navigasi, modul dan had hasil carian. Navigasi sidebar dan hasil carian halaman menggunakan sumber yang sama. |
| `config/signal.php` | Tempoh carta, tempoh lalai, status insiden aktif, polling, ambang data lapuk dan saiz senarai. Tempoh carta perlu disokong oleh data yang dikumpul. |
| `config/form-examples.php` | Sections, medan, pilihan, validasi, data contoh dan medan yang disunting melalui prompt. Kekalkan nama medan unik dalam setiap borang. |
| `resources/css/app.css` | Token warna, typography, layout shell dan komponen monitoring. |
| `resources/css/examples.css` | Gaya contoh borang dan dialog. |

Tetapkan `APP_NAME`, `APP_DESCRIPTION`, `APP_BRAND_SUBTITLE`, `APP_BRAND_ICON` dan `APP_STORAGE_KEY` untuk menyesuaikan identiti aplikasi. Ikon yang digunakan perlu didaftarkan dalam `resources/js/app.js`. Gunakan APP_STORAGE_KEY berbeza jika beberapa aplikasi berkongsi origin pelayar.

`MONITORING_ENABLED=false` mengeluarkan route monitoring dan menyediakan halaman utama ringkas. `EXAMPLES_ENABLED=false` mengeluarkan route galeri contoh. Kedua-duanya boleh dikonfigurasi secara bebas. Migrasi kekal disertakan untuk keserasian data dan kawalan akses pengguna.

Selepas mengubah konfigurasi pada pemasangan ber-cache, jalankan `php artisan optimize:clear` dan bina semula cache production.

## Struktur dan penggunaan semula

```text
app/
  Console/Commands/       Initialize template dan cipta pengguna
  Http/                   Log masuk, log keluar dan middleware akses
  Livewire/               State halaman, jadual, carian, borang dan dialog
  Monitoring/             Query SQL dan skop persekitaran pengguna
  Support/Navigation.php  Navigasi dan indeks carian halaman bersama
config/
  template.php            Identiti aplikasi dan modul
  signal.php              Tetapan monitoring
  form-examples.php       Skema borang contoh
resources/
  views/components/signal/  Komponen Blade boleh guna semula
  views/layouts/            Shell, sidebar dan profil pengguna
  views/livewire/           Paparan halaman dan panel
  js/alpine/                State sidebar/tema dan lifecycle carta
routes/
  web.php                 Komposisi modul
  auth.php                Pengesahan
  monitoring.php          Dashboard, deployments dan incidents
  examples.php            Borang dan dialog contoh
tests/Feature/            Pengesahan, skop akses, modul dan regresi borang
```

Komponen `panel`, `badge`, `select`, `empty`, `nav-link`, `icon`, `time` dan `dialog` boleh dipanggil melalui namespace Blade `x-signal`. `example-field` menggunakan skema borang dan ralat validasi Livewire.

Contoh panel boleh digunakan seperti berikut.

```blade
<x-signal.panel heading="Overview" description="Latest activity" icon="activity">
    <p>Page content</p>
</x-signal.panel>
```

Komponen `dialog` menggunakan HTML dialog dengan pengurusan fokus pelayar. `auto-open` mengawal pembukaan automatik, `on-close` memanggil method Livewire, `close-action` menentukan tindakan butang tutup dan `dismiss-on-backdrop` mengawal penutupan melalui backdrop. Untuk pembukaan melalui Alpine, gunakan `:auto-open="false"`, `x-ref` dan `showModal()` seperti dalam `dialog-examples.blade.php`.

## Fungsi contoh

- Dashboard memaparkan carta volume, grid perkhidmatan, deployment pipeline, KPI dan insiden aktif. Pilihan persekitaran dan tempoh disimpan dalam URL.
- Deployments dan Incidents menyediakan penapisan, carian, susunan masa, pagination serta dialog butiran.
- Modal laporan insiden mempunyai 4 medan. Drawer perkhidmatan mempunyai 8 medan. Halaman polisi monitoring mempunyai 16 medan dalam 4 sections.
- Prompt mengisi tajuk/nama borang. Confirm meminta pengesahan sebelum input dibuang. Alert dibuka selepas validasi penghantaran berjaya.
- Galeri turut menyediakan contoh alert, confirm dan prompt secara berasingan. Penghantaran contoh ialah simulasi.
- Sidebar, carian ruang kerja, tema dan carta berfungsi pada desktop serta telefon. Pada pelayar yang menyokong CSS reading-flow, turutan fokus mengikuti keutamaan insiden pada telefon.

## Data dan kawalan akses

`MonitoringRepository` menggunakan Query Builder dan menerima ID persekitaran yang dibenarkan oleh `MonitoringAccess`. Paparan jadual, URL terus, carian dan kemas kini Livewire menggunakan skop ini. Snapshot dibaca semula pada request Livewire supaya penarikan akses tidak mendedahkan snapshot lama.

| Jadual | Kontrak |
| --- | --- |
| `environments`, `environment_user` | Slug/nama persekitaran dan hubungan akses pengguna. Tiada peranan admin tersirat. |
| `monitored_services` | Nama unik dalam setiap persekitaran, status healthy/degraded/down dan masa pemeriksaan UTC. Status lain dipaparkan sebagai unknown. |
| `request_buckets` | Satu rekod bagi setiap persekitaran/jam UTC dengan kiraan success dan errors. Jam yang belum lengkap dipaparkan sebagai data hilang. |
| `monitoring_snapshots` | Satu snapshot terkini bagi setiap persekitaran. Pembilang uptime, latensi dan deployment merangkumi tetingkap 24 jam. Beban menggunakan kapasiti semasa. |
| `deployments`, `incidents` | Rekod yang merujuk monitored service. Status dan masa disimpan secara eksplisit. |

KPI agregat menggunakan jumlah pembilang dan penyebut. Pembekal telemetri perlu menyegerakkan jadual ini menggunakan pengenal sumber yang stabil serta cap masa UTC. Carta demo menggunakan masa snapshot asal supaya data contoh boleh diperiksa kemudian.

Untuk mencipta akaun dengan akses ke persekitaran sedia ada, gunakan arahan berikut.

```sh
php artisan template:user --environment=production --environment=staging
```

Pilihan environment boleh diulang. Slug mesti sudah wujud. Akaun yang dicipta tanpa pilihan ini boleh mengakses workspace dan contoh borang, dengan senarai monitoring kosong. Integrasi SSO, reset kata laluan dan penyedia telemetri boleh dibina mengikut keperluan projek yang menggunakan template.

## Semakan

```sh
composer validate --strict
composer check-platform-reqs
composer lint
composer test
npm run lint
npm run build
```

`composer lint` menggunakan Laravel Pint. `npm run lint` memeriksa sintaks modul JavaScript dan konfigurasi Vite menggunakan Node. `composer format` membetulkan format PHP. `composer check` menjalankan lint PHP, ujian, lint JavaScript dan build. Ujian menggunakan SQLite dalam memori dan kunci ujian sintetik. GitHub Actions menjalankan pemeriksaan yang sama pada push dan pull request.

## Production

1. Sediakan PHP-FPM/server Laravel yang menyokong PHP 8.5, HTTPS dan document root `public/`. Direktori `storage` dan `bootstrap/cache` memerlukan akses tulis.
2. Bekalkan secrets melalui pengurusan persekitaran deployment. Tetapkan `APP_ENV=production`, `APP_DEBUG=false`, `SIGNAL_DEMO=false`, `APP_URL` kepada URL HTTPS dan `SESSION_SECURE_COOKIE=true`. Gunakan APP_KEY unik dan kekalkannya merentas release.
3. Pasang dependencies runtime dengan `composer install --no-dev --prefer-dist --optimize-autoloader`. Bina aset dengan `npm ci && npm run build` dalam peringkat build.
4. Sandarkan data, kemudian jalankan `php artisan migrate --force`, `php artisan config:cache`, `php artisan route:cache` dan `php artisan view:cache`.
5. Cipta akaun serta grant persekitaran melalui `template:user`. Hubungkan telemetri, tentukan backup/log retention dan pantau endpoint kesihatan `/up`. Jika menggunakan queue, sediakan worker di bawah process manager.

SQLite sesuai untuk local atau pemasangan kecil yang dikonfigurasi dengan betul. Pilih pangkalan data dan tetapan session/cache mengikut beban deployment. Akses reverse proxy dan penghantaran e-mel perlu dikonfigurasi oleh persekitaran hosting.

## Git dan fail sensitif

`.gitignore` mengecualikan `.env`, fail database, backup, keys, logs, dependencies dan aset terjana. Commit kedua-dua lockfile dan `.env.example` yang mengandungi placeholders. Fail runtime tempatan kekal di luar repository. Semua data yang dijana oleh seeder ialah data contoh.

Projek ini menggunakan lesen MIT.
