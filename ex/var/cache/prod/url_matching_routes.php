<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
        '/e01' => [[['_route' => 'e01_index', '_controller' => 'App\\Controller\\E01Controller::index'], null, null, null, false, false, null]],
        '/e01/need_auth' => [[['_route' => 'e01_need_auth', '_controller' => 'App\\Controller\\E01Controller::need_auth'], null, null, null, false, false, null]],
        '/e01/welcome' => [[['_route' => 'e01_welcome', '_controller' => 'App\\Controller\\E01Controller::welcome'], null, null, null, false, false, null]],
        '/e01/register' => [[['_route' => 'app_register', '_controller' => 'App\\Controller\\RegistrationController::register'], null, null, null, false, false, null]],
        '/e01/login' => [[['_route' => 'app_login', '_controller' => 'App\\Controller\\SecurityController::login'], null, null, null, false, false, null]],
        '/e01/logout' => [[['_route' => 'app_logout', '_controller' => 'App\\Controller\\SecurityController::logout'], null, null, null, false, false, null]],
    ],
    [ // $regexpList
    ],
    [ // $dynamicRoutes
    ],
    null, // $checkCondition
];
