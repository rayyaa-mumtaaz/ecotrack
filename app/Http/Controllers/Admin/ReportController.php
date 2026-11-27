<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ReportExport;
use App\Http\Controllers\Controller;
use App\Models\Deposit;
use App\Models\DepositItem;
use App\Models\Partner;
use App\Models\Pickup;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Modul 5 — Laporan + ekspor PDF/Excel.
 * Data laporan dibuat oleh buildReport() dan dipakai bersama oleh halaman web, PDF, dan Excel.
 * File ekspor dibuat saat diunduh dan langsung dikirim ke browser (tidak disimpan di server).
 */
class ReportController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.reports.index', $this->buildReport($request));
    }

    public function exportPdf(Request $request)
    {
        $report = $this->buildReport($request);

        return Pdf::loadView('admin.reports.pdf', $report)
            ->setPaper('a4')
            ->download($this->fileName($report, 'pdf'));
    }

    public function exportExcel(Request $request)
    {
        $report = $this->buildReport($request);

        return Excel::download(new ReportExport($report), $this->fileName($report, 'xlsx'));
    }

    /**
     * Menghitung seluruh rekap laporan sesuai filter tanggal.
     */
    private function buildReport(Request $request): array
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ], [], [
            'start_date' => 'tanggal mulai',
            'end_date' => 'tanggal akhir',
        ]);

        $start = $request->query('start_date');
        $end = $request->query('end_date');

        // Petugas hanya melihat laporan drop point tempat ia bertugas; superadmin semua drop point
        $user = $request->user();
        $dropPointId = $user->isLimitedToDropPoint() ? $user->drop_point_id : null;
        $limited = $user->isLimitedToDropPoint();

        // Filter tanggal (dan drop point) yang dipakai berulang untuk setoran dan pengangkutan
        $depositFilter = function ($query) use ($start, $end, $limited, $dropPointId) {
            $query->when($start, fn ($query) => $query->whereDate('deposit_date', '>=', $start))
                ->when($end, fn ($query) => $query->whereDate('deposit_date', '<=', $end))
                ->when($limited, fn ($query) => $query->where('drop_point_id', $dropPointId));
        };
        $pickupFilter = function ($query) use ($start, $end, $limited, $dropPointId) {
            $query->when($start, fn ($query) => $query->whereDate('pickup_date', '>=', $start))
                ->when($end, fn ($query) => $query->whereDate('pickup_date', '<=', $end))
                ->when($limited, fn ($query) => $query->whereHas('deposits', fn ($query) => $query->where('drop_point_id', $dropPointId)));
        };

        $statusCounts = Deposit::where($depositFilter)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $summary = [
            'deposits' => Deposit::where($depositFilter)->count(),
            'weight' => (float) Deposit::where($depositFilter)->sum('total_weight'),
            'items' => (int) DepositItem::whereHas('deposit', $depositFilter)->sum('quantity'),
            'depositors' => Deposit::where($depositFilter)->distinct()->count('user_id'),
            'pickups' => Pickup::where($pickupFilter)->count(),
            'pickup_weight' => (float) Pickup::where($pickupFilter)->sum('total_weight'),
            'statuses' => collect(Deposit::STATUSES)->map(fn ($label, $status) => (int) ($statusCounts[$status] ?? 0)),
        ];

        $byCategory = DepositItem::with('category')
            ->whereHas('deposit', $depositFilter)
            ->selectRaw('category_id, SUM(quantity) as quantity, SUM(weight) as weight')
            ->groupBy('category_id')
            ->get()
            ->map(fn ($row) => ['name' => $row->category->name, 'quantity' => (int) $row->quantity, 'weight' => (float) $row->weight])
            ->sortByDesc('weight')->values();

        $byDropPoint = Deposit::with('dropPoint')
            ->where($depositFilter)
            ->selectRaw('drop_point_id, COUNT(*) as deposits, SUM(total_weight) as weight')
            ->groupBy('drop_point_id')
            ->get()
            ->map(fn ($row) => ['name' => $row->dropPoint->name, 'deposits' => (int) $row->deposits, 'weight' => (float) $row->weight])
            ->sortByDesc('weight')->values();

        $byOfficer = Deposit::with('receiver')
            ->where($depositFilter)
            ->selectRaw('received_by, COUNT(*) as deposits, SUM(total_weight) as weight')
            ->groupBy('received_by')
            ->get()
            ->map(fn ($row) => ['name' => $row->receiver?->name ?? 'Tidak tercatat', 'deposits' => (int) $row->deposits, 'weight' => (float) $row->weight])
            ->sortByDesc('weight')->values();

        // Rekap per bulan dikelompokkan di PHP agar tidak bergantung fungsi tanggal khusus MySQL
        $byPeriod = Deposit::where($depositFilter)
            ->orderBy('deposit_date')
            ->get(['deposit_date', 'total_weight'])
            ->groupBy(fn ($deposit) => $deposit->deposit_date->format('Y-m'))
            ->map(fn ($group) => [
                'name' => $group->first()->deposit_date->translatedFormat('F Y'),
                'deposits' => $group->count(),
                'weight' => (float) $group->sum('total_weight'),
            ])->values();

        $byPartner = Pickup::with('partner')
            ->where($pickupFilter)
            ->selectRaw('partner_id, COUNT(*) as pickups, SUM(total_weight) as weight')
            ->groupBy('partner_id')
            ->get()
            ->map(fn ($row) => [
                'name' => $row->partner?->name ?? 'Tidak tercatat',
                'type' => $row->partner?->typeLabel() ?? '—',
                'pickups' => (int) $row->pickups,
                'weight' => (float) $row->weight,
            ])
            ->sortByDesc('weight')->values();

        $byPartnerType = $byPartner->groupBy('type')
            ->map(fn ($group, $type) => ['name' => $type, 'pickups' => $group->sum('pickups'), 'weight' => $group->sum('weight')])
            ->sortByDesc('weight')->values();

        return [
            'filters' => [
                'start_date' => $start,
                'end_date' => $end,
                'label' => $this->periodLabel($start, $end),
                'drop_point' => $limited ? ($user->dropPoint?->name ?? 'Belum ditugaskan') : 'Semua drop point',
            ],
            'generatedAt' => now()->translatedFormat('d F Y H:i'),
            'summary' => $summary,
            'byCategory' => $byCategory,
            'byDropPoint' => $byDropPoint,
            'byOfficer' => $byOfficer,
            'byPeriod' => $byPeriod,
            'byPartner' => $byPartner,
            'byPartnerType' => $byPartnerType,
            'partnerTypes' => Partner::TYPES,
        ];
    }

    private function periodLabel(?string $start, ?string $end): string
    {
        $format = fn ($date) => Carbon::parse($date)->translatedFormat('d F Y');

        return match (true) {
            $start && $end => $format($start) . ' – ' . $format($end),
            (bool) $start => 'Sejak ' . $format($start),
            (bool) $end => 'Sampai ' . $format($end),
            default => 'Semua periode',
        };
    }

    private function fileName(array $report, string $extension): string
    {
        $period = ($report['filters']['start_date'] ?? 'awal') . '_' . ($report['filters']['end_date'] ?? now()->toDateString());

        return "laporan-ecotrack-{$period}.{$extension}";
    }
}
