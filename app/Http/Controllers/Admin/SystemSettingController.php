<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\SystemSettingsService;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function __construct(
        private SystemSettingsService $settings,
    ) {}

    public function index()
    {
        $settings = SystemSetting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        return view('admin.system-settings.index', compact('settings'));
    }

    public function update(Request $request, SystemSetting $setting)
    {
        $data = $request->validate([
            'value' => ['required', 'string', 'max:1000'],
        ]);

        $this->settings->set($setting->key, $data['value']);

        return back()->with('success', "Setting [{$setting->key}] updated.");
    }
}
