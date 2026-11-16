<?php
namespace Modules\Hr\Controllers;

use App\Controllers\BaseController;
use Modules\Hr\Models\AccessModel;

class Access extends BaseController
{
    public function index()
    {
        return $this->execute(function ($model, $group, $org) {
            return view('template/dashboard-light-aside', ['contents'=>view('Modules\Hr\Views\v_access')]);
        });
    }

    public function data()
    {
        return $this->execute(function ($model, $group, $org) {
            return response()->setJSON(['responseCode'=>'00','users'=>$model->users($group,$org),'modules'=>$model->modules(),'csrfHash'=>csrf_hash()]);
        });
    }

    public function detail()
    {
        return $this->execute(function ($model, $group, $org) {
            $id = request()->getGet('user_id');
            if (!is_string($id) || $id === '' || strlen($id)>64) throw new \InvalidArgumentException('Pilih pengguna yang valid.');
            return response()->setJSON(['responseCode'=>'00','detail'=>$model->detail($group,$org,$id),'csrfHash'=>csrf_hash()]);
        });
    }

    public function save()
    {
        return $this->execute(function ($model, $group, $org) {
            $id = request()->getPost('user_id'); $ids = request()->getPost('modules'); $revision = request()->getPost('revision');
            if (!is_string($id) || $id === '' || strlen($id)>64 || !is_string($revision) || !preg_match('/^[a-f0-9]{64}$/D',$revision)) {
                throw new \InvalidArgumentException('Data pengguna atau revisi tidak valid.');
            }
            // An empty list is explicit; a missing/malformed payload must not revoke all access.
            if (request()->getPost('modules_present') !== '1' || ($ids !== null && !is_array($ids))) throw new \InvalidArgumentException('Daftar akses belum lengkap.');
            $detail = $model->saveAccess($group,$org,(string) session()->get('userid'),$id,$ids ?? [],$revision);
            return response()->setJSON(['responseCode'=>'00','responseDesc'=>'Akses pengguna berhasil disimpan.','detail'=>$detail,'csrfHash'=>csrf_hash()]);
        }, true);
    }

    private function execute(callable $action, bool $write = false)
    {
        if (!session()->get('loggedin') || !session()->get('groupid') || !session()->get('orgid') || !session()->get('userid')) {
            return response()->setStatusCode(401)->setJSON(['responseCode'=>'01','responseDesc'=>'Sesi organisasi tidak tersedia. Silakan login kembali.']);
        }
        if ($write && request()->getMethod() !== 'post') return response()->setStatusCode(405)->setJSON(['responseCode'=>'01','responseDesc'=>'Gunakan metode POST.']);
        try {
            $model = new AccessModel(); $group=(string)session()->get('groupid'); $org=(string)session()->get('orgid');
            if (!$model->ready()) return response()->setStatusCode(503)->setJSON(['responseCode'=>'01','responseDesc'=>'Penyimpanan akses belum tersedia. Hubungi administrator.']);
            if (empty($model->state($group,$org,(string)session()->get('userid'))['manager'])) {
                return response()->setStatusCode(403)->setJSON(['responseCode'=>'01','responseDesc'=>'Hanya pengelola akses yang dapat membuka fungsi ini.']);
            }
            if ($write) service('security')->verify(request());
            return $action($model,$group,$org);
        } catch (\DomainException $e) { return $this->failure(409,$e->getMessage());
        } catch (\InvalidArgumentException $e) { return $this->failure(422,$e->getMessage());
        } catch (\CodeIgniter\Security\Exceptions\SecurityException $e) { return $this->failure(403,'Token keamanan tidak valid. Muat ulang halaman.');
        } catch (\Throwable $e) {
            log_message('error','HR access: {message}',['message'=>$e->getMessage()]);
            return $this->failure(500,'Akses belum berhasil diproses. Silakan coba kembali.');
        }
    }

    private function failure(int $code, string $message)
    {
        return response()->setStatusCode($code)->setJSON(['responseCode'=>'01','responseDesc'=>$message,'csrfHash'=>csrf_hash()]);
    }
}
