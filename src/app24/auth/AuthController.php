<?php
/**
 * File:  AuthController.php
 * Creation Date: 29/01/2018
 * description:
 *
 * @author: canals
 */

namespace app24\auth;
use app24\model\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;


/**
 * Class AuthController
 * @package app24\control
 */
class AuthController extends \app24\control\Controller {

    const SESSION_VAR_NAME = 'auth24_session';

    const ADMIN_LEVEL = 10;
    const NINJA_LEVEL = 100;

    public static function login(string $email, string $pass): User {

        try {
            $u = User::where('mail', '=', $email)
                ->firstOrFail();

            if (! password_verify($pass, $u->passwd))
                throw new AuthException("echec d'authentification");

        } catch (ModelNotFoundException $e) {
            throw new AuthException("echec d'authentification");
        }

        return $u;
    }

    public static function load_profile(User $user) {

        $_SESSION[self::SESSION_VAR_NAME]['user_id'] = $user->id;
        $_SESSION[self::SESSION_VAR_NAME]['user_email'] = $user->mail;
        $_SESSION[self::SESSION_VAR_NAME]['user_level'] = $user->auth_level;
        $_SESSION[self::SESSION_VAR_NAME]['user_name'] = $user->nom;

        session_regenerate_id();

    }

    public static function getUserProfile() {

        if (isset($_SESSION[self::SESSION_VAR_NAME] ))
            return $_SESSION[self::SESSION_VAR_NAME] ;
        return null;
    }

    public static function logout() {

        session_destroy() ;
    }

    public static function check_access(int $required) {

        $profile = self::getUserProfile();

        if ( is_null($profile) ||
             $profile['user_level'] < $required
        )
            throw new AccessControlException("droits d'accès insuffisants");

        return true;

    }


}