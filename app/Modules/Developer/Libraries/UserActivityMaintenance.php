<?php

namespace Modules\Developer\Libraries;

use Modules\Developer\Database\AddUserActivityDetails;
use Modules\Developer\Database\CreateUserActivity;

class UserActivityMaintenance
{
    public static function isReady(): bool
    {
        $db = \Config\Database::connect();
        $db->resetDataCache();
        if (!$db->tableExists('dt01_gen_user_activity')) {
            return false;
        }

        $required = ['activity_id', 'group_id', 'org_id', 'user_id', 'user_name',
            'event', 'route', 'http_method', 'http_status', 'created_at', 'details'];
        return array_diff($required, $db->getFieldNames('dt01_gen_user_activity')) === [];
    }

    public static function setup(): void
    {
        require_once APPPATH . 'Modules/Developer/Database/2026-11-24-000001_CreateUserActivity.php';
        require_once APPPATH . 'Modules/Developer/Database/2026-11-24-000002_AddUserActivityDetails.php';
        (new CreateUserActivity())->up();
        (new AddUserActivityDetails())->up();
    }
}
