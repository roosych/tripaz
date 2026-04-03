<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RegionType;
use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RegionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $type   = $request->input('type');

        $query = Region::with('parent')->orderBy('name->az');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name->az', 'like', "%{$search}%")
                  ->orWhere('name->en', 'like', "%{$search}%")
                  ->orWhere('name->ru', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($type && RegionType::tryFrom($type)) {
            $query->where('type', $type);
        }

        $regions = $query->paginate(30)->withQueryString();
        $types   = RegionType::cases();

        return view('admin.regions.index', compact('regions', 'types', 'search', 'type'));
    }

    public function create()
    {
        $types         = RegionType::cases();
        $parentOptions = Region::orderBy('name->az')->get(['id', 'name', 'type']);

        return view('admin.regions.create', compact('types', 'parentOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|integer|exists:regions,id',
            'name.az'   => 'required|string|max:255',
            'name.ru'   => 'required|string|max:255',
            'name.en'   => 'required|string|max:255',
            'slug'      => 'nullable|string|max:255|unique:regions,slug',
            'type'      => 'required|string|in:' . implode(',', array_column(RegionType::cases(), 'value')),
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']['en'] ?: $validated['name']['az']);
        }

        $slug  = $validated['slug'];
        $count = 1;
        while (Region::where('slug', $slug)->exists()) {
            $slug = $validated['slug'] . '-' . $count++;
        }
        $validated['slug']      = $slug;
        $validated['parent_id'] = $validated['parent_id'] ?: null;

        $region = Region::create($validated);

        return redirect()->route('admin.regions.index')
            ->with('success', "Region \"{$region->getTranslation('name', 'az')}\" created successfully.");
    }

    public function edit(Region $region)
    {
        $types         = RegionType::cases();
        $parentOptions = Region::where('id', '!=', $region->id)
            ->orderBy('name->az')
            ->get(['id', 'name', 'type']);

        return view('admin.regions.edit', compact('region', 'types', 'parentOptions'));
    }

    public function update(Request $request, Region $region)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|integer|exists:regions,id',
            'name.az'   => 'required|string|max:255',
            'name.ru'   => 'required|string|max:255',
            'name.en'   => 'required|string|max:255',
            'slug'      => 'required|string|max:255|unique:regions,slug,' . $region->id,
            'type'      => 'required|string|in:' . implode(',', array_column(RegionType::cases(), 'value')),
        ]);

        $validated['parent_id'] = $validated['parent_id'] ?: null;

        if ($validated['parent_id'] && $validated['parent_id'] == $region->id) {
            return back()->withErrors(['parent_id' => 'A region cannot be its own parent.'])->withInput();
        }

        $region->update($validated);

        return redirect()->route('admin.regions.index')
            ->with('success', "Region \"{$region->getTranslation('name', 'az')}\" updated successfully.");
    }

    public function destroy(Region $region)
    {
        $name = $region->getTranslation('name', 'az');

        Region::where('parent_id', $region->id)
            ->update(['parent_id' => $region->parent_id]);

        $region->delete();

        return redirect()->route('admin.regions.index')
            ->with('success', "Region \"{$name}\" deleted successfully.");
    }
}
