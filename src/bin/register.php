<?php
/**
 * File:  register.php
 * Creation Date: 29/01/2018
 * description:
 *
 * @author: canals
 */

require_once  __DIR__ . '/../vendor/autoload.php';



$settings =    require_once __DIR__ . '/../config/app_settings.php'; //new \Slim\Container(

//$errors   =    require_once __DIR__ . '/../app24/app_errors.php';
//$dependencies= require_once __DIR__ . '/../app24/app_dependencies.php';

//$app_config = array_merge($settings, $errors, $dependencies);

\app24\bootstrap\AppBootstrap::startEloquent($settings['settings']['dbconf']);

$nom = $argv[1];
$email = $argv[2];
$pass = $argv[3];
$level = ( isset($argv[4])  ? \app24\auth\AuthController::NINJA_LEVEL : \app24\auth\AuthController::ADMIN_LEVEL );

$u = new \app24\model\User();
$u->mail = $email;
$u->passwd = password_hash($pass, PASSWORD_DEFAULT, ['cost'=>12]);
$u->auth_level = $level;
$u->nom=$nom;

$u->save();

print "user $email enregistré\n";





