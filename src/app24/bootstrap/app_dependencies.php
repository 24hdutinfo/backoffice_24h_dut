<?php
/**
 * File:  api_settings.php
 * Creation Date: 03/02/2017
 * description:
 *
 * @author: canals
 */

return [

    'view' => function( $c ) {
        $view= new \Slim\Views\Twig($c['settings']['template_dir'],
            ['debug' => true, 'cache' => false]
        );
        $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
        $view->addExtension(new Slim\Views\TwigExtension($c->router, $basePath));

        $view->getEnvironment()->addFilter(new \Twig_SimpleFilter('iutname', function ($string) use($c){

            return $c['iuts'][(int)$string -1] ;//['nom'];
        }));
        $view->getEnvironment()->addFunction(new \Twig_SimpleFunction('base_path', function() use($basePath) {
            return $basePath;
        })) ;

        return $view;
    },
    'csrf' => function($c) {
        return new \Slim\Csrf\Guard();
    },

    'iuts' => function( $c ){

        return [

            "CNAM, Conservatoire des arts et métiers ",
            "IUT Aix-Marseille – Aix-en-Provence",
            "IUT Aix-Marseille – Arles",
            "IUT d'Amiens",
            "IUT d'Annecy",
            "IUT de Bayonne",
            "IUT de Belfort-Montbeliard",
            "IUT de Blagnac",
            "IUT de Bordeaux",
            "IUT de Caen",
            "IUT de Calais",
            "IUT de Clermont-Ferrand - Aubière",
            "IUT de Clermont-Ferrand - Le Puy En Velay",
            "IUT de Dijon/Auxerre",
            "IUT du Havre",
            "IUT de Grenoble",
            "IUT de La Rochelle",
            "IUT de Lannion",
            "IUT de Laval",
            "IUT de Lens",
            "IUT de Lille",
            "IUT du Limousin",
            "IUT de Lyon 1 - Bourg en Bresse",
            "IUT de Lyon 1 - Villeurbanne",
            "IUT de Marne-la-Vallée",
            "IUT de Metz",
            "IUT de Montpellier",
            "IUT de Montreuil",
            "IUT Nancy-Charlemagne",
            "IUT de Nantes",
            "IUT de Nice Côte d'Azur",
            "IUT d'Orléans",
            "IUT d'Orsay",
            "IUT Paris Descartes",
            "IUT de Reims",
            "IUT de Rodez",
            "IUT Robert Schuman – Strasbourg",
            "IUT de Saint-Dié des Vosges",
            "IUT de Sénart-Fontainebleau",
            "IUT de Toulouse – Paul Sabatier",
            "IUT de Valence",
            "IUT de Valenciennes Maubeuge",
            "IUT de Vannes",
            "IUT de Vélizy",
            "IUT de Villetaneuse"
        ];
    },

    'mailer' => function($c) {

        return new \Swift_Mailer(
            new \Swift_SmtpTransport($c['settings']['mailer']['smtp_host'],
                                     $c['settings']['mailer']['smtp_port']));
    }

] ;

