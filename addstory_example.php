<?php
	
	require "pivotal.php";
	require_once("PHP-iSDK-master/src/isdk.php");

	//Create an instance of iSDK
	$app = new iSDK;

	//Assign values from the http request
	$contactId = $_POST['contactId'];
	$lastName = $_POST['lastName'];
	$firstName = $_POST['firstName'];
	$tag = $_POST['tag'];

	if($app ->cfgCon("connectionName")){

		
		//custom points array
		$customPointsField = array(/*'Custom Response WP'=>'_CustomResponseWP',*/
								   'SEO Phase 2 Page Creation'=> '_SEO2PageCreation',
								   'Additional Work' => '_AdditionalWorkHours');


		//Set custom points to be posted on Pivotal Tracker
		if(array_key_exists($tag,$customPointsField)){



			//Get StageID
			$tempReturn = array('Id','StageName');
			$tempQuery = array('Id'=>'%');
			$stageResult = $app->dsQuery('Stage',1000,0,$tempQuery,$tempReturn);
			//Holds the stage Id that resulted in a sales
			for($i = 0; $i < count($stageResult); $i++){
				if($stageResult[$i]['StageName']=='Agent - Won' || $stageResult[$i]['StageName']=='Broker - Won'){
					$stageIdArray [] =  $stageResult[$i]['Id'];
				}
			}

			//Retrieve all the opportunities from Infusionsoft
			$returnFields = array('Id',$customPointsField[$tag],'LastUpdated','StageID','_AdditionalWork010','_SEOMarket1');
			$query = array('ContactId' => $contactId);
			$returnValues = $app->dsQuery('Lead',1000,0,$query,$returnFields);
			var_dump($returnValues);

			//Look for the Stage ID that resulted in sales and find the most recent Opporutnity
			$maxIndex = 0;
			$start = false;

			for ($i = 0; $i < count($returnValues); $i++){
				if(in_array($returnValues[$i]['StageID'],$stageIdArray)){
					if($start){
						$maxTime = strtotime($returnValues[$maxIndex]['LastUpdated']);
						$currentTime =strtotime($returnValues[$i]['LastUpdated']);
						if($maxTime < $currentTime){
							$maxIndex = $i;
						}
					} else {
						$start = true;
						$maxIndex = $i;
					}
				}
			}
			


			//Get the most recent Opportunity
	//		$recent = $returnValues[count($returnValues)-1];
			//Assign the points
			if($tag == 'Additional Work' ){
				$descriptionIndex =  '_AdditionalWork010';
			} elseif ($tag == 'SEO Phase 2 Page Creation'){
				$descriptionIndex = '_SEOMarket1';
			}
			$points = $returnValues[$maxIndex][$customPointsField[$tag]];
			$description = $returnValues[$maxIndex][$descriptionIndex];



		} else {
			if($tag == 'SEO Starter'){
				$points = 4;

			} elseif(empty($point)) {
				$points = 3;

			}
		}
		
		//Information needed to create a new story on pivotal tracker
		$token = 'YOUR TOKEN ID';
		$projectId = 'YOUR PROJECT ID';

	
		//Create an instance of the class
		$pivotal = new pivotal($token,$projectId);

		try{

			$pivotal->authenticateUser();
			//Add a new story
			$title =  $firstName . ' '. $lastName . ' ' . $tag . ' '. $contactId;

			$story = $pivotal->addStory($title,$description,'chore',$points);
			//Get label Id
			$label = $pivotal->getLabelIdWithName(strtolower($tag));
			//Add label to story
			$add = $pivotal->addLabel($story['id'],$label);
				

		} catch (Exception $ex){
			$error = $ex->getMessage();
			sendErrorEmail($error,$points);
			die();
	
		}


	}
	//Failed connect to Infusionsoft, notify by email
	else {
		$to = "EMAIL HERE";
		$subject = 	'Infusionsoft Credential Error';
		$message = 	"Please make sure permissions are setup correctly in the Infusionsoft Software Developer Kit. The file is located in iSDK in the src/conn.cfg.php\r\n"
					."Manually post/update/check to Pivotal Tracker/: {$firstName} {$lastName}: {$tag}. \r\n"
					. "Thank you,";
		mail($to,$subject,$message);

	}
	//Pivotal Tracker connection Error
	function sendErrorEmail($errorMess,$points){
		$to = "EMAIL HERE";
		$subject = 	"Pivotal Tracker Posting Error\r\n";
		$message = 	"The following occured: \r\n".
					"{$errorMess}\r\n".
					"Please post/update/check story on Pivotal Tracker manually.\r\n".
					"Failed story for: {$_POST['firstName']} {$_POST['lastName']} - {$_POST['tag']} {$points} point(s) and Infusionsoft ID: {$_POST['contactId']}.\r\n".
					"Please notify programmer/system administrator to look into the problem." ;
		mail($to,$subject,$message);

	}

	function queryDescription($array){
		global $app, $contactId;

			if($app ->cfgCon("connectionName")){
				$data = dsQuery('Lead',1000,0,$array);
			} else {

			}

	}
?>

 


