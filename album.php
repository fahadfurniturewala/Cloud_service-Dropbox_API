<?php
echo '<html>
<head>
<title>ALBUM</title>
<style>
img
{
	display: block;
    margin-left: auto;
    margin-right: auto;
	height:500px;
	width:500px;
	border-radius: 20px;
}
table{
	margin: 0 auto; 
	
}
p
{
	margin: 0 auto; 
}

#left {
    width:15%;
	height=100%;
    margin: 1%;
	float:left;
	
}
#center {
    margin:auto;
	height=100%;
    width: 40%;
	float:left;
	min-height: 100%;;
	 border-left: 6px double black;
	 min-height: 100%;;
	border-right: 6px double black;
}
#right {
    margin: 1%;
    width: 40%;
	height=100%;
	float:right;
	
}

</style>
</head>
<body background="http://i.stack.imgur.com/jGlzr.png">

<div id="left" name="upform">
<form enctype="multipart/form-data" method="POST" action="album.php">
<p>
<center>
<h2> UPLOAD FILE</h2>
</center>
</p>
<input type="file" name="upload_file" />
<br></br>
<p><input type="submit" name="upload_button" value="UPLOAD"></p>
</form>';

 
error_reporting(E_ALL);
require_once("DropboxClient.php");

// you have to create an app at https://www.dropbox.com/developers/apps and enter details below:
$dropbox = new DropboxClient(array(
	'app_key' => "c3kml0zp7ztmkc9", 
	'app_secret' => "xg4q99aw2250utd",
	'app_full_access' => true,
),'en');


// first try to load existing access token
$access_token = load_token("access");
if(!empty($access_token)) {
	$dropbox->SetAccessToken($access_token);
	//echo "loaded access token:";
	//print_r($access_token);
}
elseif(!empty($_GET['auth_callback'])) // are we coming from dropbox's auth page?
{
	// then load our previosly created request token
	$request_token = load_token($_GET['oauth_token']);
	if(empty($request_token )) die('Request token not found!');
	
	// get & store access token, the request token is not needed anymore
	$access_token = $dropbox->GetAccessToken($request_token);	
	store_token($access_token, "access");
	delete_token($_GET['oauth_token']);
}

// checks if access token is required
if(!$dropbox->IsAuthorized())
{
	// redirect user to dropbox auth page
	$return_url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']."?auth_callback=1";
	$auth_url = $dropbox->BuildAuthorizeUrl($return_url);
	$request_token = $dropbox->GetRequestToken();
	store_token($request_token, $request_token['t']);
	die("Authentication required. <a href='$auth_url'>Click here.</a>");
}
$files = $dropbox->GetFiles("",false);
if(!empty($_FILES['upload_file']))
 { 
	
	$upload_name = $_FILES["upload_file"]["name"];
	$extension=$_FILES["upload_file"]["type"];
	//print_r($extension);
	//echo $extension;
	if($extension=='image/JPG' || $extension=='image/jpg' || $extension=='image/jpeg'){	
	
	$meta = $dropbox->UploadFile($_FILES["upload_file"]["tmp_name"], $upload_name);
	header('Location:album.php');
	echo "\r\n\r\n<b>Uploading $upload_name:</b>\r\n";
	//print_r($meta);
	//echo "\r\n done!";
	}
	else{
		echo "Wrong file format";
		//echo $extension;
	}
	
		
		
		
	$file = reset($files);
	
	
	
	
}
echo '</div>';

echo '<div id="right">';
echo '<center><p><h2>PREVIEW</h2></p></center>';
if(isset($_GET['DL']))
{
	$down=$_GET['DL'];
	$list = $dropbox->GetLink($down,false);
	//$show=$_GET['link'];
	$test_file = "test_download_".basename($down);
	$meta=$dropbox->DownloadFile($down, $test_file);
	
	//echo $list;
	echo '<img src='.$list.' align="middle"></img>';
	
	//print_r($meta);
}
echo '</div>';
if(isset($_GET['delete']))
{
	$delete=$_GET['delete'];
	//$list = $dropbox->GetLink($down,false);
	//$show=$_GET['link'];
	//$test_file = "test_download_".basename($down);
	$dropbox->Delete($delete);
	header('Location:album.php');
	
}

//echo "<pre>";
//echo "<b>Account:</b>\r\n";
//print_r($dropbox->GetAccountInfo());

echo '<div id="center">';
echo '<center><p><h2>IMAGES ON CLOUD</h2></p></center>';
echo '<table cellspacing="10">';
	foreach($files as $x)
	{
	//echo $x[0];
	$list = $dropbox->GetLink($x);
	//echo basename($x->path);
	echo '<tr>';
	echo '<td>',basename($x->path),'</td>';
	echo '&nbsp';
	echo '<td>','<a href="album.php?DL='.basename($x->path).'">',"DOWNLOAD",'</a>','</td>';
	echo '&nbsp';
	echo '<td>','<a href="album.php?delete='.basename($x->path).'">',"DELETE",'</a>','</td>';
	//echo "\r\n\r\n<b>Downloading $file->path:</b>\r\n";
	//print_r($dropbox->DownloadFile($x, $test_file));
		
	
	
	//echo $list;
	echo '<br>';
	}
	echo '</div>';

// if there is no upload, show the form






function store_token($token, $name)
{
	if(!file_put_contents("tokens/$name.token", serialize($token)))
		die('<br />Could not store token! <b>Make sure that the directory `tokens` exists and is writable!</b>');
}

function load_token($name)
{
	if(!file_exists("tokens/$name.token")) return null;
	return @unserialize(@file_get_contents("tokens/$name.token"));
}

function delete_token($name)
{
	@unlink("tokens/$name.token");
}





function enable_implicit_flush()
{
	@apache_setenv('no-gzip', 1);
	@ini_set('zlib.output_compression', 0);
	@ini_set('implicit_flush', 1);
	for ($i = 0; $i < ob_get_level(); $i++) { ob_end_flush(); }
	ob_implicit_flush(1);
	echo "<!-- ".str_repeat(' ', 2000)." -->";
}
echo '</body>
</html>';

?>