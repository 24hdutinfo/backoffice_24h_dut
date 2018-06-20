<?php
/**
 * File:  Equipe.php
 * Creation Date: 22/01/2018
 * description:
 *
 * @author: canals
 */

namespace app24\model;
use Illuminate\Database\Eloquent\Model ;

/**
 * Class Equipe
 * @package app24\model
 */
class Membre extends Model {

    protected $table = 'participant';
    protected $primaryKey = 'id';
    public $timestamps=false;

    public function equipe() {
        return $this->belongsTo('\app24\model\Equipe', 'team_id');
    }

}