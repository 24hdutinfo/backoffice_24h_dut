<?php
/**
 * File:  TeamController.php
 * Creation Date: 22/01/2018
 * description:
 *
 * @author: canals
 */

namespace app24\control;

use app24\auth\AuthController;
use app24\auth\AuthException;
use app24\model\Epreuve;
use app24\model\Message;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use League\Csv\Reader;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \app24\model\Equipe ;
use \app24\model\Membre ;
use Ramsey\Uuid\Uuid;
use \League\Csv\Writer;

/**
 * Class TeamController
 * @package app24\control
 */
class AdminController extends Controller {


    public function getLoginForm(Request $rq, Response $rs, array $args=null, string $message=null) {

        $keyname = $this->c->csrf->getTokenNameKey();
        $keyval =  $this->c->csrf->getTokenValueKey();
        return  $this->c->view->render($rs, 'login.form.html.twig',
            [
                'csrf' =>[ 'keyname'=> $keyname,
                    'keyval' => $keyval,
                    'name' => $rq->getAttribute($keyname),
                    'value'=> $rq->getAttribute($keyval)
                ],
                'message' => $message
            ] );

    }

    public function doLogin(Request $rq, Response $rs) {

        if ($rq->getAttribute('has_errors'))
            return $this->getLoginForm($rq, $rs, null, "données d'authentification invalides");

        $data = $rq->getParsedBody();
        try {
            $u = AuthController::login($data['alform_email'], $data['alform_passwd']);
            AuthController::load_profile($u);

        } catch(AuthException $e ) {
            return $this->getLoginForm($rq, $rs, null, "echec de l'authentification");
        }

        return $this->getAdminHome($rq, $rs);

    }

    public function doLogout(Request $rq, Response $rs) {
        AuthController::logout();

        $uri = $rq->getUri()->withPath($this->c->router->pathFor('loginForm'));
        return $rs->withRedirect($uri, 302);

    }

    public function getAdminHome(Request $rq, Response $rs, array $args=null) {

        $u= AuthController::getUserProfile();

        return $this->c->view->render($rs, 'admin.home.html.twig' , ['user'=>$u, 'action'=>0]);

    }

    public function getTeamsList(Request $rq, Response $rs, array $args=null) {

        $list = Equipe::select(['id', 'team_name', 'team_from', 'created_at',
                                        'valid_registration', 'registration_paid', 'team_priority', 'contact_email'])
            ->get();

        $u= AuthController::getUserProfile();
        $keyname = $this->c->csrf->getTokenNameKey();
        $keyval =  $this->c->csrf->getTokenValueKey();
        return $this->c->view->render($rs, 'admin.insc.html.twig' , ['equipes'=>$list,
                                                                            'user'=>$u, 'action'=>1,
                                                                            'csrf' =>[ 'keyname'=> $keyname,
                                                                                        'keyval' => $keyval,
                                                                                        'name' => $rq->getAttribute($keyname),
                                                                                        'value'=> $rq->getAttribute($keyval)
                                                                                ]
                                                                          ]);

    }

    public function updateTeam(Request $rq, Response $rs, array $args=null ) {

        $team_id = $args['id'];

        try {
            $equipe = Equipe::where('id', '=', $team_id)->firstOrFail();

            $data=$rq->getParsedBody();

            if (isset($data['lf_paiement']) ) {
                $equipe->registration_paid = 1;
            } else {
                $equipe->registration_paid = 0;

            }
            if (isset($data['lf_valide'])) {
                $equipe->valid_registration = 1;
            } else {
                $equipe->valid_registration = 0;

            }

            $equipe->save();


        } catch(ModelNotFoundException $e) {

        }

        return $this->getTeamsList($rq, $rs);

    }

    public function listDownload(Request $rq, Response $rs, array $args=null ) {

        $list = Equipe::select(['id', 'team_name', 'team_from', 'created_at',
            'valid_registration', 'registration_paid', 'team_priority', 'contact_email', 'team_url'])
            ->get();

        $list_array = $list->toArray();

        $csv=Writer::createFromString('');
        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->setDelimiter(';');
        $csv->insertOne(['identifiant', 'nom équipe', 'IUT', 'date inscription',
            'validée', 'paiement', 'no ordre', 'email contact', 'url équipe']);
        foreach ($list_array as $team) {

            $iut = $team['team_from'];
            $iutname = $this->c->iuts[(int)$iut -1];
            $team['team_from']=$iutname;
            $team['team_name'] = htmlspecialchars_decode($team['team_name'], ENT_QUOTES);

            $csv->insertOne($team);

        }

        $rs=$rs->withHeader('Content-Type', 'text/csv;charset=utf-8');
        $rs->getBody()->write($csv);

        return $rs;


    }

    public function listParticipants(Request $rq, Response $rs, array $args=null ) {

        $list= Equipe::with('membres')
            ->orderBy('team_from')
            ->where('valid_registration', '<>', 0)
            ->get();

        //$list_array = $list->toArray();

        $csv=Writer::createFromString('');
        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->setDelimiter(';');
        $csv->insertOne(['n° équipe', 'nom équipe', 'IUT', 'nom', 'prénom', 'mail']);
        foreach ($list as $t) {
            foreach ($t->membres as $p) {
                $participant = [$t->id,
                    htmlspecialchars_decode($t->team_name, ENT_QUOTES),
                    $this->c->iuts[(int)$t->team_from -1],
                    htmlspecialchars_decode($p->lastname,ENT_QUOTES),
                    htmlspecialchars_decode($p->firstname,ENT_QUOTES),
                    $p->email
                ];

                $csv->insertOne($participant);
            }
        }

        $rs=$rs->withHeader('Content-Type', 'text/csv;charset=utf-8');
        $rs->getBody()->write($csv);

        return $rs;
    }

    public function getResultats(Request $rq, Response $rs, array $args=null, $msg=null) {

        $list = Epreuve::where('type_epreuve', '<>', Epreuve::GENERAL)->get();

        $u= AuthController::getUserProfile();

        foreach ($list as $e) {

            $e->path = $this->c->router->pathFor('file_resultat', ['id'=>$e->id]);

        }

        $keyname = $this->c->csrf->getTokenNameKey();
        $keyval =  $this->c->csrf->getTokenValueKey();


        return $this->c->view->render($rs, 'admin.res.html.twig' ,
            ['csrf' =>[ 'keyname'=> $keyname,
                         'keyval' => $keyval,
                         'name' => $rq->getAttribute($keyname),
                         'value'=> $rq->getAttribute($keyval)
                    ],
                'epreuves'=>$list,
            'user'=>$u,
                'msg'=>$msg
             ]);
    }



    public function fileResultat(Request $rq, Response $rs, array $args=null ) {
        $e=null;
        try {

            $e = Epreuve::where('id', '=', $args['id'])
                ->firstOrFail();
        } catch (ModelNotFoundException $ex) {

        }

        $list = Equipe::select(['id', 'team_name', 'team_from'])
            ->where('valid_registration', '=', 1)
            ->orderBy('team_name')
            ->get();

        //$list_array = $list->toArray();

        $csv=Writer::createFromString('');
        $csv->setOutputBOM(Writer::BOM_UTF8);
        $csv->setDelimiter(';');
        $csv->insertOne(['epreuve', 'identifiant', 'nom_equipe', 'IUT', 'rang','points']);
        foreach ($list as $team) {
            $csv_row=[];
            array_push($csv_row, $e->id);
            array_push($csv_row, $team->id);
            array_push($csv_row, htmlspecialchars_decode($team->team_name, ENT_QUOTES));
            array_push($csv_row, $this->c->iuts[(int)$team->team_from -1]);

            $csv->insertOne($csv_row);

        }

        $rs=$rs->withHeader('Content-Type', 'text/csv')
                ->withHeader('Content-disposition', 'attachment; filename="epreuve_'.$e->id.'.csv"');

        $rs->getBody()->write($csv);

        return $rs;
    }


    public function uploadFileResultat(Request $rq, Response $rs) {

        $upload_dir = $this->c['settings']['upload_dir'];
        $storage = new \Upload\Storage\FileSystem(__DIR__ . '/../../../log');
        $file = new \Upload\File('upload_file_res', $storage);

        $new_filename = uniqid().'csv';
        $file->setName($new_filename);
        //$file->addValidations([ new \Upload\Validation\Mimetype('text/csv')] );

        try {
            $file->upload();
        } catch (\Exception $e) {
            $rs->getBody()->write('upload failed'.json_encode($file->getErrors() ));
            return $rs;
        }

        $csv = Reader::createFromPath($upload_dir.'/'.$file->getNameWithExtension());
        $csv->setDelimiter(';');

        $csv->setHeaderOffset(0);

        $header=$csv->getHeader();


        $records = $csv->getRecords(); $nb_imp=0; $nb_up=0;
        foreach ($records as $row) {
            $eq=Equipe::where('id', '=', $row['identifiant'])->first();
            $ep=Epreuve::where('id','=', $row['epreuve'])->first();
            try {
                $eq->epreuves()->attach([
                    $ep->id => ['rang' => $row['rang'], 'points' => $row['points']]
                ]);
                $nb_imp++;
            } catch( QueryException $q) {
                $eq->epreuves()->updateExistingPivot($ep->id, ['rang' => $row['rang'], 'points' => $row['points']]);
                $nb_up++;
            }
            //$eq->save();


        }

        $msg='fichier résultats correctement importé : '.$nb_imp.' lignes créées - '.$nb_up.' lignes modifiées - ';

        return $this->getResultats($rq, $rs, null, $msg);

    }

    public function calculerResultatGeneral(Request $rq, Response $rs) {
        $listEquipe = Equipe::get();

        foreach ($listEquipe as $equipe) {

            $list_epreuve=$equipe->epreuves()->where('type_epreuve', '=', Epreuve::EPREUVE)
                            ->get();
            $total_points = 0;
            foreach ($list_epreuve as $e) {
                $total_points += $e->pivot->points ;

            }

            try {
                $general = $equipe->epreuves()->where('type_epreuve', '=', Epreuve::GENERAL)->firstOrFail();
                $equipe->epreuves()->updateExistingPivot($general->id, ['points'=>$total_points]);

            } catch(ModelNotFoundException $ex) {
                $general = Epreuve::where('type_epreuve', '=', Epreuve::GENERAL)
                    ->first();
                $equipe->epreuves()->attach([
                    $general->id => ['points'=>$total_points]
                ]);
            }

        }
        $general = Epreuve::with('equipes')
            ->where('type_epreuve', '=', Epreuve::GENERAL)
            ->first();

        $equipes = $general->equipes()->orderBy('resultat.points', 'desc')->get();

        $points = 0; $rang = 0;$step=1;
        foreach ($equipes as $equipe) {


            if ($equipe->pivot->points === $points) {
                $step +=1;
            } else {
                $rang +=$step;
                $step=1;
                $points = $equipe->pivot->points;
            }
            $equipe->epreuves()->updateExistingPivot($general->id, ['rang'=>$rang]);
        }



        $msg='calcul des points classement général effectué : '. count($listEquipe);

        return $this->getResultats($rq, $rs, null, $msg);


    }



    public function getJsonFileRes(Request $rq, Response $rs) {

        $epreuves = Epreuve::select('id', 'libele as titre', 'description as descr')
            ->with('equipes')
            ->where('type_epreuve', '<>', Epreuve::GENERAL)
            ->get();

        $all = [];
        foreach ($epreuves as $epr) {
            $epr_data = ['titre' => $epr->titre, 'descr' => $epr->descr, 'resultat' => []];
            foreach ($epr->equipes as $equipe) {
                array_push($epr_data['resultat'], ['nom' => htmlspecialchars_decode($equipe->team_name, ENT_QUOTES),
                    'iut' => $this->c->iuts[(int)$equipe->team_from -1],
                    'score' => $equipe->pivot->points,
                    'rang' => $equipe->pivot->rang
                ]);
            }
            $all['epreuve_' . $epr->id] = $epr_data;
        }

        $all['global'] = ['titre'=> 'Classement Général',
                          'descr'=> 'Classement général cumulant les 3 épreuves',
                           'resultat'=>[]
            ];

        foreach( Equipe::with('epreuves')->get() as $equipe) {

            $equ_data = ['nom' => htmlspecialchars_decode($equipe->team_name, ENT_QUOTES),
                         'iut' => $this->c->iuts[(int)$equipe->team_from -1]
                        ];
            foreach ($equipe->epreuves as $epreuve) {
                if ($epreuve->type_epreuve !== Epreuve::GENERAL)
                    $equ_data['score' . $epreuve->id] = $epreuve->pivot->points;
                if ($epreuve->type_epreuve === Epreuve::GENERAL) {
                    $equ_data['final'] = $epreuve->pivot->points;
                    $equ_data['rang'] = $epreuve->pivot->rang;
                }
            }
            array_push($all['global']['resultat'], $equ_data);
        }



        $rs = $rs->withHeader('Content-Type', 'application/json')
            ->withHeader('Content-disposition', 'attachment; filename="res.json"');
        $rs->getBody()->write(json_encode($all));

        return $rs;
    }

    public function getAdminMessage(Request $rq, Response $rs, $args=null, $msg=null) {



        $u= AuthController::getUserProfile();



        $keyname = $this->c->csrf->getTokenNameKey();
        $keyval =  $this->c->csrf->getTokenValueKey();


        return $this->c->view->render($rs, 'admin.message.html.twig' ,
            ['csrf' =>[ 'keyname'=> $keyname,
                'keyval' => $keyval,
                'name' => $rq->getAttribute($keyname),
                'value'=> $rq->getAttribute($keyval)
                ],
                'msg'=>$msg,
                'user'=>$u,
                'messages'=> Message::orderBy('created_at', 'desc')->get()
            ]);
    }

    public function sendMessage(Request $rq, Response $rs) {

        $data = $rq->getParsedBody();
        $stored_message = new Message();
        $stored_message->content = $data['msg_form_text'];

        $stored_message->save();

        foreach( Equipe::all() as $e) {

            $mail_message= (new \Swift_Message("Message 24h info"))
                ->setFrom([$this->c->settings['mailer']['mail_from']])
                ->setTo([$e->contact_email])
                ->setBody($stored_message->content, 'text/html');

            $this->c->mailer->send($mail_message);

        }

        return $this->getAdminMessage($rq, $rs, null, 'message sent');
    }

    public function getAddUserForm(Request $rq, Response $rs, Array $errors=null, Array $values=null, $m=null) {

        $u= AuthController::getUserProfile();



        $keyname = $this->c->csrf->getTokenNameKey();
        $keyval =  $this->c->csrf->getTokenValueKey();


        return $this->c->view->render($rs, 'admin.user.html.twig' ,
            ['csrf' =>[ 'keyname'=> $keyname,
                'keyval' => $keyval,
                'name' => $rq->getAttribute($keyname),
                'value'=> $rq->getAttribute($keyval)
            ],
                'user'=>$u,
                'errors'=>$errors,
                'v'=>$values,
                'm'=>$m
            ]);


    }

    public function registerUser(Request $rq, Response $rs) {

        $data = $rq->getParsedBody();
        if ($rq->getAttribute('has_errors')) {

            return $this->getAddUserForm($rq,$rs, $rq->getAttribute('errors'), $data);
        }

        $u = new \app24\model\User();
        $u->mail = filter_var( $data['auform_email'], FILTER_SANITIZE_EMAIL);
        $u->passwd = password_hash($data['auform_pass'], PASSWORD_DEFAULT, ['cost'=>12]);
        $u->auth_level = AuthController::ADMIN_LEVEL;
        $u->nom= filter_var( $data['auform_nom'], FILTER_SANITIZE_STRING) ;

        $u->save();

        return $this->getAddUserForm($rq,$rs, null, null, 'utilisateur créé avec succès');


    }


}
