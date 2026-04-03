<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ListingType;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $type   = $request->input('type');

        $query = Category::with('parent')
            ->orderBy('sort_order')
            ->orderBy('name->az');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name->az', 'like', "%{$search}%")
                  ->orWhere('name->en', 'like', "%{$search}%")
                  ->orWhere('name->ru', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($type) {
            if ($type === 'universal') {
                $query->whereNull('listing_type');
            } elseif (ListingType::tryFrom($type)) {
                $query->where('listing_type', $type);
            }
        }

        $categories    = $query->paginate(30)->withQueryString();
        $types         = ListingType::cases();
        $parentOptions = Category::whereNull('parent_id')
            ->orderBy('name->az')
            ->get(['id', 'name', 'listing_type']);

        return view('admin.categories.index', compact('categories', 'types', 'search', 'type', 'parentOptions'));
    }

    public function create()
    {
        $types         = ListingType::cases();
        $parentOptions = Category::whereNull('parent_id')
            ->orderBy('name->az')
            ->get(['id', 'name', 'listing_type']);

        return view('admin.categories.create', compact('types', 'parentOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'listing_type' => 'nullable|string|in:' . implode(',', array_column(ListingType::cases(), 'value')),
            'parent_id'    => 'nullable|integer|exists:categories,id',
            'name.az'      => 'required|string|max:255',
            'name.ru'      => 'required|string|max:255',
            'name.en'      => 'required|string|max:255',
            'slug'         => 'nullable|string|max:255|unique:categories,slug',
            'icon'         => 'nullable|string|max:100',
            'sort_order'   => 'nullable|integer|min:0',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']['en'] ?: $validated['name']['az']);
        }

        $slug  = $validated['slug'];
        $count = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $validated['slug'] . '-' . $count++;
        }
        $validated['slug']         = $slug;
        $validated['sort_order']   = $validated['sort_order'] ?? 0;
        $validated['listing_type'] = $validated['listing_type'] ?: null;
        $validated['parent_id']    = $validated['parent_id'] ?: null;

        $category = Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', "Category \"{$category->getTranslation('name', 'az')}\" created successfully.");
    }

    public function edit(Category $category)
    {
        $types         = ListingType::cases();
        $parentOptions = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name->az')
            ->get(['id', 'name', 'listing_type']);

        return view('admin.categories.edit', compact('category', 'types', 'parentOptions'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'listing_type' => 'nullable|string|in:' . implode(',', array_column(ListingType::cases(), 'value')),
            'parent_id'    => 'nullable|integer|exists:categories,id',
            'name.az'      => 'required|string|max:255',
            'name.ru'      => 'required|string|max:255',
            'name.en'      => 'required|string|max:255',
            'slug'         => 'required|string|max:255|unique:categories,slug,' . $category->id,
            'icon'         => 'nullable|string|max:100',
            'sort_order'   => 'nullable|integer|min:0',
        ]);

        $validated['sort_order']   = $validated['sort_order'] ?? 0;
        $validated['listing_type'] = $validated['listing_type'] ?: null;
        $validated['parent_id']    = $validated['parent_id'] ?: null;

        if ($validated['parent_id'] && $validated['parent_id'] == $category->id) {
            return back()->withErrors(['parent_id' => 'A category cannot be its own parent.'])->withInput();
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', "Category \"{$category->getTranslation('name', 'az')}\" updated successfully.");
    }

    public function destroy(Category $category)
    {
        $name = $category->getTranslation('name', 'az');

        Category::where('parent_id', $category->id)
            ->update(['parent_id' => $category->parent_id]);

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', "Category \"{$name}\" deleted successfully.");
    }
}
