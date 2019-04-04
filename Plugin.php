<?php
/**
 * TleVoice同乐语音，使用了百度语音api实现，可帮助网站个性化文章阅读，包含图片语音和视频语音功能。
 * @package TleVoice For Typecho
 * @author 二呆
 * @version 1.0.3
 * @link http://www.tongleer.com/
 * @date 2019-04-04
 */
define('TLEVOICE_VERSION', '3');
class TleVoice_Plugin implements Typecho_Plugin_Interface{
    // 激活插件
    public static function activate(){
		if(!is_dir(dirname(__FILE__)."/aip-speech/upload/voice")){
			mkdir (dirname(__FILE__)."/aip-speech/upload/voice", 0777, true );
		}
		Typecho_Plugin::factory('Widget_Archive')->footer = array('TleVoice_Plugin', 'footer');
        return _t('插件已经激活，需先配置同乐语音的信息！');
    }

    // 禁用插件
    public static function deactivate(){
        return _t('插件已被禁用');
    }

    // 插件配置面板
    public static function config(Typecho_Widget_Helper_Form $form){
		//版本检查
		$div=new Typecho_Widget_Helper_Layout();
		$div->html('版本检查：<span id="versionCode"></span>');
		$div->render();
		
		$db = Typecho_Db::get();
		$options = Typecho_Widget::widget('Widget_Options');
		$plug_url = $options->pluginUrl;
		$app_id = @isset($_POST['app_id']) ? addslashes(trim($_POST['app_id'])) : '';
		$api_key = @isset($_POST['api_key']) ? addslashes(trim($_POST['api_key'])) : '';
		$secret_key = @isset($_POST['secret_key']) ? addslashes(trim($_POST['secret_key'])) : '';
		if($app_id!=''&&$api_key!=''&&$secret_key!=''){
			file_put_contents(dirname(__FILE__).'/config.php','<?php die; ?>'.serialize(array(
				'app_id'=>$app_id,
				'api_key'=>$api_key,
				'secret_key'=>$secret_key
			)));
		}
		
		$div = new Typecho_Widget_Helper_Layout();
		$divstr1='
			<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/amazeui/2.7.2/css/amazeui.min.css"/>
			<script src="https://apps.bdimg.com/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/amazeui/2.7.2/js/amazeui.min.js" type="text/javascript"></script>
			<script>
				$.post("'.$plug_url.'/TleVoice/ajax_tovoice.php",{action:"update",version:'.TLEVOICE_VERSION.'},function(data){
					$("#versionCode").html(data);
				});
			</script>
			<h6>使用方法</h6>
			<span><p>第一步：配置插件各项参数；</p></span>
			<span>
				第二步：按格式为文章增加自定义字段；
				<pre>
<h6>添加图片语音时添加一个名为gif的字段</h6>
<font color="blue">第一人gif图片地址`{number}`第一人英文名/序列(不可重复)`第一人中文名`第一人说话内容|第二人gif图片地址`{number}`第二人英文名/序列(不可重复)`第二人中文名`第二人说话内容</font>……以此类推
1、每个对话之间用“<font color="red">|</font>”分割，对话中每个项目用“<font color="red">`</font>”分割。
2、{number}位置填写0、1、3、4，0代表普通女声、1代表普通男声、3代表情感男声、4代表情感女声。
3、注意同一文章中每个英文名不能相同。
				</pre>
				<pre>
<h6>添加视频语音时添加一个名为gifmp4的字段</h6>
<font color="blue">视频地址`{isAutoPlay}</font>
注、视频地址和{isAutoPlay}之间用“<font color="red">`</font>”分割。
				</pre>
			</span>
			<span><p>第三步：在本页文章列表中点击生成语音；</p></span>
			<span>
				第四步：将以下代码放到主题目录下post.php中任意位置，如果网站启用了pjax，一定要放在pjax容器之内。
				<pre>&lt;?=TleVoice_Plugin::output($this);?></pre>
			</span>
			<div class="am-scrollable-horizontal">
			  <table class="am-table am-table-bordered am-table-striped am-text-nowrap">
				<thead>
					<tr>
						<th>文章标题</th>
						<th>操作</th>
					</tr>
				</thead>
				<tbody>
				
		';
		$divstr2='';
		$divstr3='
				</tbody>
			</table>
		</div>
		<script>
			$(".addVoice").each(function(){
				var id=$(this).attr("id");
				$("#"+id).click( function () {
					$.post("'.$plug_url.'/TleVoice/ajax_tovoice.php",{action:"addVoice",cid:$(this).attr("data-cid")},function(data){
						location.href="plugins.php";
					});
				});
			});
			$(".delVoice").each(function(){
				var id=$(this).attr("id");
				$("#"+id).click( function () {
					$.post("'.$plug_url.'/TleVoice/ajax_tovoice.php",{action:"delVoice",cid:$(this).attr("data-cid")},function(data){
						location.href="plugins.php";
					});
				});
			});
		</script>
		';
		$query= $db->select()->from('table.contents')->where('type = ?','post')->where('status = ?','publish')->order('modified',Typecho_Db::SORT_DESC)->page(1,10);
		$result = $db->fetchAll($query);
		foreach($result as $value){
			$attr = glob(dirname(__FILE__)."/aip-speech/upload/voice/voice_".$value['cid']."_*.mp3");
			$isExist=false;
			foreach($attr as $mp3){
				$mp3 = iconv("gbk", "utf-8", $mp3);
				if(strpos($mp3,'voice_'.$value['cid'])!==false){
					$isExist=true;
				}
			}
			if($isExist){
				$divstr2.='
					<tr>
						<td>'.$value['title'].'</td>
						<td>
							<a class="delVoice" id="delVoice'.$value['cid'].'" data-cid="'.$value['cid'].'" href="javascript:;">删除语音</a>
						</td>
					</tr>
				';
			}else{
				$divstr2.='
					<tr>
						<td>'.$value['title'].'</td>
						<td>
							<a class="addVoice" id="addVoice'.$value['cid'].'" data-cid="'.$value['cid'].'" href="javascript:;">生成语音</a>
						</td>
					</tr>
				';
			}
			
		}
		$div->html($divstr1.$divstr2.$divstr3);
		$div->render();
		
		$isEnable = new Typecho_Widget_Helper_Form_Element_Radio('isEnable', array(
            'y'=>_t('启用'),
            'n'=>_t('禁用')
        ), 'n', _t('是否启用同乐语音'), _t("启用后可在对应的文章浏览页面看见个行特效。"));
        $form->addInput($isEnable->addRule('enum', _t(''), array('y', 'n')));
		
		$app_id = new Typecho_Widget_Helper_Form_Element_Text('app_id', NULL, '', _t('appid'), _t('百度语音appid'));
        $form->addInput($app_id->addRule('required', _t('app_id不能为空！')));
		
		$api_key = new Typecho_Widget_Helper_Form_Element_Text('api_key', NULL, '', _t('apikey'), _t('百度语音apikey'));
        $form->addInput($api_key->addRule('required', _t('api_key不能为空！')));
		
		$secret_key = new Typecho_Widget_Helper_Form_Element_Text('secret_key', NULL, '', _t('secretkey'), _t('百度语音secretkey'));
        $form->addInput($secret_key->addRule('required', _t('secret_key不能为空！')));
    }

    // 个人用户配置面板
    public static function personalConfig(Typecho_Widget_Helper_Form $form){
    }

    // 获得插件配置信息
    public static function getConfig(){
        return Typecho_Widget::widget('Widget_Options')->plugin('TleVoice');
    }
	
	public static function footer(){
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/layer/2.3/layer.js"></script>';
    }
	
	/**
     * 输出
     *
     * @access public
     * @return void
     */
    public static function output($obj){
		$db = Typecho_Db::get();
		$queryGif= $db->select()->from('table.fields')->where('cid = ?', $obj->cid)->where('name = ?', 'gif'); 
		$rowGif = $db->fetchRow($queryGif);
		if($rowGif){
			echo '
				<script>
				$(function(){
					var tlevoicelayer = layer.open({
						type: 2
						,title: "同乐语音"
						,id: "tlevoice"
						,area: ["60%", "60%"]
						,shade: 0
						,maxmin: true
						,offset: "auto"
						,content: "'.Helper::options()->pluginUrl.'/TleVoice/show.php?cid='.$obj->cid.'"
						,zIndex: layer.zIndex
						,success: function(layero){
						  layer.setTop(layero);
						}
					});
					layer.full(tlevoicelayer);
				});
				</script>
			';
		}
	}
}