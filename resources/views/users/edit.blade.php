@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit User</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Name<span class="text-danger">*</span></label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" id="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email<span class="text-danger">*</span></label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" id="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" id="phone" class="form-control">
        </div>

        <div class="mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea name="address" id="address" rows="3" class="form-control">{{ old('address', $user->address) }}</textarea>
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Role<span class="text-danger">*</span></label>
            <select name="role" id="role" class="form-select" required>
                <option value="">-- Select Role --</option>
                <option value="admin" {{ (old('role', $user->role) == 'admin') ? 'selected' : '' }}>Admin</option>
                <option value="manager" {{ (old('role', $user->role) == 'manager') ? 'selected' : '' }}>Manager</option>
                <option value="accountant" {{ (old('role', $user->role) == 'accountant') ? 'selected' : '' }}>Accountant</option>
                <option value="customer" {{ (old('role', $user->role) == 'customer') ? 'selected' : '' }}>Customer</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password <small>(leave blank to keep current password)</small></label>
            <input type="password" name="password" id="password" class="form-control" minlength="6">
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" minlength="6">
        </div>

        <button type="submit" class="btn btn-primary">Update User</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
