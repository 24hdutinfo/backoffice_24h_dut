<?php
/**
 * File:  CheckUserIsAdmin.php
 * Creation Date: 29/01/2018
 * description:
 *
 * @author: canals
 */

namespace app24\middleware;

use app24\auth\AccessControlException;
use app24\auth\AuthController;
use app24\auth\AuthException;
use app24\control\AdminController;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Container;

/**
 * Class CheckUserIsAdmin
 * @package app24\middleware
 */
class CheckUserIsAdmin {

    private $c;

    public function __construct(Container $c){
        $this->c = $c;
    }

    public function __invoke( Request $rq, Response $rs , callable $next) {

        try {

            AuthController::check_access(AuthController::ADMIN_LEVEL);

        } catch (AccessControlException $e) {

            $uri = $rq->getUri()->withPath($this->c->router->pathFor('loginForm'));
            return $rs->withRedirect($uri, 302);

        }

        return $next($rq, $rs);
    }


}