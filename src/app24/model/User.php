<?php
/**
 * File:  User.php
 * Creation Date: 29/01/2018
 * description:
 *
 * @author: canals
 */

namespace app24\model;


/**
 * Class User
 * @package app24\model
 */
class User extends \Illuminate\Database\Eloquent\Model {

    protected $table = 'users';
    protected $primaryKey='id';
    public $timestamps=false;

}