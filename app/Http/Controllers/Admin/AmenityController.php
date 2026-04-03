<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ListingType;
use App\Http\Controllers\Controller;
use App\Models\Amenity;
use App\Models\AmenityGroup;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    public function index(Request $request)
    {
        $search  = $request->input('search');
        $type    = $request->input('type');
        $groupId = $request->input('group');

        $query = Amenity::with('amenityGroup')
            ->leftJoin('amenity_groups', 'amenities.amenity_group_id', '=', 'amenity_groups.id')
            ->orderByRaw("COALESCE(amenity_groups.sort_order, 9999)")
            ->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(amenity_groups.name, '$.az'))")
            ->orderBy('amenities.sort_order')
            ->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(amenities.name, '$.az'))")
            ->select('amenities.*');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(amenities.name, '$.az')) like ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(amenities.name, '$.en')) like ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(amenities.name, '$.ru')) like ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(amenity_groups.name, '$.az')) like ?", ["%{$search}%"]);
            });
        }

        if ($type) {
            if ($type === 'universal') {
                $query->whereNull('amenities.listing_type');
            } elseif (ListingType::tryFrom($type)) {
                $query->where('amenities.listing_type', $type);
            }
        }

        if ($groupId) {
            $query->where('amenities.amenity_group_id', $groupId);
        }

        $amenities = $query->paginate(30)->withQueryString();
        $types     = ListingType::cases();
        $groups    = AmenityGroup::orderBy('sort_order')->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.az'))")->get();

        return view('admin.amenities.index', compact('amenities', 'types', 'groups', 'search', 'type', 'groupId'));
    }

    public function create()
    {
        $types  = ListingType::cases();
        $groups = AmenityGroup::orderBy('sort_order')->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.az'))")->get();

        return view('admin.amenities.create', compact('types', 'groups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'listing_type'    => 'nullable|string|in:' . implode(',', array_column(ListingType::cases(), 'value')),
            'name.az'         => 'required|string|max:255',
            'name.ru'         => 'required|string|max:255',
            'name.en'         => 'required|string|max:255',
            'icon'            => 'nullable|string|max:100',
            'amenity_group_id' => 'nullable|exists:amenity_groups,id',
        ]);

        $validated['listing_type']    = $validated['listing_type'] ?: null;
        $validated['amenity_group_id'] = $validated['amenity_group_id'] ?: null;

        $amenity = Amenity::create($validated);

        return redirect()->route('admin.amenities.index')
            ->with('success', "Удобство \"{$amenity->getTranslation('name', 'az')}\" успешно создано.");
    }

    public function edit(Amenity $amenity)
    {
        $types  = ListingType::cases();
        $groups = AmenityGroup::orderBy('sort_order')->orderByRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.az'))")->get();

        return view('admin.amenities.edit', compact('amenity', 'types', 'groups'));
    }

    public function update(Request $request, Amenity $amenity)
    {
        $validated = $request->validate([
            'listing_type'    => 'nullable|string|in:' . implode(',', array_column(ListingType::cases(), 'value')),
            'name.az'         => 'required|string|max:255',
            'name.ru'         => 'required|string|max:255',
            'name.en'         => 'required|string|max:255',
            'icon'            => 'nullable|string|max:100',
            'amenity_group_id' => 'nullable|exists:amenity_groups,id',
        ]);

        $validated['listing_type']    = $validated['listing_type'] ?: null;
        $validated['amenity_group_id'] = $validated['amenity_group_id'] ?: null;

        $amenity->update($validated);

        return redirect()->route('admin.amenities.index')
            ->with('success', "Удобство \"{$amenity->getTranslation('name', 'az')}\" успешно обновлено.");
    }

    public function reorder(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer', 'exists:amenities,id'],
        ]);

        foreach ($data['ids'] as $position => $id) {
            Amenity::where('id', $id)->update(['sort_order' => $position]);
        }

        return response()->json(['ok' => true]);
    }

    public function destroy(Amenity $amenity)
    {
        $name = $amenity->getTranslation('name', 'az');
        $amenity->delete();

        return redirect()->route('admin.amenities.index')
            ->with('success', "Удобство \"{$name}\" удалено.");
    }
}
