# Teman Gudang — MVP pencatatan peminjaman alat

## Yang dibuat

- Ringkasan alat tersedia, sedang dipinjam, dan melewati tanggal rencana kembali.
- Pencatatan peminjaman menggunakan nama teknisi, alat, waktu pinjam otomatis, dan rencana tanggal kembali.
- Daftar peminjaman aktif agar petugas bisa segera melihat siapa memegang alat apa.
- Pencatatan pengembalian dengan kondisi baik/rusak dan catatan opsional.
- Riwayat pengembalian yang mencatat peminjam terakhir dan kondisi alat ketika kembali.
- Pengelolaan daftar alat dan teknisi. Pengarsipan tidak menghapus riwayat.
- Antarmuka berbahasa Indonesia dengan tombol besar dan formulir sederhana untuk komputer bersama di pos gudang.

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

