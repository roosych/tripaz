<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ListingType;
use App\Http\Controllers\Controller;
use App\Models\AmenityGroup;
use Illuminate\Http\Request;

class AmenityGroupController extends Controller
{
    public function index()
    {
        $groups = AmenityGroup::withCount('amenities')
            ->orderBy('sort_order')
            ->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.az'))")
            ->get();

        return view('admin.amenity-groups.index', compact('groups'));
    }

    public function create()
    {
        $types = ListingType::cases();

        return view('admin.amenity-groups.create', compact('types'));
    }

    public function store(Request $request)
    {
        $typeValues = array_column(ListingType::cases(), 'value');

        $validated = $request->validate([
            'name.az'        => 'required|string|max:100',
            'name.ru'        => 'required|string|max:100',
            'name.en'        => 'required|string|max:100',
            'sort_order'     => 'nullable|integer|min:0|max:9999',
            'listing_types'  => 'nullable|array',
            'listing_types.*'=> 'string|in:' . implode(',', $typeValues),
        ]);

        $validated['sort_order']    = $validated['sort_order'] ?? 0;
        $validated['listing_types'] = !empty($validated['listing_types']) ? $validated['listing_types'] : null;

        AmenityGroup::create($validated);

        return redirect()->route('admin.amenity-groups.index')
            ->with('success', "Группа \"{$validated['name']['az']}\" создана.");
    }

    public function edit(AmenityGroup $amenityGroup)
    {
        $amenityGroup->loadCount('amenities');
        $types = ListingType::cases();

        return view('admin.amenity-groups.edit', compact('amenityGroup', 'types'));
    }

    public function update(Request $request, AmenityGroup $amenityGroup)
    {
        $typeValues = array_column(ListingType::cases(), 'value');

        $validated = $request->validate([
            'name.az'        => 'required|string|max:100',
            'name.ru'        => 'required|string|max:100',
            'name.en'        => 'required|string|max:100',
            'sort_order'     => 'nullable|integer|min:0|max:9999',
            'listing_types'  => 'nullable|array',
            'listing_types.*'=> 'string|in:' . implode(',', $typeValues),
        ]);

        $validated['sort_order']    = $validated['sort_order'] ?? 0;
        $validated['listing_types'] = !empty($validated['listing_types']) ? $validated['listing_types'] : null;

        $amenityGroup->update($validated);

        return redirect()->route('admin.amenity-groups.index')
            ->with('success', "Группа \"{$amenityGroup->getTranslation('name', 'az')}\" обновлена.");
    }

    public function reorder(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer', 'exists:amenity_groups,id'],
        ]);

        foreach ($data['ids'] as $position => $id) {
            AmenityGroup::where('id', $id)->update(['sort_order' => $position]);
        }

        return response()->json(['ok' => true]);
    }

    public function destroy(AmenityGroup $amenityGroup)
    {
        if ($amenityGroup->amenities()->exists()) {
            return back()->with('error',
                "Нельзя удалить группу \"{$amenityGroup->getTranslation('name', 'az')}\" — в ней есть удобства. " .
                'Сначала переназначьте или удалите удобства этой группы.'
            );
        }

        $name = $amenityGroup->getTranslation('name', 'az');
        $amenityGroup->delete();

        return redirect()->route('admin.amenity-groups.index')
            ->with('success', "Группа \"{$name}\" удалена.");
    }
}
