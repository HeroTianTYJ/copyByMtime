<?php
$config = include __DIR__.'/config.php';

function copyByMtime($sourceSrc,$targetSrc='',$mtime=0,$childSrc=''){
	$dir = opendir($sourceSrc.'/'.$childSrc);
	while (!!$file = readdir($dir)){
		if ($file!='.' && $file!='..'){
			$path = $sourceSrc.$childSrc.'/'.$file;
			if (is_dir($path)){
				copyByMtime($sourceSrc,$targetSrc,$mtime,$childSrc.'/'.$file);
			}else{
				if (filemtime($path) >= $mtime){
					$target = $targetSrc.str_replace($sourceSrc,'',dirname($path));
					if (!is_dir($target)) mkdir($target,0777,true);
					copy($path,$target.'/'.basename($path));
				}
			}
		}
	}
	closedir($dir);
}


function save($config){
	$output = '<?php return [';
	foreach ($config as $value){
		$output .= "['".$value[0]."','".$value[1]."'],";
	}
	$output = trim($output,',').'];';
	file_put_contents(__DIR__.'/config.php',$output);
}

function location($url){
	header('Location:'.$url);
	exit;
}

function tip($tip){
	exit('<script type="text/javascript">alert(\''.$tip.'\');history.back();</script>');
}

if (empty($_GET['action'])){
	if ($_POST){
		if (!isset($config[$_POST['id']])) tip('不存在此项目，请检查您是否添加了项目！');
		if (!is_dir($config[$_POST['id']][1])) mkdir($config[$_POST['id']][1],0777);
		copyByMtime($config[$_POST['id']][0],$config[$_POST['id']][1],strtotime($_POST['time'])?strtotime($_POST['time']):time());
		location('?action=');
	}
}elseif ($_GET['action'] == 'add'){
	if ($_POST){
		$config[] = [$_POST['source'],$_POST['target']];
		save($config);
		location('?action=list');
	}
}elseif ($_GET['action'] == 'update'){
	if (!isset($config[$_GET['id']])) tip('不存在此项目！');
	if ($_POST){
		$config[$_GET['id']] = [$_POST['source'],$_POST['target']];
		save($config);
		location('?action=list');
	}
}elseif ($_GET['action'] == 'delete'){
	if (!isset($config[$_GET['id']])) tip('不存在此项目！');
	unset($config[$_GET['id']]);
	save($config);
	location('?action=list');
}
?>
<!doctype html>
<html lang="zh-cn">
<head>
<meta charset="utf-8">
<title>按修改时间复制文件</title>
<?php
if (empty($_GET['action']) || $_GET['action']!='list'){
?>
<script type="text/javascript" src="public/jquery.js"></script>
<link rel="stylesheet" type="text/css" href="public/h-ui/css/H-ui.min.css">
<?php
}
if (isset($_GET['action']) && in_array($_GET['action'],['add','update'])){
?>
<script type="text/javascript" src="public/h-ui/js/H-ui.min.js"></script>
<?php
}
if (empty($_GET['action'])){
?>
<link rel="stylesheet" type="text/css" href="public/EasyUI/themes/default/easyui.css">
<?php
}
?>
<link rel="stylesheet" type="text/css" href="public/Basic.css">
</head>

<body>
<div class="header">
  <h3>按修改时间复制文件</h3>
  
  <ul>
    <li<?php if (empty($_GET['action'])){?> class="current"<?php }?>><a href="?action=">复制文件</a></li>
    <li<?php if (isset($_GET['action']) && $_GET['action']=='list'){?> class="current"<?php }?>><a href="?action=list">项目列表</a></li>
    <li<?php if (isset($_GET['action']) && $_GET['action']=='add'){?> class="current"<?php }?>><a href="?action=add">添加项目</a></li>
    <?php if (isset($_GET['action']) && $_GET['action']=='update'){?><li class="current"><a href="?action=update&id=<?php echo $_GET['id'];?>">修改项目</a></li><?php }?>
  </ul>
</div>
<?php
if (empty($_GET['action'])){
?>
<form method="post" action="" class="form" style="width:570px;">
  <dl>
    <dd>项目路径：<select name="id" class="select"><?php foreach ($config as $key=>$value){?><option value="<?php echo $key;?>"><?php echo $value[0];?> => <?php echo $value[1];?></option><?php }?></select></dd>
    <dd>时间范围：<input type="text" name="time" class="easyui-datetimebox" style="width:180px;height:30px;"></dd>
    <dd class="center"><input type="submit" value="确认复制" class="btn btn-primary radius"></dd>
  </dl>
</form>
<script type="text/javascript" src="public/EasyUI/jquery.easyui.min.js"></script>
<script type="text/javascript" src="public/EasyUI/locale/easyui-lang-zh_CN.js"></script>
<?php
}elseif ($_GET['action'] == 'list'){
	if ($config){
?>
<div class="list" style="width:1114px;">
  <table>
    <tr><th style="width:500px;">源路径</th><th style="width:500px;">目标路径</th><th style="width:80px;">操作</th></tr>
    <?php foreach ($config as $key=>$value){?>
    <tr><td><?php echo $value[0];?></td><td><?php echo $value[1];?></td><td><a href="?action=update&id=<?php echo $key;?>">修改</a>/<a href="?action=delete&id=<?php echo $key;?>" onClick="return confirm('您真的要删除这条数据么？');">删除</a></td></tr>
     <?php }?>
  </table>
</div>
<?php
	}else{
?>
<p class="nothing">暂无项目</p>
<?php
	}
}elseif ($_GET['action'] == 'add'){
?>
<form method="post" action="" class="form">
  <dl>
    <dd>源 路 径：<input type="text" name="source" class="input-text"></dd>
    <dd>目标路径：<input type="text" name="target" class="input-text"></dd>
    <dd class="center"><input type="submit" value="确认添加" class="btn btn-primary radius"></dd>
  </dl>
</form>
<?php
}elseif ($_GET['action'] == 'update'){
?>
<form method="post" action="" class="form">
  <dl>
    <dd>源 路 径：<input type="text" name="source" value="<?php echo $config[$_GET['id']][0];?>" class="input-text"></dd>
    <dd>目标路径：<input type="text" name="target" value="<?php echo $config[$_GET['id']][1];?>" class="input-text"></dd>
    <dd class="center"><input type="submit" value="确认修改" class="btn btn-primary radius"></dd>
  </dl>
</form>
<?php
}
?>
</body>
</html>
