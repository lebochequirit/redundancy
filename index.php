<?php
	/**
	 * @file
	 * @author  squarerootfury <fury224@googlemail.com>	 
	 *
	 * @section LICENSE
	 *
	 * This program is free software; you can redistribute it and/or
	 * modify it under the terms of the GNU General Public License as
	 * published by the Free Software Foundation; either version 3 of
	 * the License, or (at your option) any later version.
	 *
	 * This program is distributed in the hope that it will be useful, but
	 * WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
	 * General Public License for more details at
	 * http://www.gnu.org/copyleft/gpl.html
	 *
	 * @section DESCRIPTION
	 *
	 * Program start point
	 */
	 
	$start = microtime(true);
	//first - start a session if needed
	if (isset($_SESSION) == false)
		session_start();
	//Include the main program file
	include "./Includes/Program.inc.php";	
	/*
		Startup the program, load the configurations, but only when needed.
	*/
	//Parse the configuration file	
	if (!isset($GLOBALS["config"])){
		$GLOBALS["config"] = parse_ini_file($GLOBALS["config_dir"]."Redundancy.conf");
		//Set the program path (very important)
		$GLOBALS["Program_Dir"] = $GLOBALS["config"]["Program_Path"];
	}
	//Enable the debug mode (display errors) or not
	if ($GLOBALS["config"]["Program_Debug"] == 1)
			error_reporting(E_ALL);
	//Check xss problems and check if the user could be banned
	if (isXSS() == true || ($GLOBALS["config"]["Program_Enable_Banning"] && isBanned()))
	{
		header('HTTP/1.0 403 Forbidden');
		die();	
	}	
	//Force https if needed
	if ($GLOBALS["config"]["Program_HTTPS_Redirect"] == 1)
	{
		redirectToHTTPS();
	}
	//Load language informations
	parseLanguageData();
	if ($GLOBALS["config"]["Program_Enable_ErrorHandler"] == 1)
		setExceptionHandler();
	if ($GLOBALS["config"]["use_buffer"] == 1)
		ob_start();
	//Rename the user name value if the user is logged in and do a check if needed
	if (isset($_SESSION["user_name"])){
		renameUserSessionIfNeeded();
		if ($GLOBALS["config"]["Program_Session_Timeout"] != -1){
			if (isset($_SESSION["begin"])  == false || !checkSessionTimeout($_SESSION["begin"]))
				logoutUser("session_stopped_fail");
		}
		//Load user defined options from the database if enabled by config
		if ($GLOBALS["config"]["Program_Enable_User_Settings"] == 1){
			loadUserSettings();
		}
	}		
	//******************************Modules, which can be included directly*************************
	if (isset($_GET["module"]) && $_GET["module"] == "image" )
	{
		include "./Includes/image.inc.php";			
		exit;
	}
	elseif (isset($_GET["share"])){
		include $GLOBALS["Program_Dir"]."Includes/share.inc.php";	
	}
	elseif (isset($_GET["module"]) && $_GET["module"] == "player" )
	{
		include "./Includes/player.inc.php";
	}		
	//****************************Exceptions for dynamically loaded content//**************************
	if (isset($_GET["search"]) == true || isset($_GET["upload"]) == true || isset($_GET["newdir"]) == true)
	{		
		if (isset($_GET["search"]))
			include "./Includes/search.inc.php";
		else if (isset($_GET["newdir"]))
			include "./Includes/createdir.inc.php";
		else
			include "./Includes/upload.inc.php";
		exit;
	}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<?php
	//Internet Explorer fix
	if (isset($_FILES,$_POST) == false)
		header('Content-type: text/html; charset=utf-8');
?>
<?php if ($GLOBALS["config"]["Program_Display_Generator_Tag"] == 1): ?>
<meta name="generator" content="<?php echo $GLOBALS["config"]["Program_Name_ALT"]." ".$GLOBALS["Program_Version"];?>" />
<?php endif;?>
<link rel = "stylesheet" href="./Lib/bootstrap/css/bootstrap.min.css" type = "text/css"/>
<?php	
	if ($GLOBALS["config"]["Program_Enable_JQuery"] == 1)
		include "./Lib/JQuery.inc.php";
?>
<?php
	if (isset($GLOBALS["template"]))
		echo $GLOBALS["template"]["Template_Header"];
?>
<link rel="icon" href="./favicon.ico" >
<title>
<?php		
	//Display the Program name and calculate the user space if a session is set
	echo $GLOBALS["config"]["Program_Name_ALT"];
	if (isset($_SESSION["user_name"])){
		//Set the user contingent and refresh the information about used space
		$_SESSION["space_used"] = getUsedSpace($_SESSION['user_name']);	
		//Check the user session if any sql injections are done
		//if the $_SESSION value differs with the value result of mysqli_real_escape_string
		if (isSessionCorrupted() == true)
		{
			header('HTTP/1.0 403 Forbidden');
			die();	
		}		
	}	
?>
</title>
</head>
<body> 
<div class = 'container'>
<div class = 'row'>
<?php	
	//Pre plugin loading
	if ($GLOBALS["config"]["Program_Enable_Plugins"] == 1)
	{
		includePlugins();
	}	
?>
<?php	
	//Display mainteance message if Redundancy is not enabled
	if ($GLOBALS["config"]["Enable"] != 1 ) 
	{
		if ((isset($_GET["module"]) && ($_GET["module"] == "admin" || $_GET["module"] == "login" || $_GET["module"] == "logout" )) == false){
			include $GLOBALS["Program_Dir"]."Includes/mainteance.inc.php";		
			exit;
		}
	}
	if (isset($_SESSION["user_logged_in"]))
	{		
		//apply user informations
		loadUserChanges();
		//Include the status bar and menu and the wanted file	
		include $GLOBALS["Program_Dir"]."/Includes/Header.inc.php";
		//Display content itself		
		echo "<div class=\"panel panel-default\"> " ;
		echo "<div class=\"panel-body\">";
			//Include the requested file	
		if (isset($_GET["module"]) && strpos($_GET["module"],"..") === false && strpos($_GET["module"],".") === false){
			$path = $GLOBALS["Program_Dir"]."Includes/".$_GET["module"].".inc.php";			
			if (file_exists($path))
				include $path;		
		}
		else if (isset($_GET["module"]) == false && isset($_GET["share"]) == false){
			//The startpage is an exception, it will be displayed if the module= parameter is not set.
			include $GLOBALS["Program_Dir"]."Includes/startpage.inc.php";		
		}		
		echo "</div>";
		echo "</div>";
	}
	//Include other files (further exceptions)
	else if (isset($_GET["module"]) && $_GET["module"] == "activate")
		include $GLOBALS["Program_Dir"]."Includes/activate.inc.php";	
	else if (isset($_GET["module"]) && $_GET["module"] == "register")
		include $GLOBALS["Program_Dir"]."Includes/register.inc.php";	
	else if (isset($_GET["module"]) && $_GET["module"] == "recover")
		include $GLOBALS["Program_Dir"]."Includes/recover.inc.php";	
	else if (isset($_GET["share"]))
		include $GLOBALS["Program_Dir"]."Includes/share.inc.php";		
	else if (isset($_GET["module"]) && $_GET["module"] == "viewonly")
		include $GLOBALS["Program_Dir"]."Includes/viewonly.inc.php";		
	else if (isset($_GET["module"]) && $_GET["module"] == "setpass")
		include $GLOBALS["Program_Dir"]."Includes/setpass.inc.php";		
	else if (isset($_GET["module"]) && $_GET["module"] == "health")
		include $GLOBALS["Program_Dir"]."Includes/health.inc.php";		
	else
		include $GLOBALS["Program_Dir"]."Includes/Login.inc.php";		
?>
</div>
	<?php 
		//Display the account menu button, but only when logged in.
		if (isset($_SESSION["user_logged_in"]))
			include $GLOBALS["Program_Dir"]."Includes/Menu.inc.php";	
	?>	
<?php 
	if ($GLOBALS["config"]["use_buffer"] == 1)
		ob_end_flush();
?>
</div>
</body>
</html>