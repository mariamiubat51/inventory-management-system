@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="mb-4">Product Categories</h2>

    {{-- Display Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger mt-3">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Search Form --}}
    <form method="GET" action="{{ route('products.categories.index') }}" class="mb-3">
        <div class="row">

            <!-- Status -->
            <div class="col-md-3">
                <label for="status">Status</label>
                <select name="status" class="form-control">
                    <option value="">-- Select Status --</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <!-- From Date -->
            <div class="col-md-3">
                <label for="from_date">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>

            <!-- To Date -->
            <div class="col-md-3">
                <label for="to_date">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>

            <!-- Buttons -->
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-info m-1">Search</button>
                <a href="{{ route('products.categories.index') }}" class="btn btn-secondary m-1">Reset</a>
            </div>

        </div>
    </form>

    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal">➕ Add Category</button>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered table-hover align-middle table-striped text-center">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($categories as $idx => $category)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>{{ $category->name }}</td>
                <td>
                    @if($category->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </td>
                <td>
                    <button class="btn btn-sm btn-warning me-2"
                            data-bs-toggle="modal"
                            data-bs-target="#editCategoryModal"
                            data-action="{{ route('products.categories.update', $category) }}"
                            data-name="{{ $category->name }}"
                            data-is_active="{{ $category->is_active ? 1 : 0 }}">Edit</button>

                    <form action="{{ route('products.categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this category?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center">No categories found.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div class="mt-3">
    {{ $categories->links() }}
  </div>

</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <form action="{{ route('products.categories.store') }}" method="POST" class="modal-content">
      @csrf
      <div class="modal-header">
        <h5 class="modal-title">Add Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" id="add_is_active_cat" checked>
            <label class="form-check-label" for="add_is_active_cat">Active</label>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Cancel</button>
        <button class="btn btn-primary" type="submit">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
  <div class="modal-dialog">
    <form id="editCategoryForm" method="POST" class="modal-content">
      @csrf
      @method('PUT')
      <div class="modal-header">
        <h5 class="modal-title">Edit Category</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Name <span class="text-danger">*</span></label>
            <input id="edit_name_cat" type="text" name="name" class="form-control" required>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" id="edit_is_active_cat">
            <label class="form-check-label" for="edit_is_active_cat">Active</label>
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
    var editModal = document.getElementById('editCategoryModal');
    editModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        document.getElementById('editCategoryForm').action = button.getAttribute('data-action');
        document.getElementById('edit_name_cat').value = button.getAttribute('data-name');
        document.getElementById('edit_is_active_cat').checked = (button.getAttribute('data-is_active') === '1');
    });
});
</script>
@endpush
@endsection
