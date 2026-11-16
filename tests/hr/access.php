<?php
// Run: php tests/hr/access.php. All persistence tests use an isolated in-memory SQLite DB.
define('FCPATH',dirname(__DIR__,2).'/public/');
chdir(FCPATH);
require FCPATH.'../app/Config/Paths.php';$paths=new Config\Paths();
require rtrim($paths->systemDirectory,'/').'/bootstrap.php';
if (!defined('ENVIRONMENT')) define('ENVIRONMENT','testing');
Config\Services::codeigniter()->initialize();
use App\Libraries\ModuleAccess as Policy;
use Modules\Hr\Models\AccessModel;
$db=Config\Database::connect(['DBDriver'=>'SQLite3','database'=>':memory:','DBPrefix'=>'','DBDebug'=>true],false);
$db->query('CREATE TABLE dt01_gen_user_data (user_id TEXT PRIMARY KEY,group_id TEXT,org_id TEXT,username TEXT,name TEXT,department_id TEXT,active TEXT,suspend TEXT)');
$db->query('CREATE TABLE dt01_gen_department_ms (department_id TEXT,group_id TEXT,org_id TEXT,department TEXT)');
$db->query('CREATE TABLE dt01_gen_modules_ms (modules_id TEXT PRIMARY KEY,modules_name TEXT,modules_header_id TEXT,package TEXT,def_controller TEXT,parent TEXT,urut INTEGER,active TEXT)');
require APPPATH.'Modules/Hr/Database/2026-11-13-000001_CreateUserModuleAccess.php';
(new Modules\Hr\Database\CreateUserModuleAccess(Config\Database::forge($db)))->up();
$model=new AccessModel($db);
$count=0;
function check($test,$label){global $count;if(!$test)throw new RuntimeException($label);$count++;}
function rejects($fn,$class,$label){try{$fn();}catch(Throwable $e){check($e instanceof $class,$label.' ('.get_class($e).')');return;}throw new RuntimeException($label.' did not reject');}
foreach ([['manager','G','O'],['staff','G','O'],['other','G','X']] as $u) $db->table('dt01_gen_user_data')->insert(['user_id'=>$u[0],'username'=>$u[0],'name'=>$u[0],'group_id'=>$u[1],'org_id'=>$u[2],'active'=>'1','suspend'=>'N']);
$modules=[
 ['modules_id'=>'root','modules_name'=>'Organization','modules_header_id'=>null,'package'=>'','def_controller'=>'','parent'=>'H','urut'=>1,'active'=>'1'],
 ['modules_id'=>'hr','modules_name'=>'Human Resource','modules_header_id'=>'root','package'=>'','def_controller'=>'','parent'=>'Y','urut'=>2,'active'=>'1'],
 ['modules_id'=>'access','modules_name'=>'Access','modules_header_id'=>'hr','package'=>'hr','def_controller'=>'access','parent'=>'N','urut'=>3,'active'=>'1'],
 ['modules_id'=>'welcome','modules_name'=>'Welcome','modules_header_id'=>'root','package'=>'additional','def_controller'=>'welcomepage','parent'=>'N','urut'=>4,'active'=>'1'],
 ['modules_id'=>'ur','modules_name'=>'Dashboard UR','modules_header_id'=>'root','package'=>'ur','def_controller'=>'dashboard','parent'=>'N','urut'=>5,'active'=>'1'],
 ['modules_id'=>'pe','modules_name'=>'Dashboard PE','modules_header_id'=>'root','package'=>'pe','def_controller'=>'dashboard','parent'=>'N','urut'=>6,'active'=>'1'],
 ['modules_id'=>'old','modules_name'=>'Inactive','modules_header_id'=>'root','package'=>'qi','def_controller'=>'old','parent'=>'N','urut'=>7,'active'=>'0'],
];
$db->table('dt01_gen_modules_ms')->insertBatch($modules);
$db->table('dt01_gen_access_manager')->insert(['group_id'=>'G','org_id'=>'O','user_id'=>'manager','created_by'=>'manager','created_at'=>'2026-11-13 00:00:00']);
check($model->ready(),'schema ready');
check(count($model->users('G','O'))===2,'tenant-scoped user list');
check(count($model->modules())===2,'exclude headers, welcome, manager and inactive modules');
check($model->modules()[0]['category']==='Organization','source category path');
check($model->state('G','O','staff')['moduleIds']===[],'deny by default');
check(!$model->state('G','X','manager')['manager'],'no manager across tenants');
$state=$model->state('G','O','staff');
check(Policy::routeAllowed('additional','welcomepage',$modules,$state),'welcome available');
check(!Policy::routeAllowed('hr','access',$modules,$state),'management protected');
check(!Policy::routeAllowed('ur','dashboard',$modules,$state),'direct route denied');
check(!Policy::routeAllowed('unknown','endpoint',$modules,$state),'unknown route denied');
$initial=$model->detail('G','O','staff');
rejects(fn()=>$model->saveAccess('G','O','staff','staff',['ur'],$initial['revision']),RuntimeException::class,'non-manager cannot grant');
rejects(fn()=>$model->saveAccess('G','O','manager','other',['ur'],$initial['revision']),InvalidArgumentException::class,'cross-tenant target rejected');
foreach ([['access'],['welcome'],['old'],['not-a-module'],['ur','ur'],[['bad']]] as $ids) rejects(fn()=>$model->saveAccess('G','O','manager','staff',$ids,$initial['revision']),InvalidArgumentException::class,'invalid/privileged IDs rejected');
$saved=$model->saveAccess('G','O','manager','staff',['ur'],$initial['revision']);
check($saved['moduleIds']===['ur'],'grant persisted');
check($db->table('dt01_gen_user_module_access_log')->countAllResults()===1,'audit saved');
$state=$model->state('G','O','staff');
check(Policy::routeAllowed('UR','DASHBOARD',$modules,$state),'case normalized route allowed');
check(!Policy::routeAllowed('pe','dashboard',$modules,$state),'other module remains denied');
check(Policy::routeAllowed('ur','importtxteklaim',$modules,$state),'shared UR toolbar import allowed');
check(!Policy::routeAllowed('bahv','importtxtbahv',$modules,$state),'other package import denied');
rejects(fn()=>$model->saveAccess('G','O','manager','staff',['pe'],$initial['revision']),DomainException::class,'stale revision rejected');
check($model->detail('G','O','staff')['moduleIds']===['ur'],'stale save leaves grants intact');
$visible=Policy::navigation($modules,$state);
check(array_column($visible,'modules_id')===['root','welcome','ur'],'navigation prunes denied leaves and empty groups');
$managerState=$model->state('G','O','manager');
check(Policy::routeAllowed('hr','access',$modules,$managerState),'manager has management only');
check(!Policy::routeAllowed('ur','dashboard',$modules,$managerState),'manager needs explicit ordinary grants');
check(in_array('hr',array_column(Policy::navigation($modules,$managerState),'modules_id'),true),'retain manager ancestors');
$db->table('dt01_gen_user_module_access')->insert(['group_id'=>'G','org_id'=>'O','user_id'=>'staff','modules_id'=>'old','created_by'=>'manager','created_at'=>'2026-11-13 00:00:00']);
$current=$model->detail('G','O','staff');
$revoked=$model->saveAccess('G','O','manager','staff',[],$current['revision']);
check($revoked['moduleIds']===['old'],'preserve inactive grants while revoking editable ones');
check(!Policy::routeAllowed('ur','dashboard',$modules,$model->state('G','O','staff')),'revocation applies without new login');
$db->table('dt01_gen_user_data')->where('user_id','manager')->update(['suspend'=>'Y']);
check(!$model->state('G','O','manager')['manager'],'suspended manager denied');
rejects(fn()=>$model->saveAccess('G','O','manager','staff',['ur'],$revoked['revision']),RuntimeException::class,'suspended manager cannot save');
check(Policy::revision(['b','a'])===Policy::revision(['a','b']),'stable revision');
check(!Policy::routeAllowed('qi','old',$modules,$model->state('G','O','staff')),'inactive grant cannot open inactive module');
// Audit and grants must commit together; forced audit failure rolls all grants back.
$db->table('dt01_gen_user_data')->where('user_id','manager')->update(['suspend'=>'N']);
$logsBefore=$db->table('dt01_gen_user_module_access_log')->countAllResults();
$db->query("CREATE TRIGGER block_access_audit BEFORE INSERT ON dt01_gen_user_module_access_log BEGIN SELECT RAISE(ABORT, 'audit test failure'); END");
rejects(fn()=>$model->saveAccess('G','O','manager','staff',['ur'],$revoked['revision']),RuntimeException::class,'audit failure blocks change');
check($model->grants('G','O','staff')===['old'],'failed audit rolls grants back');
check($db->table('dt01_gen_user_module_access_log')->countAllResults()===$logsBefore,'failed audit adds no log');
$db->query('DROP TRIGGER block_access_audit');
// A denied parent URL must not become accessible merely because a child is granted.
$tree=[['modules_id'=>'parent','modules_header_id'=>'','parent'=>'Y','package'=>'qi','def_controller'=>'parent'],['modules_id'=>'child','modules_header_id'=>'parent','parent'=>'N','package'=>'qi','def_controller'=>'child']];
check(Policy::navigation($tree,['manager'=>false,'moduleIds'=>['child']])[0]['def_controller']==='','denied ancestor URL removed');
echo "PASS: $count access checks (SQLite in memory; no application records changed).\n";
