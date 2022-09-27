<?php
include_once 'config.php';

class Database extends Config {
  // Fetch all or a single user from database
    public function fetch($id = 0, $follower_id = null) {
        if ($follower_id != 0 && $id != 0) {
            $sql = 'SELECT  u.id,u.firebase_userid, u.user_name, u.name, u.mobile, u.email, u.password, u.dob, u.gender, u.address, u.type, u.categories, u.others, u.service, u.certificate_proof, u.no_of_posts, u.no_of_following, u.no_of_followers, u.profile_photo, u.created_at, f.active, f.follow_request  FROM users u inner join followers f  on f.user_id = u.id  and f.follower_id = :follower_id  and f.user_id = :id WHERE u.block="false" group by f.follower_id';
        } else if ($follower_id != 0) {
            $sql = 'SELECT u.id, u.firebase_userid, u.user_name, u.name, u.mobile, u.email, u.password, u.dob, u.gender, u.address, u.type, u.categories, u.others, u.service, u.certificate_proof, u.no_of_posts, u.no_of_following, u.no_of_followers, u.profile_photo, u.created_at, f.active, f.follow_request  FROM users u left join followers f  on f.user_id = u.id and f.follower_id = :follower_id and u.id not in (select blocker_id from blocklist where user_id = :follower_id ) WHERE u.block="false" order by u.id desc';
        
        } else {
            $sql = 'select * from users';
            if ($id != 0) {
                $sql .= ' WHERE id = :id';
            }
        }
        
        $stmt = $this->conn->prepare($sql);
        if ($follower_id != 0 && $id != 0) {
            $stmt->execute(['follower_id' => $follower_id, 'id' => $id]);
        }
        else if ($id != 0) {
            $stmt->execute(['id' => $id]);
        }
        else if ($follower_id != 0) {
            $stmt->execute(['follower_id' => $follower_id]);
        } else {
            $stmt->execute();
        }
        $rows = $stmt->fetchAll();
        return $rows;
    }
    
	  public function getprofilechatblock($user_id,$follower_id){

        $sql='SELECT chat_block from latest_chat where from_id = :user_id AND to_id = :follower_id OR from_id = :follower_id AND to_id = :user_id AND chat_block = :follower_id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([ 'follower_id' => $follower_id, 'user_id' => $user_id]);
        $rows = $stmt->fetchAll();
        return $rows;
        
    }


    public function fetchfollowback($follower_id,$id){

       $sql='SELECT CASE WHEN (user_id = :follower_id AND follower_id=:id AND active="1") THEN "1" else "0" END AS followback FROM followers WHERE user_id = :follower_id AND follower_id = :id';
       $stmt = $this->conn->prepare($sql);
       $stmt->execute(['id' => $follower_id, 'follower_id'=>$id]);
       $rows = $stmt->fetchAll();
       return $rows;
    }


    public function fetchCategories($id = 0) {
        $sql = 'SELECT * FROM categories';
        if ($id != 0) {
          $sql .= ' WHERE id = :id';
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        $rows = $stmt->fetchAll();
        return $rows;
    }

    public function fetchShopTimings($userid = 0) {
        $sql = 'SELECT * FROM shop_timings';
        if ($userid != 0) {
          $sql .= ' WHERE user_id = :user_id';
        }
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $userid]);
        $rows = $stmt->fetchAll();
        return $rows;
    }


    public function fetchFollowers($user_id, $follower_id) {
        $sql = 'SELECT * FROM followers';
        $sql .= ' WHERE user_id = :user_id and follower_id = :follower_id  ';
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'follower_id' => $follower_id]);
        $rows = $stmt->fetchAll();
        return $rows;
    }
    
     public function fetchprivacy($userid){

            $sql='SELECT privacy from users where id=:userid';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['userid' => $userid]);
            $rows = $stmt->fetchAll();
            return $rows;
            
        }


    public function fetchPost($id = null, $user_id = null, $userpost = false, $visitorid = null) {
        $sql = 'SELECT p.id, t.user_name as taguser, p.user_id, p.name, p.description, p.tag, p.gallery, p.no_of_comments, p.no_of_likes, l.active, u.user_name,u.address, u.profile_photo, u.type, b.active as bookmark_active from post p left join post_like l on l.post_id = p.id and l.user_id = :user_id  left join bookmarks b on b.post_id = p.id and b.user_id = :user_id left join users u on u.id = p.user_id left join users t on p.tag = t.id where p.id != "" ';
        if ($id != null) {
          $sql .= ' WHERE p.id = :id  ';
        }
        else if ($userpost && $visitorid != null) {
            $sql = 'SELECT p.id, t.user_name as taguser, p.user_id, p.name as postname, p.description, p.tag, p.gallery, u.profile_photo, u.name, u.user_name, u.address, p.no_of_likes, p.no_of_comments, l.active, u.user_name, u.profile_photo, u.type, b.active as bookmark_active FROM post p inner join users u on u.id = p.user_id left join post_like l on l.post_id = p.id and l.user_id = :visitorid left join bookmarks b on b.post_id = p.id and b.user_id = :user_id left join users t on p.tag = t.id';
          
            $sql .= ' WHERE p.user_id = :user_id ';
        }
        else if ($userpost) {
          $sql = 'SELECT p.id, t.user_name as taguser, p.user_id, p.name as postname, p.description, p.tag, p.gallery, u.profile_photo, u.name, u.user_name, u.address, p.no_of_likes, p.no_of_comments, l.active, u.user_name, u.profile_photo, u.type, b.active as bookmark_active FROM post p inner join users u on u.id = p.user_id left join post_like l on l.post_id = p.id and l.user_id = :user_id left join bookmarks b on b.post_id = p.id and b.user_id = :user_id left join users t on p.tag = t.id';
          
          $sql .= ' WHERE p.user_id = :user_id ';
        }

        $sql .= ' and p.id not in (select post_id from hide_post where user_id = :user_id)and p.user_id  not in (select user_id from blocklist where blocker_id = :user_id) and p.user_id not in (select blocker_id from blocklist where user_id = :user_id) and u.block="false" and u.deactivate != "true" ';
        if ($id == null) {
            $sql .= ' order by p.id desc';
        }
        //echo $sql;
        $stmt = $this->conn->prepare($sql);
        if ($id != null) {
            $stmt->execute(['id' => $id]);
        } else if ($visitorid != null && $user_id != null) {
            $stmt->execute(['user_id' => $user_id, 'visitorid' => $visitorid]);
        }
        else if ($user_id != null) {
            $stmt->execute(['user_id' => $user_id]);
        } 
        if ($id == null && $user_id == null) {
         $stmt->execute();   
        }
        $rows = $stmt->fetchAll();
        return $rows;
    }

    public function checkUsernameExists($user_name, $mobile) {
        $sql = 'SELECT * FROM users';
        $sql .= ' WHERE (user_name = :user_name or  mobile = :mobile)';
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_name' => $user_name, 'mobile' => $mobile]);
        $rows = $stmt->fetchAll();
        return $rows;
    }
    
    
     public function checkEmailExists($email){

        $sql = 'SELECT * FROM users';
        $sql .= ' WHERE (email = :email)';
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['email' => $email]);
        $rows = $stmt->fetchAll();
        return $rows;

    }

    public function login($user_name, $password) { 
        $sql = 'SELECT id,user_name,type FROM users';
        $sql .= ' WHERE (user_name = :user_name or  mobile = :user_name) and BINARY password = :password';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_name' => $user_name, 'password' => $password]);
        $rows = $stmt->fetchAll();
        return $rows;
    }
    
    
 // Insert an user in the database
    public function insert($user_name, $email, $mobile, $password, $dob, $gender, $address, $type, $categories, $others, $service, $certificate_proof, $name, $travel) {
        $created_at = date("Y-m-d H:i:s", time());
        $id = false;
                
        $sql = 'INSERT INTO users (user_name, email, mobile, password, dob, gender, address, type, categories, others, service, certificate_proof, name, created_at, travel) VALUES 
        (:user_name, :email, :mobile, :password, :dob, :gender, :address, :type, :categories, :others, :service, :certificate_proof, :name, :created_at, :travel)';

        try {

            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['user_name' => $user_name, 'email' => $email, 'mobile' => $mobile, 'password' => $password, 'dob' => $dob, 'gender' => $gender, 'address' => $address, 'type' => $type, 'categories' => $categories, 'others' => $others, 'service' => $service, 'certificate_proof' => $certificate_proof, 'name'=>$name, 'created_at' => $created_at, 'travel' => $travel]);
            $id = $this->conn->lastInsertId();

            return $id;

        } catch(PDOException $e) {
            throw new \PDOException($e->getMessage());
      }
    }
      public function lastinsertuserid()
    {
        $sql = 'SELECT LAST_INSERT_ID() AS userid FROM users';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(); 
    }

  public function updatefirbaseuserid($user_id,$firebase_userid){

        $query = 'UPDATE users SET firebase_userid = "'.$firebase_userid.'" where id = '.$user_id;
        $stmt = $this->conn->query($query);
        return true;

    }



        // Insert an user in the database
    public function update($user_id, $dob, $gender, $address, $categories, $others, $service, $certificate_proof, $name, $profile_photo, $description, $travel) {
        $updated_at = date("Y-m-d H:i:s", time());
        $id = false;

        $sql = 'UPDATE users SET name = :name, dob = :dob, gender = :gender, address = :address, categories = :categories, others = :others, service = :service, certificate_proof = :certificate_proof, profile_photo = :profile_photo, description = :description, updated_at = :updated_at, travel = :travel WHERE id = :user_id';
                
        try {

            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['name'=>$name, 'dob' => $dob, 'gender' => $gender, 'address' => $address, 'categories' => $categories, 'others' => $others, 'service' => $service, 'certificate_proof' => $certificate_proof, 'profile_photo' => $profile_photo, 'description' => $description, 'updated_at' => $updated_at, 'user_id' => $user_id, 'travel' =>$travel]);
            return true;

        } catch(PDOException $e) {
            throw new \PDOException($e->getMessage());
      }
    }
     public function deleteShopTimings($user_id, $day, $from, $to, $update){

        $sql = 'DELETE FROM shop_timings WHERE user_id = :user_id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);

  }
    public function insertShopTimings($user_id, $day, $from_time, $to_time, $update) {
        $created_at = date("Y-m-d H:i:s", time());

        
        // if ($update == true) {
        //     $sql = 'DELETE FROM shop_timings WHERE user_id = :user_id';
        //     $stmt = $this->conn->prepare($sql);
        //     $stmt->execute(['user_id' => $user_id]);
        
        // }
                
        $sql = 'INSERT INTO shop_timings (user_id, day, from_time, to_time) VALUES 
        (:user_id, :day, :from_time, :to_time)';
        try {

            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['user_id' => $user_id, 'day' => $day, 'from_time' => $from_time, 'to_time' => $to_time]);
            return true;

        } catch(PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }
    

    /*public function insertGalleryDetails($user_id, $post_id, $gallery) {
        $created_at = date("Y-m-d H:i:s", time());

                
        $sql = 'INSERT INTO post_gallery (user_id, post_id, gallery, created_at) VALUES 
        (:user_id, :post_id, :gallery, :created_at)';
        try {

            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['user_id' => $user_id, 'post_id' => $post_id, 'gallery' => $gallery, 'created_at' => $created_at]);
            return true;

        } catch(PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }*/

    public function insertCategories($name) {

        $sql = 'INSERT INTO categories (name) VALUES 
        (:name)';
        try {

            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['name' => $name]);
            return true;

        } catch(PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }

    public function insertCertificate($certificate_proof, $newfilename, $update = false, $certificate_proof_ext) {
        $fileName  =  $newfilename;
        $tempPath  =  $certificate_proof['tmp_name'];
        $fileSize  =  $certificate_proof['size'];
        $upload_path = 'certificates/'; // set upload folder path 
    
        $fileExt = strtolower(pathinfo($fileName,PATHINFO_EXTENSION)); // get image extension
        
        // valid image extensions
        $valid_extensions = array('jpeg', 'jpg', 'png', 'gif'); 
                        
        // allow valid image file formats
        if(in_array($fileExt, $valid_extensions))
        {   
            if($certificate_proof_ext != '' && file_exists($upload_path . $certificate_proof_ext))
            { 
                unlink($upload_path . $certificate_proof_ext);
            }            
            if(!file_exists($upload_path . $fileName))
            {
                // check file size '5MB'
                if($fileSize < 5000000){
                    move_uploaded_file($tempPath, $upload_path . $fileName); // move file from system temporary path to our upload folder path 
                }
                else{       
                    $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 5 MB size", "status" => false));   
                    echo $errorMSG;
                    exit;
                }
            }
            /*else
            {    
                   
                //$errorMSG = json_encode(array("message" => "Sorry, file already exists check upload folder", "status" => false));   
                //echo $errorMSG;
                //exit;
            }*/
        }
        else
        {       
            $errorMSG = json_encode(array("message" => "Sorry, only JPG, JPEG, PNG & GIF files are allowed", "status" => false));   
            echo $errorMSG;
            exit;   
        }

    }

    public function insertPhoto($profile_photo, $newfilename, $update = false, $profile_photo_ext) {
        $fileName  =  $newfilename;
        $tempPath  =  $profile_photo['tmp_name'];
        $fileSize  =  $profile_photo['size'];
        $upload_path = 'profile/'; // set upload folder path 
    
        $fileExt = strtolower(pathinfo($fileName,PATHINFO_EXTENSION)); // get image extension
        
        // valid image extensions
        $valid_extensions = array('jpeg', 'jpg', 'png', 'gif'); 
                        
        // allow valid image file formats
        if(in_array($fileExt, $valid_extensions))
        {   
            if($profile_photo_ext != '' && file_exists($upload_path . $profile_photo_ext))
            { 
                unlink($upload_path . $profile_photo_ext);
            }            
            if(!file_exists($upload_path . $fileName))
            {
                // check file size '5MB'
                if($fileSize < 5000000){
                    move_uploaded_file($tempPath, $upload_path . $fileName); // move file from system temporary path to our upload folder path 
                }
                else{       
                    $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload 5 MB size", "status" => false));   
                    echo $errorMSG;
                    exit;
                }
            }
            /*else
            {    
                   
                //$errorMSG = json_encode(array("message" => "Sorry, file already exists check upload folder", "status" => false));   
                //echo $errorMSG;
                //exit;
            }*/
        }
        else
        {       
            $errorMSG = json_encode(array("message" => "Sorry, only JPG, JPEG, PNG & GIF files are allowed", "status" => false));   
            echo $errorMSG;
            exit;   
        }

    }



    public function insertPost($name, $description, $tag, $gallery, $user_id) {
        $created_at = date("Y-m-d H:i:s", time());
        $sql = 'INSERT INTO post (name, description, tag, gallery, user_id, created_at) VALUES 
        (:name, :description, :tag, :gallery, :user_id, :created_at)';
        try {

            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['name' => $name, 'description' => $description, 'tag' => $tag, 'gallery' => $gallery, 'user_id' => $user_id, 'created_at'=>$created_at]);

            $query = 'UPDATE users SET no_of_posts = no_of_posts+1 where id = '.$user_id;
            $stmt = $this->conn->query($query);
            return true;


        } catch(PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }

    public function insertFollower($user_id, $follower_id) {
        $created_at = date("Y-m-d H:i:s", time());
        $follow_request = 1;

        $sql = 'INSERT INTO followers (user_id, follower_id, follow_request, created_at) VALUES 
        (:user_id, :follower_id, :follow_request, :created_at)';
        try {
            if ($this->fetchFollowers($user_id, $follower_id)) {
                $query = 'UPDATE followers SET follow_request = 1, updated_at = "'.$created_at.'" where follower_id = '.$follower_id." and user_id = ".$user_id;
                $stmt = $this->conn->query($query);
        
            } else {    
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['user_id' => $user_id, 'follower_id' => $follower_id, 'follow_request'=>$follow_request, 'created_at'=>$created_at]);

            }
            return true;

        } catch(PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }
    
    
   public function selectusertype($follower_id){

            $sql = 'SELECT id FROM users WHERE id = :follower_id AND type="business"';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['follower_id' => $follower_id]);
            $rows = $stmt->fetch();
            return $rows;

    }
    public function insertBusinessFollower($user_id, $follower_id){

        $created_at = date("Y-m-d H:i:s", time());
        // $follow_request = 0;
        // $active = 1;
       
        $sql = 'INSERT INTO followers (user_id, follower_id, follow_request,active, created_at) VALUES (:user_id, :follower_id, "0","1", :created_at)';
         $stmt = $this->conn->query($sql);

        try {
            if ($this->fetchFollowers($user_id, $follower_id)) {
                $query = 'UPDATE followers SET active = "1", updated_at = "'.$created_at.'" where follower_id = '.$follower_id." and user_id = ".$user_id;
                $stmt = $this->conn->query($query);
        
            } else{
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['user_id' => $user_id, 'follower_id' => $follower_id, 'created_at'=>$created_at]);
            }
            $query = 'UPDATE users SET no_of_followers = no_of_followers+1 where id = '.$follower_id;
            $stmt = $this->conn->query($query);
            
            $query1 = 'UPDATE users SET no_of_following = no_of_following+1 where id = '.$user_id;
            $stmt = $this->conn->query($query1);
        
            return true;

        } catch(PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }

    public function updateFollower($user_id, $follower_id) {
        $updated_at = date("Y-m-d H:i:s", time());
        $follow_request = 1;
        
        $sql = 'UPDATE followers SET active = 0, updated_at =:updated_at WHERE user_id = :user_id and follower_id= :follower_id';
        
        try {

            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['user_id' => $user_id, 'follower_id' => $follower_id, 'updated_at'=>$updated_at]);

            $query = 'UPDATE users SET no_of_followers = no_of_followers-1 where id = '.$follower_id;
            $stmt = $this->conn->query($query);
            
            $query1 = 'UPDATE users SET no_of_following = no_of_following-1 where id = '.$user_id;
            $stmt = $this->conn->query($query1);
        
            return true;

        } catch(PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }

    public function acceptFollower($user_id, $follower_id) {
        $updated_at = date("Y-m-d H:i:s", time());
        $follow_request = 1;
        
        
        $sql = 'UPDATE followers SET active = 1,follow_request = 0, updated_at = "'.$updated_at.'" WHERE user_id = '.$user_id.' and follower_id= '.$follower_id;
            $stmt = $this->conn->query($sql);

        try {


            $query = 'UPDATE users SET no_of_followers = no_of_followers+1 where id = '.$follower_id;
            $stmt = $this->conn->query($query);
            
            $query1 = 'UPDATE users SET no_of_following = no_of_following+1 where id = '.$user_id;
            $stmt = $this->conn->query($query1);
        
            return true;

        } catch(PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }

    public function ignoreFollower($user_id, $follower_id) {
        $updated_at = date("Y-m-d H:i:s", time());
        
         $sql = 'UPDATE followers SET `ignore` = 1,follow_request = 0, updated_at = "'.$updated_at.'" WHERE user_id = '.$user_id.' and follower_id= '.$follower_id;
            $stmt = $this->conn->query($sql);

        try {

            return true;

        } catch(PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }


    public function insertComments($user_id, $post_id, $comments) {
        $created_at = date("Y-m-d H:i:s", time());

        $sql = 'INSERT INTO comments (user_id, post_id, comments, created_at) VALUES 
        (:user_id, :post_id, :comments, :created_at)';
        try {

            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['user_id' => $user_id, 'post_id' => $post_id, 'comments'=>$comments, 'created_at'=>$created_at]);
            $query = 'UPDATE post SET no_of_comments = no_of_comments+1 where id = '.$post_id;
            $stmt = $this->conn->query($query);
            
            return true;

        } catch(PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }

    public function insertLike($user_id, $post_id) {
        $created_at = date("Y-m-d H:i:s", time());

        $sqls = 'DELETE FROM post_like WHERE user_id = :user_id and post_id= :post_id';
            $stmt = $this->conn->prepare($sqls);
            $stmt->execute(['user_id' => $user_id, 'post_id' => $post_id]);

        $sql = 'INSERT INTO post_like (user_id, post_id, created_at) VALUES 
        (:user_id, :post_id, :created_at)';
        try {

            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['user_id' => $user_id, 'post_id' => $post_id, 'created_at'=>$created_at]);
            $query = 'UPDATE post SET no_of_likes = no_of_likes+1 where id = '.$post_id;
            $stmt = $this->conn->query($query);
            
            return true;

        } catch(PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }

    public function updateLike($user_id, $post_id) {
        $updated_at = date("Y-m-d H:i:s", time());

        $sql = 'UPDATE post_like SET active = 0, updated_at = "'.$updated_at.'" WHERE user_id = '.$user_id.' and post_id= '.$post_id;
        try {

            $stmt = $this->conn->query($sql);
            $query = 'UPDATE post SET no_of_likes = no_of_likes-1 where id = '.$post_id;
            $stmt = $this->conn->query($query);
            
            return true;

        } catch(PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }

    public function insertGallery($certificate_proof, $i, $newfilename) {
        $fileName  =  $newfilename;
        $tempPath  =  $certificate_proof['tmp_name'][$i];
        $fileSize  =  $certificate_proof['size'][$i];
        $upload_path = 'gallery/'; // set upload folder path 
        $fileExt = strtolower(pathinfo($fileName,PATHINFO_EXTENSION)); // get image extension
        
        // valid image extensions
        $valid_extensions = array('jpeg', 'jpg', 'png', 'gif','mp4','mp3'); 
        // allow valid image file formats
        if ($certificate_proof["error"][$i] > 0)
        {
            $errorMSG = json_encode(array("message" => $certificate_proof["error"][$i], "status" => false));   
            echo $errorMSG;
            exit; 
        }

        if(in_array($fileExt, $valid_extensions))
        {               
            if(!file_exists($upload_path . $fileName))
            {
                // check file size '50MB'
                if($fileSize < 50000000){
                    move_uploaded_file($tempPath, $upload_path . $fileName); // move file from system temporary path to our upload folder path 
                }
                else{       
                    $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload below 50 MB size", "status" => false));   
                    echo $errorMSG;
                    exit;
                }
            }
            else
            {       
                $errorMSG = json_encode(array("message" => "Sorry, file already exists check upload folder", "status" => false));   
                echo $errorMSG;
                exit;
            }
        }
        else
        {       
            $errorMSG = json_encode(array("message" => "Sorry, only JPG, JPEG, PNG & GIF, MP3, MP4 files are allowed", "status" => false));   
            echo $errorMSG;
            exit;   
        }

    }

    public function insertCard($certificate_proof, $i, $newfilename) {
        $fileName  =  $newfilename;
        $tempPath  =  $certificate_proof['tmp_name'][$i];
        $fileSize  =  $certificate_proof['size'][$i];
        $upload_path = 'ratecard/'; // set upload folder path 
        $fileExt = strtolower(pathinfo($fileName,PATHINFO_EXTENSION)); // get image extension
        
        // valid image extensions
        $valid_extensions = array('jpeg', 'jpg', 'png', 'gif','mp4','mp3'); 
        // allow valid image file formats
        if ($certificate_proof["error"][$i] > 0)
        {
            $errorMSG = json_encode(array("message" => $certificate_proof["error"][$i], "status" => false));   
            echo $errorMSG;
            exit; 
        }

        if(in_array($fileExt, $valid_extensions))
        {               
            if(!file_exists($upload_path . $fileName))
            {
                // check file size '50MB'
                if($fileSize < 50000000){
                    move_uploaded_file($tempPath, $upload_path . $fileName); // move file from system temporary path to our upload folder path 
                }
                else{       
                    $errorMSG = json_encode(array("message" => "Sorry, your file is too large, please upload below 50 MB size", "status" => false));   
                    echo $errorMSG;
                    exit;
                }
            }
            else
            {       
                $errorMSG = json_encode(array("message" => "Sorry, file already exists check upload folder", "status" => false));   
                echo $errorMSG;
                exit;
            }
        }
        else
        {       
            $errorMSG = json_encode(array("message" => "Sorry, only JPG, JPEG, PNG & GIF, MP3, MP4 files are allowed", "status" => false));   
            echo $errorMSG;
            exit;   
        }

    }

    
    /*
    // Update an user in the database
    public function update($name, $email, $phone, $id) {
        $sql = 'UPDATE users SET name = :name, email = :email, phone = :phone WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['name' => $name, 'email' => $email, 'phone' => $phone, 'id' => $id]);
        return true;
    }

    // Delete an user from database
    public function delete($id) {
        $sql = 'DELETE FROM users WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        return true;
    }*/

    public function getLike($id) { 
        $sql = 'SELECT no_of_likes from post';
        $sql .= ' WHERE id = :id';
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        $rows = $stmt->fetchAll();
        return $rows;
    }
    public function getComments($post_id) { 
        $sql = 'SELECT c.id, c.user_id, c.post_id, c.comments, c.active, u.user_name, u.profile_photo, UNIX_TIMESTAMP(c.created_at)-(19800) AS created_at from comments c left join users u on u.id = c.user_id';
        $sql .= ' WHERE post_id = :post_id ORDER BY c.id DESC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['post_id' => $post_id]);
        $rows = $stmt->fetchAll();
        return $rows;
    }

    public function insertRateCard($ratecard, $user_id) {
        $created_at = date("Y-m-d H:i:s", time());
        $sql = 'INSERT INTO ratecard (ratecard, user_id, created_at) VALUES 
        (:ratecard, :user_id, :created_at)';
        try {

            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['ratecard' => $ratecard, 'user_id' => $user_id, 'created_at'=>$created_at]);
            return true;

        } catch(PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }
    public function getRateCard($user_id) { 
        $sql = 'SELECT * from ratecard';
        $sql .= ' WHERE user_id  = :user_id';
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        $rows = $stmt->fetchAll();
        return $rows;
    }

    public function deleteRateCard($id) {
        $sql = 'DELETE FROM ratecard WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        return true;
    }

   public function deletepost($post_id) {
        $sql = 'DELETE FROM post WHERE id = :post_id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['post_id' => $post_id]);
        return true;
    }

public function updatepost($user_id) {
        $sql = 'UPDATE users SET no_of_posts = no_of_posts-1 WHERE id = :user_id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        return true;
    }

    public function insertratingandreview($user_id, $review_user_id, $review ,$rating) {
        $created_at = date("Y-m-d H:i:s", time());

        $sql = 'INSERT INTO rating(user_id, review_user_id, reviews, rating, created_at) VALUES 
        (:user_id, :review_user_id, :review, :rating, :created_at)';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'review_user_id' => $review_user_id, 'review' => $review,'rating' =>$rating, 'created_at'=>$created_at]);

        return true;
      

    }
  public function getratingandreview($user_id) {

        $query = 'SELECT r.id, u.id, u.user_name, u.profile_photo, r.review_user_id, r.rating, r.reviews, UNIX_TIMESTAMP(r.created_at)-(19800) AS created_at FROM rating r INNER JOIN users u on u.id = r.user_id WHERE r.review_user_id = :user_id ORDER BY r.id DESC';
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['user_id' => $user_id]);
        $rows = $stmt->fetchAll();
        return $rows;

    }
    public function getRating($user_id) { 
        $sql = 'SELECT avg(rating) as avg_rating from rating';
        $sql .= ' WHERE review_user_id  = :user_id';
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        $rows = $stmt->fetchAll();
        return $rows;
    } 

    public function userSearch($key, $user_id)
    {
       $query = 'select * from users where block = "false" and deactivate != "true" and (user_name like "%'.$key.'%" or name like "%'.$key.'%")';
       $query .= ' and id not in (select blocker_id from blocklist where user_id = '.$user_id.') and id not in (select user_id from blocklist where blocker_id = '.$user_id.') ';
       
        $stmt = $this->conn->query($query);
        $rows = $stmt->fetchAll();
        return $rows;    
    }
    
  public function postSearch($key, $user_id)
    {
       $query = 'select p.id ,t.user_name as taguser, p.user_id, p.name, gallery, p.description, p.tag, p.no_of_comments, p.no_of_likes, p.created_at, u.user_name, u.profile_photo, u.type, l.active, b.active as bookmark_active from post p inner join users u on u.id = p.user_id left join post_like l on l.post_id = p.id and l.user_id = '.$user_id.' left join bookmarks b on b.post_id = p.id and b.user_id = '.$user_id.' left join users t on p.tag = t.id where u.block = "false" and u.deactivate != "true" and p.id not in (select post_id from hide_post where user_id = '.$user_id.') and (p.name like "%'.$key.'%" OR u.categories like "%'.$key.'%" or u.address like "%'.$key.'%" or p.description like "%'.$key.'%")  and p.id not in (select post_id from hide_post where user_id = '.$user_id.') and p.user_id  not in (select user_id from blocklist where blocker_id = '.$user_id.') and p.user_id not in (select blocker_id from blocklist where user_id = '.$user_id.')';

        $stmt = $this->conn->query($query);
        $rows = $stmt->fetchAll();
        return $rows;    
    }
    public function postFilter($city, $area, $category, $user_id)
    {

      $query = 'SELECT p.id,t.user_name as taguser, p.user_id, p.name, gallery, p.description, p.tag, p.no_of_comments, p.no_of_likes, p.created_at, u.user_name, u.profile_photo, u.type, l.active, b.active as bookmark_active from post p inner join users u left join post_like l on l.post_id = p.id and l.user_id = '.$user_id.' left join bookmarks b on b.post_id = p.id and b.user_id = '.$user_id.' left join users t on p.tag = t.id where u.block = "false" and u.deactivate != "true" and u.block = "false" and u.deactivate != "true" and u.id = p.user_id';
	  
       if ($city !== '') {
            $query .= ' and (u.address like "%'.$city.'%")';
       }
       if ($area !== '') {
            $query .= ' and (u.address like "%'.$area.'%")';
       }

//        if ($category !== '') {
//         $query .= ' and (u.categories like "%'.$category.'%" )';
//    }

       if ($category !== '') {
            $query .= ' and (p.name REGEXP "[[:<:]]('.$category.')[[:>:]]")';
       }
	   
       $query .= ' and p.id not in (select post_id from hide_post where user_id = '.$user_id.') and p.user_id not in (select user_id from blocklist where blocker_id = '.$user_id.') and p.user_id not in (select blocker_id from blocklist where user_id = '.$user_id.') order by p.id desc';
	   
        $stmt = $this->conn->query($query);
        $rows = $stmt->fetchAll();
        return $rows;    
    }

    public function userFilter($city, $area, $category, $user_id)
    {

        $query = 'select * from users u where u.block = "false" and u.deactivate != "true" and type !=  "user" ';
        if ($city !== '') {
            $query .= ' and (address like "%'.$city.'%")';
        }

        if ($area !== '') {
            $query .= ' and (address like "%'.$area.'%")';
        }

        if ($category !== '') {
            $query .= ' and (categories like "%'.$category.'%" )';
        }
        $query .= ' and id not in (select user_id from blocklist where blocker_id = '.$user_id.') and id not in (select blocker_id from blocklist where user_id = '.$user_id.') ';
       
        $query .= ' order by id desc';
        $stmt = $this->conn->query($query);
        $rows = $stmt->fetchAll();
        return $rows;    
    }

    public function followRequest($user_id)
    {
        $query = "select u.* from users u inner join followers f on f.user_id = u.id where f.follow_request=1 and follower_id = ".$user_id;
        $stmt = $this->conn->query($query);
        $rows = $stmt->fetchAll();
        return $rows;
    }  

    public function insertBlocker($user_id, $blocker_id) {
        $created_at = date("Y-m-d H:i:s", time());

        $sql = 'INSERT INTO blocklist (user_id, blocker_id, created_at) VALUES 
                (:user_id, :blocker_id, :created_at)';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'blocker_id' => $blocker_id, 'created_at'=>$created_at]);

        return true;
    }
	
	 public function insertBlockerinchat($user_id, $blocker_id) {
        $created_at = date("Y-m-d H:i:s", time());

        $sql = 'UPDATE latest_chat SET user_block = "1" WHERE from_id = :user_id AND to_id = :blocker_id OR from_id = :blocker_id AND to_id = :user_id';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'blocker_id' => $blocker_id]);

        return true;
    }

    public function getBlockList($user_id) {
        $query = "select u.* from users u inner join blocklist b on b.blocker_id = u.id where  user_id = ".$user_id;
        $stmt = $this->conn->query($query);
        $rows = $stmt->fetchAll();
        return $rows;
    }

    public function getIgnoreList($user_id) {
        $query = "select u.* from users u inner join followers f on f.user_id = u.id where f.ignore=1 and follower_id = ".$user_id;
        $stmt = $this->conn->query($query);
        $rows = $stmt->fetchAll();
        return $rows;
    }

    public function insertContactDetails($name, $number, $email, $message) {
        $created_at = date("Y-m-d H:i:s", time());

        $sql = 'INSERT INTO contact_details (name, `number`, email, message, created_at) VALUES 
                (:name, :mnumber, :email, :message, :created_at)';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['name' => $name, 'mnumber' => $number, 'email' => $email, 'message' => $message, 'created_at'=>$created_at]);

        return true;

    }

    public function updatePrivacy($user_id, $privacy) {
        $updated_at = date("Y-m-d H:i:s", time());
        
        $sql = 'UPDATE users SET privacy = "'.$privacy.'", updated_at = "'.$updated_at.'" WHERE id = '.$user_id;
            $stmt = $this->conn->query($sql);
        return true;

    }

    // public function insertReviews($user_id, $review_user_id, $reviews) {
    //     $created_at = date("Y-m-d H:i:s", time());

    //     $sql = 'INSERT INTO reviews (user_id, `review_user_id`, reviews, created_at) VALUES 
    //             (:user_id, :review_user_id, :reviews, :created_at)';

    //     $stmt = $this->conn->prepare($sql);
    //     $stmt->execute(['user_id' => $user_id, 'review_user_id' => $review_user_id, 'reviews' => $reviews, 'created_at'=>$created_at]);

    //     return true;

    // }
    // public function getReviews($user_id) {
    //     $query = 'SELECT f.id AS review_id,r.id AS rating_id,u.id AS review_userid,u.user_name AS review_username, u.profile_photo AS review_userprofile,f.reviews,r.review_user_id,rt.id AS rating_userid, rt.user_name AS rating_username, rt.profile_photo AS rating_userprofile, r.rating from users u inner join rating f on f.user_id = u.id inner join rating r on r.review_user_id=f.review_user_id inner join users rt on rt.id = r.user_id where f.review_user_id = :user_id OR r.review_user_id = :user_id';
    //     $stmt = $this->conn->prepare($query);
    //     $stmt->execute(['user_id' => $user_id]);
    //     $rows = $stmt->fetchAll();
    //     return $rows;

    // }

    public function insertBlockCommentsUser($user_id, $block_user_id) {
        $created_at = date("Y-m-d H:i:s", time());

        $sql = 'INSERT INTO block_comments_user_list (user_id, `block_user_id`, created_at) VALUES 
                (:user_id, :block_user_id, :created_at)';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'block_user_id' => $block_user_id, 'created_at'=>$created_at]);

        return true;
    }

    public function UnBlockCommentsUser($user_id, $block_user_id) {
       
        $sql = 'DELETE FROM block_comments_user_list WHERE user_id=:user_id AND block_user_id=:block_user_id';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'block_user_id' => $block_user_id]);

        return true;
    }

    public function getBlockComments($user_id) { 
        
    $sql = 'SELECT b.user_id, b.block_user_id,u.user_name,u.name,u.profile_photo from block_comments_user_list b JOIN users u ON b.block_user_id = u.id WHERE b.user_id = :user_id GROUP BY b.block_user_id';
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        $rows = $stmt->fetchAll();
        return $rows;
    }
    public function getBlockedCommentsbyuser($user_id, $block_user_id) { 
        $sql = 'SELECT * from block_comments_user_list WHERE user_id = :user_id and block_user_id = :block_user_id';
             
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['block_user_id' => $block_user_id,'user_id' => $user_id]);
        $rows = $stmt->fetchAll();
        return $rows;
    }


    public function fetchFollowing($id = 0) {
        $sql = 'SELECT  u.id, u.user_name, u.name, u.mobile, u.email, u.password, u.dob, u.gender, u.address, u.type, u.categories, u.others, u.service, u.certificate_proof, u.no_of_posts, u.no_of_following, u.no_of_followers, u.profile_photo, u.created_at, f.active, f.follow_request  FROM users u inner join followers f  on f.follower_id = u.id  and f.user_id = :id and f.active = 1  and u.id not in (select blocker_id from blocklist where user_id = :id)  order by u.id desc';
    

        $stmt = $this->conn->prepare($sql);
        if ($id != 0) {
            $stmt->execute(['id' => $id]);
        }
        
        $rows = $stmt->fetchAll();
        return $rows;
    }

    public function hidePost($user_id, $post_id) {
        $created_at = date("Y-m-d H:i:s", time());

      $sql = 'INSERT INTO hide_post (user_id, post_id, created_at) VALUES 
        (:user_id, :post_id, :created_at)';
        try {

            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['user_id' => $user_id, 'post_id' => $post_id, 'created_at'=>$created_at]);
            
            return true;

        } catch(PDOException $e) {
            throw new \PDOException($e->getMessage());
        }
    }

    public function removeBlocker($user_id, $blocker_id) {
        $created_at = date("Y-m-d H:i:s", time());

        $sql = 'DELETE FROM blocklist WHERE user_id = :user_id and blocker_id = :blocker_id';
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'blocker_id' => $blocker_id]);

        return true;
    }
	
	public function removeBlockerinchat($user_id, $blocker_id) {
       

        $sql = 'UPDATE latest_chat SET user_block = "0" WHERE from_id = :user_id AND to_id = :blocker_id OR from_id = :blocker_id AND to_id = :user_id';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'blocker_id' => $blocker_id]);

        return true;
    }

    public function updatePassword($mobile, $password) {
        $updated_at = date("Y-m-d H:i:s", time());
        
        $sql = 'UPDATE users SET password = "'.$password.'", updated_at = "'.$updated_at.'" WHERE mobile = '.$mobile;
            $stmt = $this->conn->query($sql);
        return true;

    }
    
    public function insertNotification($from_id, $to_id, $types, $mesg, $post_id)
    {
        //date_default_timezone_set('Asia/Kolkata');
        $created_at = date("Y-m-d H:i:s A", time());

        $sql = 'INSERT INTO notifications (from_id, to_id, types, mesg, post_id, created_at) VALUES (:from_id, :to_id, :types, :mesg, :post_id, :created_at)';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['from_id' => $from_id, 'to_id' => $to_id, 'types' => $types, 'mesg' => $mesg, 'post_id' => $post_id, 'created_at' => $created_at]);
        return true;
    }
    public function insertNotificationwithoutpid($from_id, $to_id, $types, $mesg)
    {
        //date_default_timezone_set('Asia/Kolkata');
        $created_at = date("Y-m-d H:i:s A", time());

        $sql = 'INSERT INTO notifications (from_id, to_id, types, mesg, created_at) VALUES (:from_id, :to_id, :types, :mesg, :created_at)';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['from_id' => $from_id, 'to_id' => $to_id, 'types' => $types, 'mesg' => $mesg, 'created_at' => $created_at]);
        return true;
    }

    public function fetchusersnotification($to_id)
    {
        //TIME(n.created_at) AS "created_at"
        
        $sql = 'SELECT n.id, n.from_id, n.to_id, n.types, n.mesg, n.post_id, UNIX_TIMESTAMP(n.created_at)-(19800) AS "created_at", n.read_notify, u.user_name, u.profile_photo FROM notifications n INNER JOIN users u ON n.from_id = u.id WHERE n.to_id = :to_id ORDER BY n.id DESC';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['to_id' => $to_id]);
        return $stmt->fetchAll();
    }
    public function insertnotifyactive($id)
    {
        //$read_at = date("Y-m-d h:i:s A", time());

        $sql = 'UPDATE notifications SET read_notify = "1" WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);
        return true;
    }
    
    public function usertype($from_id, $to_id){
               
        $sql = 'UPDATE chat set f_type=(Select type from users where id= :from_id), t_type=(Select type from users where id=:to_id) WHERE id = LAST_INSERT_ID()';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['from_id' => $from_id, 'to_id' => $to_id]);
        return $stmt->fetch();
        
    }
 
    
    public function insertchat($from_id, $to_id, $messages)
    {
        $created_time = date("Y-m-d H:i:s A", time());

        $sql = 'INSERT INTO chat (from_id, to_id, messages, created_time) VALUES (:from_id, :to_id, :messages, :created_time)';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['from_id' => $from_id, 'to_id' => $to_id, 'messages' => $messages, 'created_time' => $created_time]);
        return true;
    }
    public function fetchuserschat($from_id, $to_id)
    {
        $sql = 'SELECT c.id, c.from_id, c.to_id, c.messages, UNIX_TIMESTAMP(c.created_time) AS "created_time", u.user_name, u.profile_photo FROM chat c INNER JOIN users u ON c.to_id = u.id WHERE (c.from_id = :to_id AND c.to_id = :from_id) OR (c.to_id = :to_id AND c.from_id = :from_id) ORDER BY c.id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['from_id' => $from_id, 'to_id' => $to_id]);
        return $stmt->fetchAll();
    }

    public function selectchatid($from_id, $to_id)
    {
        $sql = 'SELECT from_id, to_id FROM latest_chat WHERE (from_id = :from_id AND to_id = :to_id) OR (from_id = :to_id AND to_id = :from_id)';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['from_id' => $from_id, 'to_id' => $to_id]);
        return $stmt->fetchAll();
    }
    
    
    public function insertlatestchat($from_id, $to_id, $messages)
    {
        $created_time = date("Y-m-d H:i:s A", time());

        $sql = 'INSERT INTO latest_chat (from_id, to_id, messages, created_time) VALUES (:from_id, :to_id, :messages, :created_time)';
        $stmt = $this->conn->prepare($sql);
       $stmt->execute(['from_id' => $from_id, 'to_id' => $to_id, 'messages' => $messages, 'created_time' => $created_time]);
        return true;        
    }
    
    public function updatechat($from_id, $to_id, $messages)
    {
        $created_time = date("Y-m-d H:i:s A", time());

        $sql = 'UPDATE latest_chat SET  from_id=:from_id, to_id = :to_id, messages = :messages, created_time = :created_time WHERE (from_id = :from_id AND to_id = :to_id) OR (from_id = :to_id AND to_id = :from_id)';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['from_id' => $from_id, 'to_id' => $to_id, 'messages' => $messages, 'created_time' => $created_time]);
        return true;
    }
    public function insertchatactive($from_id,$to_id)
    {
        $sql = 'UPDATE chat SET read_chat = "1" WHERE from_id = :from_id AND to_id=:to_id';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['from_id' => $from_id,'to_id'=>$to_id]);
        return true;
    }
    
 public function fetchchat($from_id)
    {
        $sql = 'SELECT l.chat_block,l.user_block,l.admin_block,l.deactivate,u.user_name AS from_username, u.firebase_userid as from_firebaseuser,u.profile_photo AS from_profile,v.user_name AS to_username,v.firebase_userid as to_firebaseuser,v.profile_photo AS to_profile, UNIX_TIMESTAMP(l.created_time)-(19800) AS "created_time", c.read_chat, l.to_id, l.from_id, l.messages AS "latest_message" FROM chat c INNER JOIN latest_chat l ON c.from_id = l.from_id INNER JOIN users u ON u.id = l.from_id INNER JOIN users v ON v.id = l.to_id WHERE l.from_id = :from_id OR l.to_id = :from_id GROUP BY l.created_time DESC'; 
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['from_id' => $from_id]);
        return $stmt->fetchAll();
   
    }


    	    public function getchatblock($from_id){

        $sql = 'SELECT blocker_id,user_id FROM `blocklist` where user_id = :from_id OR blocker_id = :from_id'; 
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['from_id' => $from_id]);
        return $stmt->fetchAll();
     
    }
    
    public function fetchnotifyswitch($user_id)
    {
        $sql = 'SELECT id,like_comment,message,follow_request,follow_accept,user_post FROM users WHERE id=:user_id'; 
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchAll();
    }

    public function fetchBookmark($user_id, $post_id) {
        $sql = 'SELECT * FROM bookmarks';
        $sql .= ' WHERE (user_id = :user_id and post_id = :post_id)';
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'post_id' => $post_id]);
        $rows = $stmt->fetchAll();
        return $rows;
    }

    public function updateBookmark($user_id, $post_id, $data) {
        $data = $data[0];
        $id = $data['id'];
        if ($id != '' && $id != 0) {
            $active = ($data['active'] == 1) ? 0 : 1;
            $query = 'UPDATE bookmarks SET active = '.$active.' where user_id = '.$user_id.' and post_id ='.$post_id ;

        } else { 
            $created_at = date("Y-m-d H:i:s", time());
            $query = 'INSERT INTO bookmarks (user_id, post_id, active, created_at) VALUES ('.$user_id.', '.$post_id.', "1", "'.$created_at.'")';

        }
        try {

            $stmt = $this->conn->query($query);
            return true;

        } catch(PDOException $e) {
            throw new \PDOException($e->getMessage());
        }

    }

    public function getBookmarks($user_id)
    {
        $sql = 'SELECT p.id, t.user_name as taguser, p.user_id, p.name, p.description, p.tag, p.gallery, p.no_of_comments, p.no_of_likes, l.active, u.user_name, u.profile_photo, u.type from post p left join post_like l on l.post_id = p.id and l.user_id = :user_id left join users u on u.id = p.user_id left join users t on p.tag = t.id where p.id != "" ';

        $sql .= ' and p.id in (select post_id from bookmarks where active =1 and user_id = :user_id) ';
        $sql .= ' order by p.id desc';

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        $rows = $stmt->fetchAll();
        return $rows;
    }
    
    
    public function insertnotifyswitch($user_id,$like_comment,$message,$follow_request,$follow_accept,$user_post){

        $sql = 'UPDATE users SET like_comment=:like_comment,message=:message,follow_request=:follow_request,follow_accept=:follow_accept,user_post=:user_post WHERE id=:user_id';
       
        $stmt = $this->conn->prepare($sql);
        $i=$stmt->execute(['user_id' => $user_id,'like_comment' => $like_comment,'message' => $message,'follow_request' => $follow_request,'follow_accept' => $follow_accept,'user_post' => $user_post]);
        return $stmt->fetchAll();
     
    }
    
      public function getchatcount($to_id){

        $sql = 'SELECT from_id, COUNT(from_id) AS Count FROM `chat` WHERE to_id = :to_id and from_id != :to_id and read_chat = "0" GROUP BY from_id'; 
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['to_id' => $to_id]);
        return $stmt->fetchAll();
     
    }
	

        public function deactivateaccount($user_id){
        
        $sql = 'UPDATE users SET deactivate = "false" WHERE id = :user_id'; 
       
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
       
        return true;
       
    }
	
    public function chatblock($chatuser_id,$chatblocker_id){

        $sql = 'INSERT INTO chat_block (user_id,blocker_id) VALUES (:chatuser_id, :chatblocker_id)'; 
       
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['chatuser_id' => $chatuser_id, 'chatblocker_id' => $chatblocker_id]);
       
        return true;
    }

    public function updatechatblock($chatuser_id,$chatblocker_id){

        $sql = 'UPDATE latest_chat SET chat_block = :chatblocker_id WHERE from_id = :chatuser_id AND to_id = :chatblocker_id OR from_id = :chatblocker_id AND to_id = :chatuser_id'; 
       
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['chatuser_id' => $chatuser_id, 'chatblocker_id' => $chatblocker_id]);
       
        return true;
    }
    public function chatUnblock($chatuser_id,$chatblocker_id){

        $sql = 'DELETE FROM chat_block WHERE user_id = :chatuser_id  AND blocker_id = :chatblocker_id'; 
       
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['chatuser_id' => $chatuser_id, 'chatblocker_id' => $chatblocker_id]);
       
        return true;
    }

    public function updatechatUnblock($chatuser_id,$chatblocker_id){

        $sql = 'UPDATE latest_chat SET chat_block = "0" WHERE from_id = :chatuser_id AND to_id = :chatblocker_id OR from_id = :chatblocker_id AND to_id = :chatuser_id'; 
       
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['chatuser_id' => $chatuser_id, 'chatblocker_id' => $chatblocker_id]);
       
        return true;
    }

    public function getchatblocklist($chatuser_id){

        $sql = 'SELECT user_id AS chatuser_id, blocker_id AS chatblocker_id from chat_block WHERE user_id = :chatuser_id'; 
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['chatuser_id' => $chatuser_id]);
        return $stmt->fetchAll();

    }
    
       public function deleteuser($user_name)
       {
           $sql = 'DELETE from users WHERE user_name = :user_name'; 
           $stmt = $this->conn->prepare($sql);
           $stmt->execute(['user_name' => $user_name]);
           return true;
       }
       
    //To get post details
    public function getPost($user_id, $post_id)
    {
        $sql = 'SELECT p.id, u.id AS user_id, p.name AS postname, p.description, p.tag, p.gallery, u.profile_photo, u.name, u.type, t.user_name AS taguser, u.user_name, u.address, p.no_of_likes, p.no_of_comments, l.active, b.active AS bookmark_active FROM post p
        LEFT JOIN post_like l ON l.post_id = p.id AND l.user_id = :user_id
        LEFT JOIN bookmarks b ON b.post_id = p.id and b.user_id = :user_id
        LEFT JOIN users u ON u.id = p.user_id  
        LEFT JOIN users t ON p.tag = t.id
        WHERE p.id = :post_id';
    
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id, 'post_id' => $post_id]);
        return $stmt->fetchAll();
    }


}

?>
