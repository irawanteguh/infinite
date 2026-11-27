<?php
// Isolated regression checks: php tests/developer/module-sync.php
define('FCPATH', dirname(__DIR__, 2) . '/public/');
chdir(FCPATH);
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '/') . '/bootstrap.php';
if (!defined('ENVIRONMENT')) define('ENVIRONMENT', 'testing');
Config\Services::codeigniter()->initialize();
use Modules\Developer\Libraries\ModuleCatalogSync as Sync;
use Modules\Developer\Models\MastermodulesModel;
$count = 0;
function check($ok, $label) { global $count; if (!$ok) throw new RuntimeException($label); $count++; }
function rejects($fn, $label) { try { $fn(); } catch (DomainException $e) { check(true, $label); return; } throw new RuntimeException($label); }
function row($id, $name, $parent = '', $controller = '') {
    return ['modules_id'=>$id,'modules_name'=>$name,'modules_header_id'=>$parent,'package'=>$controller ? 'qi' : '',
        'def_controller'=>$controller,'parent'=>$controller ? 'N' : 'H','icon'=>'bi bi-house','urut'=>1,'active'=>'1','status'=>'0','version'=>null];
}
check(Sync::routeAvailable('restapi','environment'), 'RestAPI environment deployed namespace');
check(Sync::routeAvailable('restapi','collection'), 'RestAPI collection deployed namespace');
check(!Sync::routeAvailable('restapi','missing_controller'), 'missing controller rejected');
check(!Sync::routeAvailable('../RestAPI','environment'), 'invalid package rejected');
$restapi = row('restapi-environment', 'Environment', '', 'environment');
$restapi['package'] = 'restapi';
check(Sync::plan([$restapi], [])['summary']['add'] === 1, 'new RestAPI route preview');
$target = [row('prod-root','Quality'), row('prod-page','Summary','prod-root','summary'), row('local','Local')];
$source = [row('dev-root','Quality'), row('dev-page','Updated','dev-root','summary'), row('new','New','dev-root')];
$target[1]['active'] = '0';
$plan = Sync::plan($source,$target,fn()=>true);
check($plan['summary'] === ['add'=>1,'update'=>1,'unchanged'=>1,'kept'=>1,'activePreserved'=>1], 'summary');
check($plan['entries'][1]['id']==='prod-page', 'preserve route target ID');
check($plan['entries'][2]['data']['modules_header_id']==='prod-root','map parent ID');
check($plan['entries'][1]['data']['active']==='0','preserve local active');
check(Sync::import(json_encode(Sync::export($source)))===Sync::normalize($source),'export round trip');
check(Sync::plan($source,[],fn()=>true)['summary']['add']===3,'empty target');
check(Sync::fingerprint(array_reverse($target))===Sync::fingerprint($target),'order independent fingerprint');
rejects(fn()=>Sync::import('{'),'invalid JSON');
rejects(fn()=>Sync::import('{"format":"infinite.modules","version":2,"modules":[]}'),'version');
rejects(fn()=>Sync::import(str_repeat(' ',2097153)),'size');
rejects(fn()=>Sync::normalize([$source[0],$source[0]]),'duplicate ID');
$bad=$source; $bad[0]['modules_header_id']='new';
rejects(fn()=>Sync::normalize($bad),'cycle');
$bad=$source; $bad[0]['icon']='"><script>';
rejects(fn()=>Sync::normalize($bad),'icon injection');
$unavailable = Sync::plan($source,[],fn()=>false);
check($unavailable['entries'][1]['data']['active']==='0' && $unavailable['entries'][1]['warning']!=='','unavailable new controller imported inactive with warning');
$eoc=row('eoc','Episode Of Care','','eoc'); $eoc['package']='satusehat';
$eocPlan=Sync::plan([$eoc],[]);
check($eocPlan['entries'][0]['data']['active']==='0' && $eocPlan['entries'][0]['warning']!=='','missing EOC preview');
$routeChange=$target; $routeChange[1]['active']='1';
$changed=row('prod-page','Changed','prod-root','missing_controller');
rejects(fn()=>Sync::plan([$target[0],$changed],$routeChange),'existing active route cannot become unavailable');
$ambiguous=$target; $ambiguous[]=row('other','Quality');
rejects(fn()=>Sync::plan($source,$ambiguous,fn()=>true),'ambiguous folder');
$bad=$source; $bad[1]['modules_id']='local';
rejects(fn()=>Sync::plan($bad,$target,fn()=>true),'ID route conflict');
$db=Config\Database::connect(['DBDriver'=>'SQLite3','database'=>':memory:','DBPrefix'=>'','DBDebug'=>true],false);
$db->query('CREATE TABLE dt01_gen_modules_ms (modules_id TEXT PRIMARY KEY,modules_name TEXT,modules_header_id TEXT,package TEXT,def_controller TEXT,parent TEXT,icon TEXT,urut INTEGER,active TEXT,status TEXT,version TEXT,created_by TEXT,quick TEXT)');
$db->table('dt01_gen_modules_ms')->insertBatch($target);
$db->table('dt01_gen_modules_ms')->where('modules_id','prod-page')->update(['quick'=>'Y','created_by'=>'original']);
$model=new MastermodulesModel($db);
$preview=$model->previewSync($source);
$model->applySync($source,$preview['revision'],'operator');
$saved=array_column($db->table('dt01_gen_modules_ms')->get()->getResultArray(),null,'modules_id');
check(count($saved)===4 && isset($saved['local']),'target-only preserved');
check($saved['prod-page']['modules_name']==='Updated' && $saved['prod-page']['quick']==='Y' && $saved['prod-page']['created_by']==='original','update whitelist');
check($saved['new']['created_by']==='operator','local actor');
check($model->previewSync($source)['summary']['unchanged']===3,'idempotent');
rejects(fn()=>$model->applySync($source,$preview['revision'],'operator'),'stale preview');
$next=$source; $next[1]['modules_name']='Should rollback'; $next[]=row('fail','Fail');
$preview=$model->previewSync($next);
$db->query("CREATE TRIGGER reject_module BEFORE INSERT ON dt01_gen_modules_ms WHEN NEW.modules_id = 'fail' BEGIN SELECT RAISE(ABORT, 'test failure'); END");
$failed=false;
try { $model->applySync($next,$preview['revision'],'operator'); } catch (Throwable $e) { $failed=true; }
check($failed,'insert failure rejected');
check($db->table('dt01_gen_modules_ms')->where('modules_id','prod-page')->get()->getRowArray()['modules_name']==='Updated','transaction rollback');
echo "PASS $count module sync checks\n";

