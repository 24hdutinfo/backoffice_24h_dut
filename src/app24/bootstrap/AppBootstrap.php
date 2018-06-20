<?php
/**
 * File:  LbsBootstrap.php
 * Creation Date: 25/10/2017
 * description:
 *
 * @author: canals
 */

namespace app24\bootstrap;

use \Illuminate\Database\Capsule\Manager ;

/**
 * Class LbsBootstrap
 * @package lbs\common\bootstrap
 */
class AppBootstrap {

    const MOD_PROD = 1 ;
    const MOD_DEV  = 2 ;

    private static $mode;

    public static function setMode(int $mode) {
        self::$mode = $mode;
    }
    public static function getMode() {
        return self::$mode;
    }

    public static function startEloquent(array $conf) {

        //$conf = parse_ini_string(file_get_contents($file));
        $db = new Manager();

        $db->addConnection($conf);
        $db->setAsGlobal();
        $db->bootEloquent();

    }



}