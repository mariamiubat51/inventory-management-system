@php
    $supplier = $supplier ?? null;
@endphp

<div class="form-group mb-2">
    <label>Name</label>
    <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name ?? '') }}" required>
</div>

<div class="form-group mb-2">
    <label>Phone</label>
    <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone ?? '') }}" required>
</div>

<div class="form-group mb-2">
    <label>Email</label>
    <input type="email" name="email" class="form-control" value="{{ old('email', $supplier->email ?? '') }}">
</div>

<div class="form-group mb-2">
    <label>Company</label>
    <input type="text" name="company" class="form-control" value="{{ old('company', $supplier->company ?? '') }}">
</div>

<div class="form-group mb-2">
    <label>Address</label>
    <textarea name="address" class="form-control" required>{{ old('address', $supplier->address ?? '') }}</textarea>
</div>

<div class="form-group mb-2">
    <label>Notes</label>
    <textarea name="notes" class="form-control">{{ old('notes', $supplier->notes ?? '') }}</textarea>
</div>

<div class="form-group mb-3">
    <label>Status</label>
    <select name="status" class="form-control">
        <option value="active" {{ old('status', $supplier->status ?? '') === 'active' ? 'selected' : '' }}>Active</option>
        <option value="inactive" {{ old('status', $supplier->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
    </select>
</div>
