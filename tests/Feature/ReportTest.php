<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Stage 6 — Modul 5: laporan, ekspor PDF/Excel, dan dashboard.
 * Data seeder: 5 setoran (12,76 kg); Oktober 2026: 1 setoran 2,70 kg; November 2026: 4 setoran 10,06 kg.
 */
class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        $this->admin = User::where('email', 'superadmin@ecotrack.com')->first();
    }

    public function test_report_shows_all_period_summary(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/reports')
            ->assertOk()
            ->assertSee('Semua periode')
            ->assertSee('12,76 kg')
            ->assertSee('Perangkat komputer')
            ->assertSee('Petugas Lapangan STS')       // rekap per petugas
            ->assertSee('Pengepul')                   // rekap per tipe mitra
            ->assertSee('Oktober 2026')
            ->assertSee('November 2026');
    }

    public function test_report_date_filter(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/reports?start_date=2026-11-01&end_date=2026-11-30')
            ->assertOk()
            ->assertSee('10,06 kg')        // 0,65 + 7,50 + 1,26 + 0,65
            ->assertDontSee('Oktober 2026');

        $this->assertSame(4, $response->viewData('summary')['deposits']);
        $this->assertSame(2, $response->viewData('summary')['pickups']); // PCK-20261110-0001 dan PCK-20261117-0001

        // Filter yang hanya mencakup 1 pengangkutan
        $response = $this->actingAs($this->admin)->get('/admin/reports?start_date=2026-11-15');
        $this->assertSame(1, $response->viewData('summary')['pickups']);
        $this->assertSame(1, $response->viewData('summary')['deposits']); // DEP-20261118-0001
    }

    public function test_end_date_must_not_be_before_start_date(): void
    {
        $this->actingAs($this->admin)
            ->from('/admin/reports')
            ->get('/admin/reports?start_date=2026-11-30&end_date=2026-11-01')
            ->assertRedirect('/admin/reports')
            ->assertSessionHasErrors('end_date');
    }

    public function test_export_pdf_downloads_file(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/reports/export/pdf?start_date=2026-11-01&end_date=2026-11-30');

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('laporan-ecotrack-2026-11-01_2026-11-30.pdf', $response->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_export_excel_downloads_file_without_keeping_it_on_server(): void
    {
        $tempDir = storage_path('framework/cache/laravel-excel');
        $before = File::isDirectory($tempDir) ? count(File::files($tempDir)) : 0;

        $response = $this->actingAs($this->admin)->get('/admin/reports/export/excel');

        $response->assertOk()->assertDownload();
        $this->assertStringContainsString('.xlsx', $response->headers->get('Content-Disposition'));

        // Kirim file ke browser; setelah terkirim, file sementara dihapus
        ob_start();
        $response->baseResponse->sendContent();
        $content = ob_get_clean();

        $this->assertStringStartsWith('PK', $content); // file .xlsx = arsip zip
        $after = File::isDirectory($tempDir) ? count(File::files($tempDir)) : 0;
        $this->assertSame($before, $after);
    }

    public function test_dashboard_shows_category_summary_and_waiting_deposits(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/dashboard')
            ->assertOk()
            ->assertSee('Ringkasan per Kategori')
            ->assertSee('Perangkat elektronik kecil')
            ->assertSee('menunggu diangkut')      // 2 setoran Terkumpul
            ->assertSee('drop-point-map', false); // peta mini
    }

    public function test_user_cannot_access_reports(): void
    {
        $user = User::where('email', 'rayyaa@ecotrack.com')->first();

        $this->actingAs($user)->get('/admin/reports')->assertForbidden();
        $this->actingAs($user)->get('/admin/reports/export/pdf')->assertForbidden();
        $this->actingAs($user)->get('/admin/reports/export/excel')->assertForbidden();
    }
}
