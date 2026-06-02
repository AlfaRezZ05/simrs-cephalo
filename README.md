# SIMRS Portal - Integrated AI Diagnostic Platform

SIMRS (Sistem Informasi Manajemen Rumah Sakit) adalah portal manajemen pelayanan klinis terpadu yang menggabungkan efisiensi alur administrasi dengan kemampuan kecerdasan buatan (AI) secara aman dan instan. Repositori ini memuat prototipe modul **Poli Paru** (Manajemen Pasien TB) dan **Poli Gigi** (Cephalo AI Diagnostics).

## 🚀 Fitur Utama

- **Poli Paru (TB Management)**: Sistem pencatatan digital pasien Tuberkulosis terintegrasi dengan sistem *tracking* rekam medis, kelola CRUD (Create, Read, Update, Delete) pasien lengkap dengan riwayat pengobatan dan pemantauan fase klinis.
- **Poli Gigi (Cephalo AI)**: Sistem berbasis kecerdasan buatan untuk mendeteksi *landmark* anatomi ortodonti dari hasil X-Ray Sefalometri secara non-invasif.
- **UI/UX Modern**: Desain antarmuka *dark-mode* responsif dengan *glassmorphism* dan micro-animasi modern yang dioptimalkan untuk kenyamanan operasional tenaga medis di lapangan.
- **Auto-Migration Database**: Sistem mampu membangun skema *database* secara otomatis pada saat pertama kali terhubung dengan server database (Self-healing system).

## 🛠️ Persyaratan Sistem

- PHP 8.x
- Database Server (MySQL/MariaDB untuk Lokal, atau PostgreSQL untuk Cloud)
- Python 3.9+ (Opsional: Jika ingin menjalankan mesin AI Sefalometri lokal)
- Modul PHP PDO (PDO PGSQL / PDO MYSQL)

## 📥 Panduan Instalasi Lokal (Development)

1. **Clone Repository** ini ke direktori root web server lokal Anda (misal `htdocs` pada XAMPP):
   ```bash
   git clone https://github.com/username/simrs-cephalo.git
   ```

2. **Jalankan Apache dan Database Server** pada control panel lokal Anda.

3. **Konfigurasi Environment Variable**
   Salin file `.env.example` menjadi `.env` dan sesuaikan kredensial database lokal Anda:
   ```bash
   cp .env.example .env
   ```

4. **Buka Aplikasi di Browser**
   Buka alamat aplikasi di browser:
   ```text
   http://localhost/simrs-cephalo
   ```
   > Sistem *Self-Healing* secara otomatis mendeteksi koneksi dan membangun seluruh tabel database yang diperlukan.

5. **Akses Akun Default**
   Gunakan akun administrator pengujian lokal yang terbuat otomatis:
   - **Email:** `admin@example.com` (Ubah pada database setelah instalasi selesai)
   - **Password:** `admin123`

## 🧠 Menjalankan Python AI Backend Sefalometri (Opsional)
Untuk memproses data X-Ray Gigi menggunakan model AI lokal:
1. Buka Terminal dan arahkan ke folder `cephalo/ai_engine`:
   ```bash
   cd cephalo/ai_engine
   ```
2. Install dependensi python:
   ```bash
   pip install flask flask-cors werkzeug pillow
   ```
3. Jalankan server backend AI:
   ```bash
   python app.py
   ```
   *Mesin AI lokal akan aktif dan melayani request inferensi.*

## 📁 Struktur Direktori
- `assets/` - Kumpulan aset CSS dan JavaScript global.
- `components/` - Komponen antarmuka modular.
- `config/` - Konfigurasi dan *database self-healing engine*.
- `layout/` - Kerangka navigasi (Header, Footer, Navbar).
- `tb/` - Modul Poliklinik Paru.
- `cephalo/` - Modul Poliklinik Gigi & AI Sefalometri.

## 🌐 Panduan Publikasi (Deployment)

Aplikasi SIMRS-Cephalo dirancang siap dipublikasikan ke layanan hosting awan (cloud) seperti Render, Railway, Vercel, maupun Server Shared Hosting.

### 1. Integrasi Cloud Database
1. Buat database baru di penyedia database cloud (seperti PostgreSQL Supabase/Aiven).
2. Salin kredensial koneksi ke file `.env` di server hosting Anda:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=your-database-host.com
   DB_PORT=5432
   DB_DATABASE=your_db_name
   DB_USERNAME=your_db_user
   DB_PASSWORD=your_secure_password
   ```

### 2. Deploy ke Serverless Platform (Vercel)
Aplikasi ini mendukung *deployment serverless* menggunakan runtime PHP. 
1. Pastikan file `vercel.json` ada di root folder repositori.
2. Daftarkan repositori Anda di dashboard Vercel.
3. Masukkan konfigurasi variabel lingkungan database Anda (`DB_CONNECTION`, `DB_HOST`, dsb.) pada menu **Environment Variables** di Vercel Dashboard.
4. Lakukan deployment. Seluruh database akan dimigrasikan secara otomatis pada inisialisasi aplikasi pertama kali.

---
*Keamanan Data dan Kepatuhan Privasi Rekam Medis Pasien adalah Prioritas Utama.*
