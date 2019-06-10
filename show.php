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
<style>
.video-x {
	position: relative;
	margin: auto;
}
.video-x video {
	background-color: black;
	outline: 1px solid #eee;
}
.canvas-barrage {
	position: absolute;
	width: 100%;
	height: 500;
	pointer-events: none;
	z-index: 1;
}
</style>
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
			$queryMp4= $db->select()->from('table.fields')->where('cid = ?', $cid)->where('name = ?', 'gifmp4'); 
			$rowMp4 = $db->fetchRow($queryMp4);
			if($rowMp4){
				$mp4_value=explode('`',$rowMp4['str_value']);
				$allvoice=dirname(__FILE__)."/aip-speech/upload/voice/voice_".$cid."_all_*.mp3";
				$allvoice=iconv("utf-8", "gbk", $allvoice);
				$allattr = glob($allvoice);
				$allmp3=basename(iconv("gbk", "utf-8", @$allattr[0]));
				?>
				<div style="height:100%;">
				  <div class="layui-row layui-col-space15">
					<div class="layui-col-md6" style="width:<?=$option->screenmode=="horizontal"?"70%":"30%";?>;height:100%;">
					  <div class="layui-card">
						<div class="layui-card-body">
							<div class="video-x">
								<canvas id="canvasBarrage" class="canvas-barrage"></canvas>
								<video id="videoBarrage" style="" src="<?=$mp4_value[0];?>" <?php if($mp4_value[1]=="true"){?>autoplay <?php }?> controls="controls" width="100%" height="550">您的浏览器不支持 video 标签。</video>
							</div>
						</div>
					  </div>
					</div>
					<div class="layui-col-md6" style="width:<?=$option->screenmode=="horizontal"?"30%":"70%";?>;height:100%;">
					  <div class="layui-card">
						<div class="layui-card-body">
							<ul class="layui-timeline" style="overflow:scroll;height:450px;">
								<?php
								for($i=count($str_value)-1;$i>=0;$i--){
									$values=explode('`',$str_value[$i]);
									$filename=dirname(__FILE__)."/aip-speech/upload/voice/voice_".$cid."_".$values[2]."_*.mp3";
									$filename=iconv("utf-8", "gbk", $filename);
									$attr = glob($filename);
									$mp3=basename(iconv("gbk", "utf-8", @$attr[0]));
									$index++;
									?>
									<li class="layui-timeline-item">
										<i class="layui-icon layui-timeline-axis"></i>
										<div class="layui-timeline-content layui-text">
										  <h3 class="layui-timeline-title">
											<audio src="<?=$plug_url;?>/TleVoice/aip-speech/upload/voice/<?=$mp3;?>" controls style="width:100%;">您的浏览器不支持 audio 标签。</audio>
										  </h3>
										  <p>
											<?=$values[3];?>说：<?=$values[4];?>
											<a id="sendBarrage<?=$i;?>" class="sendBarrage" data-text="<?=$values[3];?>说：<?=$values[4];?>" href="javascript:;">发送</a>
										  </p>
										</div>
									</li>
								<?php
								}
								?>
								<li>
									<center>
										<form id="barrageForm" method="post" autocomplete="off">
											<p>透明度(0-100)：<input type="range" class="range" name="opacity" value="100" min="0" max="100"> 文字大小(16-32)：<input type="range" class="range" name="fontSize" value="24" min="16" max="32"></p>
											<p>弹幕位置：<input type="radio" id="rangeFull" name="range" checked value="0,1"><label class="ui-radio" for="rangeFull"></label><label for="rangeFull">全部位置</label>
												<input type="radio" id="rangeTop" name="range" value="0,0.3"><label class="ui-radio" for="rangeTop"></label><label for="rangeTop">顶部</label>
												<input type="radio" id="rangeBottom" name="range" value="0.7,1"><label class="ui-radio" for="rangeBottom"></label><label for="rangeBottom">底部</label>
											</p>
											<p class="last">
												<input class="ui-input" id="input" name="value" required><input type="submit" class="ui-button ui-button-primary" value="发送弹幕" disabled>
											颜色：<input type="color" id="color" name="color" value="#ff0000">
												<a id="resetBarrage" href="javascript:;">重置</a>
											</p>
										</form>
									</center>
								</li>
							</ul>
							<span style="height:60px; line-height:60px;">
								<center>Copyright <?=date("Y");?> <a href="https://www.tongleer.com" target="_blank">同乐儿</a></center>
								<audio style="position:absolute;bottom:0;width:100%;" <?php if($mp4_value[1]=="true"){?>autoplay <?php }?>src="<?=$plug_url;?>/TleVoice/aip-speech/upload/voice/<?=$allmp3;?>" controls style="width:100%;">您的浏览器不支持 audio 标签。</audio>
							</span>
						</div>
					  </div>
					</div>
				  </div>
				</div>
				<?php
			}else{
				?>
				<div class="layui-carousel" id="test3">
					<div carousel-item="">
					<?php
					for($i=count($str_value)-1;$i>=0;$i--){
						$values=explode('`',$str_value[$i]);
						$filename=dirname(__FILE__)."/aip-speech/upload/voice/voice_".$cid."_".$values[2]."_*.mp3";
						$filename=iconv("utf-8", "gbk", $filename);
						$attr = glob($filename);
						$mp3=basename(iconv("gbk", "utf-8", @$attr[0]));
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
					?>
					</div>
				</div>
				<?php
			}
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
<script src="https://apps.bdimg.com/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<script src="js/canvasBarrage.js"></script>
<script>
var dataBarrage = [{
	/*
	value: 'speed设为0为非滚动',
	time: 1,
	speed: 0
}, {
	value: 'time控制弹幕时间，单位秒',
	color: 'blue',
	time: 2
}, {
	value: '视频共21秒',
	time: 5
}, {
	value: 'add()方法新增弹幕',
	time: 8
}, {
	value: 'reset()方法重置弹幕',
	time: 12
}, {
	value: '内容不错哦！',
	time: 15,
	color: 'yellow'
	*/
}];

var eleCanvas = document.getElementById('canvasBarrage');
var eleVideo = document.getElementById('videoBarrage');

var demoBarrage = new CanvasBarrage(eleCanvas, eleVideo, {
	data: dataBarrage
});

document.addEventListener("DOMContentLoaded", function() {
	demoBarrage["fontSize"] = 24;

	$(".sendBarrage").each(function(){
		var id=$(this).attr("id");
		$("#"+id).click( function () {
			demoBarrage.add({
				value: $(this).attr("data-text"),
				time: 1
			});
		});
	});
	
	$("#resetBarrage").click(function(){
		demoBarrage.reset();
	});

	$('.range').on('change', function () {
		demoBarrage[this.name] = this.value * 1;
	});
	$('input[name="range"]').on('click', function () {
		demoBarrage['range'] = this.value.split(',');
	});
	var elForm = $('#barrageForm'), elInput = $('#input');
	elForm.on('submit', function (event) {
		event.preventDefault();	
		demoBarrage.add({
			value: $('#input').val(),
			color: $('#color').val(),
			time: eleVideo.currentTime
		});
		
		elInput.val('').trigger('input');
	});
	var elSubmit = elForm.find('input[type="submit"]');
	elInput.on('input', function () {
		if (this.value.trim()) {
			elSubmit.removeAttr('disabled');
		} else {
			elSubmit.attr('disabled', 'disabled');
		}
	});
}, false);
</script>
</body>
</html>