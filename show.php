<?php
include '../../../config.inc.php';
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>同乐语音</title>
  <meta name="renderer" content="webkit">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <link rel="stylesheet" href="<?=Helper::options()->pluginUrl;?>/TleVoice/layui/css/layui.css"  media="all">
</head>
<body>
<?php
$db = Typecho_Db::get();
$options = Typecho_Widget::widget('Widget_Options');
$option=$options->plugin('TleVoice');
$plug_url = $options->pluginUrl;
$cid = @isset($_GET['cid']) ? addslashes(trim($_GET['cid'])) : '';
if($option->isEnable=='y'){
	$query= $db->select()->from('table.fields')->where('cid = ?', $cid)->where('name = ?', 'gif'); 
	$row = $db->fetchRow($query);
	if($row){
		$str_value=explode('|',$row['str_value']);
		if(count($str_value)>0){
			$index=0;
			?>
			<div class="layui-carousel" id="test3">
				<div carousel-item="">
				<?php
				$queryMp4= $db->select()->from('table.fields')->where('cid = ?', $cid)->where('name = ?', 'gifmp4'); 
				$rowMp4 = $db->fetchRow($queryMp4);
				$mp4_value=explode('`',$rowMp4['str_value']);
				if($rowMp4){
					$allvoice=dirname(__FILE__)."/aip-speech/upload/voice/voice_".$cid."_all_*.mp3";
					$allvoice=iconv("utf-8", "gbk", $allvoice);
					$allattr = glob($allvoice);
					$allmp3=basename(iconv("gbk", "utf-8", $allattr[0]));
					?>
					<div>
						<video style="float:left;" src="<?=$mp4_value[0];?>" <?php if($mp4_value[1]=="true"){?>autoplay <?php }?> controls="controls" width="80%" height="100%">您的浏览器不支持 video 标签。</video>
						<div style="width:20%;float:left;height:550px;">
							<ul class="layui-timeline" style="overflow:scroll;height:500px;">
								<?php
								for($i=count($str_value)-1;$i>=0;$i--){
									$values=explode('`',$str_value[$i]);
									$filename=dirname(__FILE__)."/aip-speech/upload/voice/voice_".$cid."_".$values[2]."_*.mp3";
									$filename=iconv("utf-8", "gbk", $filename);
									$attr = glob($filename);
									$mp3=basename(iconv("gbk", "utf-8", $attr[0]));
									$index++;
									?>
									<li class="layui-timeline-item">
										<i class="layui-icon layui-timeline-axis"></i>
										<div class="layui-timeline-content layui-text">
										  <h3 class="layui-timeline-title">
											<audio src="<?=$plug_url;?>/TleVoice/aip-speech/upload/voice/<?=$mp3;?>" controls style="width:100%;">您的浏览器不支持 audio 标签。</audio>
										  </h3>
										  <p><?=$values[3];?>说：<?=$values[4];?></p>
										</div>
									</li>
								<?php
								}
								?>
							</ul>
							<center style="height:50px; line-height:50px;">Copyright <?=date("Y");?> <a href="https://www.tongleer.com" target="_blank">同乐儿</a></center>
							<audio style="position:absolute;bottom:0;width:100%;" <?php if($mp4_value[1]=="true"){?>autoplay <?php }?>src="<?=$plug_url;?>/TleVoice/aip-speech/upload/voice/<?=$allmp3;?>" controls style="width:100%;">您的浏览器不支持 audio 标签。</audio>
						</div>
					</div>
					<?php
				}else{
					for($i=count($str_value)-1;$i>=0;$i--){
						$values=explode('`',$str_value[$i]);
						$filename=dirname(__FILE__)."/aip-speech/upload/voice/voice_".$cid."_".$values[2]."_*.mp3";
						$filename=iconv("utf-8", "gbk", $filename);
						$attr = glob($filename);
						$mp3=basename(iconv("gbk", "utf-8", $attr[0]));
						$index++;
						?>
						<div style="background:url(<?=$values[0];?>);background-size:100% 100%;">
							<center style="position:absolute;bottom:0;width:100%;height:100px; line-height:100px;font-weight:bold;font-size:20px;color:#fff;text-shadow:2px 2px 0px #000">
								<?=$values[3];?>说：<?=$values[4];?>
							</center>
							<audio style="position:absolute;bottom:0;width:100%;" src="<?=$plug_url;?>/TleVoice/aip-speech/upload/voice/<?=$mp3;?>" controls style="width:100%;">您的浏览器不支持 audio 标签。</audio>
						</div>
						<?php
					}
				}
				?>
				</div>
			</div>
			<?php
		}
	}
}
?>
<script src="<?=Helper::options()->pluginUrl;?>/TleVoice/layui/layui.js" charset="utf-8"></script>
<script>
layui.use(['carousel'], function(){
  var carousel = layui.carousel;
  
  carousel.render({
    elem: '#test3'
    ,width: '100%'
	,autoplay:false
	,full:true
	,indicator:"none"
  });
  
});
</script>
</body>
</html>