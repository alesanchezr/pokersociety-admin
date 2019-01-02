<?php
namespace Rigo\Controller;

use Rigo\Types\Calendar;
use Rigo\Types\Tournament;
use Rigo\Types\Casino;
use  WPAS\Settings\WPASThemeSettingsBuilder;

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
    
    public function getSettings(){
        $settings = [
            'mantainance_mode' => get_option( 'mantainance-mode' )
        ];
        return $settings;
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
    
    public function signup(\WP_REST_Request $request){
        $body = (array) json_decode($request->get_body());
        if(!$body) return new \WP_Error( 'invalid_params', 'Invalid body params', array( 'status' => 400 ) );
        
        
        if(empty($body['username'])) return new \WP_Error( 'invalid_username', 'Invalid Username', array( 'status' => 400 ) );
        if(empty($body['password'])) return new \WP_Error( 'invalid_password', 'Invalid Password', array( 'status' => 400 ) );
        if(empty($body['email'])) return new \WP_Error( 'invalid_email', 'Invalid Email', array( 'status' => 400 ) );
        
        $user_id = username_exists( $body['username'] );
        if ( !$user_id and email_exists($body['email']) == false ) {
        	$body["user_id"] = wp_create_user( $body['username'], $body['password'], $body['email'] );
        } else {
        	return new \WP_Error( 'already_exists', 'User already exists.', array( 'status' => 400 ) );
        }
        
        return $body;
    }
    
}
?>