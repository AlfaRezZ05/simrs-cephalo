# Menggunakan image resmi PHP 8.2 dengan server Apache
FROM php:8.2-apache

# Memperbarui sistem dan menginstal ekstensi PDO PostgreSQL (Wajib untuk Supabase)
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Mengaktifkan modul rewrite Apache (berguna untuk navigasi URL)
RUN a2enmod rewrite

# Menyalin seluruh kode website SIMRS-Cephalo ke dalam folder publik Apache
COPY . /var/www/html/

# Mengatur hak akses folder agar bisa menulis/menyimpan file (untuk upload rontgen)
RUN chown -R www-data:www-data /var/www/html/ \
    && chmod -R 755 /var/www/html/

# Membuka port 80 untuk akses web
EXPOSE 80
