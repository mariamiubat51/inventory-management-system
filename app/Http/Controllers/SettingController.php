<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\ProductUnit;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    // Show settings form
    public function index()
    {
        // Take first row or create default
        $setting = Setting::firstOrCreate(['id' => 1]);

        // Fetch all units from product_units table
        $units = ProductUnit::all();

        return view('settings.index', compact('setting', 'units'));
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
