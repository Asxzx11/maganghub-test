<?php

namespace Tests\Feature;

use App\Models\LoanRecord;
use App\Models\Technician;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToolLoanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_dashboard_shows_current_borrower_and_tool_status(): void
    {
        $tool = Tool::create(['name' => 'Multimeter', 'category' => 'Alat ukur']);
        $technician = Technician::create(['name' => 'Budi', 'department' => 'Mekanik']);
        LoanRecord::create([
            'tool_id' => $tool->id,
            'technician_id' => $technician->id,
            'borrowed_at' => now()->subDay(),
            'due_at' => today()->addDay(),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Multimeter')
            ->assertSee('Budi')
            ->assertSee('Sedang dipinjam');
    }

    public function test_dashboard_counts_only_loans_past_the_due_date_as_overdue(): void
    {
        $technician = Technician::create(['name' => 'Budi']);
        $lateTool = Tool::create(['name' => 'Multimeter', 'category' => 'Alat ukur']);
        $dueTodayTool = Tool::create(['name' => 'Jangka sorong', 'category' => 'Alat ukur']);

        LoanRecord::create([
            'tool_id' => $lateTool->id,
            'technician_id' => $technician->id,
            'borrowed_at' => now()->subDays(2),
            'due_at' => today()->subDay(),
        ]);
        LoanRecord::create([
            'tool_id' => $dueTodayTool->id,
            'technician_id' => $technician->id,
            'borrowed_at' => now()->subDay(),
            'due_at' => today(),
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('class="warn">1</strong><span>Lewat tanggal kembali</span>', false)
            ->assertSee('Terlambat');
    }

    public function test_available_tool_can_be_borrowed_and_returned_damaged_with_history(): void
    {
        $tool = Tool::create(['name' => 'Bor listrik', 'category' => 'Perkakas']);
        $technician = Technician::create(['name' => 'Sari']);

        $this->post('/loans', [
            'tool_id' => $tool->id,
            'technician_id' => $technician->id,
            'due_at' => today()->addDays(3)->toDateString(),
        ])->assertRedirect();

        $loan = LoanRecord::firstOrFail();
        $this->assertNull($loan->returned_at);
        $this->assertSame($technician->id, $loan->technician_id);

        $this->patch("/loans/{$loan->id}/return", [
            'return_condition' => 'rusak',
            'return_notes' => 'Mata bor perlu diganti.',
        ])->assertRedirect();

        $this->assertDatabaseHas('loan_records', [
            'id' => $loan->id,
            'return_condition' => 'rusak',
            'return_notes' => 'Mata bor perlu diganti.',
        ]);
        $this->assertNotNull($loan->fresh()->returned_at);
        $this->assertDatabaseHas('tools', ['id' => $tool->id, 'current_condition' => 'rusak']);

        $this->get('/')
            ->assertSee('Sari')
            ->assertSee('Dikembalikan rusak')
            ->assertSee('Mata bor perlu diganti.');
    }

    public function test_tool_cannot_be_borrowed_twice_or_when_damaged(): void
    {
        $tool = Tool::create(['name' => 'Kunci torsi', 'category' => 'Perkakas']);
        $firstTechnician = Technician::create(['name' => 'Budi']);
        $secondTechnician = Technician::create(['name' => 'Rina']);
        LoanRecord::create([
            'tool_id' => $tool->id,
            'technician_id' => $firstTechnician->id,
            'borrowed_at' => now(),
            'due_at' => today()->addDay(),
        ]);

        $this->from('/')->post('/loans', [
            'tool_id' => $tool->id,
            'technician_id' => $secondTechnician->id,
            'due_at' => today()->addDays(2)->toDateString(),
        ])->assertRedirect('/')
            ->assertSessionHasErrors('tool_id');

        $existingLoan = LoanRecord::firstOrFail();
        $existingLoan->update([
            'returned_at' => now(),
            'return_condition' => 'rusak',
        ]);
        $tool->update(['current_condition' => 'rusak']);

        $this->from('/')->post('/loans', [
            'tool_id' => $tool->id,
            'technician_id' => $secondTechnician->id,
            'due_at' => today()->addDays(2)->toDateString(),
        ])->assertRedirect('/')
            ->assertSessionHasErrors('tool_id');

        $this->assertDatabaseCount('loan_records', 1);
    }

    public function test_returned_tool_keeps_loan_history_when_archived(): void
    {
        $tool = Tool::create(['name' => 'Obeng listrik', 'category' => 'Perkakas']);
        $technician = Technician::create(['name' => 'Dewi']);
        $loan = LoanRecord::create([
            'tool_id' => $tool->id,
            'technician_id' => $technician->id,
            'borrowed_at' => now()->subDays(2),
            'due_at' => today()->subDay(),
            'returned_at' => now(),
            'return_condition' => 'baik',
        ]);

        $this->patch("/tools/{$tool->id}/archive")->assertRedirect();

        $this->assertDatabaseHas('loan_records', ['id' => $loan->id, 'technician_id' => $technician->id]);
        $this->assertDatabaseHas('tools', ['id' => $tool->id, 'is_active' => false]);
    }
}
