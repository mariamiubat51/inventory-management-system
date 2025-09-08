@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">
        Product Units
        <button class="btn btn-primary float-end" data-bs-toggle="modal" data-bs-target="#addUnitModal">➕ Add Unit</button>
    </h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Validation error:</strong>
            <ul class="mb-0">
                @foreach($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Symbol</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($units as $idx => $unit)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $unit->name }}</td>
                <td>{{ $unit->symbol }}</td>
                <td>
                    @if($unit->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </td>
                <td>
                    <button class="btn btn-sm btn-warning me-2"
                            data-bs-toggle="modal"
                            data-bs-target="#editUnitModal"
                            data-action="{{ route('products.units.update', $unit) }}"
                            data-name="{{ $unit->name }}"
                            data-symbol="{{ $unit->symbol }}"
                            data-is_active="{{ $unit->is_active ? 1 : 0 }}">Edit</button>

                    <form action="{{ route('products.units.destroy', $unit) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this unit?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5" class="text-center">No units found.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<!-- Add Unit Modal -->
<div class="modal fade" id="addUnitModal" tabindex="-1">
  <div class="modal-dialog">
    <form action="{{ route('products.units.store') }}" method="POST" class="modal-content">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Add Unit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Symbol</label>
            <input type="text" name="symbol" class="form-control">
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" id="add_is_active" checked>
            <label class="form-check-label" for="add_is_active">Active</label>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
        <button class="btn btn-primary" type="submit">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Unit Modal -->
<div class="modal fade" id="editUnitModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="editUnitForm" method="POST" class="modal-content">
      @csrf
      @method('PUT')
      <div class="modal-header">
        <h5 class="modal-title">Edit Unit</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input id="edit_name" type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Symbol</label>
            <input id="edit_symbol" type="text" name="symbol" class="form-control">
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active">
            <label class="form-check-label" for="edit_is_active">Active</label>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
        <button class="btn btn-primary" type="submit">Update</button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var editModal = document.getElementById('editUnitModal');
    editModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        document.getElementById('editUnitForm').action = button.getAttribute('data-action');
        document.getElementById('edit_name').value = button.getAttribute('data-name');
        document.getElementById('edit_symbol').value = button.getAttribute('data-symbol');
        document.getElementById('edit_is_active').checked = (button.getAttribute('data-is_active') === '1');
    });
});
</script>
@endpush
@endsection
