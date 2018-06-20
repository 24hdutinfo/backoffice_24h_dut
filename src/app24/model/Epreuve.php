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
class Epreuve extends Model {

    protected $table = 'epreuve';
    protected $primaryKey = 'id';

    const EPREUVE = 1;
    const GENERAL = 10;




    public function equipes() {
        return $this->belongsToMany('\app24\model\Equipe','resultat',
                      'epr_id', 'team_id')
            ->withPivot(['rang', 'points']);
    }

}