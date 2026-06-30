@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h4 class="fw-bold mb-4">Lacak Pesanan</h4>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-4 pb-3 border-bottom">
                        <div>
                            <span class="text-muted d-block small">No. Invoice</span>
                            <span class="fw-bold text-primary">{{ $order->invoice_number }}</span>
                        </div>
                        <div class="text-end">
                            <span class="text-muted d-block small">Total Pembayaran</span>
                            <span class="fw-bold">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Timeline Tracking -->
                    <div class="tracking-timeline position-relative">
                        <style>
                            .tracking-item {
                                position: relative;
                                padding-left: 40px;
                                margin-bottom: 30px;
                            }
                            .tracking-item::before {
                                content: '';
                                position: absolute;
                                left: 11px;
                                top: 0;
                                bottom: -30px;
                                width: 2px;
                                background-color: #e9ecef;
                            }
                            .tracking-item:last-child::before {
                                display: none;
                            }
                            .tracking-icon {
                                position: absolute;
                                left: 0;
                                top: 0;
                                width: 24px;
                                height: 24px;
                                border-radius: 50%;
                                background-color: #e9ecef;
                                border: 4px solid #fff;
                                box-shadow: 0 0 0 1px #dee2e6;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            }
                            .tracking-icon.active {
                                background-color: #198754;
                                box-shadow: 0 0 0 1px #198754;
                            }
                            .tracking-icon.active i {
                                color: #fff;
                                font-size: 10px;
                            }
                        </style>

                        @php
                            if ($order->metode_pembayaran === 'branch_request') {
                                $statuses = [
                                    'pending_payment' => 'Menunggu Persetujuan Admin',
                                    'paid' => 'Disetujui & Diproses',
                                    'shipped' => 'Sedang Dikirim',
                                    'completed' => 'Diterima di Cabang'
                                ];
                            } else {
                                $statuses = [
                                    'pending_payment' => 'Menunggu Pembayaran',
                                    'paid' => 'Pembayaran Diterima',
                                    'processing' => 'Pesanan Diproses',
                                    'shipped' => 'Sedang Dikirim',
                                    'completed' => 'Pesanan Selesai'
                                ];
                            }

                            // Menentukan status saat ini dalam angka
                            $statusKeys = array_keys($statuses);
                            $currentIndex = array_search($order->status, $statusKeys);
                            
                            // Jika cancelled, tampilkan alert
                            $isCancelled = ($order->status == 'cancelled');
                        @endphp

                        @if($isCancelled)
                            <div class="alert alert-danger text-center">
                                Pesanan ini telah dibatalkan.
                            </div>
                        @else
                            @foreach($statuses as $key => $label)
                                @php
                                    $index = array_search($key, $statusKeys);
                                    $isPassed = $currentIndex !== false && $index <= $currentIndex;
                                @endphp
                                <div class="tracking-item">
                                    <div class="tracking-icon {{ $isPassed ? 'active' : '' }}">
                                        @if($isPassed)
                                            <i class="fas fa-check"></i>
                                        @endif
                                    </div>
                                    <div class="tracking-content">
                                        <h6 class="mb-0 fw-bold {{ $isPassed ? 'text-dark' : 'text-muted' }}">{{ $label }}</h6>
                                        @if($isPassed)
                                            @if($key == 'pending_payment')
                                                <small class="text-muted">{{ $order->created_at->format('d M Y, H:i') }}</small>
                                            @elseif($key == 'paid' && $order->updated_at > $order->created_at)
                                                <small class="text-muted">{{ $order->updated_at->format('d M Y, H:i') }}</small>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    @if($trackingData && isset($trackingData['history']) && count($trackingData['history']) > 0)
                        <div class="mt-4 pt-4 border-top">
                            <h5 class="fw-bold mb-3"><i class="fas fa-shipping-fast text-primary me-2"></i> Detail Pengiriman Real-Time</h5>
                            
                            <div class="mb-4 p-3 bg-light rounded-3 small border-0">
                                <div class="row g-2">
                                    <div class="col-sm-6">
                                        <span class="text-muted d-block">Layanan Kurir</span>
                                        <strong class="text-uppercase text-dark">{{ $trackingData['courier']['name'] }} (Resi: {{ $trackingData['waybill_id'] }})</strong>
                                    </div>
                                    @if(isset($trackingData['driver']) && !empty($trackingData['driver']['name']))
                                    <div class="col-sm-6 text-sm-end">
                                        <span class="text-muted d-block">Petugas Kurir</span>
                                        <strong class="text-dark">{{ $trackingData['driver']['name'] }} @if(isset($trackingData['driver']['phone'])) ({{ $trackingData['driver']['phone'] }}) @endif</strong>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="tracking-history position-relative ms-2">
                                <style>
                                    .history-item {
                                        position: relative;
                                        padding-left: 30px;
                                        margin-bottom: 25px;
                                    }
                                    .history-item::before {
                                        content: '';
                                        position: absolute;
                                        left: 5px;
                                        top: 10px;
                                        bottom: -25px;
                                        width: 1px;
                                        border-left: 2px dashed #dee2e6;
                                    }
                                    .history-item:last-child::before {
                                        display: none;
                                    }
                                    .history-marker {
                                        position: absolute;
                                        left: 0;
                                        top: 5px;
                                        width: 12px;
                                        height: 12px;
                                        border-radius: 50%;
                                        background-color: #dee2e6;
                                        border: 2px solid #fff;
                                        box-shadow: 0 0 0 1px #dee2e6;
                                    }
                                    .history-item.latest .history-marker {
                                        background-color: #198754;
                                        box-shadow: 0 0 0 4px rgba(25, 135, 84, 0.2);
                                    }
                                    .history-item.latest .history-content .note {
                                        color: #198754 !important;
                                        font-weight: bold;
                                    }
                                </style>
                                @foreach($trackingData['history'] as $index => $history)
                                    <div class="history-item {{ $index === 0 ? 'latest' : '' }}">
                                        <div class="history-marker"></div>
                                        <div class="history-content">
                                            <div class="note text-dark small mb-1">{{ $history['note'] }}</div>
                                            <span class="text-muted font-monospace" style="font-size: 0.75rem;">
                                                {{ \Carbon\Carbon::parse($history['time'])->translatedFormat('d M Y, H:i') }} WIB
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('order.history') }}" class="btn btn-outline-secondary rounded-pill px-4">Kembali ke Riwayat</a>
            </div>

        </div>
    </div>
</div>
@endsection
