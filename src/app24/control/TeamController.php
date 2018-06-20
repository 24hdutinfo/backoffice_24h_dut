<?php
/**
 * File:  TeamController.php
 * Creation Date: 22/01/2018
 * description:
 *
 * @author: canals
 */

namespace app24\control;

use app24\model\Message;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \app24\model\Equipe ;
use \app24\model\Membre ;
use Ramsey\Uuid\Uuid;

/**
 * Class TeamController
 * @package app24\control
 */
class TeamController extends Controller {


    public function displayTeamList(Request $rq, Response $rs, Array $args) {

        $list = Equipe::with('membres')
            ->orderBy('team_from', 'asc')
            ->orderBy('team_priority', 'asc')
            ->get();

        $total_registered = Equipe::count();
        $valid_registered = Equipe::where('valid_registration', '=', 1)
                                    ->count();

        return $this->c->view->render($rs, 'team.list.html.twig',
                                                    ['teams' => $list ,
                                                      'nb_valid'=>$valid_registered,
                                                      'nb_wait'=>$total_registered - $valid_registered,
                                                        'show_name' => $this->c->settings['show_team_members_name'] ]);

    }

    public function displayTeamDetail(Request $rq, Response $rs, Array $args ) {

        try {
            $team = Equipe::with('membres')
                ->where('id', '=', $args['id'])
                ->firstOrFail();

            $data = $rq->getParsedBody();

            if ( ($data['ctform_email'] == $team->contact_email) &&
                 password_verify($data['ctform_pass'], $team->team_password)
            )

            return $this->c->view->render($rs, 'team.detail.html.twig', [
                                                        'e' => $team,
                                                        'messages'=> Message::orderBy('created_at', 'desc')
                                                            ->get()
                                                    ]
            );

        } catch(ModelNotFoundException $e) {

            return $this->c->view->render($rs, 'team.error.html.twig', ['message' => "équipe inconnue : "/*.$args['id'] */]);
        }

        return $this->c->view->render($rs, 'team.error.html.twig', ['message' => "données d'accès invalides : " /*. $data['ctform_email']. '/'.$data['ctform_pass'] */]);

    }

    public function getTeamAccess(Request $rq, Response $rs,
                                  Array $args,Array $errors=null,
                                  array $values=null) {

        $keyname = $this->c->csrf->getTokenNameKey();
        $keyval =  $this->c->csrf->getTokenValueKey();
        return  $this->c->view->render($rs, 'teamaccess.form.html.twig',
            [  'id' => $args['id'],
                'csrf' =>[ 'keyname'=> $keyname,
                    'keyval' => $keyval,
                    'name' => $rq->getAttribute($keyname),
                    'value'=> $rq->getAttribute($keyval)
                ],
                'errors'=>$errors,
                'v'=> $values
            ] );

    }

    public function getCreateTeamForm(Request $rq, Response $rs, Array $errors=null, array $values=null) {

        $keyname = $this->c->csrf->getTokenNameKey();
        $keyval =  $this->c->csrf->getTokenValueKey();
        return  $this->c->view->render($rs, 'team.form.html.twig',
            [
                'iuts'=> $this->c->iuts,
                'csrf' =>[ 'keyname'=> $keyname,
                           'keyval' => $keyval,
                           'name' => $rq->getAttribute($keyname),
                           'value'=> $rq->getAttribute($keyval)
                ],
                'errors'=>$errors,
                'v'=> $values
            ] );
    }

    public function createTeam(Request $rq, Response $rs, Array $args) {

        $data = $rq->getParsedBody();
        if ($rq->getAttribute('has_errors')) {
            return $this->getCreateTeamForm($rq, $rs, $rq->getAttribute('errors'), $data);
        }
        //$data = $rq->getParsedBody();

        $iut = $data['ctform_iut'];
        $priorite = Equipe::where('team_from', '=', $iut)->count() + 1;

        $e = new Equipe();
        $e->id= bin2hex(random_bytes(8));//Uuid::uuid4();
        $e->team_name = filter_var($data['ctform_nom_equipe'], FILTER_SANITIZE_STRING);
        $e->team_from = filter_var($data['ctform_iut'], FILTER_SANITIZE_NUMBER_INT);
        $e->team_priority = $priorite;
        $e->contact_email = filter_var($data['ctform_email'], FILTER_SANITIZE_EMAIL);
        $e->valid_registration = ( $priorite==1 ? 1 : 0 );
        $e->registration_paid = 0;
        $e->team_password = password_hash($data['ctform_pass'], PASSWORD_DEFAULT, ['cost'=>12]);
        $e->team_url = /*$rq->getUri()->getScheme() . '://' .
                       $rq->getUri()->getHost() . ':' .
                       $rq->getUri()->getPort() .*/ //'/'.
                        $this->c->settings['baseurl'] .
                       $rq->getUri()->getBasePath() .
            $this->c->router->pathFor('team', ['id'=> $e->id]);

        $e->save();

        for ($i=1 ; $i<6; $i++) {
            $p = new Membre();
            $p->lastname = filter_var($data['ctform_e'.$i.'_nom'], FILTER_SANITIZE_STRING);
            $p->firstname = filter_var($data['ctform_e'.$i.'_prenom'], FILTER_SANITIZE_STRING);
            $p->email = filter_var($data['ctform_e'.$i.'_mail'], FILTER_SANITIZE_EMAIL);
            $p->student_year= (int) filter_var($data['ctform_e'.$i.'_year'], FILTER_SANITIZE_NUMBER_INT);
            $e->membres()->save($p);
        }

        if ( (! empty($data['ctform_e6_nom'])) && (! empty($data['ctform_e6_prenom'])) &&
             (! empty($data['ctform_e6_mail'])) && isset($data['ctform_e6_year']) ) {
            $p = new Membre();
            $p->lastname = filter_var($data['ctform_e6_nom'], FILTER_SANITIZE_STRING);
            $p->firstname = filter_var($data['ctform_e6_prenom'], FILTER_SANITIZE_STRING);
            $p->email = filter_var($data['ctform_e6_mail'], FILTER_SANITIZE_EMAIL);
            $p->student_year= (int) filter_var($data['ctform_e6_year'], FILTER_SANITIZE_NUMBER_INT);
            $e->membres()->save($p);

        }

        $body = $this->c->view->fetch('team.detail.mail.twig', ['e'=>$e]);
        $message= (new \Swift_Message("inscription 24h info"))
            ->setFrom([$this->c->settings['mailer']['mail_from']])
            ->setTo([$e->contact_email])
            ->setBody($body, 'text/html');


        $this->c->mailer->send($message);

        return $this->c->view->render($rs, 'team.detail.html.twig', ['e'=>$e,
                                        'messages'=> Message::orderBy('created_at', 'desc')
                                                     ->get()] );
    }

}
