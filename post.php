<?php 
// error_reporting(E_ALL);
// ini_set("display_errors", 1);

    // Include CORS headers
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: X-Requested-With');
    //header('Content-Type: application/json');
    include_once 'db.php';
    $user = new Database();

    $api = $_SERVER['REQUEST_METHOD'];
    $link = basename($_SERVER['REQUEST_URI']);
    // get id from url
    $type = ($_GET['type'] ?? '');
    // Get all or a single user from database
    if ($api == 'GET') {
        $headers = array();
        foreach (getallheaders() as $name => $value) {
            $headers[$name] = $value;
        }
    }    
    
     if ($api == 'GET' && ($link == "privacy" || $type == "privacy")) { 
       
        $privacy=[];
        $userid = intval($headers['user_id'] ?? '');
        
      
        if($privacy = $user->fetchprivacy($userid)){
            
            echo $user->message('Privacy', false, $privacy);
        }
        else{
            echo json_encode(array('message'=>'Privacy','error'=>false,'privacy'=> null));
        }
        
    }
    
    
    
    $id = intval($headers['id'] ?? '');
    if ($api == 'GET' && ($link == "post" || $type == "post")) { 
        $data = [];
        $userid = intval($headers['userid'] ?? '');
        $userpost = ($headers['userpost'] ?? '');
        $visitorid = intval($headers['visitorid'] ?? '');
        if ($userid != 0 && $userpost == true && $visitorid != 0) {
            $data = $user->fetchPost($id, $userid, true, $visitorid); 
        } else if ($id != 0 && $userid != 0 && $userpost == true) {
            $data = $user->fetchPost($id, $userid, true); 
        } elseif ($userid != 0 && $userpost == true) {
            $data = $user->fetchPost(null, $userid, true);
        } else {
            $data = $user->fetchPost(null, $userid);
        }
         $decode = $user->decodeArray($data);
        echo $user->message('post details', false, $decode);

    }
    if ($api == 'POST' && $link == "post") { 
        $name = $user->test_input($_POST['name']);
        $description = $user->test_input($_POST['description']);
        $tag = $user->test_input($_POST['tag']);
        $galleries = ($_FILES['gallery']['name']);
        $newfilename = '';
        $galleryNames = [];
        $newfilenames = '';
        if (!empty($galleries)) {
            for($i = 0; $i < count($galleries); $i++) {
               $gallery = $galleries[$i];
                $temp = explode(".", $gallery);
                $newfilename = $temp[0]."-".round(microtime(true)) . '.' . end($temp);
                $data = $user->insertGallery($_FILES['gallery'], $i, $newfilename);
                $galleryNames[] = $newfilename;
            }
            $newfilenames = implode(",", $galleryNames);
        }
        $user_id = $_POST['user_id'];
        if ($user->insertPost($name, $description, $tag, $newfilenames, $user_id)) {
            echo $user->message('Post added successfully!', false);
        } else {
            echo $user->message('Post added failed!', true);
        }
    } else if ($api == 'POST' && $type == "hidePost") {
        $user_id = $user->test_input($_POST['user_id']);
        $post_id = $user->test_input($_POST['post_id']);
        if ($user->hidePost($user_id, $post_id)) {
            echo $user->message('hide added successfully!', false);
        } else {
            echo $user->message('hide added failed!', true);
        }
    } else if ($api == 'POST' && $link == "comments") { 
        $user_id = $user->test_input($_POST['user_id']);
        $post_id = $user->test_input($_POST['post_id']);
        $comments = $user->test_input($_POST['comments']);
        if ($user->insertComments($user_id, $post_id, $comments)) {
            echo $user->message('Comments added successfully!', false);
        } else {
            echo $user->message('Comments added failed!', true);
        }
    } else if ($api == 'POST' && $link == "like") { 
        $user_id = $user->test_input($_POST['user_id']);
        $post_id = $user->test_input($_POST['post_id']);
        if ($user->insertLike($user_id, $post_id)) {
            echo $user->message('Like added successfully!', false);
        } else {
            echo $user->message('Like added failed!', true);
        }
    } else if ($api == 'POST' && $link == "unlike") { 
        $user_id = $user->test_input($_POST['user_id']);
        $post_id = $user->test_input($_POST['post_id']);
        if ($user->updateLike($user_id, $post_id)) {
            echo $user->message('Like removed successfully!', false);
        } else {
            echo $user->message('Like removed failed!', true);
        }
    } else if ($api == 'GET' && $type == "comments") { 
        $data = [];
        $post_id = intval($headers['postid'] ?? '');
        if ($post_id != 0) {
            $data = $user->getComments($post_id);
        }
           $decode = $user->decodeArray($data);
        echo $user->message('Comments details', false, $decode);
    }
    else if ($api == 'GET' &&  $type == "like") { 
        $data = [];
        $id = intval($headers['postid'] ?? '');
        if ($id != 0) {
            $data = $user->getLike($id);
        }
        echo $user->message('Like details', false, $data);

    } else if ($api == 'GET' &&  $type == "search") { 
        $data = [];
        $key = ($headers['key'] ?? '');
        $user_id = ($headers['user_id'] ?? '');
        if ($key != '') {
            $data = $user->postSearch($key, $user_id);
        }
        echo $user->message('post details', false, $data);

    } 
    else if ($api == 'GET' &&  $type == "filter") { 
        $data = [];
        $city = ($headers['city'] ?? '');
        $area = ($headers['area'] ?? '');
        $category = ($headers['category'] ?? '');
        $user_id = ($headers['user_id'] ?? '');
        $data = $user->postFilter($city, $area, $category, $user_id);
        
        echo $user->message('post details', false, $data);

    } else if ($api == 'POST' && $type == "ratecard") { 
        $user_id = $_POST['user_id'];
        $galleries = ($_FILES['ratecard']['name']);
        $newfilename = '';
        $galleryNames = [];
        $newfilenames = '';
        if (!empty($galleries)) {
            for($i = 0; $i < count($galleries); $i++) {
               $gallery = $galleries[$i];
                $temp = explode(".", $gallery);
                $newfilename = $temp[0]."-".round(microtime(true)) . '.' . end($temp);
                $data = $user->insertCard($_FILES['ratecard'], $i, $newfilename);
                $galleryNames[] = $newfilename;
                
            }
            if ($user->insertRateCard($newfilename, $user_id)) {
                echo $user->message('Card added successfully!', false);
            } else {
                echo $user->message('Card added failed!', true);
            }

        }
        
    }
    
     
    else if ($api == 'GET' &&  $type == "ratecard") { 
        $data = [];
        $user_id = intval($headers['user_id'] ?? '');
        if ($user_id != 0) {
            $data = $user->getRateCard($user_id);
        }
        echo $user->message('RateCard details', false, $data);

    } else if ($api == 'POST' &&  $type == "deleteratecard") { 
        $id = $_POST['id'];
        if ($id != null) {
            if ($user->deleteRateCard($id)) {
              echo $user->message('card deleted successfully!', false);
            } else {
              echo $user->message('Failed to delete card!', true);
            }
        } else {
            echo $user->message('RateCard not found!', true);
        }

    } 
     else if ($api == 'POST' &&  $type == "deletepost") { 
        $post_id = $_POST['post_id'];
        $user_id = $_POST['user_id'];
        if ($post_id != null) {
            if ($user->deletepost($post_id)) {
                $user->updatepost($user_id);
              echo $user->message('post has been deleted!', false);
            } else {
              echo $user->message('something went wrong please try again later!', true);
            }
         }
     
    }
    
    else if ($api == 'POST' && $link == "rating") { 
        $user_id = $user->test_input($_POST['user_id']);
        $review_user_id = $user->test_input($_POST['review_user_id']);
        $rating = $user->test_input($_POST['rating']);
        $review = $user->test_input($_POST['review']);
        if ($user->insertratingandreview($user_id, $review_user_id, $review, $rating)) {

            echo $user->message('Rating added successfully!', false);
        } 
        else {
            echo $user->message('Rating added failed!', true);
        }
    } 
    else if ($api == 'GET' &&  $type == "rating") { 
        $data = [];
        $user_id = intval($headers['user_id'] ?? '');
        if ($user_id != 0) {
            $data = $user->getRating($user_id);
            
        }
        echo $user->message('RateCard details', false, $data);
        

    }
	else if($api == 'GET' && $type == "chatblockprofile"){

      $user_id = ($headers['user_id'] ?? '');
      $follower_id = ($headers['follower_id'] ?? '');

          $data = $user->getprofilechatblock($user_id, $follower_id);
          
          echo $user->message('chat block details!', false, $data);


  }
	
	
    
    if ($api == 'POST' && $link == "insertnotification")
    {
        $from_id = $user->test_input($_POST['from_id']);
        $to_id = $user->test_input($_POST['to_id']);
        $types = $user->test_input($_POST['types']);
        $mesg = $user->test_input($_POST['mesg']);

        if (isset($_POST['post_id']))
        {
            $post_id = $user->test_input($_POST['post_id']);
       
            if ($post_id !== '')
            {
                if ($user->insertNotification($from_id, $to_id, $types, $mesg, $post_id))
                { 
                    echo $user->message('Notification added successfully', false);
                }
                else
                {
                    echo $user->message('Failed to add notification!', true);
                }   
            }
        }  
        elseif($user->insertNotificationwithoutpid($from_id, $to_id, $types, $mesg))
        {
            echo $user->message('Notification added successfully', false);
        } 
    }

    if ($api == 'GET' && $type == "getnotifyuser")
    {
        $data = [];
        $to_id = intval($headers['to_id'] ?? '');

        if ($to_id != 0) 
        {
            if ($data = $user->fetchusersnotification($to_id))
            {
                echo $user->message('Notification details fetched successfully', false, $data);
            }else{
                echo $user->message('Notification not found', false);
            }
           
        }
        else
        {
            echo $user->message('Enter valid id to fetch notification details', true);
        }       
    }
    
    if ($api == 'POST' && $link == "notifyread")
    {
        $id = $user->test_input($_POST['id']);

        if ($user->insertnotifyactive($id))
        {
            echo $user->message('Notification read updated successfully', false);
        }
        else
        {
            echo $user->message('Failed to update notification read', true);
        }
    }
    
    if ($api == 'POST' && $link == "insertchat")
    {
        $from_id = $user->test_input($_POST['from_id']);
        $to_id = $user->test_input($_POST['to_id']);
        $messages = $user->test_input($_POST['messages']);
        
     
        if ($user->insertchat($from_id, $to_id, $messages))
        {
             $user -> usertype($from_id, $to_id);
           
            if (!($user->selectchatid($from_id, $to_id)))
            {
               
                
               if ($data = $user->insertlatestchat($from_id, $to_id, $messages))
               {
                   echo $user->message('Chat inserted successfully', false);
               }
               else
               {
                   echo $user->message('Failed to insert latest chat', true);
               }
            }
            else
            {
                if ($user->updatechat($from_id, $to_id, $messages))
                {
                    echo $user->message('Latest chat updated successfully', false);
                }
                else
                {
                    echo $user->message('Failed to update latest chat', true);
                }
            }
            //echo $user->message('Chat inserted successfully', false);
        }
        else
        {
            echo $user->message('Failed to insert chat', true);
        }
    
        
    }
    
        if ($api == 'GET' && $type == "getchatdetails")
    {
        $data = [];

        $from_id = intval($headers['from_id'] ?? '');
        $to_id = intval($headers['to_id'] ?? '');

        if ($from_id && $to_id != 0) 
        {
            if ($data = $user->fetchuserschat($from_id, $to_id))
            {
                 $decode = $user->decodeArray($data);
                echo $user->message('Users chat detail fetched successfully', false, $decode);

            }
            elseif($from_id && $to_id !== '')
            {
                echo $user->message('Enter valid from_id and to_id to fetch chat details', true); 
            }
            else
            {
                $user->message('Failed to fetch users chat data', true);
            }
        }
        else
        {
            echo $user->message('Enter valid id to fetch chat details', true);
        }
    }
    
    if ($api == 'POST' && $link == "chatseen")
    {
        $from_id = $user->test_input($_POST['from_id']);
        $to_id = $user->test_input($_POST['to_id']);

        if ($user->insertchatactive($from_id,$to_id))
        {
            echo $user->message('Chat has been read', false);
        }
        else
        {
            echo $user->message('Failed to read chat', true);
        }
    }
    
 if ($api == 'GET' && $type == "getlatestmesg")
    {
        $data = [];
        $from_id = intval($headers['from_id'] ?? '');
      

        if ($from_id != null)
        {
            if ($data = $user->fetchchat($from_id))
            {

                $decode = $user->decodeArray($data);
              
               // $userblock = $user->getchatblock($from_id);

                echo $user->message('Latest message details', false, $decode);
               

            }
            else
            {
                echo $user->message('Failed to fetch latest message details', true);
            }
        }
        else
        {
            echo $user->message('Enter valid user id to fetch data', true);
        }
    }

if ($api == 'GET' && $type == "notifyswitch")
    {
        $data = [];
        $user_id = intval($headers['user_id'] ?? '');
      

        if ($user_id != null)
        {
            if ($data = $user->fetchnotifyswitch($user_id))
            {
                echo $user->message('Fetch Notification On/off details', false, $data);
            }
            else
            {
                echo $user->message('Failed to fetch Notification On/off details', true);
            }
        }
        else
        {
            echo $user->message('Enter valid user id to fetch data', true);
        }
    }
 if ($api == 'POST' && $type == "notifyswitch")
    {
        $user_id = $user->test_input($_POST['user_id']);
        $like_comment = $user->test_input($_POST['like_comment']);
        $message = $user->test_input($_POST['message']);
        $follow_request = $user->test_input($_POST['follow_request']);
        $follow_accept = $user->test_input($_POST['follow_accept']);
        $user_post = $user->test_input($_POST['user_post']);

        if ($user_id != null)
        {
            if ($data = $user->insertnotifyswitch($user_id,$like_comment,$message,$follow_request,$follow_accept,$user_post))
            {
                echo $user->message('Fetch Notification On/off details', false, $data);
            }
            else
            {
                echo $user->message('Failed to fetch Notification On/off details', true);
            }
        }
        else
        {
            echo $user->message('Enter valid user id to fetch data', true);
        }
    }
    
    if ($api == 'GET' && $type == "chatcount")
       {

        $data = [];
        $to_id = intval($headers['to_id'] ?? '');
        
        if ($to_id != null)
        {
            if ($data=$user->getchatcount($to_id))
            {
                echo $user->message('Chat count', false, $data);
            }
            else
            {
                echo json_encode(array('message'=>'Chat count','error'=>'false','data'=>[]));
            }
        }
        else
        {
            echo $user->message('Enter valid user id to fetch data', true);
        }
        }
        
        
 if ($api == 'POST' && $type == "deactivate")
    {

        $user_id = $user->test_input($_POST['user_id']);
        //$deactivate = $user->test_input($_POST['deactivate']);
        

        if ($user_id != null)
        {
            
            if ($user->deactivateaccount($user_id))
            {
                echo $user->message('Your Account will be deactivated soon', false);
            }
            else
            {
                echo $user->message('Failed to deactivate account, Please try again later!', true);
            }

        }
        else
        {
            echo $user->message('Enter valid user id to fetch data', true);
        }
      }
      
          //To get post details
    if ($api == 'POST' && $link == "getpost")
    {
        $user_id = $user->test_input($_POST['user_id']);
        $post_id = $user->test_input($_POST['post_id']);

        if ($user_id && $post_id != null)
        {
            if ($posts = $user->getPost($user_id, $post_id))
            {
                echo json_encode(array('message' => 'Post data fetched successfully', 'error'=> false, 'data' => $posts));
            }
            else
            {
                echo json_encode(array('message' => 'Failed to fetch data', 'error' => true,'data' => []));
            }
        }
        else
        {
            echo $user->message('Enter valid user id and post id to fetch data', true);
        }
    }
	
?>
