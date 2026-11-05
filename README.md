# Teman Gudang — MVP pencatatan peminjaman alat

## Yang dibuat

- Ringkasan alat tersedia, sedang dipinjam, dan melewati tanggal rencana kembali.
- Pencatatan peminjaman menggunakan nama teknisi, alat, waktu pinjam otomatis, dan rencana tanggal kembali.
- Daftar peminjaman aktif agar petugas bisa segera melihat siapa memegang alat apa.
- Pencatatan pengembalian dengan kondisi baik/rusak dan catatan opsional.
- Riwayat pengembalian yang mencatat peminjam terakhir dan kondisi alat ketika kembali.
- Pengelolaan daftar alat dan teknisi. Pengarsipan tidak menghapus riwayat.
- Antarmuka berbahasa Indonesia dengan tombol besar dan formulir sederhana untuk komputer bersama di pos gudang.

## Pertanyaan untuk Pak Anto (urut paling penting)

1. Apakah yang perlu dicatat hanya alat yang dipinjam, atau sparepart habis pakai juga perlu dicatat jumlah stoknya?
2. Siapa yang boleh mencatat peminjaman dan pengembalian pada setiap shift? Apakah nama petugas perlu dicatat?
3. Apakah satu alat hanya boleh dipinjam satu per satu, atau ada alat yang jumlahnya banyak dan perlu dicatat sebagai stok?
4. Berapa lama peminjaman dianggap terlambat, dan apakah perlu tanggal rencana kembali untuk setiap peminjaman?
5. Informasi apa yang perlu dicatat ketika alat rusak atau dikembalikan dalam kondisi berbeda?
6. Data apa yang biasa dipakai untuk mengenali alat dan teknisi (kode inventaris, bagian, atau nama panggilan)?
7. Apakah komputer pos selalu tersambung ke jaringan, dan apakah aplikasi akan digunakan hanya di komputer gudang?

## Asumsi sementara

- MVP hanya melacak alat yang dapat dipinjam; stok sparepart habis pakai belum dikelola.
- Satu alat adalah satu unit yang bisa dipinjam oleh satu teknisi pada satu waktu.
- Petugas gudang masuk dengan akun yang dibuat administrator; pendaftaran publik tidak tersedia.
- Tanggal kembali direncanakan saat peminjaman; bawaan formulir adalah tiga hari dan dapat diubah.
- Peminjaman melewati tanggal rencana kembali ditandai terlambat. Ini pengingat, bukan sanksi.
- Kondisi alat dicatat saat pengembalian sebagai baik atau rusak; catatan teks bersifat opsional.
- Alat dan teknisi yang tidak digunakan diarsipkan, bukan dihapus, agar riwayat tetap utuh.
- Aplikasi dijalankan pada satu zona waktu Asia/Jakarta dan basis data Laravel yang dikonfigurasi untuk instalasi.

## Sengaja belum dibuat

- Pengelolaan stok sparepart, pembelian, dan mutasi gudang: belum menjadi kebutuhan utama yang dinyatakan.
- Hak akses berbeda per peran, tanda tangan digital, dan pelacakan shift: perlu konfirmasi alur kerja; MVP memberi akun yang masuk akses ke fitur pengelolaan yang sama.
- Notifikasi WhatsApp/email, barcode, laporan ekspor, dan pengingat otomatis: tidak dibutuhkan untuk membuktikan alur pencatatan dasar.
- Hukuman atau penilaian teknisi: tidak sejalan dengan tujuan Pak Anto yang ingin visibilitas, bukan pengawasan berlebihan.
- Fitur multi-gudang dan sinkronisasi offline: perlu kebutuhan dan infrastruktur jaringan yang belum diketahui.

## Menjalankan aplikasi

1. Pastikan PHP dan Composer tersedia, lalu jalankan `composer install`.
2. Siapkan `.env` dan `APP_KEY` sesuai instalasi Laravel; atur koneksi PostgreSQL.
3. Jalankan `php artisan migrate`.
4. Isi `OPERATOR_NAME`, `OPERATOR_EMAIL`, dan `OPERATOR_PASSWORD` di `.env`.
5. Jalankan `php artisan db:seed` untuk membuat akun petugas.
6. Jalankan `php artisan serve`, masuk dengan akun petugas, lalu tambahkan teknisi dan alat.

Jalankan pengujian dengan `php artisan test` (PHP CLI lokal perlu ekstensi
`pdo_sqlite` karena pengujian menggunakan SQLite in-memory).

Akun petugas dibuat oleh seeder dan tidak ada pendaftaran publik. Atur
`OPERATOR_NAME`, `OPERATOR_EMAIL`, dan `OPERATOR_PASSWORD` di environment,
kemudian jalankan `php artisan db:seed`. Gunakan kata sandi minimal 12 karakter
dan jangan commit kredensial ke repository. Seeder aman dijalankan ulang:
akun yang sudah ada tidak diubah atau di-reset.

## Deploy ke Render

Project menggunakan PostgreSQL dan menyediakan `render.yaml` untuk Web Service
berbasis Docker. Buat database PostgreSQL di Render, lalu isi environment
variables `DATABASE_URL` dengan **Internal Database URL**, `APP_KEY` dengan
hasil `php artisan key:generate --show`, `OPERATOR_EMAIL`, dan rahasia
`OPERATOR_PASSWORD` (minimal 12 karakter). Blueprint mengatur
`APP_ENV=production`, `APP_DEBUG=false`, dan `OPERATOR_NAME`. Container
menjalankan migrasi dan seeder sebelum menerima request. Login dilindungi
pembatasan percobaan dan cookie HTTPS pada konfigurasi Render; akun tidak dapat
didaftarkan dari halaman publik.
