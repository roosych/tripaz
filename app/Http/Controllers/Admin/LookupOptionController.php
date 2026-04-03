<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LookupOption;
use Illuminate\Http\Request;

class LookupOptionController extends Controller
{
    private const TYPES = [
        'language'      => 'Языки гида',
        'cuisine_type'  => 'Типы кухни',
        'property_type' => 'Типы жилья',
        'difficulty'    => 'Уровень сложности',
    ];

    public function index(Request $request)
    {
        $currentType = $request->input('type', array_key_first(self::TYPES));

        if (! array_key_exists($currentType, self::TYPES)) {
            $currentType = array_key_first(self::TYPES);
        }

        $options = LookupOption::where('type', $currentType)
            ->orderBy('sort_order')
            ->orderBy('label')
            ->get();

        return view('admin.lookup-options.index', [
            'types'       => self::TYPES,
            'currentType' => $currentType,
            'options'     => $options,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'       => ['required', 'string', 'in:' . implode(',', array_keys(self::TYPES))],
            'value'      => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/'],
            'label'      => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $exists = LookupOption::where('type', $data['type'])
            ->where('value', $data['value'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['value' => 'Такое значение уже существует для этого типа.'])->withInput();
        }

        LookupOption::create([
            'type'       => $data['type'],
            'value'      => $data['value'],
            'label'      => $data['label'],
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.lookup-options.index', ['type' => $data['type']])
            ->with('success', 'Опция добавлена.');
    }

    public function update(Request $request, LookupOption $lookupOption)
    {
        $data = $request->validate([
            'label'      => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $lookupOption->update([
            'label'      => $data['label'],
            'sort_order' => $data['sort_order'] ?? $lookupOption->sort_order,
        ]);

        return redirect()->route('admin.lookup-options.index', ['type' => $lookupOption->type])
            ->with('success', 'Опция обновлена.');
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'ids'   => ['required', 'array'],
            'ids.*' => ['integer', 'exists:lookup_options,id'],
        ]);

        foreach ($data['ids'] as $position => $id) {
            LookupOption::where('id', $id)->update(['sort_order' => $position]);
        }

        return response()->json(['ok' => true]);
    }

    public function destroy(LookupOption $lookupOption)
    {
        $type = $lookupOption->type;
        $lookupOption->delete();

        return redirect()->route('admin.lookup-options.index', ['type' => $type])
            ->with('success', 'Опция удалена.');
    }
}
