<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    // Show settings form
    public function index()
    {
        // Always take the first row (id = 1) or create default if not exist
        $setting = Setting::firstOrCreate(['id' => 1]);

        return view('settings.index', compact('setting'));
    }

    // Save/Update settings
    public function update(Request $request)
    {
        $setting = Setting::first(); // or find(1)
        $data = $request->all();

        // handle file upload if logo exists
        if($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('uploads/settings'), $filename);
            $data['logo'] = 'uploads/settings/'.$filename;
        }

        $setting->update($data);

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }

}
