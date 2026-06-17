<div class="invoice-container">
    <div class="invoice-header">
        <div class="invoice-logo">
            <h3>🕹️ PS Rent Station</h3>
            <p>Invoice Pembayaran Reservasi</p>
        </div>
        <div class="invoice-info">
            <p><strong>Invoice {{ $reservation->id }}</strong></p>
            <p>Tanggal: {{ \Carbon\Carbon::parse($reservation->date)->format('d M Y') }}</p>
            <p>Waktu: {{ $reservation->start_time }}</p>
        </div>
    </div>

    @php
        $pricePerHour = $reservation->pricePerHour();
        $totalExtensionDuration = $reservation->approvedBillingExtensionMinutes();
        $totalExtensionPrice = $reservation->approvedBillingExtensionPrice();
        $reservationTotal = $reservation->reservationSubtotalWithExtensions();
        $foodTotal = $reservation->approvedFoodOrdersTotal();
        $grandTotal = $reservation->grandInvoiceTotal();
    @endphp

    <div class="invoice-details">
        <div class="customer-info">
            <h5>Informasi Customer</h5>
            <p><strong>Nama:</strong> {{ Auth::user()->name }}</p>
            <p><strong>Email:</strong> {{ Auth::user()->email }}</p>
        </div>

        <div class="reservation-info">
            <h5>Detail Reservasi</h5>
            <table class="invoice-table">
                <tr>
                    <td>Console:</td>
                    <td>{{ $reservation->console_type }}</td>
                </tr>
                <tr>
                    <td>Durasi:</td>
                    <td>{{ $reservation->duration }} jam</td>
                </tr>
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
                <tr class="total-row">
                    <td><strong>Total Reservasi:</strong></td>
                    <td><strong>Rp {{ number_format($reservationTotal) }}</strong></td>
                </tr>
            </table>
        </div>
    </div>

    @if($foodOrders->count() > 0)
    <div class="food-orders-section" style="margin: 20px 0; padding: 15px; border: 1px solid #eee; border-radius: 8px;">
        <h5 style="color: #111; margin-bottom: 12px;">Pesanan Makanan & Minuman</h5>
        <table class="invoice-table">
            @foreach($foodOrders as $foodOrder)
                @foreach($foodOrder->items as $item)
                <tr>
                    <td>{{ $item['name'] ?? 'Item' }} x{{ $item['qty'] ?? 1 }}</td>
                    <td style="text-align: right;">Rp {{ number_format(($item['price'] ?? 0) * ($item['qty'] ?? 1)) }}</td>
                </tr>
                @endforeach
            @endforeach
            <tr class="total-row">
                <td><strong>Subtotal Makanan:</strong></td>
                <td style="text-align: right;"><strong>Rp {{ number_format($foodTotal) }}</strong></td>
            </tr>
        </table>
    </div>
    @endif

    <div class="invoice-total" style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px;">
        <table class="invoice-table">
            <tr class="total-row">
                <td><strong>Total Keseluruhan:</strong></td>
                <td style="text-align: right;"><strong style="font-size: 1.2rem;">Rp {{ number_format($grandTotal) }}</strong></td>
            </tr>
        </table>
    </div>

    <div class="invoice-footer">
        <p>Terima kasih telah menggunakan layanan PS Rent Station!</p>
        <p>Silakan pilih metode pembayaran di bawah ini.</p>
    </div>
</div>

<style>
.invoice-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    background: #fff;
}

.invoice-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 30px;
    border-bottom: 2px solid #007bff;
    padding-bottom: 20px;
}

.invoice-logo h3 {
    margin: 0;
    color: #0056b3;
}

.invoice-info {
    text-align: right;
    color: #111;
}

.invoice-details {
    margin-bottom: 30px;
}

.customer-info, .reservation-info {
    margin-bottom: 20px;
}

.customer-info h5, .reservation-info h5 {
    margin-bottom: 10px;
    color: #111;
}

.invoice-container,
.invoice-container p,
.invoice-container td,
.invoice-container h3,
.invoice-container h5 {
    color: #111;
}

.invoice-table {
    width: 100%;
    border-collapse: collapse;
}

.invoice-table td {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.total-row {
    background: #f8f9fa;
    font-weight: bold;
}

.invoice-footer {
    text-align: center;
    color: #666;
    border-top: 1px solid #ddd;
    padding-top: 20px;
}

@media (max-width: 576px) {
    .invoice-container {
        padding: 8px;
    }
    .invoice-header {
        flex-direction: column;
        gap: 4px;
        margin-bottom: 10px;
        padding-bottom: 8px;
    }
    .invoice-info {
        text-align: left;
    }
    .invoice-logo h3 {
        font-size: 0.9rem;
    }
    .invoice-logo p {
        font-size: 0.75rem;
        margin-bottom: 0;
    }
    .invoice-info p {
        font-size: 0.75rem;
        margin-bottom: 2px;
    }
    .invoice-details {
        margin-bottom: 10px;
    }
    .customer-info, .reservation-info {
        margin-bottom: 8px;
    }
    .customer-info h5, .reservation-info h5 {
        font-size: 0.8rem;
        margin-bottom: 6px;
    }
    .customer-info p, .reservation-info p {
        font-size: 0.75rem;
        margin-bottom: 2px;
    }
    .invoice-table td {
        padding: 4px 0;
        font-size: 0.75rem;
    }
    .food-orders-section {
        margin: 8px 0 !important;
        padding: 8px !important;
    }
    .food-orders-section h5 {
        font-size: 0.8rem;
        margin-bottom: 6px !important;
    }
    .food-orders-section td {
        font-size: 0.75rem;
    }
    .invoice-total {
        margin: 8px 0 !important;
        padding: 8px !important;
    }
    .invoice-total td {
        font-size: 0.75rem;
    }
    .invoice-total td strong[style*="font-size"] {
        font-size: 0.9rem !important;
    }
    .invoice-footer {
        padding-top: 8px;
    }
    .invoice-footer p {
        font-size: 0.7rem;
        margin-bottom: 2px;
    }
}
</style>