@extends('layouts.app')

@section('title', 'Pembayaran - PS Rent Station')

@section('sidebar')
    @include('partials.sidebar', ['type' => 'customer', 'active' => 'pembayaran'])
@endsection

@section('header')
    <div>
        <h2>Pembayaran 💳</h2>
        <p class="text-muted mb-0">Lakukan pembayaran setelah bermain</p>
    </div>
@endsection

@section('content')
    <div class="stats-grid mb-4">
        <div class="stat-card">
            <div class="stat-info">
                <h6>Total Pembayaran Berhasil</h6>
                <h3>Rp {{ number_format($totalSpent ?? 0) }}</h3>
            </div>
            <div class="stat-icon icon-success">
                <i class="fas fa-check-circle"></i>
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
                <h6>Pesanan Makanan Pending</h6>
                <h3>{{ $pendingFoodOrders ?? 0 }}</h3>
            </div>
            <div class="stat-icon icon-purple">
                <i class="fas fa-utensils"></i>
            </div>
        </div>
    </div>

    <div class="dashboard-card mb-4">
        <div class="card-header-custom">
            <h5 class="mb-0"><i class="fas fa-gamepad me-2"></i>Reservasi yang Perlu Dibayar</h5>
        </div>
        <div class="card-body-custom p-0">
            <div class="table-responsive">
                <table class="table-custom table-card-on-mobile">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Console</th>
                            <th>Tanggal</th>
                            <th>Durasi</th>
                            <th>Total Reservasi</th>
                            <th>Makanan</th>
                            <th>Total Keseluruhan</th>
                            <th>Status Pembayaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservations ?? [] as $reservation)
                        <tr>
                            <td data-label="ID"><strong>#{{ $reservation->id }}</strong></td>
                            <td data-label="Console">{{ $reservation->console_type }}</td>
                            <td data-label="Tanggal">{{ \Carbon\Carbon::parse($reservation->date)->format('d M Y') }}</td>
                            <td data-label="Durasi">{{ $reservation->duration }} jam</td>
                            <td data-label="Biaya Sewa">Rp {{ number_format($reservation->reservationSubtotalWithExtensions()) }}</td>
                            <td data-label="Makanan">
                                @php
                                    $reservationFoodTotal = $reservation->approvedFoodOrdersTotal();
                                @endphp
                                @if($reservationFoodTotal > 0)
                                    Rp {{ number_format($reservationFoodTotal) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td data-label="Total"><strong>Rp {{ number_format($reservation->payment ? $reservation->payment->total : $reservation->grandInvoiceTotal()) }}</strong></td>
                            <td data-label="Status">
                                @if($reservation->payment)
                                    <span class="status-badge status-{{ $reservation->payment->status }}">{{ ucfirst($reservation->payment->status) }}</span>
                                @else
                                    <span class="badge bg-secondary">Belum Bayar</span>
                                @endif
                            </td>
                            <td data-label="Aksi">
                                <div class="d-flex flex-wrap gap-1 align-items-start">
                                    @if(!$reservation->payment || $reservation->payment->status === 'cancelled')
                                    <button class="btn btn-sm btn-primary" onclick="showInvoice({{ $reservation->id }})">
                                        <i class="fas fa-credit-card"></i> Bayar
                                    </button>
                                    @elseif($reservation->payment->status === 'pending')
                                    <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Menunggu</span>
                                    @elseif($reservation->payment->status === 'rejected')
                                    <div>
                                        <span class="badge bg-danger"><i class="fas fa-times me-1"></i> Ditolak</span>
                                        @if($reservation->payment->rejection_reason)
                                        <br><small class="text-danger">{{ $reservation->payment->rejection_reason }}</small>
                                        @endif
                                    </div>
                                    @else
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i> Lunas</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">Tidak ada reservasi yang perlu dibayar</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="pagination-wrapper mt-4">
        {{ $reservations->links() ?? '' }}
    </div>

    <!-- Invoice Modal -->
    <div class="modal fade" id="invoiceModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i>Invoice Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3 p-md-4">
                    <div id="invoiceContent">
                        <!-- Invoice content will be loaded here -->
                    </div>
                    <hr class="my-3">
                    <form action="{{ route('customer.pembayaran.store') }}" method="POST" enctype="multipart/form-data" id="paymentForm">
                        @csrf
                        <input type="hidden" name="reservation_id" id="reservationIdInput">
                        <div class="mb-3">
                            <label class="form-label">Metode Pembayaran</label>
                            <select name="method" class="form-select" id="paymentMethod" required onchange="toggleProofUpload()">
                                <option value="">Pilih metode</option>
                                <option value="cash">Cash</option>
                                <option value="transfer">Transfer Bank</option>
                                <option value="qris">QRIS</option>
                            </select>
                        </div>

                        <div id="bankInfoDiv" style="display: none;" class="mb-3">
                            @if($paymentSettings && $paymentSettings->bank_name)
                            <div class="p-3 bg-light rounded border">
                                <h6 class="mb-2"><i class="fas fa-university me-2"></i>Informasi Transfer</h6>
                                <table class="table table-borderless table-sm mb-0">
                                    <tr><td class="ps-0">Bank</td><td class="pe-0 text-end"><strong>{{ $paymentSettings->bank_name }}</strong></td></tr>
                                    <tr><td class="ps-0">No. Rekening</td><td class="pe-0 text-end"><strong>{{ $paymentSettings->account_number }}</strong></td></tr>
                                    <tr><td class="ps-0">Atas Nama</td><td class="pe-0 text-end"><strong>{{ $paymentSettings->account_holder }}</strong></td></tr>
                                </table>
                            </div>
                            @else
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-1"></i> Informasi rekening bank belum diatur oleh admin.
                            </div>
                            @endif
                        </div>

                        <div id="qrisInfoDiv" style="display: none;" class="mb-3 text-center">
                            @if($paymentSettings && $paymentSettings->qris_image)
                                <h6 class="mb-2"><i class="fas fa-qrcode me-2"></i>Scan QRIS</h6>
                                <img src="{{ asset('storage/' . $paymentSettings->qris_image) }}" alt="QRIS" class="img-fluid rounded border" style="max-height: 220px;">
                            @else
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-1"></i> QRIS belum diatur oleh admin.
                            </div>
                            @endif
                        </div>

                        <div id="proofUploadDiv" style="display: none;" class="mb-3">
                            <label class="form-label">Bukti Pembayaran</label>
                            <input type="file" name="proof_image" class="form-control" accept="image/*">
                            <small class="text-muted">Upload bukti pembayaran untuk Transfer atau QRIS (JPG, PNG, max 2MB)</small>
                        </div>

                        <button type="submit" class="btn-submit w-100">
                            <i class="fas fa-paper-plane me-2"></i> Kirim Pembayaran
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
@media (max-width: 576px) {
    #invoiceModal .modal-body {
        padding: 12px !important;
    }
    #invoiceModal hr {
        margin-top: 8px !important;
        margin-bottom: 8px !important;
    }
    #invoiceModal .form-label {
        font-size: 0.8rem;
        margin-bottom: 4px;
    }
    #invoiceModal .form-select,
    #invoiceModal .form-control {
        font-size: 0.85rem !important;
        padding: 8px 10px !important;
        min-height: 38px;
    }
    #invoiceModal .mb-3 {
        margin-bottom: 8px !important;
    }
    #invoiceModal .btn-submit {
        font-size: 0.85rem;
        padding: 10px 16px;
    }
    #bankInfoDiv .p-3 {
        padding: 8px 10px !important;
    }
    #bankInfoDiv h6 {
        font-size: 0.8rem;
    }
    #bankInfoDiv td {
        font-size: 0.8rem;
        padding: 3px 0;
    }
    #qrisInfoDiv h6 {
        font-size: 0.8rem;
    }
    #qrisInfoDiv img {
        max-height: 140px !important;
    }
    #proofUploadDiv small {
        font-size: 0.7rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
    function showInvoice(reservationId) {
        // Load invoice content
        fetch(`/customer/reservasi/${reservationId}/invoice`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            },
            credentials: 'same-origin'
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.text();
            })
            .then(html => {
                document.getElementById('invoiceContent').innerHTML = html;
                document.getElementById('reservationIdInput').value = reservationId;
                new bootstrap.Modal(document.getElementById('invoiceModal')).show();
            })
            .catch(error => {
                console.error('Error loading invoice:', error);
                alert('Error loading invoice: ' + error.message);
            });
    }

    function toggleProofUpload() {
        const method = document.getElementById('paymentMethod').value;
        const proofDiv = document.getElementById('proofUploadDiv');
        const bankDiv = document.getElementById('bankInfoDiv');
        const qrisDiv = document.getElementById('qrisInfoDiv');

        if (method === 'transfer') {
            proofDiv.style.display = 'block';
            bankDiv.style.display = 'block';
            qrisDiv.style.display = 'none';
        } else if (method === 'qris') {
            proofDiv.style.display = 'block';
            bankDiv.style.display = 'none';
            qrisDiv.style.display = 'block';
        } else {
            proofDiv.style.display = 'none';
            bankDiv.style.display = 'none';
            qrisDiv.style.display = 'none';
        }
    }
</script>
@endpush
