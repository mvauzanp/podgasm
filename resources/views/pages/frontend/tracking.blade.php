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
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="{{ route('order.history') }}" class="btn btn-outline-secondary rounded-pill px-4">Kembali ke Riwayat</a>
            </div>

        </div>
    </div>
</div>
@endsection
