<?php
namespace Rigo\Types;
    
use WPAS\Types\BasePostType;
use WPAS\Messaging\WPASAdminNotifier as Notify;

class Casino extends BasePostType{
        
    const POST_TYPE = 'casino';
    
    function initialize(){
	add_filter( 'manage_'.self::POST_TYPE.'_posts_columns', [$this,'columns_header'] ) ;
	add_action( 'manage_'.self::POST_TYPE.'_posts_custom_column', [$this,'columns_content'], 10, 2 );
    }
    
    static function get($id){
        $post = (array) get_post($id);
        if(!$post) return null;
        if($post['post_type'] != 'casino') return null;
        $post['website'] = get_field('website', $id);//
        $post['location'] = get_field('location', $id);//
        return $post;
    } 
    
	function columns_header( $columns ) {
		$columns = array_merge($columns, [
			'preview' => 'Preview'
		]);
	
		return $columns;
	}
	function columns_content($column_name, $post_ID) {
	    if ($column_name == 'preview') {
	        echo '<a target="_blank" href="'.PUBLIC_URL.'/casino/'.$post_ID.'?preview">Go to live preview</a>';
	    }
	}
}

?>