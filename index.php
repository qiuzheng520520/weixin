<?php
/*
    方倍工作室
    http://www.cnblogs.com/txw1958/
    CopyRight 2014 All Rights Reserved
*/

require dirname(__FILE__).'/qz_test.php'; 

//echo "Welcome come to SAE!";

define("TOKEN", "weixin");

$wechatObj = new wechatCallbackapiTest();
if (!isset($_GET['echostr'])) {
    $wechatObj->responseMsg();
}else{
    $wechatObj->valid();
}

class wechatCallbackapiTest
{
    //验证签名
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);
        if($tmpStr == $signature){
            echo $echoStr;
            exit;
        }
    }

    //响应消息
    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        if (!empty($postStr)){
            $this->logger("R ".$postStr);
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);
             
            //消息类型分离
            switch ($RX_TYPE)
            {
                case "event":
                    $result = $this->receiveEvent($postObj);
                    break;
                case "text":
                    $result = $this->receiveText($postObj);
                    break;
                case "image":
                    $result = $this->receiveImage($postObj);
                    break;
                case "location":
                    $result = $this->receiveLocation($postObj);
                    break;
                case "voice":
                    $result = $this->receiveVoice($postObj);
                    break;
                case "video":
                    $result = $this->receiveVideo($postObj);
                    break;
                case "link":
                    $result = $this->receiveLink($postObj);
                    break;
                default:
                    $result = "unknown msg type: ".$RX_TYPE;
                    break;
            }
            $this->logger("T ".$result);
            echo $result;
        }else {
            echo "";
            exit;
        }
    }

    //接收事件消息
    private function receiveEvent($object)
    {
        $content = "";
        switch ($object->Event)
        {
            case "subscribe":
                $content = "欢迎关注Q正的公众号 ";
                $content .= (!empty($object->EventKey))?("\n来自二维码场景 ".str_replace("qrscene_","",$object->EventKey)):"";
                break;
            case "unsubscribe":
                $content = "取消关注";
                break;
            case "SCAN":
                $content = "扫描场景 ".$object->EventKey;
                break;
            case "CLICK":
                switch ($object->EventKey)
                {
                    case "COMPANY":
						$content = array();
                        $content[] = array("Title"=>"多图文1标题", "Description"=>"", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
                        break;
                    default:
                        $content = "点击菜单：".$object->EventKey;
                        break;
                }
                break;
            case "LOCATION":
                $content = "上传位置：纬度 ".$object->Latitude.";经度 ".$object->Longitude;
                break;
            case "VIEW":
                $content = "跳转链接 ".$object->EventKey;
                break;
            case "MASSSENDJOBFINISH":
                $content = "消息ID：".$object->MsgID."，结果：".$object->Status."，粉丝数：".$object->TotalCount."，过滤：".$object->FilterCount."，发送成功：".$object->SentCount."，发送失败：".$object->ErrorCount;
                break;
            default:
                $content = "receive a new event: ".$object->Event;
                break;
        }
        if(is_array($content)){
            if (isset($content[0])){
                $result = $this->transmitNews($object, $content);
            }else if (isset($content['MusicUrl'])){
                $result = $this->transmitMusic($object, $content);
            }
        }else{
            $result = $this->transmitText($object, $content);
        }

        return $result;
    }
    /*  
    private function dothings($keyword)
    {
        switch($keyword)
        {
                case "2":
            case "单图文":
                $content = array();
            $content[] = array("Title"=>"baidu",  "Description"=>"百度", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://www.baidu.com");
                break;
            case "3":
            case "多图文":
                $content = array();
                $content[] = array("Title"=>"多图文1标题", "Description"=>"", "PicUrl"=>"http://discuz.comli.com/weixin/weather/icon/cartoon.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
                $content[] = array("Title"=>"多图文2标题", "Description"=>"", "PicUrl"=>"http://d.hiphotos.bdimg.com/wisegame/pic/item/f3529822720e0cf3ac9f1ada0846f21fbe09aaa3.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
                $content[] = array("Title"=>"多图文3标题", "Description"=>"", "PicUrl"=>"http://g.hiphotos.bdimg.com/wisegame/pic/item/18cb0a46f21fbe090d338acc6a600c338644adfd.jpg", "Url" =>"http://m.cnblogs.com/?u=txw1958");
                break;
            case "4":
            case "音乐":
                $content = array();
                $content = array("Title"=>"小苹果", "Description"=>"歌手：筷子兄弟", "MusicUrl"=>"http://php-qiuzheng.coding.io/image/xpg.mp3", "HQMusicUrl"=>"http://php-qiuzheng.coding.io/image/xpg.mp3");
                break;
            default:
                $content = "命令:\n"."1.文本\n"."2.单图文\n"."3.多图文\n"."4.音乐\n"."5.天气\n"."6.IP\n";
                break;
        }
    }*/
    
    private function do_things($keyword,$sum,$re)
    {
       switch($keyword)
        {
            case "1":
            case "天气":
                if($sum == 1)
                {
                    $content = "天气命令方法：\n <1+城市> 或 <天气+城市>";
                    break;
                }
           		$content = qz_weather($re);
                break;
           case "2":
           case "公交":
          		 if($sum < 3)
                {
                    $content = "公交命令方法：\n <2+城市+线路名> 或 <公交+城市+线路名>";
                    break;
                }
           		$content = qz_bus($re);
           //$content = $re;
           		break;
            case "3":
            case "火车票":
           /*  if($sum == 1)
                {
                    $content = "火车票命令方法：\n <3+车次> 或 <火车票+车次>";
                    break;
                }*/
           		$content = "功能尚未实现，敬请期待";
           		break;
            case "4":
            case "快递":
           /*  if($sum == 1)
                {
                    $content = "快递命令方法：\n <4+单号> 或 <快递+单号>";
                    break;
                }*/
           		$content = "功能尚未实现，敬请期待";
           		break;
           	case "5":
            case "手机号":
           /*if($sum == 1)
                {
                    $content = "天气命令方法：\n <5+号码> 或 <手机号+号码>";
                    break;
                }*/
           		$content = "功能尚未实现，敬请期待";
           		break;
           	case "6":
            case "身份证号":
           /* if($sum == 1)
                {
                    $content = "身份证号命令方法：\n <6+号码> 或 <身份证号+号码>";
                    break;
                }*/
           		$content = "功能尚未实现，敬请期待";
           		break;
          	 case "7":
            case "电视节目":
           /* if($sum == 1)
                {
                    $content = "电视节目命令方法：\n <7+电视台> 或 <电视节目+电视台>";
                    break;
                }*/
           		$content = "功能尚未实现，敬请期待";
           		break;
           case "8":
            case "翻译":
           /* if($sum == 1)
                {
                    $content = "翻译命令方法：\n <8+单词> 或 <翻译+单词>";
                    break;
                }*/
           		$content = "功能尚未实现，敬请期待";
           		break;
           case "9":
            case "笑话":
           /*  if($sum == 1)
                {
                    $content = "笑话命令方法：\n <4+城市> 或 <天气+城市>";
                    break;
                }*/
          		$content = qz_joken($re);
           		break;
           case "10":
            case "听歌":
           /*  if($sum == 1)
                {
                    $content = "听歌命令方法：\n <4+歌名> 或 <听歌+歌名>";
                    break;
                }*/
           		$content = "功能尚未实现，敬请期待";
           		break;
           case "11":
            case "机器人":
           /*if($sum == 1)
                {
                    $content = "机器人命令方法：\n <4+问题> 或 <机器人+问题>";
                    break;
                }*/
           		$content = "功能尚未实现，敬请期待";
           		break;
           case "12":
            case "食物营养":
           /* if($sum == 1)
                {
                    $content = "食物营养命令方法：\n <4+食物名> 或 <食物营养+食物名>";
                    break;
                }*/
           		$content = "功能尚未实现，敬请期待";
           		break;
            case "14":
            case "新闻":
           /* if($sum == 1)
                {
                    $content = "食物营养命令方法：\n <4+食物名> 或 <食物营养+食物名>";
                    break;
                }*/
           		$content = qz_news($re);
           		break;
            case "100":
            case "IP":
            	$url = "http://1111.ip138.com/ic.asp";
            	$result = qz_open_url($url);
            	$newstr = strip_tags($result);
            	$result = substr($newstr,22,strlen($newstr)); 
           		$content = $result;
            //$content = "haha";
            	break;
            default:
                $content = "请输入命令查询:\n"."1.天气\n"."2.公交\n"."3.火车票\n"."4.快递\n"."5.手机号\n"."6.身份证号\n"."7.电视节目\n".
                    "8.翻译\n"."9.笑话\n"."10.谜语\n"."11.听歌\n"."12.机器人\n"."13.食物营养\n"."14.新闻\n"."15.社工";
                break;
        }
        
        return $content;
    
    }

    //接收文本消息
    private function receiveText($object)
    {
        $string = trim($object->Content);
        $re = explode('+',$string);
    	$sum = count($re);
        
        $keyword = $re[0];
    
         if (strstr($keyword, "您好") || strstr($keyword, "你好") || strstr($keyword, "在吗")){
            $result = $this->transmitService($object);
            return result;
        }
        
        $content = $this->do_things($keyword,$sum,$re[1]);
        
            if(is_array($content)){
                if (isset($content[0]['PicUrl'])){
                    $result = $this->transmitNews($object, $content);
                }else if (isset($content['MusicUrl'])){
                    $result = $this->transmitMusic($object, $content);
                }
            }else{
                $result = $this->transmitText($object, $content);
            }
        

        return $result;
    }


    //接收图片消息
    private function receiveImage($object)
    {
        $content = array("MediaId"=>$object->MediaId);
        $result = $this->transmitImage($object, $content);
        return $result;
    }

    //接收位置消息
    private function receiveLocation($object)
    {
        $content = "你发送的是位置，纬度为：".$object->Location_X."；经度为：".$object->Location_Y."；缩放级别为：".$object->Scale."；位置为：".$object->Label;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //接收语音消息
    private function receiveVoice($object)
    {
        if (isset($object->Recognition) && !empty($object->Recognition)){
            $content = "你刚才说的是：".$object->Recognition;
            $result = $this->transmitText($object, $content);
        }else{
            $content = array("MediaId"=>$object->MediaId);
            $result = $this->transmitVoice($object, $content);
        }

        return $result;
    }

    //接收视频消息
    private function receiveVideo($object)
    {
        $content = array("MediaId"=>$object->MediaId, "ThumbMediaId"=>$object->ThumbMediaId, "Title"=>"", "Description"=>"");
        $result = $this->transmitVideo($object, $content);
        return $result;
    }

    //接收链接消息
    private function receiveLink($object)
    {
        $content = "你发送的是链接，标题为：".$object->Title."；内容为：".$object->Description."；链接地址为：".$object->Url;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    //回复文本消息
    private function transmitText($object, $content)
    {
        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

    //回复图片消息
    private function transmitImage($object, $imageArray)
    {
        $itemTpl = "<Image>
    <MediaId><![CDATA[%s]]></MediaId>
</Image>";

        $item_str = sprintf($itemTpl, $imageArray['MediaId']);

        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[image]]></MsgType>
$item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复语音消息
    private function transmitVoice($object, $voiceArray)
    {
        $itemTpl = "<Voice>
    <MediaId><![CDATA[%s]]></MediaId>
</Voice>";

        $item_str = sprintf($itemTpl, $voiceArray['MediaId']);

        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[voice]]></MsgType>
$item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复视频消息
    private function transmitVideo($object, $videoArray)
    {
        $itemTpl = "<Video>
    <MediaId><![CDATA[%s]]></MediaId>
    <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
</Video>";

        $item_str = sprintf($itemTpl, $videoArray['MediaId'], $videoArray['ThumbMediaId'], $videoArray['Title'], $videoArray['Description']);

        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[video]]></MsgType>
$item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复图文消息
    private function transmitNews($object, $newsArray)
    {
        if(!is_array($newsArray)){
            return;
        }
        $itemTpl = "    <item>
        <Title><![CDATA[%s]]></Title>
        <Description><![CDATA[%s]]></Description>
        <PicUrl><![CDATA[%s]]></PicUrl>
        <Url><![CDATA[%s]]></Url>
    </item>
";
        $item_str = "";
        foreach ($newsArray as $item){
            $item_str .= sprintf($itemTpl, $item['Title'], $item['Description'], $item['PicUrl'], $item['Url']);
        }
        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>
<ArticleCount>%s</ArticleCount>
<Articles>
$item_str</Articles>
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time(), count($newsArray));
        return $result;
    }

    //回复音乐消息
    private function transmitMusic($object, $musicArray)
    {
        $itemTpl = "<Music>
    <Title><![CDATA[%s]]></Title>
    <Description><![CDATA[%s]]></Description>
    <MusicUrl><![CDATA[%s]]></MusicUrl>
    <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
</Music>";

        $item_str = sprintf($itemTpl, $musicArray['Title'], $musicArray['Description'], $musicArray['MusicUrl'], $musicArray['HQMusicUrl']);

        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[music]]></MsgType>
$item_str
</xml>";

        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }

    //回复多客服消息
    private function transmitService($object)
    {
        $xmlTpl = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[transfer_customer_service]]></MsgType>
</xml>";
        $result = sprintf($xmlTpl, $object->FromUserName, $object->ToUserName, time());
        return $result;
    }


    //日志记录
    private function logger($log_content)
    {
        if(isset($_SERVER['HTTP_APPNAME'])){   //SAE
            sae_set_display_errors(false);
            sae_debug($log_content);
            sae_set_display_errors(true);
        }else if($_SERVER['REMOTE_ADDR'] != "127.0.0.1"){ //LOCAL
            $max_size = 10000;
            $log_filename = "log.xml";
            if(file_exists($log_filename) and (abs(filesize($log_filename)) > $max_size)){unlink($log_filename);}
            file_put_contents($log_filename, date('H:i:s')." ".$log_content."\r\n", FILE_APPEND);
        }
    }
}
?>
