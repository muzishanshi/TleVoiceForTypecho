<?php
include '../../../config.inc.php';
require __DIR__ . '/aip-speech/AipSpeech.php';
date_default_timezone_set('Asia/Shanghai');
$db = Typecho_Db::get();

$action = @isset($_POST['action']) ? addslashes(trim($_POST['action'])) : '';
if($action=='addVoice'){
	$cid = @isset($_POST['cid']) ? addslashes(trim($_POST['cid'])) : '';
	$config=@unserialize(ltrim(file_get_contents(dirname(__FILE__).'/config.php'),'<?php die; ?>'));
	$client = new AipSpeech($config['app_id'], $config['api_key'], $config['secret_key']);
	$query= $db->select()->from('table.fields')->where('cid = ?', $cid)->where('name = ?', 'gif'); 
	$row = $db->fetchRow($query);
	if($row){
		$str_value=explode('|',$row['str_value']);
		if(count($str_value)>0){
			$words="";
			foreach($str_value as $value){
				$values=explode('`',$value);
				$words.=$values[3].'说'.$values[4].'。';
				$result = $client->synthesis($values[3].'说'.$values[4], 'zh', 1, array(
					'per' => $values[1],
				));
				// 识别正确返回语音二进制 错误则返回json 参照下面错误码
				if(!is_array($result)){
					$filename='voice_'.$cid.'_'.$values[2].'_'.time().'.mp3';
					$filename = iconv("utf-8", "gbk", $filename);
					file_put_contents('aip-speech/upload/voice/'.$filename, $result);
				}
			}
			$result = $client->synthesis($words, 'zh', 1, array(
				'per' => 4,
			));
			// 识别正确返回语音二进制 错误则返回json 参照下面错误码
			if(!is_array($result)){
				$filename='voice_'.$cid.'_all_'.time().'.mp3';
				$filename = iconv("utf-8", "gbk", $filename);
				file_put_contents('aip-speech/upload/voice/'.$filename, $result);
			}
		}
	}
}else if($action=='delVoice'){
	$cid = @isset($_POST['cid']) ? addslashes(trim($_POST['cid'])) : '';
	$attr = glob("./aip-speech/upload/voice/voice_".$cid."_*.mp3");
	foreach($attr as $mp3){
		@unlink($mp3);
	}
}
?>