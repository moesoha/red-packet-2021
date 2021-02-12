<?php
$payload = file_get_contents("payload.txt");
$payload = preg_replace("#[\\r\\n]+#", "\n", $payload);
$matrix = explode("\n", $payload);
if (count($matrix) !== 8) {
	die("wrong row count");
}
foreach($matrix as $i => $a) {
	if (strlen($a) !== 128 / 8) {
		die("wrong length at $i");
	}
}

$routes = [];
for ($c = 0; $c < 16; $c += 2) {
	$vs = [];
	for ($r = 0; $r < 8; $r++) {
		$vs[] = dechex(ord($matrix[$r][$c])).dechex(ord($matrix[$r][$c + 1]));
	}
	$routes[] = implode(":", $vs);
}
echo <<<EOS
router id 44.159.73.255;
log syslog all;
protocol device {}

ipv6 table hb2021;
protocol static {
	ipv6 { table hb2021; };

EOS;

foreach($routes as $i => $route) {
	echo "\troute $route/128 reject;".PHP_EOL;
}

echo <<<EOS
}

protocol bgp {
	passive;
	multihop;
	error wait time 5,10;
	local as 6674;
	neighbor range ::/0 as 7642;
	dynamic name "hb";
	dynamic name digits 3;
	ipv6 {
		table hb2021;
		import none;
		export all;
	};
}
EOS;