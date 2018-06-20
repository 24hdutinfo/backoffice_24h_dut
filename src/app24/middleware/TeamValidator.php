<?php
/**
 * File:  TeamValidator.php
 * Creation Date: 26/01/2018
 * description:
 *
 * @author: canals
 */

namespace app24\middleware;
use Respect\Validation\Validator as v;

/**
 * Class TeamValidator
 * @package app24\middleware
 */
class TeamValidator {



    public static function checkTeamData() {

        return [

            'ctform_nom_equipe'=> v::StringType()->alnum("-'!@+-=*éèàùöñüô^_&§;<>")->setName('nom équipe'),
            'ctform_email'     => v::email()->setName('email contact'),
            'ctform_pass'      => v::StringType()->length(8, null)->setName('password'),
            'ctform_iut'       => v::IntVal()->between(1, 45, true)->setName('IUT origine'),

            'ctform_e1_nom'    => v::StringType()->alpha("éèàùöñüô-'")->setName('nom équipier 1'),
            'ctform_e1_prenom' => v::StringType()->alpha("éèàùöñüô-'")->setName('prenom équipier 1'),
            'ctform_e1_mail'   => v::email()->setName('mail équipier 1'),
            'ctform_e1_year'   => v::IntVal()->in(["1","2"])->setName('année équipier 1'),

            'ctform_e2_nom'    => v::StringType()->alpha("éèàùöñüô-'")->setName('nom équipier 2'),
            'ctform_e2_prenom' => v::StringType()->alpha("éèàùöñüô-'")->setName('prenom équipier 2'),
            'ctform_e2_mail'   => v::email()->setName('mail équipier 2'),
            'ctform_e2_year'   => v::IntVal()->in(["1","2"])->setName('année équipier 2'),

            'ctform_e3_nom'    => v::StringType()->alpha("éèàùöñüô-'")->setName('nom équipier 3'),
            'ctform_e3_prenom' => v::StringType()->alpha("éèàùöñüô-'")->setName('prenom équipier 3'),
            'ctform_e3_mail'   => v::email()->setName('mail équipier 3'),
            'ctform_e3_year'   => v::IntVal()->in(["1","2"])->setName('année équipier 3'),

            'ctform_e4_nom'    => v::StringType()->alpha("éèàùöñüô-'")->setName('nom équipier 4'),
            'ctform_e4_prenom' => v::StringType()->alpha("éèàùöñüô-'")->setName('prenom équipier 4'),
            'ctform_e4_mail'   => v::email()->setName('mail équipier 4'),
            'ctform_e4_year'   => v::IntVal()->in(["1","2"])->setName('année équipier 4'),

            'ctform_e5_nom'    => v::StringType()->alpha("éèàùöñüô-'")->setName('nom équipier 5'),
            'ctform_e5_prenom' => v::StringType()->alpha("éèàùöñüô-'")->setName('prenom équipier 5'),
            'ctform_e5_mail'   => v::email()->setName('mail équipier 5'),
            'ctform_e5_year'   => v::IntVal()->in(["1","2"])->setName('année équipier 5'),

            'ctform_e6_nom'    => v::optional(v::StringType()->alpha("éèàùöñüô-'")->setName('nom équipier 6')),
            'ctform_e6_prenom' => v::optional(v::StringType()->alpha("éèàùöñüô-'")->setName('prenom équipier 6')),
            'ctform_e6_mail'   => v::optional(v::email())->setName('mail équipier 6'),
            'ctform_e6_year'   => v::optional(v::IntVal()->in(["1","2"]))->setName('année équipier 6')

        ];

    }

    public static function checkAdminLogin() {

        return [

            'alform_email' => v::email()->setName('email'),
            'alform_passwd'=>  v::StringType()->length(8, null)->setName('password')

        ];
    }

    public static function checkRegisterUser() {
        return [
            'auform_email'=>v::email()->setName('email'),
            'auform_pass' =>v::StringType()->length(8,null)->setName('password'),
            'auform_nom'  => v::StringType()->alpha("éèàùöñüô-'")->setName('nom ')
        ];
    }

}