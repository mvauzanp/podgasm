<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class FailedJobController extends Controller
{
    /**
     * Tampilkan daftar antrean background job yang gagal
     */
    public function index()
    {
        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->paginate(15);

        // Transform payload untuk memudahkan visualisasi di Blade
        $failedJobs->getCollection()->transform(function ($job) {
            $payload = json_decode($job->payload, true) ?? [];
            $displayName = $payload['displayName'] ?? ($payload['data']['commandName'] ?? 'Unknown Job');
            
            // Potong string exception agar ringkas
            $exceptionLines = explode("\n", $job->exception);
            $shortException = $exceptionLines[0] ?? 'Unknown Error';
            if (isset($exceptionLines[1])) {
                $shortException .= ' ' . $exceptionLines[1];
            }

            $job->job_name = class_basename($displayName);
            $job->full_job_name = $displayName;
            $job->short_exception = $shortException;
            return $job;
        });

        $totalFailed = DB::table('failed_jobs')->count();

        return view('pages.admin.failed-jobs.index', compact('failedJobs', 'totalFailed'));
    }

    /**
     * Ulangi (Retry) satu job yang gagal berdasarkan UUID / ID
     */
    public function retry(string $id)
    {
        try {
            Artisan::call('queue:retry', ['id' => [$id]]);
            return redirect()->back()->with('success', "Job [{$id}] berhasil dimasukkan kembali ke dalam antrean untuk diproses ulang.");
        } catch (\Exception $e) {
            Log::error('Failed Job Retry Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['msg' => 'Gagal mengulang job: ' . $e->getMessage()]);
        }
    }

    /**
     * Ulangi SEMUA (Retry All) job yang gagal
     */
    public function retryAll()
    {
        try {
            Artisan::call('queue:retry', ['id' => ['all']]);
            return redirect()->back()->with('success', 'Seluruh job yang gagal berhasil dimasukkan kembali ke dalam antrean!');
        } catch (\Exception $e) {
            Log::error('Failed Job Retry All Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['msg' => 'Gagal mengulang semua job: ' . $e->getMessage()]);
        }
    }

    /**
     * Hapus (Forget) satu riwayat failed job
     */
    public function destroy(string $id)
    {
        try {
            Artisan::call('queue:forget', ['id' => $id]);
            return redirect()->back()->with('success', "Riwayat Failed Job [{$id}] berhasil dihapus.");
        } catch (\Exception $e) {
            Log::error('Failed Job Forget Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['msg' => 'Gagal menghapus job: ' . $e->getMessage()]);
        }
    }

    /**
     * Bersihkan (Flush) semua log failed jobs
     */
    public function flush()
    {
        try {
            Artisan::call('queue:flush');
            return redirect()->back()->with('success', 'Seluruh log antrean gagal berhasil dibersihkan.');
        } catch (\Exception $e) {
            Log::error('Failed Job Flush Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['msg' => 'Gagal membersihkan failed jobs: ' . $e->getMessage()]);
        }
    }
}
