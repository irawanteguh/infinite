<?php

namespace Modules\Ca\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Security\Exceptions\SecurityException;
use Modules\Ca\Models\CaskriningModel;
use Modules\Ca\Models\MasterqusionerModel;

class Caskrining extends BaseController
{
    private const TOKEN_KEY = 'ca_screening_tokens';

    public function index()
    {
        return $this->page(request()->getGet('code'));
    }

    public function submit()
    {
        if (strtolower(request()->getMethod()) !== 'post') {
            return response()->setStatusCode(405)->setHeader('Allow', 'POST');
        }
        return $this->page(request()->getPost('code'), true);
    }

    public function start()
    {
        if (strtolower(request()->getMethod()) !== 'post') {
            return response()->setStatusCode(405)->setHeader('Allow', 'POST');
        }
        return $this->page(request()->getPost('code'), false, true);
    }

    private function page($code, bool $submit = false, bool $start = false)
    {
        $session = session();
        $scopeInput = ($submit || $start) ? request()->getPost() : request()->getGet();
        $group_id = $scopeInput['group_id'] ?? $session->get('groupid') ?? '';
        $org_id = $scopeInput['org_id'] ?? $session->get('orgid') ?? '';
        if (!is_string($group_id) || !is_string($org_id) || $group_id === '' || $org_id === ''
            || strlen($group_id) > 64 || strlen($org_id) > 64) {
            return response()->setStatusCode(422)->setBody('Tautan skrining harus menyertakan group_id dan org_id organisasi.');
        }
        $user = $session->get('loggedin') ? (string) $session->get('userid') : '';
        $publicScope = ['group_id' => $group_id, 'org_id' => $org_id];
        $url = site_url('ca/caskrining') . '?' . http_build_query($publicScope);

        $instruments = [];
        $instrument = null;
        $error = '';
        $token = '';
        $context = null;
        $values = [];
        $scope = [$group_id, $org_id, $user];
        $result = ($submit || request()->getGet('welcome') === '1') ? null : $session->getFlashdata('ca_screening_result');
        if (($result['scope'] ?? null) !== $scope) {
            $result = null;
        }

        try {
            $master = new MasterqusionerModel();
            $instruments = array_column($master->masters($group_id, $org_id, $master->ready()), null, 'code');
            if ($code !== null || $submit || $start) {
                $selected = is_string($code) ? ($instruments[$code] ?? null) : null;
                if (!$selected || !$selected['writable']) {
                    throw new \InvalidArgumentException('Instrumen skrining tidak tersedia untuk organisasi ini.');
                }

                if ($start) {
                    service('security')->verify(request());
                    if (!$selected['initialized']) {
                        throw new \InvalidArgumentException('Instrumen belum tersedia. Hubungi pengelola organisasi.');
                    }
                    return redirect()->to($url . '&' . http_build_query(['code' => $code]), 303)->withCookies();
                }

                $available = $master->activeInstrument($group_id, $org_id, $code);
                if (!$available || !$available['questions']) {
                    throw new \InvalidArgumentException('Instrumen skrining belum aktif atau belum memiliki pertanyaan aktif.');
                }
                $instrument = $available;
                $revision = CaskriningModel::revision($instrument);
                $tokens = $session->get(self::TOKEN_KEY) ?? [];
                $context = [$scope, $code, $revision];

                if ($submit) {
                    service('security')->verify(request());
                    $postedToken = request()->getPost('form_token');
                    if (!is_string($postedToken) || ($tokens[$postedToken] ?? null) !== $context) {
                        throw new \InvalidArgumentException('Formulir sudah berubah, kedaluwarsa, atau sudah disimpan. Silakan isi formulir terbaru.');
                    }

                    $token = $postedToken;
                    $answers = request()->getPost('answers');
                    $name = request()->getPost('respondent_name') ?? '';
                    $name = is_string($name) ? trim($name) : $name;
                    $domicile = request()->getPost('domicile') ?? '';
                    $phone = request()->getPost('phone_number') ?? '';
                    $consent = request()->getPost('contact_consent');
                    $values = [
                        'respondent_name' => is_string($name) ? $name : '',
                        'phone_number' => is_string($phone) ? $phone : '',
                        'contact_consent' => $consent === '1',
                        'domicile' => is_string($domicile) ? $domicile : '',
                        'answers' => is_array($answers) ? $answers : [],
                    ];
                    if (!is_string($name) || mb_strlen($name) > 200) {
                        throw new \InvalidArgumentException('Nama pasien maksimal 200 karakter.');
                    }
                    if (!is_string($domicile) || ($domicile !== '' && !isset(CaskriningModel::DOMICILES[$domicile]))) {
                        throw new \InvalidArgumentException('Pilih domisili Jakarta atau di luar Jakarta.');
                    }
                    if (!is_string($phone) || ($phone !== '' && !preg_match('/^[1-9][0-9]{7,12}$/D', $phone))) {
                        throw new \InvalidArgumentException('Isi nomor HP tanpa awalan 0 atau 62, sebanyak 8–13 digit.');
                    }
                    if ($consent !== null && $consent !== '1') {
                        throw new \InvalidArgumentException('Pilihan izin dihubungi tidak valid.');
                    }
                    if ($consent === '1' && $phone === '') {
                        throw new \InvalidArgumentException('Isi nomor HP jika bersedia dihubungi.');
                    }
                    if (!is_array($answers)) {
                        throw new \InvalidArgumentException('Lengkapi pilihan pada setiap pertanyaan.');
                    }
                    $result = (new CaskriningModel())->store($instrument, $answers, $name === '' ? null : $name, $domicile === '' ? null : $domicile, $user, $phone === '' ? null : '62' . $phone, $consent === '1');
                    unset($tokens[$postedToken]);
                    $session->set(self::TOKEN_KEY, $tokens);
                    $session->setFlashdata('ca_screening_result', ['scope' => $scope] + $result);
                    return redirect()->to($url, 303)->withCookies();
                }
            }
        } catch (SecurityException $exception) {
            response()->setStatusCode(403);
            $error = 'Token keamanan tidak valid. Silakan isi ulang formulir.';
        } catch (\InvalidArgumentException $exception) {
            response()->setStatusCode(422);
            $error = $exception->getMessage();
        } catch (\Throwable $exception) {
            log_message('error', 'CA skrining: {message}', ['message' => $exception->getMessage()]);
            response()->setStatusCode(503);
            $error = 'Skrining belum dapat diproses. Silakan coba lagi.';
        }

        if ($context !== null && $token === '') {
            $token = bin2hex(random_bytes(32));
            $tokens[$token] = $context;
            $session->set(self::TOKEN_KEY, array_slice($tokens, -10, null, true));
        }

        response()->setHeader('Cache-Control', 'no-store');
        $domiciles = CaskriningModel::DOMICILES;
        return view('template/dashboard-light-blank', [
            'contents' => view('Modules\Ca\Views\v_caskrining', compact('instruments', 'instrument', 'error', 'token', 'values', 'result', 'domiciles', 'group_id', 'org_id', 'url')),
        ]);
    }
}
