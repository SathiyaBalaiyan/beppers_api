<?php 
    error_reporting(E_ALL);
    ini_set("display_errors", 1);

    // Include CORS headers
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: X-Requested-With');
    header('Content-Type: application/json');
 
    include_once 'db.php';

    $user = new Database();

    $api = $_SERVER['REQUEST_METHOD']; 
    $link = basename($_SERVER['REQUEST_URI']);

    // get id from url
    $type = ($_GET['type'] ?? '');

    if ($api == 'GET') {
        $headers = array();
        foreach (getallheaders() as $name => $value) {
            $headers[$name] = $value;
        }
    }
    
  if ($api == 'GET' && $type == "getchatblock")
    {
        $data = [];
        $chatuser_id = intval($headers['chatuser_id'] ?? '');
      

         if ($data = $user->getchatblocklist($chatuser_id))
            {

                $decode = $user->decodeArray($data);

                echo $user->message('user chat block list', true,$decode);
            
            }
            else
            {
                echo $user->message('Failed to fetch chat block list', true);
            }
      
    }  
	
	
	
     if($api == 'POST' && $link == "chatblock"){

        $chatuser_id =  $user->test_input($_POST['chatuser_id']);
        $chatblocker_id =  $user->test_input($_POST['chatblocker_id']);
      
       
            if ($user->chatblock($chatuser_id,$chatblocker_id)) {

              $user->updatechatblock($chatuser_id,$chatblocker_id);
              
              echo $user->message('chat has been blocked!', false);

            } else {
              echo $user->message('chat has been unblocked!', true);
            }
         
      }

      if($api == 'POST' && $link == "chatunblock"){

        $chatuser_id =  $user->test_input($_POST['chatuser_id']);
        $chatblocker_id =  $user->test_input($_POST['chatblocker_id']);
      
        if ($chatuser_id && $chatblocker_id != null) {

            if ($user->chatUnblock($chatuser_id,$chatblocker_id)) {

              $user->updatechatUnblock($chatuser_id,$chatblocker_id);

              echo $user->message('chat has been Unblocked!', false);

            } else {
              echo $user->message('something went wrong!', true);
            }
         }
        }
        
        
        
        if($api == 'POST' && $link == "deleteuser"){

          $user_name =  $user->test_input($_POST['user_name']);
          
          if ($user_name != null) {
  
              if ($user->deleteuser($user_name)) {
  
                echo $user->message('user has been deleted!', false);
  
              } else {
                echo $user->message('something went wrong!', true);
              }
              
           } else {
            echo $user->message('Please enter correct user_id!', true);
          }
          }
        
        


    ?>