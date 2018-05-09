<?php
namespace Rigo\Types;
    
use WPAS\Types\BasePostType;
use WPAS\Messaging\WPASAdminNotifier as Notify;

class Tournament extends BasePostType{
    
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
}

?>