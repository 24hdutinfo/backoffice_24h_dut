<?php
/**
 * File:  CheckOrigin.php
 * Creation Date: 23/01/2018
 * description:
 *
 * @author: canals
 */

namespace app24\middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Container;

/**
 * Class CheckOrigin
 * @package app24\middleware
 */
class CheckOrigin {

    private $c;

    public function __construct(Container $c){
        $this->c = $c;
    }

    public function __invoke( Request $rq, Response $rs , callable $next) {


        $localhost = $this->c->settings['baseurl'];


    if  ( ($rq->hasHeader('Origin')) &&
          ($rq->getHeader('Origin')[0] != $localhost) //$rq->getUri()->getBaseUrl() )
              //getScheme().'://'.$rq->getHeaderLine('Host')) //getHost() )
    )
    {
        $rs->getBody()->write("Bad value for header Origin : " .
                                $rq->getHeader('Origin')[0] . ' != '. $localhost);//$rq->getUri()->getBaseUrl() );
            //getScheme().'://'.$rq->getHeaderLine('Host'));
        return $rs;

    }

    return $next($rq, $rs);


}

}