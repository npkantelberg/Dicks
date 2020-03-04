<?php
defined('ABSPATH') || exit;

function duplicator_pro_get_home_path()
{
    static $homePath = null;
    if (is_null($homePath)) {
        if (!function_exists('get_home_path')) {
            require_once(ABSPATH.'wp-admin/includes/file.php');
        }
        $homePath = DupProSnapLibIOU::safePathUntrailingslashit(get_home_path(), true);
    }
    return $homePath;
}

function duplicator_pro_get_abs_path()
{
    static $absPath = null;
    if (is_null($absPath)) {
        $absPath = DupProSnapLibIOU::safePathUntrailingslashit(ABSPATH, true);
    }
    return $absPath;
}
