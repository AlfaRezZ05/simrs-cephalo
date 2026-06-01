# SIMRS Portal - Integrated AI Diagnostic Platform

SIMRS (Sistem Informasi Manajemen Rumah Sakit) adalah portal cerdas yang mengintegrasikan berbagai poliklinik dengan kemampuan kecerdasan buatan (AI) secara mulus. Repository ini memuat prototipe modul **Poli Paru** (Manajemen Pasien TB) dan **Poli Gigi** (Cephalo AI Diagnostics).

## 🚀 Fitur Utama

- **Poli Paru (TB Management)**: Sistem pencatatan digital pasien Tuberkulosis dengan integrasi sistem *tracking* rekam medis, kelola CRUD (Create, Read, Update, Delete) pasien lengkap dengan riwayat pengobatan dan fase.
- **Poli Gigi (Cephalo AI)**: Sistem berbasis kecerdasan buatan untuk mendeteksi *landmark* anatomi ortodonti dari hasil X-Ray Sefalometri.
- **UI/UX Modern**: Desain antarmuka *dark-mode* responsif dengan *glassmorphism* dan micro-animasi liquid yang memanjakan mata.
- **Auto-Migration Database**: Sistem mampu membangun *database* secara otomatis pada saat pertama kali dijalankan (Self-healing system).

## 🛠️ Persyaratan Sistem

- PHP 8.x
- MySQL / MariaDB (via XAMPP atau sejenisnya)
- Python 3.9+ (Opsional: Hanya jika ingin menjalankan AI Sefalometri lokal)
- Modul PHP PDO (secara default sudah aktif di XAMPP)

## 📥 Panduan Instalasi (Plug and Play)

1. **Clone Repository** ini ke dalam folder `htdocs` (jika menggunakan XAMPP) atau `www` (WAMP).
   ```bash
   cd c:\xampp\htdocs
   git clone https://github.com/username/simrs-cephalo.git
   ```

2. **Jalankan Web Server dan Database**
   Buka XAMPP Control Panel, lalu jalankan **Apache** dan **MySQL**.

3. **Buka Aplikasi di Browser**
   Anda **tidak perlu** mengimpor atau membuat database manual di phpMyAdmin. Cukup buka tautan berikut di browser:
   ```text
   http://localhost/simrs-cephalo
   ```
   > Sistem secara otomatis akan membuat *database* `backbone_medweb` beserta tabel `users`, `tb_patients`, dan `cephalo_patients`.

4. **Login ke Sistem**
   Sistem telah membuat akun administrator default secara otomatis:
   - **Email:** `admin@simrs.com`
   - **Password:** `admin123`

## 🧠 (Opsional) Menjalankan Python AI Backend Sefalometri
Untuk bisa menguji unggah foto Rontgen Gigi pada fitur Cephalo AI, *backend* Python wajib dijalankan:
1. Buka Terminal / Command Prompt.
2. Masuk ke direktori `cephalo/ai_engine`:
   ```bash
   cd c:\xampp\htdocs\simrs-cephalo\cephalo\ai_engine
   ```
3. Install dependensi (disarankan menggunakan virtual environment):
   ```bash
   pip install flask flask-cors werkzeug pillow
   ```
4. Jalankan AI Engine:
   ```bash
   python app.py
   ```
   *Terminal akan memunculkan info bahwa server berjalan di `http://127.0.0.1:5000/predict`*

## 📁 Struktur Direktori
- `assets/` - Kumpulan aset CSS (Tailwind) dan JavaScript.
- `components/` - Kumpulan komponen UI modular bergaya *shadcn*.
- `config/` - Konfigurasi dan *database self-healing script*.
- `layout/` - Header, footer, navbar dan struktur sistem *portal hub*.
- `tb/` - Modul Poliklinik Paru.
- `cephalo/` - Modul Poliklinik Gigi dan Direktori Upload Rontgen.
- `cephalo/ai_engine/` - Modul *backend* Python untuk *machine learning* dan AI inference.

## 🌐 Panduan Publikasi ke Internet & Migrasi Supabase

Aplikasi SIMRS-Cephalo kini telah ditingkatkan agar mendukung arsitektur *cloud-ready* dengan integrasi **Supabase (PostgreSQL)** untuk *database production* dan siap di-deploy ke hosting publik (seperti Render, Railway, atau Shared Hosting cPanel).

### 1. Migrasi Database ke Supabase
1. Buat proyek baru di [Supabase](https://supabase.com).
2. Salin kredensial database dari menu **Settings -> Database** di dashboard Supabase.
3. Buat file `.env` di direktori root proyek (salin dari `.env.example`):
   ```bash
   cp .env.example .env
   ```
4. Ubah isi `.env` sesuai kredensial database Supabase Anda:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=aws-0-ap-southeast-1.pooler.supabase.com
   DB_PORT=5432
   DB_DATABASE=postgres
   DB_USERNAME=postgres
   DB_PASSWORD=password-supabase-anda
   ```
5. Saat pertama kali diakses, sistem *Self-Healing* SIMRS akan otomatis mendeteksi database PostgreSQL Supabase Anda dan membuat seluruh tabel yang diperlukan secara instan!

### 2. Panduan Deploy ke Vercel (100% Gratis & Serverless)
Aplikasi ini sudah dilengkapi dengan file `vercel.json` sehingga siap untuk langsung dijalankan di Vercel menggunakan runtime `vercel-php`.

**Langkah Deploy via Vercel Dashboard:**
1. Push kode Anda ke repository **GitHub** pribadi atau publik.
2. Masuk ke [Vercel.com](https://vercel.com) dan login dengan akun GitHub Anda.
3. Klik **Add New -> Project**, lalu pilih repository SIMRS-Cephalo Anda.
4. Di bagian konfigurasi **Environment Variables**, masukkan seluruh variabel dari file `.env` Anda (seperti `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).
5. Klik **Deploy**, dan dalam beberapa detik aplikasi Anda akan langsung *online*!

> **Catatan Serverless:** Karena Vercel menggunakan sistem file *ephemeral* (sementara), file seperti unggahan Rontgen mungkin tidak akan tersimpan permanen antar sesi, namun seluruh fitur database rekam medis akan berjalan sempurna.

---
Dikembangkan dengan ❤️ sebagai portal diagnostik cerdas generasi selanjutnya.

