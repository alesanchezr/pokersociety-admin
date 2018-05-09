<?php

/**
 * To create new API calls, you have to instanciate the API controller and start adding endpoints
*/
$api = new \WPAS\Controller\WPASAPIController([ 
    'version' => '1', 
    'application_name' => 'ps', 
    'namespace' => 'Rigo\\Controller\\' 
]);


/**
 * Then you can start adding each endpoint one by one
*/
$api->get([ 'path' => '/casino', 'controller' => 'SampleController:getAllCasinos' ]); 
$api->get([ 'path' => '/casino/(?P<id>\d+)', 'controller' => 'SampleController:getSingleCasino' ]); 

$api->get([ 'path' => '/tournament/calendar/(?P<id>\d+)', 'controller' => 'SampleController:getAllTournamentsFromCalendar' ]); 
$api->get([ 'path' => '/tournament', 'controller' => 'SampleController:getAllTournaments' ]); 
$api->get([ 'path' => '/tournament/(?P<id>\d+)', 'controller' => 'SampleController:getSingleTournament' ]); 

$api->get([ 'path' => '/calendar/(?P<id>\d+)', 'controller' => 'SampleController:getCalendar' ]); 
$api->get([ 'path' => '/calendar/', 'controller' => 'SampleController:getAllCalendars' ]); 