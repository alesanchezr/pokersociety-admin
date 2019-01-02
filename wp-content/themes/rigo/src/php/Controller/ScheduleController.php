<?php
namespace Rigo\Controller;

use Rigo\Types\Calendar;
use Rigo\Types\Tournament;
use Rigo\Types\Casino;
use Exception;
use  WPAS\Settings\WPASThemeSettingsBuilder;

class ScheduleController{
    
    public function getUserSchedules(\WP_REST_Request $request){
        
        if(empty($request['username']))  return new \WP_Error( 'invalid_username', 'Invalid Username', array( 'status' => 400 ) );
        
        $schedules = [];
        try{
            $schedules = $this->_getSchedules($request['username']);
            return $schedules;
        }
        catch(Exception $e){
            return new \WP_Error( 'error', $e->getMessage(), array( 'status' => $e->getCode() ) );
        }
    }
    
    public function saveUserSchedules(\WP_REST_Request $request){
        $schedules = (array) json_decode($request->get_body());
        if(!$schedules) return new \WP_Error( 'invalid_params', 'Invalid body params', array( 'status' => 400 ) );
        
        if(empty($request['username']))  return new \WP_Error( 'invalid_username', 'Invalid Username', array( 'status' => 400 ) );
        
        $user_id = username_exists( $request['username'] );
        if(!$user_id) return new \WP_Error( 'invalid_user', 'Invalid User', array( 'status' => 400 ) );
        
        try{
            $schedules = $this->_validateSchedules($schedules);
            $this->_saveSchedules($request['username'], $schedules);
            return $schedules;
        }
        catch(Exception $e){
            return new \WP_Error( 'error', $e->getMessage(), array( 'status' => $e->getCode() ) );
        }
        
    }
    
    private function _validateSchedules($schedules){
        if(!is_array($schedules)) throw new Exception('The user schedules must be an array', 400);
        $ids = [];
        for($i = 0; $i<count($schedules); $i++){
            $schedule = (array) $schedules[$i];
            if(empty($schedule["id"])) throw new Exception('Missing schedule id', 400);
            else if(in_array($schedule["id"], $ids)) throw new Exception('Repeated id for schedule '.$schedule["id"], 400);
            
            $ids[] = $schedule["id"];
            if(empty($schedule["name"])) throw new Exception('Missing schedule name for schedule '.$schedule["id"], 400);
            if(empty($schedule["total"])) throw new Exception('Missing schedule total for schedule '.$schedule["id"], 400);
            if(empty($schedule["attempts"])) throw new Exception('Missing schedule attempts for schedule '.$schedule["id"], 400);
            $total = 0;
            
            for($j = 0; $j<count($schedule["attempts"]); $j++){
                $a = (array) $schedule["attempts"][$j];
                $total = intval($a["price"]) * intval($a["bullets"]);
                if(empty($a["tournamentName"])) throw new Exception('Missing attempt tournamentName', 400);
                if(empty($a["tournamentId"])) throw new Exception('Missing attempt tournamentId', 400);
                if(empty($a["price"])) throw new Exception('Missing attempt price', 400);
                if(empty($a["bullets"])) throw new Exception('Missing attempt bullets', 400);
                $schedule["attempts"][$j] = $a;
            }
            $schedule["total"] = $total;
            $schedules[$i] = $schedule;
        }
        return $schedules;
    }
    
    private function _getSchedules($userName){
        
        $user_id = username_exists( $userName );
        if(!$user_id) throw new Exception('Schedules not found for user '.$userName, 404);
        
		$upload = wp_upload_dir();
		
		if(!file_exists($upload['basedir'].'/static/schedules/'.$userName.'.json'))
		    throw new Exception('Schedules not found for user '.$userName, 404);
		
		$content = @file_get_contents($upload['basedir'].'/static/schedules/'.$userName.'.json', 'r');
		if($content)
		{
			$schedules = json_decode($content);
			if(!$schedules) throw new Exception('Invalid schedules format', 500);
			
			return $schedules;
		}
		else throw new Exception('Could not open the user schedules', 500);
	}
    
    private function _saveSchedules($userName,$schedules){
		$upload = wp_upload_dir();
		
		$schedules = json_encode($schedules);
		if(!$schedules) throw new Exception('Invalid schedules format', 500);
		
		if(!file_exists($upload['basedir'].'/static/')) mkdir($upload['basedir'].'/static/', 0777, true);
		if(!file_exists($upload['basedir'].'/static/schedules')) mkdir($upload['basedir'].'/static/schedules/', 0777, true);
		
		$uploadPath = $upload['basedir'].'/static/schedules/'.$userName.'.json';
		$fp = fopen($uploadPath, 'w+');
		if($fp)
		{
			$result = fwrite($fp, $schedules);
			fclose($fp);
			if(!$result) throw new Exception('Could not write on the schedules', 500);
			
			return $uploadPath;
		}
		else throw new Exception('Could not open or create the schedules', 500);
	}
    
}
?>