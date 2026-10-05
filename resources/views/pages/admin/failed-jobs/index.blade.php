@extends('layouts.admin')

@section('content_admin')
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark"><i class="fas fa-microchip text-danger me-2"></i> Monitoring Background Queue &amp; Failed Jobs</h4>
            <p class="text-muted small mb-0">Pantau dan kelola antrean background job (Webhook Olsera, kalkulasi Safety Stock, dan pesanan) yang mengalami kegagalan.</p>
        </div>
        <div class="d-flex gap-2">
            @if($totalFailed > 0)
                <form action="{{ route('admin.failed-jobs.retry-all') }}" method="POST" onsubmit="return confirm('Ulangi (Retry) seluruh job yang gagal sekarang?');">
                    @csrf
                    <button type="submit" class="btn btn-warning rounded-pill px-3 py-2 shadow-sm fw-semibold small">
                        <i class="fas fa-rotate me-1"></i> Retry Semua Job
                    </button>
                </form>
                <form action="{{ route('admin.failed-jobs.flush') }}" method="POST" onsubmit="return confirm('Yakin ingin membersihkan seluruh log failed jobs? Data tidak dapat dikembalikan.');">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger rounded-pill px-3 py-2 shadow-sm fw-semibold small">
                        <i class="fas fa-trash-can me-1"></i> Bersihkan Log
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Stats Banner --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 {{ $totalFailed > 0 ? 'bg-danger-subtle border-start border-danger border-4' : 'bg-success-subtle border-start border-success border-4' }}">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-uppercase fw-bold text-muted small mb-1" style="font-size: 0.75rem;">Status Antrean Worker</div>
                        <h5 class="fw-bold mb-1 {{ $totalFailed > 0 ? 'text-danger' : 'text-success' }}">
                            {{ $totalFailed > 0 ? $totalFailed . ' Job Gagal Perlu Perhatian' : 'Semua Antrean Berjalan Normal' }}
                        </h5>
                        <small class="text-muted d-block mt-1">
                            {{ $totalFailed > 0 ? 'Beberapa webhook atau cron job mengalami exception jaringan/data.' : 'Tidak ada failed job yang tertahan di antrean.' }}
                        </small>
                    </div>
                    <div class="rounded-circle p-3 {{ $totalFailed > 0 ? 'bg-danger text-white' : 'bg-success text-white' }} d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="fas {{ $totalFailed > 0 ? 'fa-triangle-exclamation' : 'fa-circle-check' }} fa-lg"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                <h6 class="fw-bold mb-2 text-dark"><i class="fas fa-info-circle text-primary me-2"></i> Panduan Queue Worker</h6>
                <p class="text-muted small mb-0">
                    Sistem menggunakan antrean <strong>Database Queue</strong>. Saat Olsera POS menembakkan webhook pembaruan stok atau sistem menghitung Safety Stock, task diproses di background. Jika terjadi kegagalan jaringan atau timeout, worker otomatis melakukan retry 3x dengan backoff berulang. Jika tetap gagal, job akan tercatat di tabel bawah ini untuk dapat diulang manual dengan tombol <strong>Retry</strong>.
                </p>
            </div>
        </div>
    </div>

    {{-- Failed Jobs Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-list-check text-danger me-2"></i> Daftar Antrean Gagal (Failed Jobs)</h6>
            <span class="badge bg-secondary rounded-pill px-3 py-1.5">{{ $totalFailed }} Total Record</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase small" style="font-size: 0.78rem;">
                        <tr>
                            <th class="ps-4">ID / UUID</th>
                            <th>Nama Job Class</th>
                            <th>Queue</th>
                            <th>Pesan Kesalahan (Exception)</th>
                            <th>Waktu Gagal</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($failedJobs as $job)
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.75rem;">
                                        {{ Str::limit($job->uuid, 12) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark small">{{ $job->job_name }}</div>
                                    <small class="text-muted d-block text-truncate" style="max-width: 200px;" title="{{ $job->full_job_name }}">
                                        {{ $job->full_job_name }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small">
                                        {{ $job->queue }}
                                    </span>
                                </td>
                                <td>
                                    <div class="text-danger small fw-semibold text-truncate" style="max-width: 320px;" title="{{ $job->exception }}">
                                        {{ $job->short_exception }}
                                    </div>
                                </td>
                                <td>
                                    <span class="small text-muted">
                                        <i class="far fa-clock me-1"></i> {{ date('d M Y, H:i', strtotime($job->failed_at)) }}
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex gap-1">
                                        <form action="{{ route('admin.failed-jobs.retry', $job->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3 shadow-none fw-semibold" title="Proses Ulang Job Ini">
                                                <i class="fas fa-rotate me-1"></i> Retry
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.failed-jobs.destroy', $job->id) }}" method="POST" onsubmit="return confirm('Hapus log failed job ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-0 d-inline-flex align-items-center justify-content-center shadow-none" style="width: 31px; height: 31px;" title="Hapus Riwayat">
                                                <i class="fas fa-trash-can" style="font-size: 0.8rem;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-circle-check fa-3x text-success mb-3 opacity-75"></i>
                                        <h6 class="fw-bold text-dark mb-1">Semua Background Jobs Berjalan Sempurna!</h6>
                                        <p class="small text-muted mb-0">Tidak ada antrean gagal yang ditemukan. Webhook dan cron worker beroperasi normal.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($failedJobs->hasPages())
            <div class="card-footer bg-white border-top py-3 px-4">
                {{ $failedJobs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
