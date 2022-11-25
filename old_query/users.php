<?php 
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

    // Include CORS headers
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    header('Access-Control-Allow-Headers: X-Requested-With');
    //header('Content-Type: application/json');
    // Include action.php file
    //echo "hai";exit;
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
    $id = intval($headers['id'] ?? '');
    $userid = intval($headers['userid'] ?? '');
    $follower_id = intval($headers['follower_id'] ?? '');
    
    // Get all or a single user from database
    if ($api == 'GET' && ($link == "categories" || $type == "categories")) {
        $data = [];
        if ($id != 0) {
            $data = $user->fetchCategories($id);
        } else {
            $data = $user->fetchCategories();
        }
         $decode = $user->decodeArray($data);
        echo $user->message('categories details', false, $decode);

    } else if ($api == 'GET' && ($link == "shoptimings" || $type == "shoptimings")) {
        if ($userid != 0) {
            $data = $user->fetchShopTimings($userid);
        } else {
            $data = $user->fetchShopTimings();
        }
        echo $user->message('categories details', false, $data);

    } else if ($api == 'GET' && ($link == "search" || $type == "search")) {
        $key = ($headers['key'] ?? '');
        $user_id = ($headers['user_id'] ?? '');

        $data = [];
        if ($key != '') {
            $data = $user->userSearch($key, $user_id);
        }
         $decode = $user->decodeArray($data);
        echo $user->message('user details', false, $decode);

    } else if ($api == 'GET' && ($link == "followRequest" || $type == "followRequest")) {
        $user_id = ($headers['user_id'] ?? '');
        $data = [];
        if ($user_id != '') {
            $data = $user->followRequest($user_id);
        }
        echo $user->message('Follow Request details', false, $data);

    } else if ($api == 'GET' && ($link == "reviews" || $type == "reviews")) {
        $user_id = ($headers['user_id'] ?? '');
        $data = [];
        if ($user_id != '') 
        {
            $data = $user->getratingandreview($user_id);
        }
         $decode = $user->decodeArray($data);
        echo $user->message('Review details', false, $decode);

    } else if ($api == 'GET' && ($link == "bookmarks" || $type == "bookmarks")) {
        $user_id = ($headers['user_id'] ?? '');
        $data = [];
        if ($user_id != '') {
            $data = $user->getBookmarks($user_id);
        }
        echo $user->message('Bookmarks details', false, $data);

    } else if ($api == 'GET' && ($link == "blockComments" || $type == "blockComments")) {
        $user_id = ($headers['user_id'] ?? '');
        $data = [];
        if ($user_id != '') {
            $data = $user->getBlockComments($user_id);
        }
        echo $user->message('Review details', false, $data);

    }
	  else if ($api == 'POST' && $link == "unblockComments") { 
        $user_id = $user->test_input($_POST['user_id']);
        $block_user_id = $user->test_input($_POST['block_user_id']);

        if ($user->UnBlockCommentsUser($user_id, $block_user_id)) {
            echo $user->message('Comment has been unblocked!', false);
        } else {
            echo $user->message('Comment unblock failed!', true);
        }
      }
	
	else if ($api == 'GET' && ($link == "blockedCommentsbyuser" || $type == "blockedCommentsbyuser")) {
        $block_user_id = ($headers['block_user_id'] ?? '');
        $user_id = ($headers['user_id'] ?? '');
        $data = [];
        if ($block_user_id && $user_id != '') {
            $data = $user->getBlockedCommentsbyuser($user_id,$block_user_id);
        }
        echo $user->message('Who blocked my comments', false, $data);

    } else if ($api == 'GET' && ($link == "getBlockList" || $type == "blockList")) {
        $user_id = ($headers['user_id'] ?? '');
        $data = [];
        if ($user_id != '') {
            $data = $user->getBlockList($user_id);
        }
        echo $user->message('Block List details', false, $data);

    } else if ($api == 'GET' && ($link == "getIgnoreList" || $type == "ignorelist")) {
        $user_id = ($headers['user_id'] ?? '');
        $data = [];
        if ($user_id != '') {
            $data = $user->getIgnoreList($user_id);
        }
        echo $user->message('Ignore List details', false, $data);

    } else if ($api == 'GET' && $type == "filter") { 
        $data = [];
        $city = ($headers['city'] ?? '');
        $area = ($headers['area'] ?? '');
        $category = ($headers['category'] ?? '');
        $user_id = ($headers['user_id'] ?? '');
            $data = $user->userFilter($city, $area, $category, $user_id);
         $decode = $user->decodeArray($data);
        echo $user->message('user details', false, $decode);

    } else if ($api == 'GET' && $type == "fetchFollowing") {
        $data = [];

        if ($id != 0) {
            $data = $user->fetchFollowing($id);
        }
        echo $user->message('user details', false, $data);
    }  else if ($api == 'GET') {
        $data = [];

        if ($id != 0 && $follower_id != 0) {
            $data = $user->fetch($id, $follower_id);
            $followback = $user->fetchfollowback($id, $follower_id);
           echo json_encode(array('message'=>'user details', 'error'=>false, 'data'=>$data, 'followback'=>$followback));
        } else if ($id != 0) {
            $data = $user->fetch($id);
             $decode = $user->decodeArray($data);
             echo $user->message('user details!', false, $decode);
        } else if ($follower_id != 0) {
            $data = $user->fetch(0, $follower_id);
             $decode = $user->decodeArray($data);
             echo $user->message('user details!', false, $decode);
        } else {
            $data = $user->fetch();
             $decode = $user->decodeArray($data);
             echo $user->message('user details!', false, $decode);
        }
        
    }
    // Add a new user into database
    if ($api == 'POST' && $link == "login") {
        $user_name = $user->test_input($_POST['username']);
        $password = $user->test_input($_POST['password']);
        $data = $user->login($user_name, $password);
        if ($data) {
            echo $user->message('Login successfully!', false, $data);
            //echo json_encode($data);
        } else {
            echo $user->message('Failed to login an user!',true);
        }
      
    } else if ($api == 'POST' && $link == "categories") { 
        $name = $user->test_input($_POST['name']);
        if ($user->insertCategories($name)) {
            echo $user->message('categories added successfully!', false);
        } else {
            echo $user->message('categories added failed!', true);
        }
    } else if ($api == 'POST' && $link == "follow") { 
        $user_id = $user->test_input($_POST['user_id']);
        $follower_id = $user->test_input($_POST['follower_id']);

        if($user->selectusertype($follower_id)){

            $user-> insertBusinessFollower($user_id, $follower_id);
            echo $user->message('Follow accepted!', false);
        }
        else{
        if ($user->insertFollower($user_id, $follower_id)) {
            echo $user->message('Follow request sent successfully!', false);
        } else {
            echo $user->message('Follow request sent failed!', true);
       }
    
    }
    }
    else if ($api == 'POST' && $link == "changePassword") { 
        $mobile = $user->test_input($_POST['mobile']);
        $password = $user->test_input($_POST['password']);
        if ($user->updatePassword($mobile, $password)) {
            echo $user->message('password updated successfully!', false);
        } else {
            echo $user->message('password updated failed!', true);
        }
    } else if ($api == 'POST' && $link == "followAccept") { 
        $user_id = $user->test_input($_POST['follower_id']);
        $follower_id = $user->test_input($_POST['user_id']);

        if ($user->acceptFollower($user_id, $follower_id)) {
            echo $user->message('Follow request Accept successfully!', false);
        } else {
            echo $user->message('Follow request Accept failed!', true);
        }
    } else if ($api == 'POST' && $link == "followIgnore") { 
        $user_id = $user->test_input($_POST['follower_id']);
        $follower_id = $user->test_input($_POST['user_id']);

        if ($user->ignoreFollower($user_id, $follower_id)) {
            echo $user->message('Follow request Ignored successfully!', false);
        } else {
            echo $user->message('Follow request Ignored failed!', true);
        }
    } else if ($api == 'POST' && $link == "unfollow") { 
        $user_id = $user->test_input($_POST['user_id']);
        $follower_id = $user->test_input($_POST['follower_id']);

        if ($user->updateFollower($user_id, $follower_id)) {
            echo $user->message('UnFollow request sent successfully!', false);
        } else {
            echo $user->message('UnFollow request sent failed!', true);
        }
    }  else if ($api == 'POST' && $link == "block") { 

        $user_id = $user->test_input($_POST['user_id']);
        $blocker_id = $user->test_input($_POST['blocker_id']);

        if ($user->insertBlocker($user_id, $blocker_id)) {
            $user->insertBlockerinchat($user_id, $blocker_id);
            echo $user->message('You have blocked this user!', false);
           
        } else {
            echo $user->message('Block request sent failed!', true);
        }

    } else if ($api == 'POST' && $link == "unblock") { 
        $user_id = $user->test_input($_POST['user_id']);
        $blocker_id = $user->test_input($_POST['blocker_id']);

        if ($user->removeBlocker($user_id, $blocker_id)) {
            $user->removeBlockerinchat($user_id, $blocker_id);
            echo $user->message('Block request sent successfully!', false);
        } else {
            echo $user->message('Block request sent failed!', true);
        }
    }
	else if ($api == 'POST' && $link == "privacy") { 
        $user_id = $user->test_input($_POST['user_id']);
        $privacy = $user->test_input($_POST['privacy']);

        if ($user->updatePrivacy($user_id, $privacy)) {
            echo $user->message('Privacy request sent successfully!', false);
        } else {
            echo $user->message('Privacy request sent failed!', true);
        }
    } else if ($api == 'POST' && $link == "reviews") { 
        $user_id = $user->test_input($_POST['user_id']);
        $review_user_id = $user->test_input($_POST['review_user_id']);
        $reviews = $user->test_input($_POST['reviews']);

        if ($user->insertReviews($user_id, $review_user_id, $reviews)) {
            echo $user->message('Reviews sent successfully!', false);
        } else {
            echo $user->message('Reviews sent failed!', true);
        }
    } else if ($api == 'POST' && $link == "contactdetails") { 
        $name = $user->test_input($_POST['name']);
        $number = $user->test_input($_POST['number']);
        $email = $user->test_input($_POST['email']);
        $message = $user->test_input($_POST['message']);

        if ($user->insertContactDetails($name, $number, $email, $message)) {
            echo $user->message('Contact details sent successfully!', false);
        } else {
            echo $user->message('Contact details sent failed!', true);
        }
    } else if ($api == 'POST' && $link == "blockComments") { 
        $user_id = $user->test_input($_POST['user_id']);
        $block_user_id = $user->test_input($_POST['block_user_id']);

        if ($user->insertBlockCommentsUser($user_id, $block_user_id)) {
            echo $user->message('Block user details sent successfully!', false);
        } else {
            echo $user->message('Block user details sent failed!', true);
        }
    } else if ($api == 'POST' && $link == "bookmarks") { 
        $user_id = $user->test_input($_POST['user_id']);
        $post_id = $user->test_input($_POST['post_id']);

        $data = $user->fetchBookmark($user_id, $post_id);
        
        if ($user->updateBookmark($user_id, $post_id, $data)) {

            $data = $data[0];
            $active = $data['active'];
            if ($active == 1) {
                echo $user->message('bookmarks removed successfully!', false);
            } else {
                echo $user->message('bookmarks request sent successfully!', false);
            }
        } else {
            echo $user->message('bookmarks sent failed!', true);
        }
    } 
   
     else if ($api == 'POST' && $link == "users") {
        $user_name = $user->test_input($_POST['user_name']);
       // $firebase_userid = $user->test_input($_POST['firebase_userid']);
        $name = $user->test_input($_POST['name']);
        $email = $user->test_input($_POST['email']);
        $mobile = $user->test_input($_POST['mobile']);
        $password = $user->test_input($_POST['password']);
        $dob = $user->test_input($_POST['dob']);
        $travel = $user->test_input($_POST['travel']);
        $gender = $user->test_input($_POST['gender']);
        $address = $user->test_input($_POST['address']);
        $type = $user->test_input($_POST['type']);
        $categories = $user->test_input($_POST['categories']);
        $others = $user->test_input($_POST['others']);
        $service = $user->test_input($_POST['service']);
        $shop_timings = json_decode($_POST['shop_timings']);
        $certificate_proof = $_FILES['certificate_proof']['name'];
        
        $emailchk = $user->checkEmailExists($email);
        $data = $user->checkUsernameExists($user_name);
        $mobileno = $user->checkMobileExists($mobile);
        
        if ($data) {
            echo $user->message('Already username Exists!',true);
        }
        elseif ($mobileno) {
            echo $user->message('Already mobile number Exists!',true);
        } else {
            if(!($emailchk)){
            if ($type == "business") {
                $newfilename = '';
                if (trim($certificate_proof) !== '') {                    
                    $temp = explode(".", $certificate_proof);
                    $newfilename = $temp[0]."-".round(microtime(true)) . '.' . end($temp);
                    $data = $user->insertCertificate($_FILES['certificate_proof'], $newfilename, false, false);
                } 
            }
            $id = $user->insert($user_name, $email, $mobile, $password, $dob, $gender, $address, $type, $categories, $others, $service, $newfilename, $name, $travel);
            if ($id) {
                 
                if ($type == "business") {
                    
					if ($uid = $user->lastinsertuserid())
                    {
                    echo $user->message('User added successfully', false, $uid,$mobile);
                    }
					
                    foreach($shop_timings as $key => $value) {
                        $day = $key;
                        $from = $value->from;
                        $to = $value->to;
                        $user->insertShopTimings($id, $day, $from, $to, false);
                    }
                    
                } else {
                    if ($uid = $user->lastinsertuserid())
                    {
                   echo $user->message('User added successfully', false, $uid,$mobile);
                    }
                }

            }
            else {
                    echo $user->message('Failed to add an user!',true);
            }
        }
        else {
            echo $user->message('Email already exist!',true);
            }
    }
    }

  else if($api == 'POST' && $link == "updatefirebaseuid"){

        $firebase_userid = $user->test_input($_POST['firebase_userid']);
        $user_id = $user->test_input($_POST['user_id']);

        $userid = $user->updatefirbaseuserid($user_id,$firebase_userid);
  
        if($userid){

            echo $user->message('User updated successfully!', false);

        }else{

            echo $user->message('User updation failed!', false);

        }

    }


    else if ($api == 'POST' && $link == "update") {
        $user_id = $user->test_input($_POST['user_id']);
        $name = $user->test_input($_POST['name']);
        $dob = $user->test_input($_POST['dob']);
        $gender = $user->test_input($_POST['gender']);
        $address = $user->test_input($_POST['address']);
        $categories = $user->test_input($_POST['categories']);
        $others = $user->test_input($_POST['others']);
        $service = $user->test_input($_POST['service']);
        $shop_timings = json_decode($_POST['shop_timings']);
        $certificate_proof = $_FILES['certificate_proof']['name'];
        $profile_photo = $_FILES['profile_photo']['name'];
        $travel = $user->test_input($_POST['travel']);
        $description = $user->test_input($_POST['description']);
        $data = $user->fetch($user_id);
        $data = $data[0];
        $type = $data['type'];
        $certificate_proof_ext = $data['certificate_proof'];
        $profile_photo_ext = $data['profile_photo'];
        if ($type == "business") {
            $newfilename = '';
            if (trim($certificate_proof) !== '') {                    
                $temp = explode(".", $certificate_proof);
                $newfilename = $temp[0]."-".round(microtime(true)) . '.' . end($temp);
                $data = $user->insertCertificate($_FILES['certificate_proof'], $newfilename, true, $certificate_proof_ext);
                $newfilename = ($newfilename);
            } else {
            
                $newfilename = $certificate_proof_ext;
            }
            
        } 
        $newphotoname  = '';
        if (trim($profile_photo) !== '') { 

                $temp = explode(".", $profile_photo);
                $newphotoname = $temp[0]."-".round(microtime(true)) . '.' . end($temp);
                $data = $user->insertPhoto($_FILES['profile_photo'], $newphotoname, true, $profile_photo_ext);
                $profile_photo = ($newphotoname);
        } else {

            $profile_photo = $profile_photo_ext;
        }
        $id = $user->update($user_id, $dob, $gender, $address, $categories, $others, $service, $newfilename, $name, $profile_photo, $description, $travel);
        if ($id) {
            if ($type == "business") {
                
                $user->deleteShopTimings($user_id, $day, $from, $to, $update);
         foreach($shop_timings as $key => $value) {
                    $day = $key;
                    $from = $value->from;
                    $to = $value->to;
                    $update = true;
                    $user->insertShopTimings($user_id, $day, $from, $to, $update);
                }
            
                echo $user->message('User updated successfully!', false);
            } else {
                echo $user->message('User updated successfully!', false);
            }

        }
        else {
                echo $user->message('Failed to add an user!',true);
        }
        
    }
 
    

    /*else if ($api == 'PUT' && $link == "users") { 
        //parse_str(file_get_contents('php://input'), $post_input);
        $post_input = json_decode(file_get_contents('php://input'));
        $postinput = json_decode(json_encode($post_input),true);
        $user_id = $user->test_input($postinput['user_id']);
        $name = $user->test_input($postinput['name']);
        $email = $user->test_input($postinput['email']);
        $dob = $user->test_input($postinput['dob']);
        $gender = $user->test_input($postinput['gender']);
        $address = $user->test_input($postinput['address']);
        $categories = $user->test_input($postinput['categories']);
        $others = $user->test_input($postinput['others']);
        $service = $user->test_input($postinput['service']);
        $shop_timings = json_decode($postinput['shop_timings']);
        $certificate_proof = $_FILES['profile']['name'];
        $type = $user->test_input($postinput['type']);
        //$certificate_proof = $_FILES['certificate_proof']['name'];

            $newfilename = '';
            if (trim($certificate_proof) !== '') {                    
                $temp = explode(".", $certificate_proof);
                $newfilename = $temp[0]."-".round(microtime(true)) . '.' . end($temp);
                $data = $user->insertCertificate($_FILES['certificate_proof'], $newfilename);
            }
                
            $id = $user->update($user_name, $email, $mobile, $password, $dob, $gender, $address, $type, $categories, $others, $service, $newfilename, $name);
            if ($id) {
                if ($type == "business") {
                    
                    foreach($shop_timings as $key => $value) {
                        $day = $key;
                        $from = $value->from;
                        $to = $value->to;
                        $user->insertShopTimings($id, $day, $from, $to, $update = true);
                    }
                
                    echo $user->message('User updated successfully!', false);
                } else {
                    echo $user->message('User updated successfully!', false);
                }

            }
            
        }*/
    
    // Update an user in database
    /*if ($api == 'PUT') {
      parse_str(file_get_contents('php://input'), $post_input);

      $name = $user->test_input($post_input['username']);
      $email = $user->test_input($post_input['email']);
      $phone = $user->test_input($post_input['phone']);

      if ($id != null) {
        if ($user->update($name, $email, $phone, $id)) {
          echo $user->message('User updated successfully!',false);
        } else {
          echo $user->message('Failed to update an user!',true);
        }
      } else {
        echo $user->message('User not found!',true);
      }
    }

    // Delete an user from database
    if ($api == 'DELETE') {
      if ($id != null) {
        if ($user->delete($id)) {
          echo $user->message('User deleted successfully!', false);
        } else {
          echo $user->message('Failed to delete an user!', true);
        }
      } else {
        echo $user->message('User not found!', true);
      }
    }*/

    
?>
