<?php
	require('../vendor/autoload.php');
	//connects to mongodb hosted at mlabs
	$uri = "mongodb://sirmiq:door5454@ds119718.mlab.com:19718/fproject";
	$client = new MongoDB\Client($uri);
	$db = $client->fproject;
	$users = $db->users;
	$places = $db->places;
	
	
	//gathers data sent
	$method = $_SERVER['REQUEST_METHOD'];
	$request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
	$doc = preg_replace('/[^a-z0-9_]+/i','', array_shift($request));
	$gId = preg_replace('/[^a-z0-9_]+/i','', array_shift($request));
	$place = preg_replace('/[^a-z0-9_]+/i','', array_shift($request));
	$pname = preg_replace('/[^a-z0-9_]+/i','', array_shift($request));
	
	if($method == "GET")
	{
		if(empty($gId))
		{			
			$data = $users->find();
			header('Content-type: application/json');
			echo json_encode(iterator_to_array($data));
		}
		
		else if(empty($place))
		{
			$query = array('gId' => $gId);
			$data= $users->findOne($query);	

			
				if(empty($data))
				{
					$data = array("response" => "User does not exist.");
					header('Content-type: application/json');
					echo json_encode((object)($data));
				}	
				
				else
				{
					header('Content-type: application/json');
					echo json_encode($data);	
				}
		}	
				
		else
		{
			$query = array("gId" => $gId);
			$data= $places->find($query);	
			
				if(empty($data))
				{
					$data = array("response" => "User has no places.");
					header('Content-type: application/json');
					echo json_encode((object)($data));
				}
				
			else
			{		
				header('Content-type: application/json');
				echo json_encode(iterator_to_array($data));
			}	
		}	
	}	
				
	
	else if($method == "POST")
	{
		$data = json_decode(file_get_contents("php://input"), true);
		
		if(empty($gId))
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
				'email' => $data['email']);
					
				$users->insertOne($addUser);
					
				//creates response to send to client
				$response = array('response' => 'user was added');
				header('Content-type: application/json');
				echo json_encode((object)$response);
					
			}			
		}
		
		else if($place)
		{
			$query = array('name' => $data['name']);
			$unique = $places->findOne($query);	
			
			if($unique)
			{
				//creates response to send to client
				$response = array("response" => "The user has already included this place.  Entry not added.");					
				header('Content-type: application/json');
				echo json_encode((object)$response);
			}	
			
			else
			{	
				$addPlace = array(
				'gId' => $data['gId'],
				'category' => $data['category'],	
			    'name' => $data['name'],
				'address' => $data['address'],		
				'rating' => $data['rating'],
				'comments' => $data['comments']);
					
				$places->insertOne($addPlace);
					
				//creates response to send to client
				$response = array('response' => 'Place was added');
				header('Content-type: application/json');
				echo json_encode((object)$response);
					
			}				
			
		}	
	}
	
	else if($method == "PUT")
	{		
		$data = json_decode(file_get_contents("php://input"), true);
		if(empty($gId))
		{
			$data = array("response" => "Must give a gId to Change a user, and a gId and name to Change a place.");
			header('Content-type: application/json');
			echo json_encode((object)($data));
		}
		
		else if(empty($place))
		{
			$query = array('gId' => $gId);
				$replaceUser = array(
				'gId' => $data['gId'],
				'fname' => $data['fname'],					
				'lname' => $data['lname'],		
				'email' => $data['email']);
			$users->replaceOne($query, $replaceUser);
			
			$data = array("response" => "User Info Updated");
			header('Content-type: application/json');
			echo json_encode((object)($data));					
		}
		
		else if(empty($pname))
		{
			$data = array("response" => "Must provide place name");
			header('Content-type: application/json');
			echo json_encode((object)($data));	
		}	
		
		else
		{
			$query = array($and, 'gId' => $gId, 'name' => $pname);
			
			$changePlace = array(
			'gId' => $data['gId'],
			'category' => $data['category'],	
			'name' => $data['name'],
			'address' => $data['address'],		
			'rating' => $data['rating'],
			'comments' => $data['comments']);
			
			$places->replaceOne($query, $changePlace);
			
			$data = array("response" => "place Info Updated");
			header('Content-type: application/json');
			echo json_encode((object)($data));	
		
		}
	}
	
	else if($method == "DELETE")
	{		
		if(empty($gId))
		{
			$data = array("response" => "Must give a gId to delete a user, and a gId and name to delete a place.");
			header('Content-type: application/json');
			echo json_encode((object)($data));
		}
		
		else if(empty($place))
		{
			$query = array('gId' => $gId);
			
			$users->deleteOne($query);
			$places->deleteMany($query);
			$data = array("response" => "User Info and Places Deleted");
			header('Content-type: application/json');
			echo json_encode((object)($data));					
		}
		
		//Checks to see if place name was provided.  If not deletes of 
		//of users places
		else if(empty($pname))
		{			
			$query = array('gId' => $gId);
			$places->deleteMany($query);
			$data = array("response" => "Deleted all places.");
			header('Content-type: application/json');
			echo json_encode((object)($data));	
		}

		//deletes single user place that matches given name
		else
		{
			$query = array($and, 'gId' => $gId, 'name' => $pname);
			$places->deleteOne($query);
			$data = array("response" => "Place Deleted");
			header('Content-type: application/json');
			echo json_encode((object)($data));	
		}
	}
	
	
	
?>	