<?php

header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

session_start();
//session_unset();

# control $_SESSION inactivity time
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    // last request was more than 30 minates ago
    @session_destroy();   // destroy session data in storage
    session_unset();     // unset $_SESSION variable for the runtime
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

//if($_GET['func'] == 'logout')  session_unset();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
			
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="title" content="Lcgaste" />
<meta name="description" content="Lcgaste.com fotos"  />
<meta name="Keywords" content="photos fotos album" />
<meta name="viewport" content="width=device-width,initial-scale=0.3">	
<link rel="icon" type="image/png" href="img/favicon.png" />
<!--<link rel="shortcut icon" href="img/favicon.ico" />-->
<link href="css/main.css" rel="stylesheet" type="text/css" />
<?php

# Includes  ----------------------------------

include 'inc/config.php';
include $conf_include_path .'comm.php';
include $conf_include_path .'connect.php';
include $conf_include_path .'oops_comm.php';

# add include path for PEAR extensions
/*$path = '/usr/local/lib/php';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);
@include_once 'Mail.php';
@include_once 'Mail/mime.php';
*/

/*include $conf_include_path .'oops_sc.php';
if($_GET['mod'] == 'admin') include $conf_include_path .'comm_admin.php';
*/
if(!$_GET['lang'] && !$_SESSION['misc']['lang']) $_GET['lang'] = $conf_default_lang;
if($_GET['lang']) $_SESSION['misc']['lang'] = $_GET['lang'];

include $conf_include_path .'translation.php'; 

date_default_timezone_set($conf_timezone);

# Sanitize get and post  ----------------------------------
sanitize_input();

# Logout user  ----------------------------------
if($_GET['func'] == 'logout') {
	# store record of user logging out?
	session_unset(); session_destroy();
	jump_to($conf_main_page);
	exit();
}

$now = new date_time('now');

# Manage modules  ----------------------------------
if(!$_GET['mod']) $_GET['mod'] = $conf_default_mod;

?>
<script language="javascript" src="<?= $conf_include_path; ?>comm.js"></script>
<script language="javascript" src="<?= $conf_include_path; ?>ajax.js"></script>
<title>::: LCGASTE.COM :::
<?php  //echo ucfirst($_SESSION['login']['modules'][$_GET['mod']]['name']); ?>
</title>
</head>
<body>
<div id="container">
  <div id="header">
    <h3><a href="<?= $conf_main_page; ?>" class="no_decoration">Lcgaste.com &ndash; Photo albums</a></h3>
  </div>
<?php
if(!$_SESSION['id']) {
	$_SESSION['id'] = $_GET['id'];
	write_log_db('login', 'login', 'ID: '. $_GET['id'], 'index.php');
}

if($_SESSION['id']) {

?>
  <div id="content">
    <div id="alerts_wrapper">
      <div id="alerts" class="notice_info"> </div>
      <div id="alerts_close" onclick="JavaScript:close_alerts_box();"> </div>
    </div>

    <div class="language_selector"><!-- toolbox -->
      <p>
	  <?php
	if($_SESSION['id'] == '123') {
?>
<a href="<?= $conf_main_page; ?>?mod=home&view=new_album">Nuevo Álbum</a>&nbsp;|&nbsp;
	  <a href="places">Lugares</a>&nbsp;|&nbsp;<a href="Users">Usuarios</a>&nbsp;|&nbsp;
        <?php
	  print_languages_flags();
	  ?>
        &nbsp;|&nbsp;
		<?php
	}	//	if($_SESSION['id'] == '123') {
?><a href="<?= $conf_main_page; ?>?func=logout">Cerrar</a></p>
    </div>


    <div id="module_wrapper">
      <?php

	# -------------------- INCLUDE THE MODULE view --------------------- #	
	if(!$_GET['view']) $_GET['view'] = 'main';
	
	$include_file = 'mod/'. $_GET['mod'] .'/'. $_GET['view'] .'.php';
	
	include $include_file;
}
	?>
    </div>
  </div>
  <div id="footer">
    
  </div>
</div>
</body>
</html>