<?php
namespace Rigo\Types;
    
use WPAS\Types\BasePostType;
use WPAS\Messaging\WPASAdminNotifier as Notify;
use WP_Query;

class Tournament extends BasePostType{
    
    const POST_TYPE = 'tournament';
    
    function initialize(){
		add_filter( 'manage_'.self::POST_TYPE.'_posts_columns', [$this,'columns_header'] ) ;
		add_action( 'manage_'.self::POST_TYPE.'_posts_custom_column', [$this,'columns_content'], 10, 2 );
    }
    
	function columns_header( $columns ) {
		$columns = array_merge($columns, [
			'calendar' => 'Calendar'
		]);
	
		return $columns;
	}
	
	function columns_content($column_name, $post_ID) {
	    if ($column_name == 'calendar') {
	        $parent = get_post_ancestors($post_ID);
	        if(isset($parent[0])){
	            $parent = get_post($parent[0]);
	            echo  $parent->post_title;
	        } 
	        else {
	            echo 'none';
	        }
	    }
	}
    static function get($id){
        $post = (array) get_post($id);
        if(!$post) return null;
        if($post['post_type'] != 'tournament') return null;
        $post['structure-sheet'] = get_field('structure-sheet', $id);
        $post['buy-in'] = get_field('buy-in', $id);//
        $post['tournament-time'] = get_field('tournament-time', $id);//
        $post['blinds'] = get_field('blinds', $id);
        $post['tournament-date'] = get_field('tournament-date', $id);//
        $post['starting-stack'] = get_field('starting-stack', $id);
        $post['results-link'] = get_field('results-link', $id);
        $post['casino-id'] = get_field('casino-id', $id);
        return $post;
    }   
    
    public static function getFromCalendar($calId){

        $query = new WP_Query([
        	'post_parent' => $calId,
        	'post_type' => 'tournament',
        	'posts_per_page' => -1
        ]);
        return $query->posts;
    }
}

?>