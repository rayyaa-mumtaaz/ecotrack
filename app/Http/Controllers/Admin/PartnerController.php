<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Modul 6 — Partner Management (admin).
 * Mitra tidak harus pendaur ulang: bisa pengrajin, pengepul, servis, dan lainnya.
 */
class PartnerController extends Controller
{
    public function index(Request $request)
    {
        $partners = Partner::withCount('pickups')
            ->withSum('pickups', 'total_weight')
            ->when($request->query('search'), function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('contact_person', 'like', "%{$search}%");
                });
            })
            ->when($request->query('type'), fn ($query, $type) => $query->where('type', $type))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.partners.index', compact('partners'));
    }

    public function create()
    {
        return view('admin.partners.create', ['partner' => new Partner(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        $partner = Partner::create($this->validatePartner($request));

        return redirect()->route('admin.partners.show', $partner)
            ->with('success', "Mitra {$partner->name} berhasil ditambahkan.");
    }

    /**
     * Detail mitra beserta riwayat pengangkutan yang diterima.
     */
    public function show(Partner $partner)
    {
        $partner->loadCount('pickups')->loadSum('pickups', 'total_weight');

        $pickups = $partner->pickups()->withCount('deposits')->latest('pickup_date')->get();

        return view('admin.partners.show', compact('partner', 'pickups'));
    }

    public function edit(Partner $partner)
    {
        return view('admin.partners.edit', compact('partner'));
    }

    public function update(Request $request, Partner $partner)
    {
        $partner->update($this->validatePartner($request));

        return redirect()->route('admin.partners.show', $partner)
            ->with('success', "Mitra {$partner->name} berhasil diperbarui.");
    }

    /**
     * Mitra yang sudah menerima pengangkutan tidak boleh dihapus.
     */
    public function destroy(Partner $partner)
    {
        if ($partner->pickups()->exists()) {
            return back()->with('error', "Mitra {$partner->name} sudah memiliki riwayat pengangkutan sehingga tidak dapat dihapus. Nonaktifkan saja.");
        }

        $partner->delete();

        return redirect()->route('admin.partners.index')
            ->with('success', "Mitra {$partner->name} berhasil dihapus.");
    }

    private function validatePartner(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'type' => ['required', Rule::in(array_keys(Partner::TYPES))],
            'contact_person' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
