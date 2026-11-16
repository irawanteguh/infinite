<?php
namespace Modules\Klaim\Models;

use CodeIgniter\Model;
use Modules\Klaim\Services\PendingAnalytics;

class PendingModel extends Model
{
    protected $DBGroup = null;

    public function periode(){return array_map(static fn($year)=>(object)['periode'=>$year],range((int)date('Y'),2014));}

    protected function scoped(string $provider,array $filters)
    {
        $year=(int)$filters['year'];
        $builder=$this->db->table('dt01_bpjs_pending_klaim a')->where('a.KODEPPK',$provider)
            ->where('a.TGLPULANG >=',$year.'-01-01')->where('a.TGLPULANG <',($year+1).'-01-01');
        if($filters['month']!==''){
            $start=sprintf('%04d-%02d-01',$year,(int)$filters['month']);
            $builder->where('a.TGLPULANG >=',$start)->where('a.TGLPULANG <',(new \DateTimeImmutable($start))->modify('+1 month')->format('Y-m-d'));
        }
        if($filters['service']!=='')$builder->where('a.JNSPEL',$filters['service']);
        if($filters['status']!=='')$builder->where('UPPER(TRIM(a.STATUS))',$filters['status']);
        return $builder;
    }

    private function legacyFilters($year): array{return ['year'=>(int)$year,'month'=>'','service'=>'','status'=>''];}
    public function dataraweklaim($provider,$year){return $this->aggregate((string)$provider,$this->legacyFilters($year));}

    public function aggregate(string $provider,array $filters): array
    {
        return $this->scoped($provider,$filters)->select("MONTH(a.TGLPULANG) AS periode, UPPER(TRIM(a.STATUS)) AS status, a.JNSPEL AS jnspel, COUNT(*) AS jumlah_kasus, COUNT(a.PENGAJUAN) AS valued_count, SUM(a.PENGAJUAN) AS pengajuan, MIN(a.TGLPULANG) AS first_date, MAX(a.TGLPULANG) AS last_date",false)
            ->groupBy('MONTH(a.TGLPULANG),UPPER(TRIM(a.STATUS)),a.JNSPEL',false)->orderBy('periode')->get()->getResultArray();
    }

    public function dataalgoritmatype($provider,$year){return $this->types((string)$provider,$this->legacyFilters($year));}
    public function types(string $provider,array $filters): array
    {
        return $this->scoped($provider,$filters)->select("UPPER(TRIM(TRAILING '.' FROM TRIM(a.ALGORITMA_TYPE))) AS algoritma_type, COUNT(*) AS jumlah_kasus",false)
            ->groupBy('algoritma_type')->orderBy('jumlah_kasus','DESC')->get()->getResultArray();
    }

    public function dataalgoritmabreakdown($provider,$year){return $this->algorithms((string)$provider,$this->legacyFilters($year));}
    public function algorithms(string $provider,array $filters): array
    {
        return PendingAnalytics::algorithms($this->scoped($provider,$filters)->select('a.ALGORITMA AS algoritma')->get()->getResultArray());
    }

    public function summary(string $provider,array $filters): array
    {
        $rows=$this->aggregate($provider,$filters);
        $first=array_column($rows,'first_date');$last=array_column($rows,'last_date');
        return PendingAnalytics::build($rows)+['types'=>$this->types($provider,$filters),'algorithms'=>$this->algorithms($provider,$filters),
            'firstDate'=>$first?min($first):null,'lastDate'=>$last?max($last):null,'generatedAt'=>date('d/m/Y H:i')];
    }

    public function details(string $provider,array $filters,int $start,int $length,string $search): array
    {
        $total=$this->scoped($provider,$filters)->countAllResults();
        $builder=$this->scoped($provider,$filters);
        if($search!=='')$builder->groupStart()->like('a.NOSEP',$search)->orLike('a.NMPESERTA',$search)->orLike('a.KDINACBG',$search)
            ->orLike('a.ALGORITMA_TYPE',$search)->orLike('a.ALGORITMA',$search)->orLike('a.KETERANGAN_VERIFIKASI',$search)->groupEnd();
        $filtered=(clone $builder)->countAllResults();
        $rows=$builder->select("a.NOSEP AS sep,a.NMPESERTA AS nama_pasien,a.JNSPEL AS jnspel,a.KDINACBG AS kdinacbg,a.SL AS sl,a.PENGAJUAN AS pengajuan,a.ALGORITMA_TYPE AS algoritma_type,a.ALGORITMA AS algoritma,a.KETERANGAN_VERIFIKASI AS keterangan_verifikasi,UPPER(TRIM(a.STATUS)) AS status,DATE_FORMAT(a.TGLSEP,'%d/%m/%Y') AS admission_date,DATE_FORMAT(a.TGLPULANG,'%d/%m/%Y') AS discharge_date,DATE_FORMAT(a.TGLVERIF,'%d/%m/%Y') AS verification_date",false)
            ->orderBy('a.TGLPULANG','DESC')->orderBy('a.NOSEP')->limit($length,$start)->get()->getResultArray();
        return ['recordsTotal'=>$total,'recordsFiltered'=>$filtered,'data'=>$rows];
    }
}
