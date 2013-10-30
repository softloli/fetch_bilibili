<?php
include_once("curl.php");
$curl = new curl();
$link = mysql_connect('localhost','root','h7m5kb49t8');
!$link && die('数据库连接失败！'.mysql_error());
mysql_select_db('bilibili',$link);
mysql_query("set charset utf8");
$start_time = time();
set_time_limit(0);
// 设置你要采集的数目
$num = 547000;
/*=======================多线程测试==========================*/
/*$id =1;
$url = array();
while ($id <= 30) {
	$aurl = "http://api.bilibili.tv/view?type=json&appkey=f3475f94b513da26&id=".$id."&page=1";
	$url=array($aurl);
	$callback=array('myway',array($id));
	$curl->add($url,$callback);
	$id +=1;
}
$curl->go();
function myway($r,$id){
	global $curl;
	$url = "http://www.bilibili.tv/video/av".$id;
	if($r['info']['http_code']==200){
		$str = json_decode($r['content'],true);
		if (isset($str['error'])  && $str['error']=="overspeed") {
			echo $id.'<br>';
			sleep(2);
		}elseif(isset($str['play'])){
			// print_r($str);
			$time = strtotime($str['created_at']);
			mysql_query("INSERT INTO `post` (`aid`,`play`,`review`,`video_review`,`favorites`,`title`,`description`,`posttime`,`website`)values ('$id','$str[play]','$str[review]','$str[video_review]','$str[favorites]','$str[title]','$str[description]','$time','$url')"); 				
		}
	}
	$curl->status(0);
}
exit();*/
/*==========================================================*/

/*$ctx = stream_context_create(array(  
   'http' => array(  
       'timeout' => 5 //设置一个超时时间，单位为秒  
       )  
   )  
);*/
$new_arr = mysql_query('select `aid` from `post` order by `aid` desc limit 1');
$aid = mysql_fetch_assoc($new_arr);
!isset($aid['aid']) && $aid['aid'] = 0;
$id = $aid['aid']+1;
$first = floor($id/30);
// $first = 500;
$limit = ceil($num/30);
for ($i=$first; $i <$limit ; $i++) {
	$idnum = $id - 30*$i; 
	while ($idnum <= 30) {
		$url = "http://api.bilibili.tv/view?type=json&appkey=f3475f94b513da26&id=".$id."&page=1";
		$result=$curl->read($url);
		if($result['info']['http_code']==200){
			$str[$id] = json_decode($result['content'],true);
			$str[$id]['url'] = "http://www.bilibili.tv/video/av".$id;
			if (isset($str[$id]['error']) && $str[$id]['error']=="overspeed") {
				sleep(2);
			}else{
				if ($id>=$num) {
					break;
				}
				$id +=1;
				$idnum += 1;
			}
		}else{
			sleep(5);
		}
	}
	foreach ($str as $key => $value) {
		if(isset($value['play'])){
			$time = strtotime($value['created_at']);
			mysql_query("INSERT INTO `post` (`aid`,`play`,`review`,`video_review`,`favorites`,`tag`,`pic`,`title`,`description`,`posttime`,`website`,`author`,`mid`,`cid`)values ('$key','$value[play]','$value[review]','$value[video_review]','$value[favorites]','$value[tag]','$value[pic]','$value[title]','$value[description]','$time','$value[url]','$value[author]','$value[mid]','$value[cid]')");	
		}
	}
	unset($str);
}
$end_time = time();
$excute_time = ceil(($end_time-$start_time)/60);
echo $excute_time;
if (mysql_affected_rows()>=0) {
	die('success!');
}else{
	die('false!');
}