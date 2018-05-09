<?php
namespace Rigo\Types;
    
use WPAS\Types\BasePostType;
use Exception;
use WPAS\Messaging\WPASAdminNotifier as Notify;

class Calendar extends BasePostType{
    
    const POST_TYPE = 'calendar';
    
    function initialize(){
        add_action('acf/save_post', [$this,'slug_save_post_callback'],1);
        //add_action( 'save_post_'.self::POST_TYPE, array($this,'slug_save_post_callback'), 10, 3 );
    }

	//function slug_save_post_callback( $post_ID, $post, $update ) {
	function slug_save_post_callback( $post_ID ) {
	    // allow 'publish' only
	    if (!isset($_POST['action']) || $_POST['action']!='editpost') return;
	    if(!isset($_POST['post_type']) || $_POST['post_type']!=self::POST_TYPE) return;
	    if(!isset($_POST['acf'])) return;

	    $csvFile = get_post($_POST['acf']['field_5aebd04c137e4']);
	    $csvURL = $csvFile->guid;
	    
	    $csvFile = get_post($_POST['acf']['field_5aebd04c137e4']);
		$forceUpdate = (isset($_POST['acf']['field_5aebd07c137e5']) && $_POST['acf']['field_5aebd07c137e5']==true);
	    
	    $jsonURL = $this->csvToJSON($post_ID,$csvURL,$forceUpdate);
	    // re-hook this function
	    add_action( 'save_post_'.self::POST_TYPE, array($this,'slug_save_post_callback'), 10, 3 );
	}

	function csvToJSON($post_ID,$url,$forceUpdate = false){
		
		try{
			if(empty($url)) throw new Exception('The calendar has no new CSV file to upload');
			
			$array = array_map('str_getcsv', file($url));
			if(!$array) throw new Exception('The CSV has invalid caracters or format');
			//print_r($array);die();
			//I have to make sure that the CSV will be encoded successfully later
			$result = json_encode($array);
			if(!$result) throw new Exception('The CSV has invalid caracters or format');
			
			//Create the tournaments posts into wordpress
			if($this->validateTournaments($array)) $tournaments = $this->createTournaments($array, $forceUpdate);
			
			//Encode into a json and save it in the uploads folder
			$jsonURL = $this->saveJSON($post_ID,$tournaments);
		}
		catch(Exception $e)
		{
			Notify::error($e->getMessage());
		}
		
		return $tournaments;
	}
	
	function saveJSON($post_ID,$data){
		$upload = wp_upload_dir();
		
		if(!file_exists($upload['basedir'].'/static/')) mkdir($upload['basedir'].'/static/', 0777, true);
		
		$uploadPath = $upload['basedir'].'/static/poker-calendar-'.$post_ID.'.json';
		$fp = fopen($uploadPath, 'w+');
		if($fp)
		{
			$result = fwrite($fp, json_encode($data));
			fclose($fp);
			if(!$result) throw new Exception('Could not write on the calendar.json file');
			
			return $uploadPath;
		}
		else throw new Exception('Could not open or create the calendar.json file');
	}
	
	function validateTournaments($tournaments){

		$errors = [];
		
		if(!$tournaments or count($tournaments)==0) $errors[] = 'No tournaments found in the CSV or the format was incorrect';
		if($tournaments[0] and !isset($tournaments[0][12])) $errors[] = 'The CSV needs to have 13 columns exactly';
		
		if(count($errors))
		{
			for($i=0;$i<count($tournaments);$i++)
			{
				if($i==0) continue;//it's the header of the CSV table
				
				$t = $tournaments[$i];
				
				//at least the tournament ID of the h1 needs to have a value
				if(empty($t[10]) and empty($t[11])) $errors[] = "The row $i has no tournament_id and no h1 either";
				
				//If there is a tournament ID we are going to try to re-use the same from the DB
				if(!empty($t[10]) and is_numeric($t[10])){
					
					//If the tournament is not in our database
					if(!get_post($t[10])) $errors[] = "The tournament_id in the row $i was not found in the Database.";
					//if it is, then we skip this update
					else continue;
				}//it means that the tournament is already created, it is goign to be re-used form a past tournament
				
				if(empty($t[11])) $errors[] = "The h1 in the row $i is empty";
				else
				{
					$post = get_page_by_title($t[11], OBJECT, 'tournament');
					if($post) $errors[] = "A tournament with the same h1 as the one in row $i was found in the Database. You should set that tournament_id";
				}
				
			}
		}
		
		if(count($errors)>20) throw new Exception('More than 20 errors where found in the calendar, here is a few: '.$this->arrayToHTML($errors));
		if(count($errors)>0) throw new Exception('The calendar was not imported because the following erros have been found: '.$this->arrayToHTML($errors));
		
		return true;
	}
	
	private function arrayToHTML($array){
		$content = '<ul>';
		$i = 0;
		while($i < count($array) and $i < 20) 
		{
			$content .= '<li>'.$array[$i].'</li>';
			$i++;
		}
		$content .= '</ul>';
		
		return $content;
	}
	
	function createTournaments($tournaments, $forceUpdate = false){
		
		$changes = [];
		$changes['updated'] = 0;
		$changes['created'] = 0;
		$changes['ignored'] = 0;
		$totalTournaments = count($tournaments);
		
		for($i=0;$i<$totalTournaments;$i++)
		{
			if($i==0) continue;//it's the header of the CSV table
			$t = $tournaments[$i];
			if(!isset($t[13])) $t[13] = null;
			
			if(!$forceUpdate){
				if(!empty($t[10]) and is_numeric($t[10])){
					$changes['ignored'] += 1;
					continue;
				}
			}
			
			$data = [
				'post_content' => $t[12],
				'post_title' => $t[11],
				'post_name' => sanitize_title_with_dashes($t[11].'-'.$t[0]),
				'post_status' => 'publish',
				'post_type' => 'tournament',
				'post_parent' => $t[9]//casino id
				];
				
			$post = null;
			if($forceUpdate and !empty($t[10]) and is_numeric($t[10])) $post = get_post($t[10]);
			//If there is a tournament with that title, update it.
			//if(!$post) $post = get_page_by_title($t[11], OBJECT, 'tournament');
			
			if($post) 
			{
				$data['ID'] = $post->ID;//update the post then
				$this->setCalendarCustomFields($data['ID'], array(
					'tournament-date'	=> $t[0],
					'tournament-time'	=> $t[2],
					'structure-sheet'	=> $t[8],
					'buy-in'			=> $t[5],
					'starting-stack'	=> $t[6],
					'blinds'			=> $t[7],
					'casino-id'			=> $t[9],
					'results-link'	=> $t[13]
					));
				$tournaments[$i][10] = $data['ID'];
				
				$changes['updated'] += 1;
			}
			//if there is not, create it.
			else
			{
				$newId = wp_insert_post($data,true);//true because we want a WP_Error on failure
				if($newId){
					$this->setCalendarCustomFields($newId, array(
						'tournament-date'	=> $t[0],
						'tournament-time'	=> $t[2],
						'structure-sheet'	=> $t[8],
						'buy-in'			=> $t[5],
						'starting-stack'	=> $t[6],
						'blinds'			=> $t[7],
						'casino-id'			=> $t[9],
						'results-link'	=> $t[13]
						));
					$tournaments[$i][10] = $newId;//now that we have a tournament ID, we can set it into the array
					$changes['created'] += 1;
				}
				else throw new Exception('There was an error trying to create the row:'.$i.'. Tournament '.$t[11]);
			}
			
		}
		
		$totalTournaments--;
		//WPASAdminNotifier::addTransientMessage(Utils\BCNotification::ERROR,'There has been an error');
		Notify::info($changes['ignored'].' out of '.$totalTournaments.' posts where ignored (because they had known ID).');
		Notify::info($changes['created'].' out of '.$totalTournaments.' posts where created (because they had uknown ID and H1).');
		
		$updateReason = '';
		if($forceUpdate) $updateReason = ' because the "Force Update" options was enforced';
		Notify::info($changes['updated'].' out of '.$totalTournaments.' posts where updated ('.$updateReason.').');
		
		return $tournaments;
	}

	private function setCalendarCustomFields($postId, $fields){
		foreach($fields as $key => $val) $this->setCustomField($postId, $key, $val);
	}
	
	private function setCustomField($postId, $key, $value){
		//if(!empty($value)) update_post_meta( $postId, 'wpcf-'.$key, $value ); 
		if(!empty($value)) update_field($key, $value, $postId);
	}
}

?>