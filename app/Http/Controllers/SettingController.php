<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit()
    {
        $setting = Setting::instance();
        return view('settings.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'address1'     => 'nullable|string|max:255',
            'address2'     => 'nullable|string|max:255',
            'city'         => 'nullable|string|max:100',
            'state'        => 'nullable|string|max:100',
            'postcode'     => 'nullable|string|max:10',
            'phone'        => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:255',
            'logo'         => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $setting = Setting::instance();

        $data = $request->only(['company_name', 'address1', 'address2', 'city', 'state', 'postcode', 'phone', 'email']);

        if ($request->hasFile('logo')) {
            if ($setting->logo_path) {
                Storage::disk('public')->delete($setting->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $setting->update($data);

        return redirect()->route('settings.edit')->with('success', 'Settings updated successfully.');
    }
}
