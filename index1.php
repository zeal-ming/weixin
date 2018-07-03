<?php
/**
 * wechat php test
 */

//define your token
define("TOKEN", "test");
define('ACCESS_TOKEN','11_CxBuNRoLEjNDWNNl4VSxa5LZFsmiDfx4AfxS6nU9M6HLW3uabh2eeR94-WaWOQcyV_sI4CjoYIa-YKB0W8rTFKkgyVc_4t7iGNQtflWpfJAj1KGAPI4HM-7e7Q8XIVoJ83QX5d8yLhWPOhQsHBTaAHAYSK');
$wechatObj = new wechatCallbackapiTest();
//验证签名
//$wechatObj->valid();
//创建菜单
$wechatObj->createMenu();
//消息响应
//$wechatObj->responseMsg();
//调用第三方接口——天气查询(https://www.sojson.com/open/api/weather/json.shtml?city=北京)
class wechatCallbackapiTest
{
    //在微信后台配置url时，会自动调用url进行校验
    function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    //签名检验
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr,SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    //返回信息给微信服务器
    function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        //extract post data
        if (!empty($postStr)){

            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $keyword = trim($postObj->Content);
            $msgType = $postObj->MsgType;

            //关键词回复(对话框中输入的信息回复)
            if(!empty( $keyword ))
            {
                if($keyword == '图文'){
                    $this->sendNewMsg($postObj);
                    exit(1);
                }elseif($keyword == '模板'){
                   $this->sendTpl($postObj);
                    exit(1);
                }else{
                    //先调用天气接口，失败则直接使用关键词回复
                    $url = "https://www.sojson.com/open/api/weather/json.shtml?city=".$keyword;
                    $res = $this->requestHttp($url);
                    $arr = json_decode($res);
                    if($arr->status == 200){
                        $data = $arr->data;
                        $str = "湿度{$data->shidu},空气质量:{$data->quality},当前温度：{$data->wendu}";
                        $this->sendText($postObj,$str);
                    }else{
                        $contentStr = "<a href='http://baidu.com'>$keyword</a>";
                        $this->sendText($postObj,$contentStr);
                    }
                    exit(1);
                }
            }else{
                echo "Input something...";
            }

            //事件回复
            switch ($msgType) {
                case 'location':
                    $contentStr = 'location_x:'.$postObj->Location_X;
                    $this->sendText($postObj,$contentStr);
                    break;
                case 'image':
                    $this->sendPic($postObj);
                    break;
                case 'event':
                    $event = $postObj->Event;
                    $eventKey = $postObj->EventKey;
                    if($event == 'SCAN' && $eventKey == '123'){
                        $contentStr = "扫码成功";
                        $this->sendText($postObj,$contentStr);
                    }
                    if($event == 'TEMPLATESENDJOBFINISH'){
                        file_put_contents('data.json','status:'.$postObj->Status);
                        exit('success');
                    }
                    break;
            }

            //菜单事件调用
            $this->menuClick($postObj);
        }else {
            echo "error";
            exit;
        }
        exit("success");
    }

    //返回文本信息
    private function sendText($postObj,$contentStr='暂无该消息的回复'){
        $textTpl = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Content><![CDATA[%s]]></Content>
							<FuncFlag>0</FuncFlag>
							</xml>";
        $msgType = "text";
        $resultStr = sprintf($textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), $msgType, $contentStr);
        file_put_contents('data.json',$resultStr);
        echo $resultStr;
    }
    //返回图文信息
    private function sendNewMsg($postObj)
        {
                $toUser = $postObj->FromUserName;
                $fromUser = $postObj->ToUserName;
                $arr = array(
                    array(
                        'title' => 'imooc',
                        'description' => "imooc is very cool",
                        'picUrl' => 'http://www.imooc.com/static/img/common/logo.png',
                        'url' => 'http://www.imooc.com',
                    ),
                    array(
                        'title' => 'hao123',
                        'description' => "hao123 is very cool",
                        'picUrl' => 'https://www.baidu.com/img/bdlogo.png',
                        'url' => 'http://www.hao123.com',
                    ),
                    array(
                        'title' => 'qq',
                        'description' => "qq is very cool",
                        'picUrl' => 'http://www.imooc.com/static/img/common/logo.png',
                        'url' => 'http://www.qq.com',
                    ),
                );
                $template = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<ArticleCount>" . count($arr) . "</ArticleCount>
						<Articles>";
                foreach ($arr as $k => $v) {
                    $template .= "<item>
							<Title><![CDATA[" . $v['title'] . "]]></Title>
							<Description><![CDATA[" . $v['description'] . "]]></Description>
							<PicUrl><![CDATA[" . $v['picUrl'] . "]]></PicUrl>
							<Url><![CDATA[" . $v['url'] . "]]></Url>
							</item>";
                }
                $template .= "</Articles>
						</xml> ";
                $str = sprintf($template, $toUser, $fromUser, time(), 'news');
                echo $str;
        }
    //返回图片信息
    private function sendPic($postObj){
        $imgTpl = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Image><MediaId><![CDATA[%s]]></MediaId></Image>
                        </xml>";
        $msgType = "image";
        $contentStr = "xbo1bSQ1Yq78jT7hyWgNd3tuqMF_1e-YbEybi9sAkTQEEdm_R0MJdzWbF-AmY2mR";
        $resultStr = sprintf($imgTpl, $postObj->FromUserName, $postObj->ToUserName, time(), $msgType, $contentStr);
        echo $resultStr;
    }
    //发送模板信息
    private function sendTpl($postObj){

        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".ACCESS_TOKEN;
        $post_data = '{
                       "touser":"oRPXe1FLe_lvWJSYdcb552ysWKKI",
                       "template_id":"Z71-3OV6j2-kovkiJSgkjyad48wEFyJNC9_jArqk8AY",
                       "url":"http://baidu.com",
                       "data":{
                               "first": {
                                   "value":"张铭",
                                   "color":"#173177"
                               },
                               "orderMoneySum":{
                                   "value":"10000",
                                   "color":"#173177"
                               },
                               "orderProductName": {
                                   "value":"大保健",
                                   "color":"#173177"
                               },
            
                               "remark":{
                                   "value":"欢迎再次购买！",
                                   "color":"#173177"
                               }
                       }
                   }';
        $this->requestHttp($url, 'post',$post_data);
    }
    //二维码接口
    private function getQrcode(){
        //获取ticket
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".ACCESS_TOKEN;
        $post_data = '{"expire_seconds": 604800, "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": 123}}}';
        $res = $this->requestHttp($url,$post_data);
        $data = json_decode($res);
        //获取二维码
        $url = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".urlencode($data['ticket']);
        file_put_contents('data.json',$url);
        $url = $this->requestHttp($url);
        echo "<img src='".$url."'/>";
        exit(1);
    }

    //菜单创建接口
    public function createMenu(){
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".ACCESS_TOKEN;
        $post_data = '{
             "button":[
             {
                  "type":"click",
                  "name":"今日歌曲1",
                  "key":"V1001_TODAY_MUSIC"
              },
               {
                  "type":"click",
                  "name":"天气查询",
                  "key":"WEATHER_SEARCH"
              },
              {
                   "name":"菜单",
                   "sub_button":[
                   {
                       "type":"view",
                       "name":"搜索",
                       "url":"http://www.soso.com/"
                    },
                    {
                       "type":"click",
                       "name":"赞一下我们",
                       "key":"V1001_GOOD"
                    }]
               }]
         }';
//        $post_data = '
//        {
//            "button": [
//                {
//                    "name": "扫码",
//                    "sub_button": [
//                        {
//                            "type": "scancode_waitmsg",
//                            "name": "扫码带提示",
//                            "key": "rselfmenu_0_0",
//                            "sub_button": [ ]
//                        },
//                        {
//                            "type": "scancode_push",
//                            "name": "扫码推事件",
//                            "key": "rselfmenu_0_1",
//                            "sub_button": [ ]
//                        }
//                    ]
//                },
//                {
//                    "name": "发图",
//                    "sub_button": [
//                        {
//                            "type": "pic_sysphoto",
//                            "name": "系统拍照发图",
//                            "key": "rselfmenu_1_0",
//                           "sub_button": [ ]
//                         },
//                        {
//                            "type": "pic_photo_or_album",
//                            "name": "拍照或者相册发图",
//                            "key": "rselfmenu_1_1",
//                            "sub_button": [ ]
//                        },
//                        {
//                            "type": "pic_weixin",
//                            "name": "微信相册发图",
//                            "key": "rselfmenu_1_2",
//                            "sub_button": [ ]
//                        }
//                    ]
//                }
//            ]
//        }';
        $this->requestHttp($url,$method='post',$post_data);
    }
    //菜单查询接口
    public function getMenu(){
        $url = "https://api.weixin.qq.com/cgi-bin/menu/get?access_token=".ACCESS_TOKEN;
        $res = $this->requestHttp($url);
        file_put_contents('menu.php',$res);         //保存查询到的菜单到文件
        exit('success');
    }

    //菜单事件推送
    public function menuClick($postObj){

        $event = $postObj->Event;
        $eventKey = $postObj->EventKey;
//        file_put_contents('data.json','event='.$event.' eventKey='.$eventKey);
        if($event == 'CLICK' && $eventKey == 'V1001_TODAY_MUSIC'){
            $this->sendText($postObj,'欢迎点歌');
        }elseif($event == 'CLICK' && $eventKey == 'V1001_GOOD'){
            $this->sendText($postObj,'谢谢点赞');
        }elseif($event == 'CLICK' && $eventKey == 'WEATHER_SEARCH'){
            $this->sendText($postObj,'请输入您要查询天气的城市');
        }

    }

    //向微信服务器发起请求
    private function requestHttp($url='',$method='get',$post_data=''){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        if($method == 'post'){
            curl_setopt($ch, CURLOPT_POST,1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        }

        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
}


//jssdk
//require_once './index.php';