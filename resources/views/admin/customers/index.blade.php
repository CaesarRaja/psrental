@extends('layouts.app')

@section('title', 'Manajemen Customer - Admin')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'admin', 'active' => 'customers'])
@endsection

@section('header')
    <div>
        <h2>Manajemen Customer</h2>
        <p class="text-muted mb-0">Kelola semua akun customer</p>
    </div>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="dashboard-card">
        <div class="card-header-custom">
            <h5><i class="fas fa-users me-2"></i>Daftar Customer</h5>
        </div>
        <div class="card-body-custom">
            <form action="{{ route('admin.customers') }}" method="GET" class="row g-2 mb-4">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Cari nama, email, atau no. HP..."
                               value="{{ $search ?? '' }}">
                        <button type="submit" class="btn btn-primary">Cari</button>
                        @if($search)
                            <a href="{{ route('admin.customers') }}" class="btn btn-outline-secondary">Reset</a>
                        @endif
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table-custom table-card-on-mobile">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>No. HP</th>
                            <th>Alamat</th>
                            <th>Bergabung</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $index => $customer)
                            <tr>
                                <td data-label="#">{{ $customers->firstItem() + $index }}</td>
                                <td data-label="Nama">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0"
                                             style="width: 36px; height: 36px; font-size: 0.75rem; font-weight: 600;">
                                            {{ strtoupper(substr($customer->name, 0, 2)) }}
                                        </div>
                                        <span class="fw-semibold">{{ $customer->name }}</span>
                                    </div>
                                </td>
                                <td data-label="Email">{{ $customer->email }}</td>
                                <td data-label="No. HP">{{ $customer->phone ?? '-' }}</td>
                                <td data-label="Alamat">
                                    <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $customer->address }}">
                                        {{ $customer->address ?? '-' }}
                                    </span>
                                </td>
                                <td data-label="Bergabung">{{ $customer->created_at->format('d M Y') }}</td>
                                <td data-label="Aksi">
                                    <a href="{{ route('admin.customers.edit', $customer->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox me-2"></i>Belum ada customer terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper mt-4">
                {{ $customers->links() }}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function confirmDelete(id, name) {
            if (confirm('Apakah kamu yakin ingin menghapus akun customer "' + name + '"? Semua data (reservasi, pesanan, keluhan, pembayaran) miliknya juga akan dihapus. Tindakan ini tidak dapat dibatalkan.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/customers/' + id;

                const csrf = document.createElement('input');
                csrf.type = 'hidden';
                csrf.name = '_token';
                csrf.value = '{{ csrf_token() }}';
                form.appendChild(csrf);

                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';
                form.appendChild(method);

                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
@endpush
