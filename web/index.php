<?php
	require('../vendor/autoload.php');
	
	//connects to mongodb hosted at mlabs
	$uri = "mongodb://sirmiq:door5454@ds119718.mlab.com:19718/fproject";
	$client = new MongoDB\Client($uri);
	$db = $client->fproject;
	$users = $db->users;

	
	//gathers data sent
	$method = $_SERVER['REQUEST_METHOD'];
	$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
	$doc = preg_replace('/[^a-z0-9_]+/i','', array_shift($request));
	$gId = 	preg_replace('/[^a-z0-9_]+/i','', array_shift($request));

	
	if($method == "POST")
	{
		 $datas = json_decode(file_get_contents("php://input"), true);
	}

	if(doc == "users")
	{
		if($method == "GET")
		{
			if(empty($gId))
			{			
				$data = $users->find();
					
				foreach($data as $user)
				{
					$list[] = $team["name"];
				}			
			}

			else
			{
				$query = array('gId' => $gId);
				$data= $users->findOne($query);	
				
				if(empty($data))
				{
					$data = array("response" => "No user with that gId was found.");
				}	
			}	
			
			header('Content-type: application/json');
			echo json_encode($data);	
		}	
		
		if($method == "POST")
		{
			$query = array('gId' => $data['gId']);
			$unique = $users->findOne($query);	
			
			if($unique)
			{
				//creates response to send to client
				$response = array("response" => "The user must me unique.  Entry not added.");					
				header('Content-type: application/json');
				echo json_encode((object)$response);
			}				
				
			else
			{	
				$addUser = array(
				'gId' => $data['gId'],
				'fname' => $data['fname'],					
				'lname' => $data['lname'],		
				'division' => $data['division']);
					
				$users->insertOne($addUser);
					
				//creates response to send to client
				$response = array('response' => 'user was added');
				header('Content-type: application/json');
				echo json_encode((object)$response);
					
			}			
		}	
	}	
	
	
?>	