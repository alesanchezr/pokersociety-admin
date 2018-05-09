<?php
namespace Rigo\Types;
    
use WPAS\Types\BasePostType;
use WPAS\Messaging\WPASAdminNotifier as Notify;

class Casino extends BasePostType{
        static function get($id){
        $post = (array) get_post($id);
        if(!$post) return null;
        if($post['post_type'] != 'casino') return null;
        $post['website'] = get_field('website', $id);//
        $post['location'] = get_field('location', $id);//
        return $post;
    } 
}

?>