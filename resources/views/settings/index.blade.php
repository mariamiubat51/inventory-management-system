@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="mb-4">System Settings</h2>

    {{-- Single Form --}}
    <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Tabs --}}
        <ul class="nav nav-tabs" id="settingsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="company-tab" data-bs-toggle="tab" data-bs-target="#company" type="button" role="tab">Company Info</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="system-tab" data-bs-toggle="tab" data-bs-target="#system" type="button" role="tab">System Preferences</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">Inventory</button>
            </li>
        </ul>

        {{-- Tab Contents --}}
        <div class="tab-content mt-3" id="settingsTabContent">

            {{-- Company Info Tab --}}
            <div class="tab-pane fade show active" id="company" role="tabpanel">
                <div class="mb-3">
                    <label>Company Name</label>
                    <input type="text" name="company_name" class="form-control" value="{{ $setting->company_name ?? '' }}">
                </div>
                <div class="mb-3">
                    <label>Logo</label>
                    <input type="file" name="logo" class="form-control">
                    @if($setting && $setting->logo)
                        <img src="{{ asset($setting->logo) }}" alt="Logo" class="img-thumbnail mt-2" style="max-width:150px;">
                    @endif
                </div>
                <div class="mb-3">
                    <label>Address</label>
                    <input type="text" name="address" class="form-control" value="{{ $setting->address ?? '' }}">
                </div>
                <div class="mb-3">
                    <label>Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ $setting->phone ?? '' }}">
                </div>
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ $setting->email ?? '' }}">
                </div>
            </div>

            {{-- System Preferences Tab --}}
            <div class="tab-pane fade" id="system" role="tabpanel">
                <div class="mb-3">
                    <label>Timezone</label>
                    <select name="timezone" class="form-select">
                        @foreach(timezone_identifiers_list() as $tz)
                            <option value="{{ $tz }}" {{ ($setting->timezone ?? '') == $tz ? 'selected' : '' }}>{{ $tz }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Default Currency</label>
                    <input type="text" name="currency" class="form-control" value="{{ $setting->currency ?? '' }}">
                </div>
            </div>

            {{-- Inventory Tab --}}
            <div class="tab-pane fade" id="inventory" role="tabpanel">
                <div class="mb-3">
                    <label>Low Stock Alert</label>
                    <input type="number" name="low_stock_alert" class="form-control" value="{{ $setting->low_stock_alert ?? '' }}">
                </div>
                <div class="mb-3">
                    <label>Default Unit</label>
                    <select name="default_unit" class="form-select">
                        @foreach($units as $unit)
                            <option value="{{ $unit->name }}" {{ ($setting->default_unit ?? '') == $unit->name ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

        </div>

        {{-- Single Save Button --}}
        <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
    </form>
</div>
@endsection
