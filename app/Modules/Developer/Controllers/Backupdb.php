<?php
namespace Modules\Developer\Controllers;

use App\Controllers\BaseController;
use Modules\Developer\Libraries\DatabaseDump;

class Backupdb extends BaseController
{
    public function index()
    {
        if (!session()->get('loggedin')) return redirect()->to(site_url('/'));
        return view('template/dashboard-light-aside', ['contents'=>view('Modules\Developer\Views\v_backupdb')]);
    }

    public function backup() { return $this->createBackup('full'); }
    public function backupics() { return $this->createBackup('ics'); }

    private function createBackup(string $scope)
    {
        if ($denied = $this->authorizeMutation(['POST'])) return $denied;
        try {
            session()->close();
            $result = (new DatabaseDump())->create($scope);
            return $this->json('00', 'Dump database berhasil dibuat.', $result);
        } catch (\Throwable $e) {
            log_message('error', 'Database backup: {message}', ['message'=>$e->getMessage()]);
            return $this->json('01', $e instanceof \RuntimeException ? $e->getMessage() : 'Backup gagal dibuat. Periksa log server.', [], 500);
        }
    }

    public function listbackup()
    {
        if (!session()->get('loggedin')) return $this->json('01','Sesi berakhir. Silakan login kembali.',[],401);
        $files = [];
        foreach (glob(WRITEPATH.'backups/*.sql') ?: [] as $path) {
            $name = basename($path);
            if (!$this->validName($name) || is_link($path) || !is_file($path)) continue;
            $size = filesize($path);
            $files[] = ['filename'=>$name,'size'=>$size,'sizeFormatted'=>$this->formatBytes($size),'created'=>date('Y-m-d H:i:s',filemtime($path)),'scope'=>strpos($name,'infinite_backup_ics_')===0?'ICS':'Seluruh database'];
        }
        usort($files,static fn($a,$b)=>strcmp($b['created'],$a['created']));
        return $this->json('00', $files ? 'Daftar backup tersedia.' : 'Belum ada file backup.', $files);
    }

    public function download($filename)
    {
        if (!session()->get('loggedin')) return $this->json('01','Sesi berakhir. Silakan login kembali.',[],401);
        $path = $this->backupPath($filename);
        if (!$path) return $this->json('01','File backup tidak ditemukan.',[],404);
        return response()->download($path,null)->setFileName(basename($path))->setHeader('Cache-Control','private, no-store')->setHeader('X-Content-Type-Options','nosniff');
    }

    public function delete($filename)
    {
        if ($denied = $this->authorizeMutation(['POST','DELETE'])) return $denied;
        $path = $this->backupPath($filename);
        if (!$path) return $this->json('01','File backup tidak ditemukan.',[],404);
        if (!unlink($path)) return $this->json('01','File backup tidak dapat dihapus.',[],500);
        return $this->json('00','File backup berhasil dihapus.');
    }

    private function authorizeMutation(array $methods)
    {
        if (!session()->get('loggedin')) return $this->json('01','Sesi berakhir. Silakan login kembali.',[],401);
        if (!in_array(strtoupper(request()->getMethod()),$methods,true)) return $this->json('01','Metode permintaan tidak diizinkan.',[],405);
        try { service('security')->verify(request()); }
        catch (\CodeIgniter\Security\Exceptions\SecurityException $e) { return $this->json('01','Token keamanan berakhir. Muat ulang halaman lalu coba kembali.',[],403); }
        return null;
    }

    private function validName($name): bool
    {
        return is_string($name) && preg_match('/\Ainfinite_backup_(?:ics_)?[0-9]{8}_[0-9]{6}(?:_[a-f0-9]{8})?\.sql\z/D',$name) === 1;
    }

    private function backupPath($filename): ?string
    {
        if (!$this->validName($filename)) return null;
        $root = realpath(WRITEPATH.'backups');
        if (!$root) return null;
        $candidate = $root.DIRECTORY_SEPARATOR.$filename;
        if (is_link($candidate) || !is_file($candidate)) return null;
        $path = realpath($candidate);
        return $path && dirname($path) === $root ? $path : null;
    }

    private function json(string $code,string $message,array $result=[],int $status=200)
    {
        return response()->setStatusCode($status)->setHeader('Cache-Control','private, no-store')->setJSON(['responseCode'=>$code,'responseHead'=>$code==='00'?'success':'error','responseDesc'=>$message,'responseResult'=>$result,'csrfName'=>csrf_token(),'csrfHash'=>csrf_hash()]);
    }

    private function formatBytes($bytes): string
    {
        if ($bytes <= 0) return '0 B';
        $units=['B','KB','MB','GB','TB'];$power=min((int)floor(log($bytes,1024)),count($units)-1);
        return number_format($bytes/pow(1024,$power),2,',','.').' '.$units[$power];
    }
}
