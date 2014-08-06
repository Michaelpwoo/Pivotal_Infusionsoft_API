<?php


	require_once("PHP-iSDK-master/src/isdk.php");
	$app = new iSDK;

	$postedData = json_decode(file_get_contents('php://input'), TRUE);

	$title = $postedData['highlight'];
	$storyTitle = $postedData['primary_resources'][0]['name'];
	$storyId = $postedData['primary_resources'][0]['id'];
	$id = getId($storyTitle);

	//Check if Id is numeric
	if(!is_numeric($id)){
		die();
	}

	$bundleName = getBundle($storyTitle);


	if($title == 'started'){
		//Overwrite message because default messafge will be 'name' started this chore
		$header = 'Project Started: ' . $bundleName;
		$message = "Pivotal Tracker ID: {$storyId}";

	} elseif($title == 'accepted'){
		$header = 'Project Finished: '. $bundleName;
		$message = "Pivotal Tracker ID: {$storyId}";

	}elseif($title == 'added comment:'){
		$header = 'Updates: '.$bundleName . " {$storyId}";
		$message = $postedData['message'];
	}

	//header is not initialized  means no updates needed
	if(!isset($header) || !isset($message)){
		die();
	}
	//Connect to Infusionsoft and update notes
	if($app ->cfgCon("connectionName")){
		$conData = array('ContactId' => $id, 'ActionDescription' => $header, 'CreationNotes' =>$message);
		$data = $app->dsAdd('ContactAction',$conData);
		//add tag to notify Infusionsoft project in done
		$idTagUpdate = getTag('Updates From Pivotal');
		$idTagPost = getTag('Posted On Pivotal Tracker');
		if($title == 'accepted'){
			//remove update tag
			$app->grpRemove($id,$idTagUpdate);
		} elseif ($title == 'started'){
			//read update tag and remove the post tag
			$app->grpAssign($id,$idTagUpdate);
			$app->grpRemove($id,$idTagPost);
		}
	//Failed to connection to Infusionsoft noticy via email
	}else{
			$to = "EMAIL HERE";
			$subject = 	'Infusionsoft Credential Error';
			$message = 	"Please make sure permissions are setup correctly in the Infusionsoft Software Developer Kit. The file is located in iSDK in the src/conn.cfg.php\r\n"
						."Pivotal Tracker web hook (updates) could not be posted on Infusionsoft. \r\n"
						. "Thank you,";
			mail($to,$subject,$message);
	}


		//Pass in title of story and retrieve the Id
		function getId($string){
			$array = explode(' ', $string);
			$id = $array[count($array)-1];
			return $id;
		}

		//Returns name of bundle. -3 is to remove first name, last name, and id
		function getBundle($string){
			$array = explode(' ',$string);
			$length = count($array) - 3;
			$bundleArray = array_slice($array, 2,$length);
			$bundle = implode(' ',$bundleArray);
			return $bundle;

		}

		function getTag($name){
			global $app;
			if($app ->cfgCon("connectionName")){
				$fields = array('Id');
				$query = array('GroupName' => $name);
				$result = $app->dsQuery('ContactGroup',1000,0,$query,$fields);
				$tag = $result[0]['Id'];
				return $tag;
			} else {
				echo ' failed';
			}
		}

?>