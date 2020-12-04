<?php
/**
 *
 *  Wallpaper Monitor by Akkariin
 *
 *  开源服务器监控桌面壁纸程序
 *
 *  +--------------------------------------------------------+
 *  | 仅可用于 Linux 系统，如果读取出现问题请检查 plugins 和 |
 *  | logs 目录是否有读写权限，plugins 需要拥有可执行权限    |
 *  +--------------------------------------------------------+
 *
 */

$logo = <<<EOF
\033[1;36m    __        __    _ _                              \033[0m __  __             _ _             
\033[1;36m    \ \      / /_ _| | |_ __   __ _ _ __   ___ _ __  \033[0m|  \/  | ___  _ __ (_) |_ ___  _ __ 
\033[1;36m     \ \ /\ / / _` | | | '_ \ / _` | '_ \ / _ \ '__| \033[0m| |\/| |/ _ \| '_ \| | __/ _ \| '__|
\033[1;36m      \ V  V / (_| | | | |_) | (_| | |_) |  __/ |    \033[0m| |  | | (_) | | | | | || (_) | |   
\033[1;36m       \_/\_/ \__,_|_|_| .__/ \__,_| .__/ \___|_|    \033[0m|_|  |_|\___/|_| |_|_|\__\___/|_|   
\033[1;36m                       |_|         |_|               \033[0m                                    


EOF;
// Daemon 部分，用命令行运行
if(php_sapi_name() == "cli") {
	
	system('clear');
	echo "Wallpaper Monitor\nVersion: 1.0 by Akkariin\nData loading, please wait...";
	$firstrun = true;
	
	// 获取 CPU 信息
	$cpuname = shell_exec('lscpu | grep "Model name"');
	$exp = explode(":", $cpuname);
	$cpuname = trim($exp[1]);
	
	// 获取内存信息
	$mem_1 = trim(shell_exec('dmidecode -t memory | grep "Manufacturer" | grep -v "NO DIMM" | grep -v "\\[Empty\\]" | awk \'{print$2}\' | sed -n "1p"'));
	$mem_2 = trim(shell_exec('dmidecode -t memory | grep "Part Number" | grep -v "NO DIMM" | grep -v "\\[Empty\\]" | awk \'{print$3}\' | sed -n "1p"'));
	$mem_3 = trim(shell_exec('dmidecode -t memory | grep "Size" | grep -v "No Module Installed" | awk \'{print$2$3}\' | sed -n "1p"'));
	
	// 获取主板信息
	$brd_1 = trim(shell_exec('dmidecode -t system | grep "Manufacturer" | awk \'{print$2}\''));
	$brd_2 = trim(shell_exec('dmidecode -t system | grep "Product Name" | awk \'{print$3$4$5$6}\''));
	
	// 拼凑字符串
	$memname = "{$mem_1} {$mem_2} {$mem_3}";
	$brdname = "{$brd_1} {$brd_2}";
	
	$arr = Array(
		'cpu' => $cpuname,
		'mem' => $memname,
		'brd' => $brdname
	);
	
	@file_put_contents(__DIR__ . "/logs/sysinfo.json", json_encode($arr));
	
	while(true) {

		// 取得系统负载
		$v_load = round(shell_exec(__DIR__ . "/plugins/getload"), 2);
		@file_put_contents(__DIR__ . "/logs/uptime.log", $v_load);
		
		// 取得 CPU 温度
		$result = shell_exec('sensors | grep "Core" | awk \'{print $3}\' > /logs/temp_sensors.log');
		$data   = @file_get_contents(__DIR__ . "/logs/temp_sensors.log");
		$exp    = explode("\n", $data);
		$all    = 0.00;
		$i      = 0;
		
		// 取得所有核心温度
		foreach($exp as $core) {
			$core = str_replace("°C", "", $core);
			$core = Floatval($core);
			$all  = $all + $core;
			$i++;
		}
		
		// 计算平均温度
		$v_temp = round($all / $i, 2);
		@file_put_contents(__DIR__ . "/logs/coretemp.log", $v_temp);
		@unlink(__DIR__ . "/logs/temp_sensors.log");
		
		// 取得内存使用率
		$result = trim(shell_exec("free -m | grep 'Mem' | awk '{print \$2 \"|\" \$3}'"));
		$v_mems = explode("|", $result);
		@file_put_contents(__DIR__ . "/logs/memory.log", $result);
		
		// Daemon 图形界面
		$memavg  = round(($v_mems[1] / $v_mems[0]) * 100, 2);
		$nowtime = date("Y-m-d H:i:s");
		$memtext = "已使用 {$v_mems[1]}MB，物理内存内存总大小 {$v_mems[0]}MB";
		$distext = "当前服务器 CPU 负载为 \033[1;32m{$v_load}%\033[0m，已使用内存 \033[1;36m{$memavg}%\033[0m，总共 {$v_mems[0]}MB";
		
		// 清理缓冲区
		if($firstrun) {
			system('clear');
			$firstrun = false;
		}
		echo chr(27) . "[21A";
		echo $logo;
		echo "\033[0;36m+----------------------------------------------------------------------------------------------------+\033[0m\n";
		echo "\033[0;36m|\033[0m {$distext}" . str_repeat(" ", 103 - mb_strlen($distext)) . " \033[0;36m|\033[0m\n";
		echo "\033[0;36m+----------------------------------------------------------------------------------------------------+\033[0m\n";
		echo "\033[0;36m[\033[1;32m" . str_repeat("|", Intval($v_load)) . str_repeat(" ", 100 - Intval($v_load)) . "\033[0m\033[0;36m]\033[0m\n";
		echo "\033[0;36m[\033[1;36m" . str_repeat("|", Intval($memavg)) . str_repeat(" ", 100 - Intval($memavg)) . "\033[0m\033[0;36m]\033[0m\n";
		echo "\033[0;36m+----------+-----------------------------------------------------------------------------------------+\033[0m\n";
		echo "\033[0;36m|\033[0m 主板型号 \033[0;36m|\033[0m {$brdname}" . str_repeat(" ", 87 - mb_strlen($brdname)) . " \033[0;36m|\033[0m\n";
		echo "\033[0;36m|\033[0m CPU 型号 \033[0;36m|\033[0m {$cpuname}" . str_repeat(" ", 87 - mb_strlen($cpuname)) . " \033[0;36m|\033[0m\n";
		echo "\033[0;36m|\033[0m 内存型号 \033[0;36m|\033[0m {$memname}" . str_repeat(" ", 87 - mb_strlen($memname)) . " \033[0;36m|\033[0m\n";
		echo "\033[0;36m|\033[0m 当前时间 \033[0;36m|\033[0m {$nowtime}" . str_repeat(" ", 87 - mb_strlen($nowtime)) . " \033[0;36m|\033[0m\n";
		echo "\033[0;36m|\033[0m 系统负载 \033[0;36m|\033[0m {$v_load}%" . str_repeat(" ", 86 - mb_strlen($v_load)) . " \033[0;36m|\033[0m\n";
		echo "\033[0;36m|\033[0m 内存使用 \033[0;36m|\033[0m {$memtext}" . str_repeat(" ", 74 - mb_strlen($memtext)) . " \033[0;36m|\033[0m\n";
		echo "\033[0;36m|\033[0m 系统温度 \033[0;36m|\033[0m {$v_temp}°C" . str_repeat(" ", 85 - mb_strlen($v_temp)) . " \033[0;36m|\033[0m\n";
		echo "\033[0;36m+----------+-----------------------------------------------------------------------------------------+\033[0m\n";
		
		// 延迟时间，数字越大刷新速度越慢，越小刷新越快
		// 如果服务器性能非常辣鸡可以适当增加此数字避免对服务器造成过大压力
		sleep(1);
	}
	exit;
}


if(isset($_GET['s']) && $_GET['s'] == 'load') {
	$result = round(@file_get_contents(__DIR__ . "/logs/uptime.log"), 2);
	if(!stristr("{$result}", ".")) {
		$result .= ".00";
	}
	echo $result;
	exit;
} elseif(isset($_GET['s']) && $_GET['s'] == 'temp') {
	$result = round(@file_get_contents(__DIR__ . "/logs/coretemp.log"), 2);
	if(!stristr("{$result}", ".")) {
		$result .= ".00";
	}
	echo $result;
	exit;
} elseif(isset($_GET['s']) && $_GET['s'] == 'mem') {
	$result = @file_get_contents(__DIR__ . "/logs/memory.log");
	$exp = explode("|", $result);
	echo json_encode(Array(
		'total' => Intval($exp[0]),
		'used'  => Intval($exp[1])
	));
	exit;
}

$sysinfo = @file_get_contents(__DIR__ . "/logs/sysinfo.json");
$sysinfo = json_decode($sysinfo, true);
if(!$sysinfo) {
	$sysinfo = Array(
		'cpu' => '未知',
		'mem' => '未知',
		'brd' => '未知'
	);
}
?>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=11">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" crossorigin="anonymous">
		<link rel="stylesheet" href="https://cdn.bootcss.com/font-awesome/4.7.0/css/font-awesome.min.css" crossorigin="anonymous">
		<link rel="stylesheet" href="css/style.css" crossorigin="anonymous">
		<title>Wallpaper Monitor</title>
		<script src="https://apps.bdimg.com/libs/jquery/2.1.4/jquery.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
		<script src="https://d3js.org/d3.v5.min.js"></script>
		<style type="text/css">
			body {
				/* 背景图地址，URL 内的图片地址可自定义 */
				background-image: url(https://i.natfrp.org/cf55c89fc72018b2743b3d4900b9de00.png);
				/* 背景图位置调整，默认居中 */
				background-position: center;
			}
			/* 这是负责设置服务器信息那个 div 的地方，修改 bottom 和 right 就可以调整位置 */
			.sysinfo {
				bottom: 256px;
				right: 77px;
			}
		</style>
	</head>
	<body>
		<div class="sysinfo">
			<!-- 这里随便写点什么，自己看的 -->
			<h2>// 服务器信息</h2>
			<p>主板型号：<?php echo $sysinfo['brd']; ?></p>
			<p>CPU型号：<?php echo $sysinfo['cpu']; ?></p>
			<p>内存型号：<?php echo $sysinfo['mem']; ?></p>
			<!-- 下面这两个会动态更新，span 的 id 不要修改 -->
			<p>系统温度：<span id="temperature">Loading</span></p>
			<p>内存使用：<span id="memused">Loading</span> 已用 / <span id="memtotal">Loading</span> 总共</p>
		</div>
		<svg id="cg1" width="256" height="200" style="margin-top: 16px;"></svg>
		<svg id="cg2" width="256" height="200" style="margin-top: 16px;"></svg>
		<script src="js/monitor.js"></script>
	</body>
</html>
