<?php
namespace Rigo\Controller;

use Rigo\Types\Calendar;
use Rigo\Types\Tournament;
use Rigo\Types\Casino;

class SampleController{
    
    public function getHomeData(){
        return [
            'name' => 'Rigoberto'
        ];
    }
    
    public function getAllCasinos(){
        $query = Casino::all([
            'posts_per_page' => -1
        ]);
        return $query->posts;
    }
    
    public function getAllTournaments(){
        $query = Tournament::all([
            'posts_per_page' => -1
        ]);
        return $query->posts;
    }
    
    public function getAllCalendars(){
        $query = Calendar::all([
            'posts_per_page' => -1
        ]);
        return $query->posts;
    }
    
    public function getAllTournamentsFromCalendar(\WP_REST_Request $request){

        $post = get_post($request['id']);
        if(!$post || $post->post_type != 'calendar') return new \WP_Error( 'no_calendar', 'Invalid calendar', array( 'status' => 404 ) );
        
        $content = file_get_contents(API_HOST.'/wp-content/uploads/static/poker-calendar-'.$post->ID.'.json');
        if(!$content) return new \WP_Error( 'no_calendar', 'Unable to find calendar file', array( 'status' => 404 ) );
        
        $contentArray = json_decode($content);
        if(!$contentArray) return new \WP_Error( 'no_calendar', 'Invalid Calendar JSON Formar', array( 'status' => 404 ) );
        
        array_shift($contentArray);
        return $contentArray;
    }
    
    public function getCalendar(\WP_REST_Request $request){

        $post = get_post($request['id']);
        if($post->post_type == 'calendar') return $post;
        else return new \WP_Error( 'no_calendar', 'Invalid calendar', array( 'status' => 404 ) );
    }
    
    public function getSingleTournament(\WP_REST_Request $request){
        $post = Tournament::get($request['id']);
        if(!$post) return new \WP_Error( 'no_tournament', 'Invalid Tournament', array( 'status' => 404 ) );
        return $post;
    }
    
    public function getSingleCasino(\WP_REST_Request $request){
        $post = Casino::get($request['id']);
        if(!$post) return new \WP_Error( 'no_casino', 'Invalid Casino', array( 'status' => 404 ) );
        return $post;
    }
    
}
?>