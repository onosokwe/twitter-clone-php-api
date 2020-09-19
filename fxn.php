<?php
include_once('sql.php');
date_default_timezone_set ("Africa/Lagos");
class fxn extends socialApp {
	public $conn;
	public function __construct(){
		$this->conn = new socialApp(DB); return $this->conn;
	}
	// USER
	public function isUserRegistered($email, $phone){
		$table = "users"; 
		$cols = "*";
		$where = "WHERE `uemail` = '".trim(strtolower($email))."' OR `uphone` = '".trim($phone)."'";
		if($this->conn->select_fetch($table,$cols,$where)){
		return TRUE;} else {return FALSE;}
	}
	public function isHandleTaken($handle){
		$table = "users"; 
		$cols = "*";
		$where = "WHERE `uhandle` = '".trim(strtolower($handle))."'";
		if($this->conn->select_fetch($table,$cols,$where)){
		return TRUE;} else {return FALSE;}
	}
	public function isEmailPasswordMatch($email, $pass){
		$table = "users"; $cols = "*"; 
		$ps = sha1(md5($pass));
		$where = "WHERE `uemail` = '".trim(strtolower($email))."' && `upass`='".trim($ps)."'";
		if($fet = $this->conn->select_fetch($table,$cols,$where)){
		return $fet;} else {return FALSE;}
	}
	public function createUser($phone,$email,$pass){ 
		$uuid = rand(10000000, 99999999);
		$npass = sha1(md5($pass)); 
		$today = date('Y-m-d H:i:s'); 
		$table = "users"; 
		$cols = "`user`,`uuid`,`uemail`,`uphone`,`upass`,`date_joined`,`status`"; 
		$vals = "'New User','$uuid','$email','$phone','$npass','$today','1'"; 
		if($this->conn->insert($table,$cols,$vals)){return TRUE;} else {return FALSE;}
	}
	public function resetPassword($email, $pass){
		$table = "users"; 
		$ps = sha1(md5($pass));
		$colVals = "`upass` = '$ps'"; 
		$where = "WHERE `uemail` = '$email'";
		if($this->conn->update($table,$colVals,$where)){
		return true;} else {return FALSE;}
	}
	// TWEET
	public function createTweet($tweet,$name,$uuid){
		$table = "tweets"; $today = time();
		$twid = rand(10000000, 99999999);
		$cols = "`tweet`,`tweetid`,`user`,`uuid`,`avatar`,`handle`,`posted_on`,`status`"; 
		$vals = "'$tweet','$twid','$name','$uuid','','','$today','1'"; 
		if($this->conn->insert($table,$cols,$vals)){return true;} else {return FALSE;}
	}
	public function getAllTweets(){
		$table = "tweets"; 
		$cols = "*";
		$where = "WHERE `tid` > '0' && `status` = '1' ORDER BY `posted_on` DESC LIMIT 8";
		$found = $this->conn->select_fetch($table,$cols,$where);
		if($found > 0){return $found;} else {return false;} return false;
	}
	public function getUserTweets($userid){
		$table = "tweets"; 
		$cols = "*";
		$where = "WHERE `tid` > '0' && `userid` = '$userid' && `status` = '1' ORDER BY `posted_on` DESC";
		$found = $this->conn->select_fetch($table,$cols,$where);
		if($found > 0){return $found;} else {return false;} return false;
	}
	public function hasUserLikedTweet($tweetid, $userid){
		$table = "likes"; 
		$cols = "*";
		$where = "WHERE `tweetid` = '$tweetid' && `userid`='$userid' && `status` = '1'";
		if($this->conn->select($table,$cols,$where)){
		return TRUE;} else {return FALSE;}
	}
	public function likeTweet($tweetid, $userid){
		$likes = $this->conn->getTweetLikes($tweetid);
		$has = $this->hasUserLikedTweet($tweetid, $userid);
		$table = "tweets";
		$today = time();
		$likeCols = "`tweetid`, `userid`, `date_liked`, `status`";
		$likeVals = "'$tweetid', '$userid', '$today', '1'";
		$hasWhr = "WHERE `tweetid` = '$tweetid' ";
		if($has){
			// user has liked before. Update like table to 0 and decrement tweet likes
			$likes = --$likes;
			$delColsVals = "`status` = '0'";
			$delWhere = "WHERE `tweetid` = '$tweetid' && `userid` = '$userid'";
			$hasVals = "`likes` = '$likes'";
			if($this->conn->update('likes',$delColsVals, $delWhere)){
				if($this->conn->update($table,$hasVals,$hasWhr)){
					return $likes;} else {return false;} 
			} else {return false;} 
		} else {
			// If user has not liked before. Add to like table and increment tweet likes
			$likes ? ++$likes : $likes = 1;
			$hasVals = "`likes` = '$likes'";
			if($this->conn->insert('likes',$likeCols,$likeVals)){
				if($this->conn->update($table,$hasVals,$hasWhr)){
					return $likes;} else {return false;}
			} else {return false;} 
		}
	}


}
?>