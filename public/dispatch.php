<?php
/**
 * File:  rest.php
 * Creation Date: 24/10/2017
 * description:
 *
 * @author: canals
 */

require_once  __DIR__ . '/../src/vendor/autoload.php';

use \DavidePastore\Slim\Validation\Validation as sv;
use \app24\middleware\CheckOrigin;
use \app24\middleware\TeamValidator;
use \app24\control\AdminController;
use \app24\control\TeamController;


session_start();


\app24\bootstrap\AppBootstrap::setMode(\app24\bootstrap\AppBootstrap::MOD_DEV);

$settings =    require_once __DIR__ . '/../src/config/app_settings.php';
$errors   =    require_once __DIR__ . '/../src/app24/bootstrap/app_errors.php';
$dependencies= require_once __DIR__ . '/../src/app24/bootstrap/app_dependencies.php';

$app_config = array_merge($settings, $errors, $dependencies);

\app24\bootstrap\AppBootstrap::startEloquent($app_config['settings']['db']);


$app = new \Slim\App( new \Slim\Container($app_config) );

$app->add(new CheckOrigin($app->getContainer()))
    ->add($app->getContainer()->get('csrf'));


$app->get('/teams/{id}', TeamController::class  . ':getTeamAccess')
    ->setName('team');

$app->post('/teams/{id}', TeamController::class  . ':displayTeamDetail')
    ->setName('TeamAccess');

$app->get('/teams[/]', TeamController::class  . ':displayTeamList')
    ->setName('teams');



$app->post('/teams[/]', TeamController::class.':createTeam')
    ->add(new sv(TeamValidator::checkTeamData()))
    ->setName('createTeam');

$app->get('/inscription[/]', TeamController::class . ':getCreateTeamForm');


$app->get('/admin/login[/]', AdminController::class . ':getLoginForm')
    ->setName('loginForm');

$app->post('/admin/login[/]', AdminController::class . ':doLogin')
    ->add(new sv(TeamValidator::checkAdminLogin()))
    ->setName('login');

$app->get('/admin[/]', AdminController::class . ':getAdminHome')
    ->add(new \app24\middleware\CheckUserIsAdmin($app->getContainer()))
    ->setName('adminHome');

$app->get('/admin/inscriptions[/]', AdminController::class . ':getTeamsList')
    ->add(new \app24\middleware\CheckUserIsAdmin($app->getContainer()))
    ->setName('adm_inscriptions');

$app->get('/admin/resultats[/]', AdminController::class . ':getResultats')
    ->add(new \app24\middleware\CheckUserIsAdmin($app->getContainer()))
    ->setName('adm_resultats');

$app->get('/admin/register[/]', AdminController::class . ':getAddUserForm')
    ->add(new \app24\middleware\CheckUserIsNinja($app->getContainer()))
    ->setName('adm_register');

$app->get('/admin/messages[/]', AdminController::class . ':getAdminMessage')
    ->add(new \app24\middleware\CheckUserIsAdmin($app->getContainer()))
    ->setName('adm_messages');

$app->post('/admin/messages[/]', AdminController::class . ':sendMessage')
    ->add(new \app24\middleware\CheckUserIsAdmin($app->getContainer()))
    ->setName('send_message');

$app->get('/admin/logout[/]', AdminController::class . ':doLogout')
    ->add(new \app24\middleware\CheckUserIsAdmin($app->getContainer()))
    ->setName('logout');

$app->get('/admin/teams/liste_inscription.csv', AdminController::class . ':listDownload')
    ->add(new \app24\middleware\CheckUserIsAdmin($app->getContainer()))
    ->setName('teams_download');

$app->get('/admin/teams/liste_participants.csv', AdminController::class . ':listParticipants')
    ->add(new \app24\middleware\CheckUserIsAdmin($app->getContainer()))
    ->setName('participants_download');

$app->get('/admin/resultats/file/{id}.csv', AdminController::class.':fileResultat')
    ->add(new \app24\middleware\CheckUserIsAdmin($app->getContainer()))
    ->setName('file_resultat');

$app->get('/admin/resultats/file/res.json', AdminController::class.':getJsonFileRes')
    ->add(new \app24\middleware\CheckUserIsAdmin($app->getContainer()))
    ->setName('json_resultat');

$app->post('/admin/resultats/file', AdminController::class.':uploadFileResultat')
    ->add(new \app24\middleware\CheckUserIsAdmin($app->getContainer()))
    ->setName('upload_file_res');

$app->post('/admin/resultats/general', AdminController::class.':calculerResultatGeneral')
    ->add(new \app24\middleware\CheckUserIsAdmin($app->getContainer()))
    ->setName('calculer_general');

$app->post('/admin/teams/{id}', AdminController::class . ':updateTeam')
    ->add(new \app24\middleware\CheckUserIsAdmin($app->getContainer())) ;


$app->post('/admin/users', AdminController::class . ':registerUser')
    ->add(new \app24\middleware\CheckUserIsNinja($app->getContainer()))
    ->add(new sv(TeamValidator::checkRegisterUser()))
    ->setName('create_user');




$app->run();

