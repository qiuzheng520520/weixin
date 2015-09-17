<?php

//$url = 'http://shouji.51240.com/1234567890__shouji/';
//$url = 'http://www.cpooo.com/company/postcode.php';
//$url = "http://qq.ip138.com/weather/guangdong/ShenZhen.html";

$api = 'http://api.qingyunke.com/api.php?key=free&appid=0&msg=';


function qz_open_url($url)
    {
        
    $fh = fopen($url, 'r');
    if($fh){
       while(!feof($fh)) {
         //  echo fgets($fh);
          $result .=fgets($fh);
      }
        //echo $result;
        return $result;
    }
    }

function qz_request_post($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }
        
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        
        return $data;
    }

 function qz_post(){
        $url = 'http://shouji.51240.com/1234567890__shouji/';
     /* $post_data['appid']       = '10';
        $post_data['appkey']      = 'cmbohpffXVR03nIpkkQXaAA1Vf5nO4nQ';
        $post_data['member_name'] = 'zsjs123';
        $post_data['password']    = '123456';
        $post_data['email']    = 'zsjs123@126.com';*/
     	$post_data['t_j_n_r_value']       = '1234567890';
        $o = "";
        foreach ( $post_data as $k => $v ) 
        { 
            $o.= "$k=" . urlencode( $v ). "&" ;
        }
        $post_data = substr($o,0,-1);

        $res = qz_request_post($url, $post_data);       
        print_r($res);

    }

//天气
function qz_weather($re)
{
    /*$url = "http://qq.ip138.com/weather/guangdong/ShenZhen.html";
    $result = qz_open_url($url);
    $newstr = strip_tags($result);
    $result = substr($newstr,90,strlen($newstr)-260); 
    $content = $result;*/
    
    $api = 'http://api.qingyunke.com/api.php?key=free&appid=0&msg=';
    $url = sprintf("%s天气%s",$api,$re);
    $result = qz_open_url($url);
    
    $obj = json_decode($result);
    $result = $obj->{'content'};
    
    $content = str_replace("{br}","\n",$result);
    return $content;
}

//公交
function qz_bus($re)
{
           		$url = "http://m.8684.cn/shenzhen_x_1df76dff";
           		$result = qz_open_url($url);
           
            	$regex4='/text_normal_small\">(.*?)auto973adid1070/iU';  
 				preg_match_all($regex4, $result, $matches);
				$re1 = "{$matches[1][0]}";
				$newstr1 = strip_tags($re1);
           		//echo $newstr."<br>";
           
           		$regex4='/lineConent\">(.*?)lineConent/iU';  
 				preg_match_all($regex4, $result, $matches);
				$re2 = "{$matches[1][0]}";
				$newstr2 = strip_tags($re2);
          		 //echo $newstr."<br>";
           
           		$content = $re[1]." 公交 ".$re[2]."路\n".$newstr1."\n".$newstr2;
    
    return $content;
}

//笑话
function qz_joken($re)
{
	$api = 'http://api.qingyunke.com/api.php?key=free&appid=0&msg=';
    $url = sprintf("%s笑话%s",$api,$re);
    $result = qz_open_url($url);
    
    $obj = json_decode($result);
    $result = $obj->{'content'};
    
    $content = str_replace("{br}","\n",$result);
    return $content;
}

//新闻
function qz_news($re)
{
	$api = 'http://api.1-blog.com/biz/bizserver/news/list.do';
    $url = $api;
    $result = qz_open_url($url);
    
    $obj = json_decode($result);
    $result = $obj->{'detail'};
    
    for($i=0; $i<$count; $i++)
    {
    	$obj = $result[$i];
		$count .= $obj->{'title'};
		$count .= "<br>";
    }
    return $content;
}

    
?>
