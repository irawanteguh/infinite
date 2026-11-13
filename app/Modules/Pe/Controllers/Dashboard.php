<?php

namespace Modules\Pe\Controllers;

use App\Controllers\BaseController;
use Modules\Pe\Models\DashboardModel;

class Dashboard extends BaseController
{
    public function index()
    {
        if (!session()->get('loggedin')) return redirect()->to(site_url('/'));
        $group = (string) session()->get('groupid');
        $org = (string) session()->get('orgid');
        $filters = ['start' => date('Y') . '-01-01', 'end' => date('Y') . '-12-31', 'service' => '', 'instrument' => ''];
        $instruments = [];
        $data = null;
        $error = '';
        try {
            if ($group === '' || $org === '') throw new \InvalidArgumentException('Sesi organisasi tidak tersedia. Silakan login kembali.');
            $model = new DashboardModel();
            $instruments = $model->instruments($group, $org);
            foreach ($filters as $key => $default) {
                $value = request()->getGet($key);
                if ($value !== null) {
                    if (!is_string($value)) throw new \InvalidArgumentException('Format filter tidak valid.');
                    $filters[$key] = trim($value);
                }
            }
            foreach (['start', 'end'] as $key) {
                $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $filters[$key]);
                if (!$date || $date->format('Y-m-d') !== $filters[$key]) throw new \InvalidArgumentException('Tanggal filter tidak valid.');
            }
            if ($filters['start'] > $filters['end']) throw new \InvalidArgumentException('Tanggal mulai harus sebelum atau sama dengan tanggal akhir.');
            if ((new \DateTimeImmutable($filters['start']))->diff(new \DateTimeImmutable($filters['end']))->days > 1096) {
                throw new \InvalidArgumentException('Pilih rentang periode maksimal tiga tahun.');
            }
            if (!in_array($filters['service'], ['', 'RJ', 'RI', 'IGD'], true)) throw new \InvalidArgumentException('Jenis pelayanan tidak valid.');
            if ($filters['instrument'] !== '' && !in_array($filters['instrument'], array_column($instruments, 'instrument_id'), true)) {
                throw new \InvalidArgumentException('Instrumen tidak tersedia pada organisasi ini.');
            }
            $data = $model->analytics($group, $org, $filters);
        } catch (\InvalidArgumentException $e) {
            $error = $e->getMessage();
            response()->setStatusCode(422);
        } catch (\Throwable $e) {
            log_message('error', 'PE dashboard: {message}', ['message' => $e->getMessage()]);
            $error = 'Analitik belum dapat dimuat. Silakan coba kembali atau hubungi administrator.';
            response()->setStatusCode(503);
        }
        return view('template/dashboard-light-aside', [
            'contents' => view('Modules\Pe\Views\v_dashboard', compact('filters', 'instruments', 'data', 'error')),
        ]);
    }
    public function annualreport()
    {
        if (!session()->get('loggedin')) return redirect()->to(site_url('/'));
        $group = (string) session()->get('groupid');
        $org = (string) session()->get('orgid');
        if ($group === '' || $org === '') return response()->setStatusCode(401)->setBody('Sesi organisasi tidak tersedia. Silakan login kembali.');
        $year = request()->getGet('year');
        if (!is_string($year) || !preg_match('/^[0-9]{4}$/D', $year) || (int) $year < 2000 || (int) $year > (int) date('Y')) {
            return response()->setStatusCode(422)->setBody('Pilih tahun antara 2000 dan tahun berjalan.');
        }
        try {
            $model = new DashboardModel();
            $data = $model->analytics($group, $org, ['start' => $year . '-01-01', 'end' => $year . '-12-31', 'service' => '', 'instrument' => '']);
            $html = view('Modules\Pe\Views\v_annualreport_pdf', [
                'data' => $data, 'year' => $year,
                'organization' => (string) (session()->get('orgname') ?: 'Rumah Sakit'),
                'generatedAt' => date('d/m/Y H:i'), 'isCurrentYear' => $year === date('Y'),
            ]);
            $pdf = new \Mpdf\Mpdf([
                'format' => 'A4', 'margin_left' => 14, 'margin_right' => 14,
                'margin_top' => 16, 'margin_bottom' => 18,
                'tempDir' => WRITEPATH . 'cache', 'default_font' => 'dejavusans',
            ]);
            $pdf->SetTitle('Laporan Tahunan Patient Experience ' . $year);
            $pdf->SetAuthor('INFINITE');
            $pdf->SetHTMLFooter('<div style="font-size:8pt;color:#718096;text-align:right">INFINITE | Patient Experience ' . $year . ' | Halaman {PAGENO} / {nbpg}</div>');
            $pdf->WriteHTML($html);
            return response()->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'inline; filename="Laporan_PE_' . $year . '.pdf"')
                ->setHeader('Cache-Control', 'private, no-store')->setHeader('X-Content-Type-Options', 'nosniff')
                ->setBody($pdf->Output('', 'S'));
        } catch (\Throwable $e) {
            log_message('error', 'PE annual PDF: {message}', ['message' => $e->getMessage()]);
            return response()->setStatusCode(503)->setBody('Laporan PDF belum dapat dibuat. Silakan coba kembali atau hubungi administrator.');
        }
    }
}