<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Headers: X-Requested-With, content-type, access-control-allow-headers, access-control-allow-origin, access-control-allow-methods');
header("Content-Type: application/json; charset=UTF-8");
require_once __DIR__ . '/fxn.php';
require_once __DIR__ . '/val.php';
$fxn = new fxn(); $val = new VALIDATE();
$request = json_decode(file_get_contents('php://input'), TRUE);
if (json_last_error() === JSON_ERROR_NONE) {
$arr = ['LOG', 'SUP', 'GATS', 'POS', 'PTW', 'LKT', 'FTLKS', 'COS',];
if (in_array($request['key'], $arr)) {
$code = null; $data = null; $result;
// login api
if($request['key'] == 'LOG'){
    $email = $val->valid_email(trim(strtolower($request['email'])));                          
    $pass = $val->valid_pass($request['password']);
    if($email && $pass){
        $match = $fxn->isEmailPasswordMatch($email, $pass);
        if($match > 0){
            $code = '00'; 
            $data = array(
                'name' => $match[0]["user"], 
                'userid' => $match[0]["uuid"],
                'handle' => $match[0]["uhandle"],
                'email' => $match[0]["uemail"],  
                'phone' => $match[0]["uphone"], 
                'avatar' => $match[0]["avatar"],
                'friends' => $match[0]["friends"],
                'models' => $match[0]["models"],
                'joined' => $match[0]["date_joined"],
                'message' => "Login Successful");
        } else {$code = '01'; $data = 'Login Failed!';}
    } else {$code = '01'; $data = 'Invalid Input!';}
} 
// signup
if($request['key'] == 'SUP'){                        
    $email = $val->valid_email(trim(strtolower($request['email'])));                          
    $pass = $val->valid_pass($request['password']); 
    $phone = $val->valid_phone($request['phone']);
    $res = $fxn->isUserRegistered($email, $phone);
    if($phone && $email){
        if($res === FALSE){
            if($pass != FALSE){
                if($fxn->createUser($phone,$email,$pass)){
                    $code = '00'; $data = 'Sign Up Successful!';
                } else {$code = '01'; $data = 'Sign Up Failed';} 
            }else {$code = "01"; $data = "Password must include uppercase, lowercase and number";}   
        } else {$code = '01'; $data = 'Duplicate User Found!';}
    } else {$code = '01'; $data = 'Invalid Input Format!';}
} 
// get all tweets
if($request['key'] == 'GATS'){
    if($items = $fxn->getAllTweets()){
        $code = '00'; $data = $items;
    } else {$code = '01'; $data = 'No tweet found!';}
} 
// get user tweets
if($request['key'] == 'POS'){
    $userid = $request['userid'];
    if($items = $fxn->getUserTweets($userid)){
        $code = '00'; $data = $items;
    } else {$code = '01'; $data = 'No orders found!';}
} 
// post tweet
if($request['key'] == 'PTW'){
    $tweet = trim(ucwords($request['tweet']));
    $name = trim(ucwords($request['user']));
    $uuid = $val->valid_id(trim($request['uuid']));
    if($items = $fxn->createTweet($tweet,$name,$uuid)){
        $code = '00'; $data = 'You just tweeted!';
    } else {$code = '01'; $data = 'Tweet Failed!';}
} 
// like tweet
if($request['key'] == 'LKT'){
    $tweetid = $val->valid_id(trim($request['tweetid']));
    $userid = $val->valid_id(trim($request['userid']));
    if($li = $fxn->likeTweet($tweetid, $userid)){
        $code = '00'; $data = array('You liked a tweet!', $li);
    } else {$code = '01'; $data = array('You unliked a tweet!', $li);}
} 
// get tweet likes
if($request['key'] == 'FTLKS'){
    $id = $val->valid_id(trim($request['tweetid']));
    if($likes = $fxn->conn->getTweetLikes($id)){
        $code = '00'; $data = $likes;
    } else {$code = '01'; $data = 0;}
} 




























// get product categories
// if($request['key'] == 'FPC'){
//     if($items = $fxn->getCategories()){
//         $code = '00'; $data = $items;
//     } else {$code = '01'; $data = 'No category found!';}
// } 
// // add product
// if($request['key'] == 'APS'){
//     $name = trim(ucwords($request['name']));
//     $category = trim(ucwords($request['category']));
//     $price = $request['price'];
//     $desc = $request['description'];
//     $quantity = $request['quantity'];
//     $userid = $request['userid'];
//     if($items = $fxn->createProduct($name,$category,$price,$desc,$quantity,$userid)){
//         $code = '00'; $data = 'Product Added Successfully!';
//     } else {$code = '01'; $data = 'Product Added Failed!';}
// } 
// // create order
// if($request['key'] == 'COS'){
//     $name = trim(ucwords($request['name']));
//     $email = trim(strtolower($request['email']));
//     $address = trim(ucwords($request['address']));
//     $items = $request['cartItems'];
//     $total = $request['total'];
//     if($done = $fxn->createOrders($name,$email,$address,$items,$total)){
//         $code = '00'; $data = "Order created successfully!";
//     } else {$code = '01'; $data = 'Order creation failed!';}
// } 
echo json_encode(['code'=> $code, 'data' => $data]);
    } else {echo 'wrong or empty key';}
} else {echo "Response not JSON";}
?> 