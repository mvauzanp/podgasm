@extends('layouts.frontend')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <h4 class="fw-bold mb-4">Riwayat Belanja</h4>

            @forelse($orders as $order)
            <div class="card border-0 shadow-sm rounded-4 mb-3 overflow-hidden">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <span class="text-muted small d-block">Tanggal</span>
                            <span class="fw-bold">{{ $order->created_at->format('d M Y') }}</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">No. Invoice</span>
                            <span class="text-primary fw-bold">{{ $order->invoice_number }}</span>
                        </div>
                        <div class="col-md-2">
                            <span class="text-muted small d-block">Total</span>
                            <span class="fw-bold">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted small d-block">Status</span>
                            @if($order->metode_pembayaran === 'branch_request' && $order->status === 'pending_payment')
                                <span class="badge bg-warning text-dark">Menunggu Persetujuan</span>
                            @elseif($order->status == 'pending' || $order->status == 'pending_payment')
                                <span class="badge bg-warning text-dark">Menunggu Pembayaran</span>
                            @elseif($order->status == 'completed')
                                <span class="badge bg-success">Selesai</span>
                            @else
                                <span class="badge bg-info">
                                    @if($order->status === 'paid')
                                        Disetujui / Diproses
                                    @else
                                        {{ ucfirst($order->status) }}
                                    @endif
                                </span>
                            @endif
                        </div>
                        <div class="col-md-2 text-end">
                            <a href="{{ route('order.track', $order->id) }}" class="btn btn-outline-success btn-sm rounded-pill px-3 mb-1">
                                Lacak
                            </a>
                            <br>
                            <a href="{{ route('order.show', $order->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5">
                <img src="{{ asset('images/empty-history.png') }}" width="150" class="mb-3 opacity-50">
                <h5 class="text-muted">Belum ada transaksi apapun</h5>
                <a href="{{ route('home') }}" class="btn btn-primary rounded-pill mt-3">Mulai Belanja</a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection