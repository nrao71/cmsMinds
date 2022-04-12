<?php
header('content-type:text/html;charset=utf-8');
require_once '../include/DbHandler.php';
require_once '../include/EmailService.php';
require_once '../include/SmsService.php';
require '.././libs/Slim/Slim.php';


// \Stripe\Stripe::setApiKey($stripe['secret_key']);

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

// User id from db - Global Variable
$user_id = NULL;
$session_token= NULL;
/**
 * Adding Middle Layer to authenticate every request
 * Checking if the request has valid api key in the 'Authorization' header
 */
function authenticate(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
    // Verifying Authorization Header
    if (isset($headers['Authorization']))
    {
        $db = new DbHandler();
        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key

    if (!$db->isValidApiKey($api_key))
    {
            $response["status"] ="error";
            $response["message"] = "Access Denied";
            //$response["message"] = "Access Denied. Invalid Api key";
            echoRespnse(401, $response);
            $app->stop();
        }
        else
        {
            global $user_id;
            //get user primary key id
           $user_id = $db->getUserId($api_key);

        }
    }
    else
    {
        // api key is missing in header
        $response["status"] ="error";
        //$response["message"] = "Api key is misssing";
        $response["message"] = "Access Denied";
        echoRespnse(401, $response);
        $app->stop();
    }
}


function accessToken($user_id) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
    // Verifying Authorization Header
    if (isset($headers['sessiontoken']))
    {
        $db = new DbHandler();
        // get the api key
        $api_key = $headers['sessiontoken'];
        // validating api key
        if (!$db->isValidSessionToken($api_key,$user_id))
        {
            $response["status"] ="error";
            $response["message"] = "Token Expired";
            //$response["message"] = "Access Denied. Invalid Api key";
            echoRespnse(401, $response);
            $app->stop();
        }
    }
    else
    {
        // api key is missing in header
        $response["status"] ="error";
        //$response["message"] = "Api key is misssing";
        $response["message"] = "sessiontoken key is missing";
        echoRespnse(401, $response);
        $app->stop();
    }
}

/*** Indian Date Time Generation ***/
  function getCurrentDateTime(){
    $datetime = date('Y-m-d H:i:s');
    $given = new DateTime($datetime, new DateTimeZone("UTC"));
    $given->setTimezone(new DateTimeZone("asia/kolkata"));
    $output = $given->format("Y-m-d H:i:s");
    return $output;
  }

function authenticatedefault(\Slim\Route $route) {
    // Getting request headers
    $headers = apache_request_headers();
    $response = array();
    $app = \Slim\Slim::getInstance();
    $APPKEY = "b8416f2680eb194d61b33f9909f94b9d";
    // Verifying Authorization Header
   //print_r($headers);exit;
    if (isset($headers['Authorization']) || isset($headers['authorization']))
    {
    if(isset($headers['authorization']))
    {
      $headers['Authorization']=$headers['authorization'];
    }

        $db = new DbHandler();

        // get the api key
        $api_key = $headers['Authorization'];
        // validating api key

        if($api_key != $APPKEY)
        {
      $response["status"] ="error";
            $response["message"] = "Access Denied";
            echoRespnse(401, $response);
            $app->stop();
    }
       else
        {
            global $user_id;
            // get user primary key id
          //$user_id = $db->getUserId($api_key);

        }
    }
    else
    {
        // api key is missing in header
        $response["status"] ="error";
        //$response["message"] = "Api key is misssing";
        $response["message"] = "Access Denied";
        echoRespnse(401, $response);
        $app->stop();
    }
}

///////////////////////////////////////


//getotpfrommobile
$app->post('/getotpfrommobile', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];

    $mobile = $data['mobile_number'];

    $response = array();
    $db = new DbHandler();
    $result=$db->getotpfrommobile($mobile);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "sent otp successfully";
           $response["sent otp"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'Incorrect Passcode';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });



//addmoney_txn
$app->post('/addmoney', 'authenticatedefault', function() use ($app)
{

            // reading post params

    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $cust_id  = $data['cust_id'];
    $status  = $data['status'];
    $checksum  = $data['checksum'];
    $bankname  = $data['bankname'];
    $orderid  = $data['orderid'];
    $txnamount  = $data['txnamount'];
    $txndate  = $data['txndate'];

    $mid  = $data['mid'];
    $txnid  = $data['txnid'];
    $response_code  = $data['response_code'];
    $payment_mode  = $data['payment_mode'];
    $bank_transaction_id  = $data['bank_transaction_id'];

    $currency  = $data['currency'];
    $gateway_name  = $data['gateway_name'];
    $resp_msg  = $data['resp_msg'];


    $response = array();
    $db = new DbHandler();
    $result=$db->addMoney($cust_id,$status, $checksum,$bankname,$orderid,$txnamount,$txndate,$mid,$txnid,$response_code,$payment_mode,$bank_transaction_id,$currency,$gateway_name,$resp_msg);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "bid inserted successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'not submitted';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });
//disbtobank
$app->post('/disbtobank', 'authenticatedefault', function() use ($app)
{

            // reading post params

    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $cust_id  = $data['cust_id'];
    $status  = $data['status'];
    $checksum  = $data['checksum'];
    $bankname  = $data['bankname'];
    $orderid  = $data['orderid'];
    $txnamount  = $data['txnamount'];
    $txndate  = $data['txndate'];

    $mid  = $data['mid'];
    $txnid  = $data['txnid'];
    $response_code  = $data['response_code'];
    $payment_mode  = $data['payment_mode'];
    $bank_transaction_id  = $data['bank_transaction_id'];

    $currency  = $data['currency'];
    $gateway_name  = $data['gateway_name'];
    $resp_msg  = $data['resp_msg'];


    $response = array();
    $db = new DbHandler();
    $result=$db->disbtobank($cust_id,$status, $checksum,$bankname,$orderid,$txnamount,$txndate,$mid,$txnid,$response_code,$payment_mode,$bank_transaction_id,$currency,$gateway_name,$resp_msg);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "bid inserted successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'not submitted';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });
//addmoneypaytm
$app->post('/addMoneypaytm', 'authenticatedefault', function() use ($app)
{

            // reading post params

    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $cust_id  = $data['cust_id'];
    $status  = $data['status'];
    $checksum  = $data['checksum'];
    $bankname  = $data['bankname'];
    $orderid  = $data['orderid'];
    $txnamount  = $data['txnamount'];
    $txndate  = $data['txndate'];

    $mid  = $data['mid'];
    $txnid  = $data['txnid'];
    $response_code  = $data['response_code'];
    $payment_mode  = $data['payment_mode'];
    $bank_transaction_id  = $data['bank_transaction_id'];

    $currency  = $data['currency'];
    $gateway_name  = $data['gateway_name'];
    $resp_msg  = $data['resp_msg'];


    $response = array();
    $db = new DbHandler();
    $result=$db->addMoney($cust_id,$status, $checksum,$bankname,$orderid,$txnamount,$txndate,$mid,$txnid,$response_code,$payment_mode,$bank_transaction_id,$currency,$gateway_name,$resp_msg);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "bid inserted successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'not submitted';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });


//login through gmail

$app->post('/loginGmail', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];


     $email = $data['email'];
     $response = array();
    $db = new DbHandler();
    $result=$db->loginGmail($email);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "Logged in successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'Incorrect mail';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);


 });


//contests_status

$app->post('/contests_status', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

   //  $user_id   = $data['user_id'];


     $contest_type_id = $data['contest_type_id'];
     $response = array();
    $db = new DbHandler();
    $result=$db->contests_status($contest_type_id);
   //$user_details=$db->contests_status($user_id,$contest_type_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "get contests details successfully";
           $response["contests details"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'query not executed';
          $response["contests details"]=array();
      }

  echoRespnse(200, $response);

 });
//contests

$app->post('/contests', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

   $category_id   = $data['category_id'];
    $user_id   = $data['user_id'];



     $contest_status_id  = $data['contest_status_id '];
     $response = array();
    $db = new DbHandler();
    $result=$db->contests($user_id,$category_id,$contest_status_id);
   //$user_details=$db->contests_status($user_id,$contest_type_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "get contests details successfully";
           $response["contests details"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'query not executed';
          $response["contests details"]=array();
      }

  echoRespnse(200, $response);

 });

// TIMELINE WITH MOOD
 
 $app->post('/timeline', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    //$result = implode(',',$data);

    // $platform   = $data['platform'];


     $user_id = $data['user_id'];
     $mood_id = $data['mood_id'];
  $category_id = $data['category_id'];
    $response = array();
    $db = new DbHandler();
    $result=$db->timeline($user_id, $mood_id,$category_id);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']=='1')
     {
           $response["status"] ='True';
           $response['message'] = "data extracted  successfully";
           $response["postdetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] ='0';
          $response['message'] = 'query not executed';
         
      }

  echoRespnse(200, $response);

 });
 //login
 $app->post('/login', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    

     $email = $data['email'];
     $password = $data['password'];

    $response = array();
    $db = new DbHandler();
    $result=$db->login($email,$password);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']=='True')
     {
           $response["status"] ='True';
           $response['message'] = "Logged in successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] ='False';
          $response['message'] = 'Incorrect Username and Password';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });


//login through facebook

$app->post('/loginFacebook', 'authenticatedefault', function() use ($app)
{




     $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $facebook = $data['facebook'];

    $response = array();
    $db = new DbHandler();
    $result=$db->loginFacebook($facebook);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "Logged in successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'Incorrect address';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });




//resend otp post method
$app->post('/resendOTP', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $userid  = $data['id'];
    $mobile = $data['mobile'];

    $response = array();
    $db = new DbHandler();
    $result=$db->resendOtp($userid,$mobile);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "Logged in successfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'Incorrect Passcode';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });




//getBalance

$app->post('/getBalance', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $userid  = $data['user_id'];

    $response = array();
    $db = new DbHandler();
    $result=$db->getbalancedb($userid);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "got balance successfully";
           $response["balance details"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'query not executed';
         // $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });


$app->post('/changePassword', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
   $user_id  = $data['user_id'];
   $password = $data['password'];

    $response = array();
    $db = new DbHandler();
    $result=$db->changePwd($user_id,$password);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] ='True';
           $response['message'] = "Updated sucessfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'Incorrect userid';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });




//reset password


$app->post('/resetPassword', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    $password   = $data['new_password'];
   $userid  = $data['user_id'];

    $response = array();
    $db = new DbHandler();
    $result=$db->resetPwd($userid,$password);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "password changed succussfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'Incorrect userid';
         // $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });






$app->post('/hforgotPassword', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $email  = $data['email'];

    $response = array();
    $db = new DbHandler();
    $result=$db->hforgotPassword($email);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']=="Success")
     {
           $response["status"] ="Success";
           $response['message'] = "sucessfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] ="False";
          $response['message'] = 'Invalid Details';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });

// Forgor password email
$app->post('/forgotPasswordEmail', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $email  = $data['email'];

    $response = array();
    $db = new DbHandler();
    $result=$db->forgotPasswordEmail($email);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']=="Success")
     {
           $response["status"] ="Success";
           $response['message'] = "sucessfully";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] ="False";
          $response['message'] = 'Invalid Details';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });



// Happygo signUp API

$app->post('/hsignUp', 'authenticatedefault', function() use ($app)
{
    $json = $app->request->getBody();
    $data = json_decode($json, true);
    // $result = implode(',',$data);

    // $platform   = $data['platform'];
      $name=$data['Name'];
      $mobile=$data['Mobile_no'];
      $password=$data['Password'];
      $email= $data['Email'];
      $created_on= $data['Created_on'];
    $response = array();
    $db = new DbHandler();
    $result=$db->hsignUp($name,$mobile,$password,$email,$created_on);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']=="True")
     {
           $response["status"] ="True";
           $response['message'] = "Inserted successfully";
           $response["userDetails"]=$result['userDetails'];
      }
     else if($result['status']=="False")
      {
          $response['status'] ="False";
          $response['message'] = 'not inserted';
          $response["userDetails"]=array();
      }
        else 
      {
          $response['status'] =2;
          $response['message'] ='user already exists';

      }

  echoRespnse(200, $response);

 });


// Create Post

$app->post('/createPost', 'authenticatedefault', function() use ($app)
{
    $json = $app->request->getBody();
    $data = json_decode($json, true);
    // $result = implode(',',$data);

    $user_id = $data['user_id'];
    $post_title=$data['post_name']; 
    $location=$data['location'];
    $description = $data['description'];
    $instruction = $data['instruction'];
    $category_id = $data['category_id'];
	$media_type = $data['media_type'];
	$hashtags = $data['hashtags'];
	
    $response = array();
    $db = new DbHandler();
    $result=$db->createPost($user_id,$post_title, $location,$description,$category_id,$instruction,$post_media,$hashtags);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']=="True")
     {
           $response["status"] ="True";
           $response['message'] = "Post created successfully";
     }
     else if($result['status']=="False")
      {
          $response['status'] ="False";
          $response['message'] = 'Not created';
      }
        
  echoRespnse(200, $response);

 });


// Register user

$app->post('/register', 'authenticatedefault', function() use ($app)
{
    $json = $app->request->getBody();
    $data = json_decode($json, true);
    // $result = implode(',',$data);

    // $platform   = $data['platform'];
    $user_name = $data['username'];
    $first_name=$data['firstname']; 
    $last_name=$data['lastname'];
    $email=$data['email'];
    $mobile = $data['mobile'];
    $dob = $data['Date_of_birth'];
	$password = $data['Password'];
	$profile_pic = $data['user_pic'];
	
    $response = array();
    $db = new DbHandler();
    $result=$db->register($user_name,$first_name, $last_name,$email,$mobile,$dob,$password, $profile_pic);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']=="True")
     {
           $response["status"] ="True";
           $response['message'] = "Inserted successfully";
           $response["userDetails"]=$result['userDetails'];
      }
     else if($result['status']=="False")
      {
          $response['status'] ="False";
          $response['message'] = 'not inserted';
          $response["userDetails"]=array();
      }
        else 
      {
          $response['status'] =2;
          $response['message'] ='user already exists';

      }

  echoRespnse(200, $response);

 });


// Comment Insert

$app->post('/commentInsert', 'authenticatedefault', function() use ($app)
{
    $json = $app->request->getBody();
    $data = json_decode($json, true);
    // $result = implode(',',$data);

      $post_id = $data['post_id'];
      $user_id=$data['user_id'];
      $comment_description = $data['comment_description'];
      $comment_media = $data['comment_media'];
      
      $response = array();
    $db = new DbHandler();
    $result=$db->commentInsert($post_id,$user_id, $comment_description, $comment_media);
    if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "Comment created successfully";
           $response["commentsinserted"]=$result['comments'];
      }
     else if ($result['status']==0)
      {
          $response['status'] =0;
          $response['message'] = 'No Comments inserted';
          $response["comments"]=array();
      }
        
  echoRespnse(200, $response);
});




// Moods details

$app->post('/moods', 'authenticatedefault', function() use ($app)
{
    $json = $app->request->getBody();
    $data = json_decode($json, true);
    // $result = implode(',',$data);

      $user_id=$data['user_id'];
      $response = array();
    $db = new DbHandler();
    $result=$db->moods($user_id);
    if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "success";
           $response["moods"]=$result['moods'];
      }
     else if ($result['status']==0)
      {
          $response['status'] =0;
          $response['message'] = 'No moods found';
          $response["moods"]=array();
      }
        
  echoRespnse(200, $response);
});



// Category List

$app->post('/categories', 'authenticatedefault', function() use ($app)
{
    $json = $app->request->getBody();
    $data = json_decode($json, true);
    // $result = implode(',',$data);

      $user_id=$data['user_id'];
      $response = array();
    $db = new DbHandler();
    $result=$db->categories($user_id);
    if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "success";
           $response["categories"]=$result['categories'];
      }
     else if ($result['status']==0)
      {
          $response['status'] =0;
          $response['message'] = 'NO categories found';
          $response["categories"]=array();
      }
        
  echoRespnse(200, $response);
});
// comments List

$app->post('/listcomments', 'authenticatedefault', function() use ($app)
{
    $json = $app->request->getBody();
    $data = json_decode($json, true);
    // $result = implode(',',$data);

      $post_id=$data['post_id'];
      $response = array();
    $db = new DbHandler();
    $result=$db->listcomments($post_id);
    if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "success";
           $response["commentslist"]=$result['listofcomments'];
      }
     else if ($result['status']==0)
      {
          $response['status'] =0;
          $response['message'] = 'NOcomments found';
         
      }
        
  echoRespnse(200, $response);
});
//insert post shares


$app->post('/postsharesinsert', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
  
    $user_id = $data['user_id'];
    $post_id = $data['post_id'];
  

    $response = array();
    $db = new DbHandler();
    $result=$db->postsharesinsert($user_id,$post_id);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "inserted successfully";
           $response["shareinsertdetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'not inserted';
        
      }

  echoRespnse(200, $response);

 });

//insertpostlikes


$app->post('/postlikes', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
  
    $user_id = $data['user_id'];
    $post_id = $data['post_id'];
  

    $response = array();
    $db = new DbHandler();
    $result=$db->postlikes($user_id,$post_id);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "inserted successfully";
           $response["likeisertdetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'not inserted';
          $response["likeisertdetails"]=array();
      }

  echoRespnse(200, $response);

 });




//Getting profile

$app->post('/getprofile', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $id  = $data['id'];

    $response = array();
    $db = new DbHandler();
    $result=$db->getprofile($id);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "User details are";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'error';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });




//getMessage

$app->post('/getmessage', 'authenticatedefault', function() use ($app)
{


    $json = $app->request->getBody();
    $data = json_decode($json, true);
    $result = implode(',',$data);

    // $platform   = $data['platform'];
    $sno  = $data['sno'];

    $response = array();
    $db = new DbHandler();
    $result=$db->getmessage($sno);
   //$user_details=$db->userDetails($user_id);
     if ($result['status']==1)
     {
           $response["status"] =1;
           $response['message'] = "message is";
           $response["userDetails"]=$result['userDetails'];
      }
      else
      {
          $response['status'] =0;
          $response['message'] = 'error to get message';
          $response["userDetails"]=array();
      }

  echoRespnse(200, $response);

 });














//print_r($error);
//exit;
    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();
        //$response["error"] = true;
        $response["status"] =0;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
        echoRespnse(200, $response);
        $app->stop();
    }
}

/**
 * Validating email address
 */
function validateEmail($email) {
    $app = \Slim\Slim::getInstance();
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response["error"] = true;
        $response["message"] = 'Email address is not valid';
        echoRespnse(200, $response);
        $app->stop();
    }
}

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}
$app->run();
?>
