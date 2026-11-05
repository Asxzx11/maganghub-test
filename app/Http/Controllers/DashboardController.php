<?php

namespace App\Http\Controllers;

use App\Models\LoanRecord;
use App\Models\Technician;
use App\Models\Tool;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $tools = Tool::with(['loanRecords' => fn ($query) => $query->whereNotNull('returned_at')])
            ->orderBy('name')
            ->get();
        $activeLoans = LoanRecord::with(['tool', 'technician'])
            ->whereNull('returned_at')
            ->orderBy('due_at')
            ->orderBy('borrowed_at')
            ->get();
        $technicians = Technician::where('is_active', true)->orderBy('name')->get();
        $history = LoanRecord::with(['tool', 'technician'])
            ->whereNotNull('returned_at')
            ->latest('returned_at')
            ->limit(10)
            ->get();
        $availableTools = $tools->filter(fn (Tool $tool) => $tool->is_active
            && $tool->current_condition === 'baik'
            && ! $activeLoans->contains('tool_id', $tool->id));

        return view('dashboard', [
            'tools' => $tools,
            'activeLoans' => $activeLoans,
            'technicians' => $technicians,
            'history' => $history,
            'availableTools' => $availableTools,
            'availableCount' => $availableTools->count(),
            'loanedCount' => $activeLoans->count(),
            'overdueCount' => $activeLoans->filter(fn (LoanRecord $loan) => $loan->due_at->lt(today()))->count(),
        ]);
    }

    public function storeTool(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['nullable', 'string', 'max:40', 'unique:tools,code'],
            'category' => ['required', 'string', 'max:80'],
            'location' => ['nullable', 'string', 'max:120'],
        ]);

        Tool::create($validated);

        return back()->with('success', 'Alat berhasil ditambahkan.');
    }

    public function updateToolCondition(Request $request, Tool $tool): RedirectResponse
    {
        $validated = $request->validate([
            'current_condition' => ['required', Rule::in(['baik', 'rusak'])],
        ]);

        if ($validated['current_condition'] === 'rusak' && $tool->loanRecords()->whereNull('returned_at')->exists()) {
            return back()->withErrors(['condition' => 'Alat yang sedang dipinjam harus dikembalikan terlebih dahulu.']);
        }

        $tool->update($validated);

        return back()->with('success', 'Kondisi alat berhasil diperbarui.');
    }

    public function archiveTool(Tool $tool): RedirectResponse
    {
        if ($tool->loanRecords()->whereNull('returned_at')->exists()) {
            return back()->withErrors(['tool' => 'Alat yang sedang dipinjam tidak dapat diarsipkan.']);
        }

        $tool->update(['is_active' => false]);

        return back()->with('success', 'Alat diarsipkan. Riwayat peminjamannya tetap tersimpan.');
    }

    public function storeTechnician(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'department' => ['nullable', 'string', 'max:100'],
        ]);

        Technician::create($validated);

        return back()->with('success', 'Nama teknisi berhasil ditambahkan.');
    }

    public function archiveTechnician(Technician $technician): RedirectResponse
    {
        if ($technician->loanRecords()->whereNull('returned_at')->exists()) {
            return back()->withErrors(['technician' => 'Teknisi masih memiliki alat yang belum dikembalikan.']);
        }

        $technician->update(['is_active' => false]);

        return back()->with('success', 'Teknisi diarsipkan. Riwayat peminjamannya tetap tersimpan.');
    }

    public function borrow(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tool_id' => ['required', 'integer', 'exists:tools,id'],
            'technician_id' => ['required', 'integer', 'exists:technicians,id'],
            'due_at' => ['required', 'date', 'after_or_equal:today'],
        ]);

        DB::transaction(function () use ($validated): void {
            $tool = Tool::query()->lockForUpdate()->findOrFail($validated['tool_id']);
            $technician = Technician::findOrFail($validated['technician_id']);

            if (! $tool->is_active || $tool->current_condition !== 'baik') {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'tool_id' => 'Alat ini tidak tersedia untuk dipinjam.',
                ]);
            }

            if (! $technician->is_active) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'technician_id' => 'Teknisi ini sudah tidak aktif.',
                ]);
            }

            if ($tool->loanRecords()->whereNull('returned_at')->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'tool_id' => 'Alat ini sedang dipinjam. Pilih alat lain.',
                ]);
            }

            $tool->loanRecords()->create([
                'technician_id' => $technician->id,
                'borrowed_at' => now(),
                'due_at' => $validated['due_at'],
            ]);
        });

        return back()->with('success', 'Peminjaman berhasil dicatat.');
    }

    public function returnTool(Request $request, LoanRecord $loan): RedirectResponse
    {
        $validated = $request->validate([
            'return_condition' => ['required', Rule::in(['baik', 'rusak'])],
            'return_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($loan->returned_at) {
            return back()->withErrors(['loan' => 'Peminjaman ini sudah ditutup.']);
        }

        DB::transaction(function () use ($loan, $validated): void {
            $loan->update([
                'returned_at' => now(),
                'return_condition' => $validated['return_condition'],
                'return_notes' => $validated['return_notes'] ?? null,
            ]);
            $loan->tool()->update(['current_condition' => $validated['return_condition']]);
        });

        return back()->with('success', 'Pengembalian berhasil dicatat.');
    }
}
