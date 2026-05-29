@extends('layouts.app')

@section('title', 'Pembayaran - Admin')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'admin', 'active' => 'pembayaran'])
@endsection

@section('header')
    <div>
        <h2>Kelola Pembayaran</h2>
        <p class="text-muted mb-0">Tinjau dan konfirmasi pembayaran customer</p>
    </div>
@endsection

@section('content')
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-info">
                <h6>Pendapatan Hari Ini</h6>
                <h3>Rp {{ number_format($todayRevenue ?? 0) }}</h3>
            </div>
            <div class="stat-icon icon-success">
                <i class="fas fa-coins"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h6>Pembayaran Pending</h6>
                <h3>{{ $pendingPayments ?? 0 }}</h3>
            </div>
            <div class="stat-icon icon-warning">
                <i class="fas fa-clock"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h6>Pembayaran Berhasil</h6>
                <h3>{{ $successfulPayments ?? 0 }}</h3>
            </div>
            <div class="stat-icon icon-primary">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>

    <div class="dashboard-card">
        <div class="card-header-custom header-wrap">
            <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>Daftar Pembayaran</h5>
            <form action="{{ route('admin.pembayaran.destroyAll') }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus SEMUA pembayaran? Tindakan ini tidak dapat dibatalkan.');">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-trash-alt me-1"></i> Hapus Semua
                </button>
            </form>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table-custom table-card-on-mobile">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Metode</th>
                            <th>Bukti</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments ?? [] as $payment)
                        <tr>
                            <td data-label="ID"><strong>#{{ $payment->id }}</strong></td>
                            <td data-label="Customer">{{ $payment->customer->name ?? '-' }}</td>
                            <td data-label="Total"><strong>Rp {{ number_format($payment->total) }}</strong></td>
                            <td data-label="Metode">{{ ucfirst($payment->method) }}</td>
                            <td data-label="Bukti">
                                @if($payment->proof_image)
                                    <a href="{{ route('admin.pembayaran.proof', $payment->id) }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-image"></i> Lihat
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td data-label="Status">
                                <span class="status-badge status-{{ $payment->status }}">{{ ucfirst($payment->status) }}</span>
                                @if($payment->status === 'rejected' && $payment->rejection_reason)
                                    <br><small class="text-danger">{{ $payment->rejection_reason }}</small>
                                @endif
                            </td>
                            <td data-label="Tanggal">{{ \Carbon\Carbon::parse($payment->created_at)->format('d M Y H:i') }}</td>
                            <td data-label="Aksi">
                                <div class="d-flex flex-wrap gap-1">
                                    @if($payment->reservation)
                                        <button class="btn btn-sm btn-outline-primary" onclick="showInvoice({{ $payment->id }})">
                                            <i class="fas fa-file-invoice"></i> Invoice
                                        </button>
                                    @endif
                                    @if($payment->status === 'pending')
                                        <button class="btn btn-sm btn-success" onclick="approvePayment({{ $payment->id }})">Approve</button>
                                        <button class="btn btn-sm btn-danger" onclick="rejectPayment({{ $payment->id }})">Tolak</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Belum ada pembayaran</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="pagination-wrapper mt-4">
        {{ $payments->links() ?? '' }}
    </div>

    <!-- Invoice Modal -->
    <div class="modal fade" id="invoiceModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Invoice Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 p-md-4" id="invoiceModalBody">
                    @foreach($payments as $payment)
                        @if($payment->reservation)
                            <div id="invoice-content-{{ $payment->id }}" class="invoice-content" style="display:none;">
                                <div class="invoice-container-admin">
                                    <div class="invoice-header-admin">
                                        <div class="invoice-logo-admin">
                                            <h3>🕹️ PS Rent Station</h3>
                                            <p>Invoice Pembayaran Reservasi</p>
                                        </div>
                                        <div class="invoice-info-admin">
                                            <p><strong>Invoice #{{ $payment->reservation->id }}</strong></p>
                                            <p>Tanggal: {{ \Carbon\Carbon::parse($payment->reservation->date)->format('d M Y') }}</p>
                                            <p>Waktu: {{ $payment->reservation->start_time }}</p>
                                        </div>
                                    </div>
                                    <div class="invoice-details-admin">
                                        <div class="customer-info-admin">
                                            <h5>Informasi Customer</h5>
                                            <p><strong>Nama:</strong> {{ $payment->customer->name ?? '-' }}</p>
                                            <p><strong>Email:</strong> {{ $payment->customer->email ?? '-' }}</p>
                                        </div>
                                        <div class="reservation-info-admin">
                                            <h5>Detail Reservasi</h5>
                                            <table class="invoice-table-admin">
                                                <tr>
                                                    <td>Console:</td>
                                                    <td>{{ $payment->reservation->console_type }}</td>
                                                </tr>
                                                <tr>
                                                    <td>Durasi:</td>
                                                    <td>{{ $payment->reservation->duration }} jam</td>
                                                </tr>
                                                @php
                                                    $r = $payment->reservation;
                                                    $pricePerHour = $r->pricePerHour();
                                                    $totalExtensionDuration = $r->approvedBillingExtensionMinutes();
                                                    $totalExtensionPrice = $r->approvedBillingExtensionPrice();
                                                    $reservationTotal = $r->reservationSubtotalWithExtensions();
                                                    $foodOrdersApproved = $r->foodOrders->whereIn('status', ['approved', 'delivered']);
                                                    $foodTotal = $r->approvedFoodOrdersTotal();
                                                @endphp
                                                <tr>
                                                    <td>Harga per Jam:</td>
                                                    <td>Rp {{ number_format($pricePerHour) }}</td>
                                                </tr>
                                                @if($totalExtensionDuration > 0)
                                                <tr>
                                                    <td>Tambahan Waktu:</td>
                                                    <td>{{ $totalExtensionDuration }} menit</td>
                                                </tr>
                                                <tr>
                                                    <td>Harga Tambahan:</td>
                                                    <td>Rp {{ number_format($totalExtensionPrice) }}</td>
                                                </tr>
                                                @endif
                                                <tr class="total-row-admin">
                                                    <td><strong>Total Reservasi:</strong></td>
                                                    <td><strong>Rp {{ number_format($reservationTotal) }}</strong></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    @if($foodOrdersApproved->count() > 0)
                                    <div class="food-orders-section-admin" style="margin: 20px 0; padding: 15px; border: 1px solid #eee; border-radius: 8px;">
                                        <h5 style="color: #111; margin-bottom: 12px;">Pesanan Makanan & Minuman</h5>
                                        <table class="invoice-table-admin">
                                            @foreach($foodOrdersApproved as $foodOrder)
                                                @foreach($foodOrder->items as $item)
                                                <tr>
                                                    <td>{{ $item['name'] ?? 'Item' }} x{{ $item['qty'] ?? 1 }}</td>
                                                    <td style="text-align: right;">Rp {{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 1)) }}</td>
                                                </tr>
                                                @endforeach
                                            @endforeach
                                            <tr class="total-row-admin">
                                                <td><strong>Subtotal Makanan:</strong></td>
                                                <td style="text-align: right;"><strong>Rp {{ number_format($foodTotal) }}</strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                    @endif
                                    <div class="invoice-total-admin" style="margin: 12px 0; padding: 12px; background: #f8f9fa; border-radius: 8px;">
                                        <table class="invoice-table-admin">
                                            <tr class="total-row-admin">
                                                <td><strong>Total Tagihan:</strong></td>
                                                <td style="text-align: right;"><strong style="font-size: 1.1rem;">Rp {{ number_format($payment->total) }}</strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="invoice-footer-admin">
                                        <p>Terima kasih telah menggunakan layanan PS Rent Station!</p>
                                        <p>Status Pembayaran: <strong>{{ ucfirst($payment->status) }}</strong></p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="rejectForm">
                    @csrf
                    <div class="modal-body p-3">
                        <div class="mb-3">
                            <label class="form-label">Alasan Penolakan</label>
                            <textarea class="form-control" name="reason" rows="3" required placeholder="Jelaskan alasan pembayaran ditolak..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger">Tolak Pembayaran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function showInvoice(paymentId) {
        document.querySelectorAll('.invoice-content').forEach(el => el.style.display = 'none');
        const content = document.getElementById('invoice-content-' + paymentId);
        if (content) {
            content.style.display = 'block';
            new bootstrap.Modal(document.getElementById('invoiceModal')).show();
        }
    }

    function approvePayment(paymentId) {
        if (confirm('Apakah Anda yakin ingin menyetujui pembayaran ini?')) {
            const formData = new FormData();
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
            
            fetch(`/admin/pembayaran/${paymentId}/confirm`, {
                method: 'POST',
                body: formData,
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Terjadi kesalahan');
                }
            });
        }
    }

    function rejectPayment(paymentId) {
        document.getElementById('rejectForm').action = `/admin/pembayaran/${paymentId}/cancel`;
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }

    document.getElementById('rejectForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Terjadi kesalahan');
            }
        });
    });
</script>

<style>
.invoice-container-admin {
    max-width: 100%;
    margin: 0 auto;
    padding: 16px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #fff;
}
@media (max-width: 576px) {
    .invoice-container-admin {
        padding: 6px;
    }
    .invoice-table-admin td {
        font-size: 0.75rem;
        padding: 3px 0;
    }
    .invoice-header-admin {
        margin-bottom: 8px;
        padding-bottom: 6px;
        gap: 4px;
    }
    .invoice-logo-admin h3 {
        font-size: 0.85rem;
    }
    .invoice-logo-admin p {
        font-size: 0.7rem;
        margin-bottom: 0;
    }
    .invoice-info-admin p {
        font-size: 0.7rem;
        margin-bottom: 2px;
    }
    .invoice-details-admin {
        margin-bottom: 8px;
    }
    .customer-info-admin, .reservation-info-admin {
        margin-bottom: 6px;
    }
    .customer-info-admin h5, .reservation-info-admin h5 {
        font-size: 0.8rem;
        margin-bottom: 4px;
    }
    .customer-info-admin p, .reservation-info-admin p {
        font-size: 0.7rem;
        margin-bottom: 2px;
    }
    .food-orders-section-admin {
        margin: 6px 0 !important;
        padding: 6px !important;
    }
    .food-orders-section-admin h5 {
        font-size: 0.8rem;
        margin-bottom: 4px !important;
    }
    .food-orders-section-admin td {
        font-size: 0.75rem;
    }
    .invoice-total-admin {
        margin: 6px 0 !important;
        padding: 6px !important;
    }
    .invoice-total-admin td {
        font-size: 0.75rem;
    }
    .invoice-total-admin td strong[style*="font-size"] {
        font-size: 0.85rem !important;
    }
    .invoice-footer-admin {
        padding-top: 8px;
    }
    .invoice-footer-admin p {
        font-size: 0.7rem;
        margin-bottom: 2px;
    }
    #invoiceModal .modal-body {
        padding: 8px !important;
    }
    #invoiceModal .modal-footer {
        padding: 8px 12px;
    }
    #invoiceModal .modal-footer .btn {
        font-size: 0.85rem;
        padding: 8px 16px;
    }
}
.invoice-header-admin {
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    border-bottom: 2px solid #007bff;
    padding-bottom: 16px;
    flex-wrap: wrap;
    gap: 12px;
}
.invoice-logo-admin h3 {
    margin: 0;
    color: #0056b3;
    font-size: 1.15rem;
}
.invoice-info-admin {
    text-align: right;
    color: #111;
}
@media (max-width: 576px) {
    .invoice-header-admin {
        flex-direction: column;
    }
    .invoice-info-admin {
        text-align: left;
    }
}
.invoice-details-admin {
    margin-bottom: 30px;
}
.customer-info-admin, .reservation-info-admin {
    margin-bottom: 20px;
}
.customer-info-admin h5, .reservation-info-admin h5 {
    margin-bottom: 10px;
    color: #111;
}
.invoice-container-admin,
.invoice-container-admin p,
.invoice-container-admin td,
.invoice-container-admin h3,
.invoice-container-admin h5 {
    color: #111;
}
.invoice-table-admin {
    width: 100%;
    border-collapse: collapse;
}
.invoice-table-admin td {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}
.total-row-admin {
    background: #f8f9fa;
    font-weight: bold;
}
.invoice-footer-admin {
    text-align: center;
    color: #666;
    border-top: 1px solid #ddd;
    padding-top: 20px;
}
</style>
@endpush
