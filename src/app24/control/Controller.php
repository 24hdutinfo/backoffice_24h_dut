<?php
/**
 * File:  Controller.php
 * Creation Date: 22/01/2018
 * description:
 *
 * @author: canals
 */

namespace app24\control;


/**
 * Class Controller
 * @package app24\control
 */
abstract class Controller {

    protected $c ;

    public function __construct(\Slim\Container $c) {
        $this->c = $c;
    }

}