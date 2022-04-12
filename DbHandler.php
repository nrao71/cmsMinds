<?php
/**
 * Class to handle all db operations
 * This class will have CRUD methods for database tables
 *
 * @author manikanta sarma
 * @link URL Tutorial link
 */
ini_set("allow_url_fopen", 1);

class DbHandler {
private $conn;

    function __construct() {
        require_once dirname(__FILE__) . '/DbConnect.php';
        require_once dirname(__FILE__) . '/SmsService.php';
        require_once dirname(__FILE__) . '/PasswordHash.php';
        // opening db connection
        date_default_timezone_set('UTC');
        $db = new DbConnect();
        $this->conn = $db->connect();
    }

    /************function for check is valid api key*******************************/
    function isValidApiKey($token)
    {
        //echo 'SELECT userId FROM registerCustomers WHERE apiToken="'.$token.'"';exit;
        $query ='SELECT userId FROM registerCustomers WHERE apiToken="'.$token.'"';// AND password = $userPass";
        $result = mysqli_query($this->conn, $query);
        $num=mysqli_num_rows($result);
        return $num;
	}

	/************function for check is valid api key*******************************/
    function isValidSessionToken($token,$user_id)
    {
		//echo 'SELECT userId FROM registerCustomers WHERE apiToken="'.$token.'"';exit;
		$query ='SELECT * FROM ohrm_user_token WHERE userid = "'.$user_id.'" and session_token ="'.$token.'"';// AND password = $userPass";
		$result = mysqli_query($this->conn, $query);
		$num=mysqli_num_rows($result);
		return $num;
	}
		/**
     * Generating random Unique MD5 String for user Api key
     */
    function generateApiKey() {
        return md5(uniqid(rand(), true));
    }
	/** Password Encryption Algorithim*/
	function encrypt($str)
	{
		$key='grubvanapp1#20!8';
		$block = mcrypt_get_block_size('rijndael_128', 'ecb');
		$pad = $block - (strlen($str) % $block);
		$str .= str_repeat(chr($pad), $pad);
		$rst = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $str,MCRYPT_MODE_ECB,str_repeat("\0", 16)));
		return str_ireplace('+', '-', $rst);
	}

   /************function for check is valid api key*******************************/
  function getUserId($token)
    {
		$user_id='';
		// $query = "SELECT userId FROM  registerCustomers WHERE apiToken='$token'"; //table
		// $result=mysqli_query($this->conn, $query);
		// if(mysqli_num_rows($result)>0)
		// {
		//    $row = mysqli_fetch_array($result);
		//    $user_id=$row['userId'];
	 //    }
	   return 6;
	}
 //uploadImage($ticket_id,$image,$fileName,$fileType,$fileSize)
    function uploadImage($ticket_id,$file_name,$file_type,$file_size,$file_content,$created_on,$user_id)
    {
        $data=array();

        $created_by = $this->getEmpnumberByUsrId($user_id);
   			// Prepare an insert statement
			$sql = "INSERT INTO ohrm_ticket_attachment (ticket_id,file_name,file_type,file_size,file_content,created_on,created_by) VALUES (?,?,?,?,?,?,?)";
			 
			if($stmt = mysqli_prepare($this->conn, $sql)){
			    // Bind variables to the prepared statement as parameters
			     mysqli_stmt_bind_param($stmt, "ississs" , $ticket_id,$file_name,$file_type,$file_size,
			     	$file_content,$created_on,$created_by);
			    			   
			    // Attempt to execute the prepared statement
			    if(mysqli_stmt_execute($stmt)){
			        $data['uploadImage'] = "Image added successfully";
			        $data['status']=1;
			    } else{
			        //echo "ERROR: Could not execute query: $sql. " . mysqli_error($this->conn);
			        $data['status']=0;
			    }
			} else{
			    //echo "ERROR: Could not prepare query: $sql. " . mysqli_error($this->conn);
			    $data['status']=0;
			}	

        return $data;
    }
	function generateSessionToken($user_id)
	{
		$data=array();
		$token=$this->generateApiKey();
		$query = "SELECT * FROM ohrm_user_token WHERE userid = $user_id";
		$count=mysqli_query($this->conn, $query);

		if(mysqli_num_rows($count) > 0)
		{
			$row=mysqli_fetch_assoc($count);
			$token_userid = $row['userid'];
				if($token_userid == $user_id){
					$updatesql ="UPDATE ohrm_user_token SET session_token='$token' WHERE userid=$user_id";
					if($result2 = mysqli_query($this->conn, $updatesql)){
						$data['session_token'] = $token;
				        $data['status']=1;
					}else{
					    $data['status']=0;
					}
				}else{
					$data['status']=0;
				}
		}
		return $data;
    }
//usersignupfrommobile
  // User SignUp
    function userSignUpfrommobile($password,$mobile,$username,$email)
    {
        $data=array();
        	$data['password'] = $password;
    		$data['uname'] = $username;
			$data['mobile'] = $mobile;
			$data['email'] = $email;
           

			// Prepare an insert statement
		$sql = "insert into users(uname,mobile,email,password)values($username,$mobile,$email,$password)'";
		$res = $this->conn->query($sql);
		

		if($res){
		$data['userDetails'] = $data;
		$data['status']=1;
		}else{
			$data['status']=0;
		}
	return $data;
    }
    
// Happygo hsignUp    
    
    function hsignUp($name,$mobile,$password,$email,$created_on)
    {
        $data=array();
        	$data['Name'] = $name;
    		$data['Mobile_no'] = $mobile;
			$data['Password'] = $password;
			$data['Email']=$email;
			$data['Created_on'] =$created_on;
			// Prepare an insert statement
		$sql = "INSERT INTO `users`(`name`, `mobile`, `password`, `email`, `created_on`) VALUES ('".$name."','".$mobile."','".$password."','".$email."','".$created_on."')";
		$res = $this->conn->query($sql);
		if($res){
		$data['userDetails'] = $data;
		$data['status']="True";
		}else{
			$data['status']="False";
		}
	return $data;
    }

function createPost($user_id,$post_title, $location,$description,$category_id,$instruction,$post_media,$hashtags)
    {
        $data=array();
        	
			// Prepare an insert statement
		$sql1 = "select user_id from users where user_id='$user_id'";
		$res1 = $this->conn->query($sql1);
		$check_row1 = $res1->fetch_array();
		if($check_row1){
		    $user_id = $check_row1['user_id'];
		}
		$number = rand(100,100000);
		$t=time();
		$random = $number.''.$t;


		$sql2 = "INSERT INTO `posts`(`user_id`, `post_name`,`location`,`post_description`,`category_id`,`instruction`,`uniquenumber`) VALUES
		            ('".$user_id."',  '".$post_title."','".$location."','".$description."','".$category_id."','".$instruction."','".$random."')";
		$res2 = $this->conn->query($sql2);
			
		if($res2){
		    $sqlgetpost = "select post_id from posts where uniquenumber = '$random'";
		$resgetpost = $this->conn->query($sqlgetpost);
		$postid = $resgetpost['post_id'];
		    foreach($post_media as $value){
		       $name  = $value['name'];
		       $type = $value['type'];
		      $thumbnail   = $value['thumbnail'];
		        
    $sqlmedia = "INSERT INTO `post_media`( `post_id`, `media_name`, `media_thumbnail`, `media_type`) VALUES ('$postid','$name','$type','$thumbnail')";
		$resmedia = $this->conn->query($sqlmedia);
		
		}
		 foreach($hashtags as $value){
		      $hashtag_id  = $value['hashtag_id'];
		      
    $sqlmedia = "INSERT INTO `post_hashtags`(`post_id`, `hashtag_id`) VALUES('$postid','$hashtag_id')";
		$resmedia = $this->conn->query($sqlmedia);
		 }	
//}
		    
		    $data['status']="True";
		    
		}else{
			$data['status']="False";
		}
	return $data;
    }


function register($user_name,$first_name,$last_name, $email, $mobile, $dob,$password,$profile_pic)
    {
        $data=array();
        	
			// Prepare an insert statement
		$sql = "INSERT INTO `users`(`user_name`, `first_name`,`last_name`,`email`,`mobile`,`dob`, `password`, `profile_image`) VALUES
		            ('".$user_name."',  '".$first_name."','".$last_name."','".$email."','".$mobile."','".$dob."','".$password."', '".$profile_pic."')";
		$res = $this->conn->query($sql);
		if($res){
		    $sql2 = "select * from users where mobile = '$mobile' and user_name = '$user_name'";
		    $res2 = $this->conn->query($sql2);
		    $check_row1 = $res2->fetch_array();
		    if ($check_row1){
		        
		        $data['userDetails'] = $check_row1;
		        $data['status']="True";
		    }
		}else{
			$data['status']="False";
		}
	return $data;
    }



function hforgotPassword($email)
    {
        $data=array();
        	$sql1 = "SELECT * FROM users WHERE email = '$email'";
        	$res1 = $this->conn->query($sql1);
        	$check_row1 = $res1->fetch_array();
        	if($check_row1) {
        		$otp = rand(100000, 999999);
				$data['token'] = null;
				$data['user_id'] = $check_row1['user_id'];
				$data['otp'] = $otp;
					
				$data['userDetails'] = $data;
				$data['status']="Success";
		}else{
			$data['status']="False";
		}
	return $data;

   	}
   	
//forgotpassword email   	

function forgotPasswordEmail($email)
    {
        $data=array();
        	$sql1 = "SELECT * FROM users WHERE email = '$email'";
        	$res1 = $this->conn->query($sql1);
        	$check_row1 = $res1->fetch_array();
        	if($check_row1) {
        		$otp = rand(100000, 999999);
				$user_id = $data['user_id'] = $check_row1['user_id'];
				$data['otp'] = $otp;
				$sql2 = "UPDATE forgot_password SET otp='$otp' WHERE user_id='$user_id'";
				$res2 = $this->conn->query($sql2);
					
				$data['userDetails'] = $data;
				$data['status']="Success";
		}else{
			$data['status']="False";
		}
	return $data;

   	}
   	
function getotpfrommobile($mobile)
    {
        $data=array();


			$sql1 = "SELECT * FROM users where mobile='$mobile' ";
		$res1 = $this->conn->query($sql1);
		$check_row1 = $res1->fetch_array();
		if($check_row1){
			  $data['mobile'] = $mobile;
			  $data['user_id'] = $check_row1['user_id'];
			$otp = rand(100000, 999999);
			$data['otp'] = $otp;
			$sql1 = "UPDATE users SET otp='$otp' WHERE mobile = '$mobile' ";
			 $res1 = $this->conn->query($sql1);   //Message Here
			$un="rx100";
			$pw="Raju@sms";
			$sid="RXGAME";

			$url1 = 'http://smslogin.mobi/spanelv2/api.php?username='.$un.'&password='.$pw.'&to='.$mobile.'&from='.$sid.'&message='.$otp;

			$ret = file($url1);
			$data['userDetails'] = $data;
				$data['status']=1;


				}

	return $data;
}

 // Login Services
    function userLogineExists($mobile,$password){
    	// $pass = md5($password);
    	$data = array();
    	$sql = "SELECT * FROM user where Mobile_Number = '$mobile' AND Password = '$password'";
		$res = $this->conn->query($sql);
		$check_row = $res->fetch_array();
    if($check_row){
		$data['Full_Name'] = $check_row['Full_Name'];
        $data['Mobile_Number']= $check_row['Mobile_Number'];
        $data['Designation']=$check_row['Designation'];
        $data['Shift_Timings']=$check_row['Shift_Timings'];
		$data['Password'] = $check_row['Password'];
		$data['doj'] = $check_row['doj'];
		$data['role'] = $check_row['role'];
		$data['Employee_Id'] = $check_row['Employee_Id'];
			

			$data['userDetails'] = $data;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;
    }
    // contests_status
    function contests_status($user_id,$contest_type_id){
    	// $pass = md5($password);
    	$data = array();
    	$sql = "SELECT * FROM contest_master where type_id = '$contest_type_id'";
		$res = $this->conn->query($sql);
	if($res){
    while($check_row = $res->fetch_array())
		{
			 array_push($data,$check_row);
		}	 

			$data['userDetails'] = $data;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;
    }
/*// contests
    function contests($user_id,$category_id,$contest_status_id){
    	//$pass = md5($password);
    	$data = array();
    	$data['user_id']=$user_id;
    	$data['category_id']=$category_id;
    	$data['contest_status_id']=$contest_status_id;
    	$sql = "SELECT * FROM contest_master where category_id = '$contest_type_id' and contest_status = '$contest_status_id' ";
		$res = $this->conn->query($sql);
	if($res){
    while($check_row = $res->fetch_array())
		{
			 array_push($data,$check_row);
		}	 

			$data['userDetails'] = $data;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;
    }*/

 // SiteName
  function siteName($sitename,$address,$location,$job)
    {
        $data=array();
        $data['sitename'] = $sitename;
        $data['address']= $address;
        $data['location']=$location;
        $data['job']=$job;
			// Prepare an insert statement
		

		$sql1 = "Select * from site where sitename='$sitename' ";
		
        $res1 = $this->conn->query($sql1);

if(mysqli_num_rows($res1)==0){
	$sql = "INSERT INTO `site`(`sitename`,`address`, `location`, `job`) VALUES ('$sitename','$address','$location','$job')";

	$res = $this->conn->query($sql);


if($res){
		$data['userDetails'] = $data;
		$data['status']=1;
		}else  {
			$data['status']=0;
			$data['error']="not inserted sucessfully";
		}
	}else {

		$data['status']=2;
		$data['error']="already user existed";
	}
	return $data;
    }

  function commentInsert($post_id,$user_id, $comment_description, $comment_media)
    {
        $data=array();
        $data['post_id']= $post_id;
       $data['user_id']= $user_id;
       $data['comment_description']= $comment_description;
       $data['comment_media']= $comment_media;
        	// Prepare an insert statement
	    $sql = "INSERT INTO `comments`(`post_id`,`user_id`, `comment_description`, `comment_media`) 
	            VALUES ('$post_id','$user_id','$comment_description','$comment_media')";

	    $res = $this->conn->query($sql);

        if($res){
		     $data['status']=1;
		      $data["comments"]=$data;
		    }else  {
			    $data['status']=0;
			    $data['error']="not inserted sucessfully";
		}
	    
	return $data;
    }



//Moods List

function moods($user_id)
{
        $data=array();
        
		$sql1 = "SELECT mood_id, mood_name, emoji, created_on from mood_master";
        $res1 = $this->conn->query($sql1);
        if($res1){
            $i=0;
            while($check_row1 = $res1->fetch_assoc())
            { 
			         $i++;
			        $arr['mood_id'] = $check_row1['mood_id'];
			        $arr['mood_title'] = $check_row1['mood_name'];
			        $arr['mood_emoji'] = $check_row1['emoji'];
			        $arr['created_on'] = $check_row1['created_on'];
			        array_push($data,$arr);
			   
            }
            $data['moods'] = $data;
			$data['status']=1;

		 }else  {
		       $data['status']=0;
			   $data['error']="Moods list not found ";
		  }
	return $data;
}


//insertpostlikes

    function postlikes($user_id,$post_id)
    {
      
			$data['user_id'] = $user_id;
			$data['post_id'] = $post_id;
			
			// Prepare an insert statement
		$sql = "INSERT INTO likes (user_id,post_id) VALUES ('$user_id','$post_id')";
		$res = $this->conn->query($sql);
		

		if($res){
		$data['userDetails'] = $data;
		$data['status']=1;
		}else{
			$data['status']=0;
		}
	return $data;
    }
    
    /////insert post shares
  function postsharesinsert($user_id,$post_id)
    {
      
			$data['user_id'] = $user_id;
			$data['post_id'] = $post_id;
			
			// Prepare an insert statement
		$sql = "INSERT INTO post_shares (user_id,post_id) VALUES ('$user_id','$post_id')";
		$res = $this->conn->query($sql);
		

		if($res){
		$data['userDetails'] = $data;
		$data['status']=1;
		}else{
			$data['status']=0;
		}
	return $data;
    }


//Categories List

function categories($user_id)
{
        $data=array();
        
		$sql1 = "SELECT category_id, category_name, picture, created_on from category_master where category_id in 
		( select distinct category_id from user_posts where user_id = '$user_id' )";
        $res1 = $this->conn->query($sql1);
        if($res1){
            $i=0;
            while($check_row1 = $res1->fetch_assoc())
            { 
			         $i++;
			        $arr['category_id'] = $check_row1['category_id'];
			        $arr['category_title'] = $check_row1['category_name'];
			        $arr['category_pic'] = $check_row1['picture'];
			        $arr['created_date'] = $check_row1['created_on'];
			        array_push($data,$arr);
			   
            }
            $data['categories'] = $data;
			$data['status']=1;

		 }else  {
		       $data['status']=0;
			   $data['error']="No categories found for the user";
		  }
	return $data;
}

//Categories List

function listcomments($post_id)
{
        $data=array();
        
		$sql1 = "SELECT * from comments where post_id  = '$post_id'";
        $res1 = $this->conn->query($sql1);
        if($res1){
          
            while($check_row1 = $res1->fetch_assoc())
            { 
			      
			        array_push($data,$check_row1);
			   
            }
            $data['listofcomments'] = $data;
			$data['status']=1;

		 }else  {
		       $data['status']=0;
			   $data['error']="No list found for the user";
		  }
	return $data;
}

    
    //Description

     function addclient($clientname,$address,$location)
    {
        $data=array();
        $data['client_name'] = $_POST['clientname'];
        $data['address']= $_POST['address'];
        $data['location']= $_POST['location'];
        $data['image'] = $_FILES['image']['name'];
        $tmpName = $_FILES['image']['tmp_name'];
        $moveFile = move_uploaded_file($tmpName, '../uploadedFiles/'.$data['image']);
	$sql = "INSERT INTO `client`(`client_name`,`address`, `location`,`image`) VALUES ('".$data['clientname']."','".$data['address']."','".$data['location']."','".$_FILES['image']['name']."')";
$res = $this->conn->query($sql);
if($res){
		$data['Description'] = $data;
		$data['status']=1;
		}else  {
			$data['status']=0;
			$data['error']="not inserted sucessfully";
		}
	return $data;
    }
//Clientlist
function Clientlist()
    {
     $sql =  "SELECT * FROM client";
	$res = $this->conn->query($sql);   //Message Here
            if($res){
            $i=0;
               $data=array();
			 while($check_row = $res->fetch_assoc())
			 { 
			     $i++;
			   $arr['client_name'] = $check_row['client_name'];
			    $arr['address'] = $check_row['address'];
			    $arr['location'] = $check_row['location'];
			    $arr['job_description'] = $check_row['job_description'];
			    $arr['image'] = $check_row['image'];
			    $arr['mobile'] = $check_row['mobile'];
			    $arr['company'] = $check_row['company'];
			     array_push($data,$arr);
			   
            }
            $data['Clientlist'] = $data;
			$data['status']=1;
        } else{
        	$data['status']=0;
			$data['error']="not selected list sucessfully";
    }
        	return $data;
    }

//SiteList
    function siteList()
    {
      
        // $data['Employee_Id'] = $employee;
			// Prepare an insert statement
			$sql =  "SELECT * FROM site";
			 $res = $this->conn->query($sql);   //Message Here
            if($res){
            $i=0;
               $data=array();
			 while($check_row = $res->fetch_assoc())
			 { 
			     $i++;
			   $arr['sitename'] = $check_row['sitename'];
			    $arr['address'] = $check_row['address'];
			    $arr['location'] = $check_row['location'];
			    $arr['job'] = $check_row['job'];
			     array_push($data,$arr);
			   }
            $data['sitelist'] = $data;
			$data['status']=1;
        } else{
        	$data['status']=0;
			$data['error']="not selected list sucessfully";
    }
        	return $data;
    }
   //   function siteList($employee)
   //  {
      
   //      $data['Employee_Id'] = $employee;
			// // Prepare an insert statement
			// $sql =  "SELECT * FROM site where Employee_Id='$employee'";
			//  $res = $this->conn->query($sql);   //Message Here
   //          if($res){
   //          $i=0;
   //             $data=array();
			//  while($check_row = $res->fetch_assoc())
			//  { 
			//      $i++;
			//    $arr[$i]['sitename'] = $check_row['sitename'];
			//     $arr[$i]['address'] = $check_row['address'];
			//     $arr[$i]['location'] = $check_row['location'];
			//     $arr[$i]['job'] = $check_row['job'];
			//      array_push($data,$arr);
			   
   //          }
   //          $data['sitelist'] = $data;
			// $data['status']=1;
   //      } else{
   //      	$data['status']=0;
			// $data['error']="not selected list sucessfully";
   //  }
   //      	return $data;
   //  }
    
//employeelist
     function EmployeeList()
    {
      
        // $data['Employee_Id'] = $employee;
			// Prepare an insert statement
			//$sql =  "SELECT * FROM user WHERE Employee_Id='$employee'";
        $sql =  "SELECT * FROM user";
			 $res = $this->conn->query($sql);   //Message Here
            if($res){
            $i=0;
               $data=array();
			 while($check_row = $res->fetch_assoc())
			 { 
			     $i++;
			   $arr['Full_Name'] = $check_row['Full_Name'];
			    $arr['Mobile_Number'] = $check_row['Mobile_Number'];
			    $arr['Designation'] = $check_row['Designation'];
			    $arr['Shift_Timings'] = $check_row['Shift_Timings'];
			     array_push($data,$arr);
			   
            }
            $data['sitelist'] = $data;
			$data['status']=1;
        } else{
        	$data['status']=0;
			$data['error']="not selected list sucessfully";
    }
        	return $data;
    }
// Add Client
//   function AddClient($clientname,$address,$location)
//     {
//         $data=array();
//         $data['client_name'] = $clientname;
//         $data['address']= $address;
//         $data['location']=$location;
// 			// Prepare an insert statement
// 		$sql1 = "Select * from client where client_name='$clientname' ";
// 		$res1 = $this->conn->query($sql1);

// if(mysqli_num_rows($res1)==0){
// 	$sql = "INSERT INTO `client`(`client_name`,`address`, `location`) VALUES ('$clientname','$address','$location')";
// $res = $this->conn->query($sql);
// if($res){
// 		$data['userDetails'] = $data;
// 		$data['status']=1;
// 		}else  {
// 			$data['status']=0;
// 			$data['error']="not inserted sucessfully";
// 		}
// 	}else {

// 		$data['status']=2;
// 		$data['error']="already user existed";
// 	}
// 	return $data;
//     }

    // Client Registration
  function ClientReg($clientname,$address,$company,$mobile,$description)
    {
        $data=array();
        $data['client_name'] = $clientname;
        $data['address']= $address;
        $data['company']=$company;
        $data['mobile'] =$mobile;
        $data['job_description'] =$description;
			// Prepare an insert statement
		$sql1 = "Select * from client where mobile='$mobile' ";
		$res1 = $this->conn->query($sql1);

if(mysqli_num_rows($res1)==0){
	$sql = "INSERT INTO `client`(`client_name`, `address`, `company`, `mobile`, `job_description`) VALUES ('$clientname','$address','$company','$mobile','$description')";
$res = $this->conn->query($sql);
if($res){
		$data['userDetails'] = $data;
		$data['status']=1;
		}else  {
			$data['status']=0;
			$data['error']="not inserted sucessfully";
		}
	}else {

		$data['status']=2;
		$data['error']="already user existed";
	}
	return $data;
    }

//GettingProfile

    function timeline($user_id, $mood_id,$category_id){
	$data = array();
		$arr = array();
if($user_id == "all"){
   	$sql = "SELECT * FROM posts where  mood_id = '$mood_id' and category_id = '$category_id'"; 
}else {
    
    	$sql = "SELECT * FROM posts where user_id = '$user_id' and mood_id = '$mood_id' and category_id = '$category_id'";
}
		$res = $this->conn->query($sql);
	if($res){
		while($check_row = $res->fetch_array()){
		    $arr['post_id']=$check_row['post_id'];
		    $arr['post_name']=$check_row['post_name'];
		    $arr['post_status']=$check_row['post_status'];
		    $arr['created_on']=$check_row['created_on'];
		    $arr['updated_on']=$check_row['updated_on'];
		    $arr['likes']=$check_row['likes'];
		    $arr['views']=$check_row['views'];
		    $arr['comments']=$check_row['comments'];
		      $arr['shares']=$check_row['shares'];
		      $arr['mood_id']=$check_row['mood_id'];
		      $arr['category_id']=$check_row['category_id'];
$uid=$check_row['user_id'];
$pid=$check_row['post_id'];
	$sqlmedia = "SELECT * FROM post_media where 	post_id = '$pid'";
		

		$resmedia = $this->conn->query($sqlmedia);
		$media = array();
			$mediaar = array();
		while($check_rowmedia = $resmedia->fetch_array()){
		    $media['media_name'] = $check_rowmedia['media_name'];
		     $media['media_type'] = $check_rowmedia['media_type'];
		      $media['media_thumbnail'] = $check_rowmedia['media_thumbnail'];
		     array_push($mediaar,$media);
		}
		 $arr['media']=$mediaar;
		$sqluser = "SELECT * FROM users where user_id = '$uid'";
		

		$resuser = $this->conn->query($sqluser);
	
		while($check_rowuser = $resuser->fetch_array()){
		    $arr['user_name']=$check_rowuser['user_name'];
		      $arr['user_id']=$check_rowuser['user_id'];
		       $arr['user_pic']=$check_rowuser['profile_image'];
		     
		    
		}
		
		
          array_push($data,$arr);
		    
		}

        $data['userDetails'] = $data;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;
    }



 //GettingProfile

    function getprofile($id){

    	$data = array();
    	$sql = "SELECT * FROM user where id = '$id' ";
		$res = $this->conn->query($sql);
		$check_row = $res->fetch_array();

		if($check_row)
		{
		
            $data['name'] =$check_row['name'];
			$data['password'] =$check_row ['password'];
			$data['mobile'] = $check_row ['mobile'];
			$data['email'] = $check_row ['email'];
			$data['age'] = $check_row ['age'];
			$data['father_name'] = $check_row ['father_name'];
			$data['passed_year'] =  $check_row['passed_year'];
		    $data['qualification'] = $check_row ['qualification'];
		    $data['technical'] =  $check_row['technical'];
			$data['qualified_doctor'] = $check_row ['qualified_doctor'];
			$data['ownclinic_exp'] =  $check_row['ownclinic_exp'];
			$data['present_wplace'] = $check_row ['present_wplace'];
			$data['mandal'] = $check_row ['mandal'];
			$data['district'] = $check_row ['district'];
			$data['postal_address'] = $check_row ['postal_address'];
			$data['pin_code'] = $check_row ['pin_code'];
			$data['landline'] = $check_row ['landline'];
            $data['image_name'] =  $check_row['image_name'];
			$data['image_loc'] = $check_row ['image_loc'];
			$data['paid'] = $check_row ['paid'];

        $data['userDetails'] = $data;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;
    }




    //getimageloc
     
     function getimageloc($id,$image_loc){

    	$data = array();

			$data['id'] = $id;
			$data['image_loc'] = $image_loc;
			// $data['image_name'] = $image_name;
			$sql1 = "UPDATE user SET image_loc='$image_loc' WHERE id = '$id' ";
			 $res1 = $this->conn->query($sql1);   //Message Here

			if($res1){
		$data['userDetails'] = $data;
		$data['status']=1;
		}else{
			$data['status']=0;
		}
	return $data;
    }


//getMessage


   function getmessage($sno){

    	$data = array();
    	$sql = "SELECT * FROM message ORDER BY sno DESC LIMIT 1 ";
		$res = $this->conn->query($sql);
		$check_row = $res->fetch_array();

		if($check_row){
			
			$data['message'] = $check_row['message'];
			


			$data['userDetails'] = $data;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;
    }




// Login through gmail

    function loginGmail($email){

    	$data = array();
    	$sql = "SELECT * FROM users where email = '$email'" ;
		$res = $this->conn->query($sql);
		$check_row = $res->fetch_array();

		if($check_row){
			$data['email'] = $check_row['email'];
			$data['mobile'] = $check_row['mobile'];


			$mobile = $check_row['mobile'];
			$data['userId'] = $check_row['id'];
			$otp = rand(100000, 999999);
			$data['otp'] = $otp;
			$sql1 = "UPDATE users SET otp='$otp' WHERE email = '$email'";
			 $res1 = $this->conn->query($sql1);   //Message Here
$un="rx100";
$pw="Raju@sms";
$sid="RXGAME";

$url1 = 'http://smslogin.mobi/spanelv2/api.php?username='.$un.'&password='.$pw.'&to='.$mobile.'&from='.$sid.'&message='.$otp;

$res = file($url1);
			$data['userDetails'] = $data;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;
    }

/* happygo  API for login 21-08-20 */
/*
function login($email, $password){

    	$data = array();
    	$sql = "SELECT * FROM users where email = '$email' and password= '$password'" ;
		$res = $this->conn->query($sql);
		$check_row = $res->fetch_array();

		if($check_row){
			$data['email'] = $check_row['email'];
			//$data['password'] = $check_row['password'];


			$mobile = $check_row['mobile'];
			$data['user_id'] = $check_row['user_id'];
			$token = $this->generateApiKey();
			$data['token'] = $token;
			$sql1 = "UPDATE users SET token='$token' WHERE email = '$email' and password='$password'";
			$res1 = $this->conn->query($sql1);   //Message Here

			$data['userDetails'] = $data;
			$data['status']='True';
		}else{
			$data['status']='False';
		}
		return $data;
    }
*/

function login($email, $password){

    	$data = array();
    	$pass = md5($password);
    	echo $pass." we are testing";
    	$sql = "SELECT * FROM users WHERE email = '$email' and password= '$pass'" ;
		$res = $this->conn->query($sql);
		$check_row = $res->fetch_array();

		if($check_row){
			
			$data['user_id'] = $check_row['user_id'];
            $data['first_name']  = $check_row['first_name'];
            $data['last_name']  = $check_row['last_name'];
            $data['user_name']  = $check_row['user_name'];
            $data['email']     = $check_row['email'];
            $data['mobile_number'] = $check_row['mobile'];
            $data['user_pic'] = $check_row['profile_image']; 
            $data['date_of_birth'] = $check_row['dob'];
             $data['isVerfiedUser'] = $check_row['isVerfiedUser'];
              $data['isTravelExpert'] = $check_row['isTravelExpert'];
            $data['Location']      = $check_row['location'];
            $data['bio'] = $check_row['bio'];  

			/*$token = $this->generateApiKey();
			$data['token'] = $token;
			$sql1 = "UPDATE users SET token='$token' WHERE email = '$email' and password='$password'";
			$res1 = $this->conn->query($sql1);   //Message Here
            */
			$data['userDetails'] = $data;
			$data['status']='True';
		}else{
			$data['status']='False';
		}
		return $data;
    }
    

    
// Login through facebook
    function loginFacebook($facebook){

    	$data = array();
    	$sql = "SELECT * FROM users where facebook = '$facebook'" ;
		$res = $this->conn->query($sql);
		$check_row = $res->fetch_array();

		if($check_row){
			$data['facebook'] = $check_row['facebook'];
			$data['mobile'] = $check_row['mobile'];


			$mobile = $check_row['mobile'];
			$data['userId'] = $check_row['id'];
			$otp = rand(100000, 999999);
			$data['otp'] = $otp;
			$sql1 = "UPDATE users SET otp='$otp' WHERE facebook = '$facebook'";
			 $res1 = $this->conn->query($sql1);   //Message Here
$un="rx100";
$pw="Raju@sms";
$sid="RXGAME";


$url1 = 'http://smslogin.mobi/spanelv2/api.php?username='.$un.'&password='.$pw.'&to='.$mobile.'&from='.$sid.'&message='.$otp;

$ret = file($url1);
			$data['userDetails'] = $data;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;
    }



   



    //getBalance
       function getbalancedb($userid){

    	$data = array();
    	$sql = "SELECT * FROM user_coins where 	user_id='$userid' ";
		$res = $this->conn->query($sql);
	
		$check_row = $res->fetch_array();

		if($check_row){
			$data['available_coins'] = $check_row['coins'];
			
	        $sqlw = "SELECT * FROM  user_wallet where 	user_id='$userid' ";
		    $resw = $this->conn->query($sqlw);
			$check_roww = $resw->fetch_array();
            if($check_roww){
			$data['available_balance'] = $check_roww['amount'];
           }
			$data['userDetails'] = $data;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;
    }

    //getresultdb
       function getresultdb($userid){

    	$data = array();
    	$sql = "SELECT * FROM game_result";
		$res = $this->conn->query($sql);
		$check_row = $res->fetch_array();
		$sql1 = "SELECT * FROM game_wallet_balance where cust_id='$userid'";
		$res1 = $this->conn->query($sql1);
		$check_row1 = $res1->fetch_array();

if($check_row1){
			$data['g_balance'] = $check_row1['g_balance'];
			$data['w_balance'] = $check_row1['w_balance'];

}
		if($check_row){
			$data['gameid'] = $check_row['gameid'];
			$data['resultcard'] = $check_row['resultcard'];


			$data['userDetails'] = $data;
			$data['status']=1;
		}else{
			$data['status']=0;
		}
		return $data;
    }
//Resend otp
     function resendOtp($userid,$mobile){

    	$data = array();



			$otp = rand(100000, 999999);
			$data['otp'] = $otp;
			$data['id'] = $userid;
			$data['mobile'] = $mobile;
			$sql1 = "UPDATE users SET otp='$otp' WHERE id = '$userid' AND mobile = '$mobile' ";
			 $res1 = $this->conn->query($sql1);   //Message Here
$un="rx100";
$pw="Raju@sms";
$sid="RXGAME";

$url1 = 'http://smslogin.mobi/spanelv2/api.php?username='.$un.'&password='.$pw.'&to='.$mobile.'&from='.$sid.'&message='.$otp;

$ret = file($url1);
			$data['userDetails'] = $data;
			$data['status']=1;

		return $data;
    }

    //forgot password

     /*function forgotPassword($userid){

    	$data = array();

			$data['id'] = $userid;

			$sql =  "SELECT mobile FROM users where id='$userid' ";
			 $res = $this->conn->query($sql);   //Message Here
           $check_row = $res->fetch_array();


			 if($check_row){
			$data['mobile'] = $check_row['mobile'];



			$data['userDetails'] = $data;
			$data['status']=1;
		}else{
			$data['status']=0;
		}

		return $data;
    }
*/

    //change Password
     function changePwd($user_id,$password){

    	$data = array();

			//$data['user_id'] = $user_id;
			//$data['password'] = $password;
			$sql1 = "UPDATE users SET password='$password' WHERE user_id = '$user_id' ";
			 $res1 = $this->conn->query($sql1);   //Message Here

			if($res1){
		$data['userDetails'] = $data;
		$data['status']=1;
		}else{
			$data['status']=0;
		}
	return $data;
    }




    //Reset Password
    
     function resetPwd($userid,$password){

    	$data = array();

			$data['user_id'] = $userid;
			$data['password'] = $password;
			$sql1 = "UPDATE users SET password='$password' WHERE user_id = '$userid' ";
			 $res1 = $this->conn->query($sql1);   //Message Here

			if($res1){
			   	$sql = "select* from users where user_id =  '$userid'";
			 $res = $this->conn->query($sql); 
			 
		$data['userDetails'] = $res;
		$data['status']=1;
		}else{
			$data['status']=0;
		}
	return $data;
    }


  

    //getstartTime
     function timeStarts($gameid){

    	$data = array();

    	$sql = "SELECT * FROM game_result";
		$res = $this->conn->query($sql);

	$check_row = $res->fetch_array();
		if($check_row){

			$data['game_status'] = $check_row['game_status'];
			$data['game_start_time'] = $check_row['game_start_time'];
			$data['gameid'] = $check_row['gameid'];
			$data['userDetails'] = $data;
				$data['status']=1;
		}else{
			$data['status']=0;
		}
	return $data;
    }




    //bidding insert
    function bidInstert($gameid,$custid,$card,$amount)
    {
        $data=array();
        $data['gameid'] = $gameid;
			$data['cust_id'] = $custid;
			$data['card'] = $card;
			$data['amount'] = $amount;
			$sql1 = "SELECT * FROM game_wallet_balance where cust_id='$custid'";
		$res1 = $this->conn->query($sql1);
		$check_row1 = $res1->fetch_array();
if($check_row1){
			$data['g_balance'] = $check_row1['g_balance'];

			if($check_row1['w_balance'] >= $amount)

			{
					$newwallet =  $check_row1['w_balance'] - $amount;
					$sql2 = "UPDATE game_wallet_balance SET w_balance='$newwallet' WHERE cust_id='$custid'  ";
					$res2 = $this->conn->query($sql2);
					$sql = "INSERT INTO bidding_log (gameid,cust_id,card,amount) VALUES ('$gameid','$custid','$card','$amount')";
			$res = $this->conn->query($sql);
			$data['w_balance'] = $newwallet;

			if($res){
			$data['userDetails'] = $data;
				$data['status']=1;
			}

			}
			else{
			$data['status']=0;
		}
	return $data;
    }


			}


    //ADDMONEY

    function addMoney($cust_id,$status, $checksum,$bankname,$orderid,$txnamount,$txndate,$mid,$txnid,$response_code,$payment_mode,$bank_transaction_id,$currency,$gateway_name,$resp_msg)
    {
        $data=array();
      $data['cust_id'] = $cust_id;
      $data['status'] = $status;
      $data['checksum'] = $checksum;
       $data['bankname'] = $bankname;
       $data['orderid'] = $orderid;
        $data['txnamount'] = $txnamount;
         $data['txndate'] = $txndate;
          $data['mid'] = $mid;
           $data['txnid'] = $txnid;
            $data['response_code'] = $response_code;
             $data['payment_mode'] = $payment_mode;
              $data['bank_transaction_id'] = $bank_transaction_id;
               $data['currency'] = $currency;
                $data['gateway_name'] = $gateway_name;
                 $data['resp_msg'] = $resp_msg;

			// Prepare an insert statement
	$sql = "INSERT INTO addmoney_txn (cust_id,status,checksum,bankname,orderid,txnamount,txndate,mid,txnid,response_code,payment_mode,bank_transaction_id,currency,gateway_name,resp_msg) VALUES ($cust_id,'$status', '$checksum','$bankname','$orderid','$txnamount','$txndate','$mid','$txnid','$response_code','$payment_mode','$bank_transaction_id','$currency','$gateway_name','$resp_msg') ";
		$res = $this->conn->query($sql);

		if($res){
		$data['userDetails'] = $data;
		$data['status']=1;
		}else{
			$data['status']=0;
		}
	return $data;
    }
    //wallet to game money
     function gmtow($custid,$amount)
    {
        $data=array();

			$data['cust_id'] = $custid;

			$data['amount'] = $amount;
$sql1 = "SELECT * FROM game_wallet_balance where cust_id='$custid'";

		$res1 = $this->conn->query($sql1);
		$check_row1 = $res1->fetch_array();
if($check_row1){
			$data['w_balance'] = $check_row1['w_balance'];

			if($check_row1['g_balance'] >= $amount)

			{
					$newwallet =  $check_row1['g_balance'] - $amount;
					$newwallet2 = $check_row1['w_balance'] + $amount;
					$sql2 = "UPDATE game_wallet_balance SET g_balance='$newwallet', w_balance='$newwallet2' WHERE cust_id='$custid'  ";
					$res2 = $this->conn->query($sql2);

			$data['g_balance'] = $newwallet;

$data['w_balance'] = $newwallet2;


			$data['userDetails'] = $data;
				$data['status']=1;


			}
			else{
			$data['status']=0;
		}
	return $data;
    }


			}

}
?>
