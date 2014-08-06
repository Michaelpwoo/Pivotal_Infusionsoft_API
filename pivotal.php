<?php
	class pivotal {

		//Private var
		private $token;
		private $productId;

		//--Constructor--
		//Requires token and product Id
		function pivotal($token, $productId){
			$this->token = $token;
			$this->productId = $productId;
		}


		//--addStory
		//Create a new story with name and description
		public function addStory($name, $description, $story_type,$estimate/*,$label*/){
		
			//Create an array with the data to send
			$fields = array('name' => $name,
						'description' => $description,
						'story_type' => $story_type,
						'estimate' => $estimate,
						);

			$fields_string = '';
			//Prepare data to be sent
			foreach($fields as $key=>$value) { 
				$fields_string .= $key.'='.$value.'&'; 
			}



			//Initialize curl
			$ch = curl_init("https://www.pivotaltracker.com/services/v5/projects/{$this->productId}/stories");
			//Set sending option for cUrl
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-TrackerToken: {$this->token}"));
			echo "<br>";
			echo "token ID: {$this->token}<br>";
			echo "Product ID: {$this->productId}<br>";
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POST, count($fields));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

			//Store the result
			$result = curl_exec($ch);
			//Convert to Json
			$json = json_decode($result,true);
			//Close curl
			curl_close($ch);
			if($json['kind']=='error'){
				$error = 'Error: '."{$json['error']}";
				throw new Exception($error);
			}
			return $json;
		}

		//Gets the id of a label in the current project
		public function getLabelIdWithName($name) {
			//Retrieve all labels in the project
			$ch = curl_init("https://www.pivotaltracker.com/services/v5/projects/{$this->productId}/labels?date_format=millis");
			//Set sending option
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-TrackerToken: {$this->token}"));
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

			$result = curl_exec($ch);
			//Store data in array
			$json = json_decode($result,true);
			curl_close($ch);
			if($json['kind']=='error'){
				$error = 'Error: '."{$json['error']}";
				throw new Exception($error);
			}
			
			//Find the correct name and returns the ID
			$counter = 0;
			foreach ($json as $entry){	 
				foreach ($entry as $key => $value) {
					if($key == 'name' && $value == $name){
						$id = $json[$counter]['id'];
						echo $id;
						return $id;
					}

				}
				$counter++;
			}

		}

		public function addLabel($story,$id){

			$ch = curl_init("https://www.pivotaltracker.com/services/v5/projects/{$this->productId}/stories/{$story}/labels");
			//Set sending option for cUrl
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-TrackerToken: {$this->token}"));

			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "id={$id}&");
			$result = curl_exec($ch);
			$json = json_decode($result,true);
			curl_close($ch);
			if($json['kind']=='error'){
				$error = 'Error: '."{$json['error']}";
				throw new Exception($error);
			}

			return $json;
		}
	
		public function getComments($story){
	
			$ch = curl_init("https://www.pivotaltracker.com/services/v5/projects/{$this->productId}/stories/{$story}/comments");
			//Set sending option for cUrl
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-TrackerToken: {$this->token}"));
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			$result = curl_exec($ch);
			$json = json_decode($result,true);
			curl_close($ch);
			if($json['kind']=='error'){
				$error = 'Error: '."{$json['error']}";
				throw new Exception($error);
			}

			return $json;
		}
		
		public function getStory($story){
	
			$ch = curl_init("https://www.pivotaltracker.com/services/v5/projects/{$this->productId}/stories/{$story}");
			//Set sending option for cUrl
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-TrackerToken: {$this->token}"));
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			$result = curl_exec($ch);
			$json = json_decode($result,true);
			curl_close($ch);
			if($json['kind']=='error'){
				$error = 'Error: '."{$json['error']}";
				throw new Exception($error);
			}
			return $json;
		}

		public function authenticateUser(){

			$ch = curl_init("https://www.pivotaltracker.com/services/v5/me");
			//Set sending option for cUrl
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-TrackerToken: {$this->token}"));
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
			$result = curl_exec($ch);
			$json = json_decode($result,true);
			curl_close($ch);
			if($json['kind']=='error'){
				$error = 'Error: '."{$json['error']}";
				throw new Exception($error);
			}
			return $json;
		}

	}

?>