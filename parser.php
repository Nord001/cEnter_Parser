<?php
error_reporting(E_ALL^E_NOTICE);
define("BASE_URL", "http://www.enter.ru");
define("DB", "center");
define("DB_TABLE", "goods");
require_once dirname(__FILE__).'/Threadi/Loader.php';
require_once 'Loader.php';
spl_autoload_register(array('Loader', 'autoload'));
require_once 'database/ClassLoader.php';
spl_autoload_register(array('ClassLoader', 'autoload'));
//$proxies = file(dirname(__FILE__)."/proxies.txt");
//$php = array();
//foreach ($proxies as $line => $pr) {
//    $els = explode("\t", $pr);
//    $phpStr[] = "\tarray('ip' => '".$els[0]."', 'port' => '".$els[1]."', 'user' => '".$els[2]."', 'pass' => '".trim($els[3], "\r\n")."')";
//}
//$res = "<?php".PHP_EOL."return array(".PHP_EOL.implode(",".PHP_EOL, $phpStr).PHP_EOL.");".PHP_EOL."";
//file_put_contents(dirname(__FILE__)."/proxies.php", $res);exit;
//сначала получим урлы корневых категорий
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://enter.ru");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
$result = curl_exec($ch);
curl_close($ch);

//ищем линки главного меню
preg_match_all("/class=\"bToplink\"[\s\S]*?href=\"(.*)?\"/", $result, $matches);
$superCategoryCount = sizeof($matches[1]);
//echo $superCategoryCount.PHP_EOL;
//var_dump($matches);
$threads = array();
$joinPoint = new Threadi_JoinPoint;
$proxies = require_once 'proxies.php';
$proxiesCount = sizeof($proxies);
$treadProxyCount = floor($proxiesCount/$superCategoryCount);
file_put_contents(dirname(__FILE__)."/urls.txt", "");
for ($i = 0; $i < $superCategoryCount; $i++) {
    $threads[$i] = Threadi_ThreadFactory::getReturnableThread(array('Parser_SuperCategory', 'parse'));
    $threads[$i] -> start($matches[1][$i], Parser_Proxy::getProxyList($superCategoryCount, $i));
    $joinPoint->add($threads[$i]);
}
$joinPoint->waitTillReady();
?>