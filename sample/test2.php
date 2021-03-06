<?php
/* 組み込み簡易サーバー */
/* php -t sample -S localhost:8080 */

$code = $_GET['code'];

/* vuln: localhost:8080/test2.php?code=hello;%20ls&ev=shell_exec(%22ls%22);&ev2=echo%20shell_exec(%22ls%22); */
/* http://localhost:8080/test2.php?code=hello;%20ls&ev=shell_exec(%22ls%22);&file=../main.php&ev=shell_exec(%22ls%22);&nya=shell_exec&nyao=%27ls%27&reg=%2f%28%2e%2a%29%2fe&ev2=print%20shell_exec(%22ls%22); */
/* http://php.net/manual/ja/ref.exec.php */

print '[exec]' . '<br>';
exec("echo ${code}", $output);
print implode(' ', $output);

// 汚染されていない
exec("echo hello world", $temp);

print '<hr>';

print '[shell_exec]' . '<br>';
print shell_exec('echo ' . $code);

print '<hr>';

// これは検出したくない
print '[shell_exec with escapeshellarg]' . '<br>';
print shell_exec('echo ' . escapeshellarg($code));

print '<hr>';

// これは検出したくない
print '[shell_exec with escapeshellcmd]' . '<br>';
print shell_exec(escapeshellcmd("echo $code"));

print '<hr>';

print '[passthru]' . '<br>';
passthru("echo {$code}");

print '<hr>';

print '[system]' . '<br>';
system('echo ' . $code);

print '<hr>';

print '[Backtick]' . '<br>';
print `echo $code`;
//　汚染されていない
print `echo hello`;

print '<hr>';

// ; でコマンドつなげても無理っぽい
print '[popen]' . '<br>';

$handle = popen("echo " . $code, "r");
$read = fread($handle, 2096);
echo $read;
pclose($handle);

print '<hr>';

print '[proc_open]' . '<br>';

$descriptorspec = array(
  0 => array("pipe", "r"),
  1 => array("pipe", "w"),
  2 => array("pipe", "w")
);

$process = proc_open('echo ' . $code, $descriptorspec, $pipes);
if (is_resource($process)) {
  fclose($pipes[0]);

  echo stream_get_contents($pipes[1]);
  fclose($pipes[1]);
  echo stream_get_contents($pipes[2]);
  fclose($pipes[2]);
  
  $return_value = proc_close($process);
  //echo "command returned $return_value\n";
}

print '<hr>';

// require: -enable-pcntl
// これも ; でコマンドつなげてもダメっぽい
//print '[pcntl_exec]' . '<br>';
//pcntl_exec('ls', array($code));

//print '<hr>';


$ev = $_GET['ev'];
$reg = $_GET['reg'];

print '[preg_replace]' . '<br>';
print preg_replace('/(.*)/' . 'ei', $ev, '');
preg_replace('/(.*)/e', $ev, '');
preg_replace("$reg", "$ev", '');

// ok
preg_replace('/(.*)/i', $ev, '');
preg_replace("/{$ev}/i", 'hoge', '');
preg_replace("$reg", 'hoge', '');

print '<hr>';

print '[create_function]' . '<br>';

$f = create_function('', "return $ev");
print $f();

print '<hr>';

$file = $_GET['file'];

print '[FunctionCall]' . '<br>';
$nya = $_GET['nya'];
$nyao = $_GET['nyao'];
print $nya($nyao);

print '<hr>';

print '[FunctionCall2]' . '<br>';
$pao = array($_GET['nya'], $_GET['nyao']);
print $pao[0]($pao[1]);

print '<hr>';

print '[assert]' . '<br>';
assert($_GET['ev2']);

print '<hr>';

print '[require / include]' . '<br>';

require($file);
require_once($file);
include($file);
include_once($file);

print '<hr>';

