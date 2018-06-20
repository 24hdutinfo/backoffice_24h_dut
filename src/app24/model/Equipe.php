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
class Equipe extends Model {

    protected $table = 'team';
    protected $primaryKey = 'id';

    public $incrementing=false;
    public $keyType='string';



    public function membres() {
        return $this->hasMany('\app24\model\Membre', 'team_id');
    }

    public function epreuves() {
        return $this->belongsToMany('\app24\model\Epreuve','resultat', 'team_id', 'epr_id')
            ->withPivot([ 'rang','points']);
    }

}