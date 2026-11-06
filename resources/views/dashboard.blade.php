<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gudang Pak Anto — Peminjaman Alat</title>
    <style>
        :root{font-family:Arial,Helvetica,sans-serif;color:#1d2939;background:#f4f6f8;font-size:16px}
        *{box-sizing:border-box}body{margin:0}button,input,select,textarea{font:inherit}
        .topbar{background:#123b32;color:#fff;padding:19px max(24px,calc((100% - 1180px)/2));display:flex;align-items:center;justify-content:space-between;gap:16px}
        .brand{font-size:21px;font-weight:700}.brand small{display:block;font-size:13px;font-weight:400;color:#d0e4dd;margin-top:4px}
        .top-actions{display:flex;align-items:center;gap:14px}.today{font-size:14px;color:#e3eee9}
        .top-actions form{margin:0}.logout{background:transparent;color:#fff;border:1px solid #b5cec4;border-radius:7px;padding:9px 13px;min-height:40px;font-weight:700;cursor:pointer}
        .logout:hover,.logout:focus{background:#ffffff1a;outline:3px solid #b8e4d6}
        .container{max-width:1180px;margin:0 auto;padding:28px 22px 56px}
        h1{font-size:29px;margin:0 0 7px}.intro{color:#586579;margin:0 0 24px}
        .nav{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:24px}.nav a{background:#fff;border:1px solid #d5dce3;padding:11px 16px;border-radius:8px;color:#1d2939;text-decoration:none;font-weight:700}
        .nav a:hover,.nav a:focus{border-color:#16745e;color:#11624f;outline:3px solid #d6efe7}
        .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px}.stat,.panel{background:#fff;border:1px solid #e1e6eb;border-radius:11px}
        .stat{padding:18px}.stat strong{font-size:30px;display:block;margin-bottom:5px}.stat span{color:#586579}
        .warn{color:#a44905}.section{scroll-margin-top:16px;margin:25px 0}.section-title{display:flex;align-items:center;justify-content:space-between;gap:12px;margin:0 0 12px}
        h2{font-size:21px;margin:0}.panel{padding:18px;margin-bottom:14px}.table-wrap{overflow-x:auto}
        table{width:100%;border-collapse:collapse;min-width:700px}th,td{text-align:left;padding:13px 10px;border-bottom:1px solid #edf0f2;vertical-align:top}
        th{font-size:13px;color:#586579;background:#f8fafb}tr:last-child td{border-bottom:0}
        .muted{color:#667085;font-size:14px}.pill{display:inline-block;padding:5px 9px;border-radius:20px;background:#e8f4ee;color:#176447;font-size:13px;font-weight:700}
        .pill.red{background:#fff0ed;color:#b42318}.pill.amber{background:#fff4db;color:#925c00}
        .button{border:0;border-radius:7px;background:#176b56;color:white;padding:11px 15px;font-weight:700;cursor:pointer;min-height:44px}
        .button:hover,.button:focus{background:#10513f;outline:3px solid #b8e4d6}.button.secondary{background:#edf3f1;color:#185c4a}.button.danger{background:#fff0ed;color:#a52a20}
        .inline-form{display:flex;align-items:center;gap:7px;flex-wrap:wrap}.inline-form select,.inline-form textarea{padding:9px;border:1px solid #cbd5df;border-radius:6px;max-width:180px}
        .forms{display:grid;grid-template-columns:1fr 1fr;gap:16px}.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        label{display:block;font-size:14px;font-weight:700;margin-bottom:5px}input,select,textarea{width:100%;border:1px solid #b9c4cf;border-radius:7px;padding:11px;background:#fff;min-height:44px}
        input:focus,select:focus,textarea:focus{outline:3px solid #c5e8dc;border-color:#176b56}.field{margin-bottom:13px}.form-actions{margin-top:5px}
        .notice{padding:13px 16px;border-radius:8px;margin-bottom:16px}.notice.success{background:#e8f4ee;color:#155d41}.notice.error{background:#fff0ed;color:#9b271d}
        .empty{text-align:center;padding:24px;color:#667085}.actions{display:flex;gap:8px;align-items:center}.danger-text{color:#b42318}
        @media(max-width:750px){.topbar{padding:16px 20px}.container{padding:22px 15px 40px}.stats{grid-template-columns:1fr 1fr}.forms{grid-template-columns:1fr}}
        @media(max-width:450px){.today{display:none}.stat{padding:14px}.stat strong{font-size:25px}.form-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
<header class="topbar">
    <div class="brand">Gudang Pak Anto <small>Pencatatan peminjaman alat</small></div>
    <div class="top-actions">
        <div class="today">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="logout" type="submit">Keluar</button>
        </form>
    </div>
</header>
<main class="container">
    <h1>Selamat datang, {{ Auth::user()->name }}</h1>
    <p class="intro">Lihat alat yang sedang dipinjam dan catat peminjaman dengan mudah.</p>

    @if (session('success'))
        <div class="notice success" role="status">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="notice error" role="alert">
            <strong>Belum berhasil disimpan.</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <nav class="nav" aria-label="Navigasi halaman">
        <a href="#peminjaman">Alat dipinjam</a>
        <a href="#alat">Daftar alat</a>
        <a href="#teknisi">Daftar teknisi</a>
        <a href="#riwayat">Riwayat</a>
    </nav>

    <section class="stats" aria-label="Ringkasan gudang">
        <div class="stat"><strong>{{ $availableCount }}</strong><span>Alat siap dipinjam</span></div>
        <div class="stat"><strong>{{ $loanedCount }}</strong><span>Sedang dipinjam</span></div>
        <div class="stat"><strong class="{{ $overdueCount ? 'warn' : '' }}">{{ $overdueCount }}</strong><span>Lewat tanggal kembali</span></div>
        <div class="stat"><strong>{{ $tools->where('is_active', true)->count() }}</strong><span>Total alat aktif</span></div>
    </section>

    <section class="section" id="peminjaman">
        <div class="section-title"><h2>Alat yang sedang dipinjam</h2></div>
        <div class="panel table-wrap">
            @if ($activeLoans->isEmpty())
                <div class="empty">Belum ada alat yang dipinjam.</div>
            @else
                <table>
                    <thead><tr><th>Nama alat</th><th>Peminjam</th><th>Dipinjam sejak</th><th>Batas kembali</th><th>Tindakan pengembalian</th></tr></thead>
                    <tbody>
                    @foreach ($activeLoans as $loan)
                        <tr>
                            <td><strong>{{ $loan->tool->name }}</strong><div class="muted">{{ $loan->tool->code ?: $loan->tool->category }}</div></td>
                            <td>{{ $loan->technician->name }}<div class="muted">{{ $loan->technician->department }}</div></td>
                            <td>{{ $loan->borrowed_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @if ($loan->due_at->lt(today()))<span class="pill red">Terlambat</span><br>@endif
                                {{ $loan->due_at->format('d/m/Y') }}
                            </td>
                            <td>
                                <form class="inline-form" method="POST" action="{{ route('loans.return', $loan) }}">
                                    @csrf @method('PATCH')
                                    <select name="return_condition" aria-label="Kondisi alat saat kembali" required>
                                        <option value="baik">Kembali baik</option><option value="rusak">Kembali rusak</option>
                                    </select>
                                    <input type="text" name="return_notes" aria-label="Catatan kondisi" placeholder="Catatan (opsional)">
                                    <button class="button" type="submit">Catat kembali</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>

    <section class="section" id="catat-peminjaman">
        <div class="section-title"><h2>Catat peminjaman baru</h2></div>
        <div class="panel">
            @if ($availableTools->isEmpty() || $technicians->isEmpty())
                <p class="muted">Tambahkan alat dan teknisi aktif terlebih dahulu untuk mulai mencatat peminjaman.</p>
            @else
                <form method="POST" action="{{ route('loans.store') }}" class="form-grid">
                    @csrf
                    <div class="field"><label for="tool_id">Pilih alat</label>
                        <select id="tool_id" name="tool_id" required><option value="">Pilih alat</option>
                            @foreach ($availableTools as $tool)<option value="{{ $tool->id }}">{{ $tool->name }}{{ $tool->code ? ' — '.$tool->code : '' }}</option>@endforeach
                        </select>
                    </div>
                    <div class="field"><label for="technician_id">Nama teknisi</label>
                        <select id="technician_id" name="technician_id" required><option value="">Pilih teknisi</option>
                            @foreach ($technicians as $technician)<option value="{{ $technician->id }}">{{ $technician->name }}{{ $technician->department ? ' — '.$technician->department : '' }}</option>@endforeach
                        </select>
                    </div>
                    <div class="field"><label for="due_at">Rencana tanggal kembali</label>
                        <input id="due_at" type="date" name="due_at" min="{{ now()->toDateString() }}" value="{{ old('due_at', now()->addDays(3)->toDateString()) }}" required>
                    </div>
                    <div class="field form-actions"><label>&nbsp;</label><button class="button" type="submit">Simpan peminjaman</button></div>
                </form>
            @endif
        </div>
    </section>

    <section class="section" id="alat">
        <div class="section-title"><h2>Daftar alat</h2></div>
        <div class="panel table-wrap">
            @if ($tools->isEmpty())<div class="empty">Belum ada alat. Tambahkan alat pada formulir di bawah.</div>
            @else
                <table><thead><tr><th>Alat</th><th>Kategori</th><th>Lokasi</th><th>Status</th><th>Peminjam terakhir</th><th>Pengaturan</th></tr></thead><tbody>
                    @foreach ($tools as $tool)
                        @php($lastLoan = $tool->loanRecords->sortByDesc('returned_at')->first())
                        <tr>
                            <td><strong>{{ $tool->name }}</strong><div class="muted">{{ $tool->code ?: 'Tanpa kode' }}</div></td>
                            <td>{{ $tool->category }}</td><td>{{ $tool->location ?: '—' }}</td>
                            <td>
                                @if (! $tool->is_active)<span class="pill">Diarsipkan</span>
                                @elseif ($activeLoans->contains('tool_id', $tool->id))<span class="pill amber">Dipinjam</span>
                                @elseif ($tool->current_condition === 'rusak')<span class="pill red">Rusak</span>
                                @else<span class="pill">Tersedia</span>@endif
                            </td>
                            <td>
                                @if ($lastLoan)
                                    {{ $lastLoan->technician->name }}<div class="muted">{{ $lastLoan->returned_at->format('d/m/Y') }}
                                    @if ($lastLoan->return_condition === 'rusak') · <span class="danger-text">Dikembalikan rusak</span>@endif</div>
                                    @if ($lastLoan->return_notes)<div class="muted">{{ $lastLoan->return_notes }}</div>@endif
                                @else<span class="muted">Belum pernah dipinjam</span>@endif
                            </td>
                            <td>
                                @if ($tool->is_active && ! $activeLoans->contains('tool_id', $tool->id))
                                    <div class="actions">
                                        <form class="inline-form" method="POST" action="{{ route('tools.condition', $tool) }}">@csrf @method('PATCH')
                                            <select name="current_condition" aria-label="Kondisi {{ $tool->name }}">
                                                <option value="baik" @selected($tool->current_condition === 'baik')>Kondisi baik</option>
                                                <option value="rusak" @selected($tool->current_condition === 'rusak')>Tandai rusak</option>
                                            </select>
                                            <button class="button secondary" type="submit">Simpan</button>
                                        </form>
                                        <form method="POST" action="{{ route('tools.archive', $tool) }}" onsubmit="return confirm('Arsipkan alat ini? Riwayatnya tetap tersimpan.')">
                                            @csrf @method('PATCH')<button class="button secondary" type="submit">Arsipkan</button>
                                        </form>
                                    </div>
                                @else<span class="muted">—</span>@endif
                            </td>
                        </tr>
                    @endforeach
                </tbody></table>
            @endif
        </div>
        <div class="forms">
            <div class="panel"><h2>Tambah alat</h2><p class="muted">Masukkan satu alat yang boleh dipinjam.</p>
                <form method="POST" action="{{ route('tools.store') }}">@csrf
                    <div class="field"><label for="name">Nama alat</label><input id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Multimeter digital" required maxlength="120"></div>
                    <div class="form-grid">
                        <div class="field"><label for="code">Kode alat (opsional)</label><input id="code" name="code" value="{{ old('code') }}" placeholder="Contoh: MT-001" maxlength="40"></div>
                        <div class="field"><label for="category">Kategori</label><input id="category" name="category" value="{{ old('category') }}" placeholder="Contoh: Alat ukur" required maxlength="80"></div>
                    </div>
                    <div class="field"><label for="location">Lokasi penyimpanan (opsional)</label><input id="location" name="location" value="{{ old('location') }}" placeholder="Contoh: Lemari A" maxlength="120"></div>
                    <button class="button" type="submit">Tambah alat</button>
                </form>
            </div>
            <div class="panel" id="teknisi"><h2>Daftar teknisi</h2><p class="muted">Pilih nama teknisi saat mencatat peminjaman.</p>
                @if ($technicians->isNotEmpty())
                    <div class="table-wrap"><table style="min-width:0"><thead><tr><th>Nama</th><th>Bagian</th><th></th></tr></thead><tbody>
                        @foreach ($technicians as $technician)<tr><td>{{ $technician->name }}</td><td>{{ $technician->department ?: '—' }}</td><td>
                            @if (! $technician->loanRecords()->whereNull('returned_at')->exists())
                                <form method="POST" action="{{ route('technicians.archive', $technician) }}" onsubmit="return confirm('Arsipkan teknisi ini? Riwayatnya tetap tersimpan.')">@csrf @method('PATCH')<button class="button secondary" type="submit">Arsipkan</button></form>
                            @else<span class="muted">Sedang meminjam</span>@endif
                        </td></tr>@endforeach
                    </tbody></table></div>
                @else<div class="empty">Belum ada teknisi aktif.</div>@endif
                <form method="POST" action="{{ route('technicians.store') }}" style="margin-top:18px">@csrf
                    <div class="field"><label for="technician_name">Tambah nama teknisi</label><input id="technician_name" name="name" placeholder="Nama teknisi" required maxlength="120"></div>
                    <div class="field"><label for="department">Bagian (opsional)</label><input id="department" name="department" placeholder="Contoh: Mekanik" maxlength="100"></div>
                    <button class="button" type="submit">Tambah teknisi</button>
                </form>
            </div>
        </div>
    </section>

    <section class="section" id="riwayat">
        <div class="section-title"><h2>Riwayat pengembalian terakhir</h2></div>
        <div class="panel table-wrap">
            @if ($history->isEmpty())<div class="empty">Riwayat pengembalian akan muncul di sini.</div>
            @else<table><thead><tr><th>Alat</th><th>Peminjam</th><th>Dikembalikan pada</th><th>Kondisi saat kembali</th><th>Catatan</th></tr></thead><tbody>
                @foreach ($history as $record)<tr><td>{{ $record->tool->name }}</td><td>{{ $record->technician->name }}</td><td>{{ $record->returned_at->format('d/m/Y H:i') }}</td>
                    <td><span class="pill {{ $record->return_condition === 'rusak' ? 'red' : '' }}">{{ ucfirst($record->return_condition) }}</span></td><td>{{ $record->return_notes ?: '—' }}</td></tr>@endforeach
            </tbody></table>@endif
        </div>
    </section>
    <p class="muted">Aplikasi pencatatan sederhana untuk petugas gudang. Data tetap tersimpan meski alat atau teknisi diarsipkan.</p>
</main>
</body>
</html>
