<?php
require_once "jssdk.php";
$jssdk = new JSSDK("wx9a683d6fac5aa1c6", "a8f7eb424a71130af5c023b6d9a86c12");
$signPackage = $jssdk->GetSignPackage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title></title>
</head>
<body>
  <h1>haha</h1>
  <button onclick="takePhoto()">拍照</button>
  <button onclick="scan()">扫一扫</button>
</body>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
  /*
   * 注意：
   * 1. 所有的JS接口只能在公众号绑定的域名下调用，公众号开发者需要先登录微信公众平台进入“公众号设置”的“功能设置”里填写“JS接口安全域名”。
   * 2. 如果发现在 Android 不能分享自定义内容，请到官网下载最新的包覆盖安装，Android 自定义分享接口需升级至 6.0.2.58 版本及以上。
   * 3. 常见问题及完整 JS-SDK 文档地址：http://mp.weixin.qq.com/wiki/7/aaa137b55fb2e0456bf8dd9148dd613f.html
   *
   * 开发中遇到问题详见文档“附录5-常见错误及解决办法”解决，如仍未能解决可通过以下渠道反馈：
   * 邮箱地址：weixin-open@qq.com
   * 邮件主题：【微信JS-SDK反馈】具体问题
   * 邮件内容说明：用简明的语言描述问题所在，并交代清楚遇到该问题的场景，可附上截屏图片，微信团队会尽快处理你的反馈。
   */
  var action = '';

  function takePhoto() {
      wx.ready(function () {
          //拍照接口
          wx.chooseImage({
              count: 1, // 默认9
              sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
              sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
              success: function (res) {
                  var localIds = res.localIds; // 返回选定照片的本地ID列表，localId可以作为img标签的src属性显示图片
                  // alert(localIds);
              }
          });

      });
  }
  function scan() {
      wx.ready(function () {
          //扫一扫接口
          wx.scanQRCode({
              needResult: 0, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
              scanType: ["qrCode","barCode"], // 可以指定扫二维码还是一维码，默认二者都有
              success: function (res) {
                  var result = res.resultStr; // 当needResult 为 1 时，扫码返回的结果
              }
          });

      });
  }
  wx.config({
    debug: true,
    appId: '<?php echo $signPackage["appId"];?>',
    timestamp: <?php echo $signPackage["timestamp"];?>,
    nonceStr: '<?php echo $signPackage["nonceStr"];?>',
    signature: '<?php echo $signPackage["signature"];?>',
    jsApiList: [
        // 所有要调用的 API 都要加到这个列表中
        'checkJsApi',
        'onMenuShareTimeline',
        'onMenuShareAppMessage',
        'onMenuShareQQ',
        'onMenuShareWeibo',
        'onMenuShareQZone',
        'hideMenuItems',
        'showMenuItems',
        'hideAllNonBaseMenuItem',
        'showAllNonBaseMenuItem',
        'translateVoice',
        'startRecord',
        'stopRecord',
        'onVoiceRecordEnd',
        'playVoice',
        'onVoicePlayEnd',
        'pauseVoice',
        'stopVoice',
        'uploadVoice',
        'downloadVoice',
        'chooseImage',
        'previewImage',
        'uploadImage',
        'downloadImage',
        'getNetworkType',
        'openLocation',
        'getLocation',
        'hideOptionMenu',
        'showOptionMenu',
        'closeWindow',
        'scanQRCode',
        'chooseWXPay',
        'openProductSpecificView',
        'addCard',
        'chooseCard',
        'openCard'
    ]
  });

  wx.ready(function () {

      //分享接口
      wx.onMenuShareTimeline({
          title: '巴西进入八强', // 分享标题
          link: 'http://baidu.com', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
          imgUrl: 'https://www.baidu.com/img/bglogo.png', // 分享图标
          success: function () {
              // 用户点击了分享后执行的回调函数
              alert('已分享给用户');
          }
      });
      wx.onMenuShareAppMessage({
          title: '日本惜败比利时', // 分享标题
          desc: '日本以一球之差惜败', // 分享描述
          link: 'http://baidu.com', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
          imgUrl: 'https://www.baidu.com/img/bglogo.png', // 分享图标
          type: '', // 分享类型,music、video或link，不填默认为link
          dataUrl: '', // 如果type是music或video，则要提供数据链接，默认为空
          success: function () {
              // 用户点击了分享后执行的回调函数
              alert('已分享给朋友');
          }
      });

      //拍照接口
      // wx.chooseImage({
      //         count: 1, // 默认9
      //         sizeType: ['original', 'compressed'], // 可以指定是原图还是压缩图，默认二者都有
      //         sourceType: ['album', 'camera'], // 可以指定来源是相册还是相机，默认二者都有
      //         success: function (res) {
      //             var localIds = res.localIds; // 返回选定照片的本地ID列表，localId可以作为img标签的src属性显示图片
      //             // alert(localIds);
      //         }
      //     });
      //
      // //扫一扫接口
      // wx.scanQRCode({
      //         needResult: 0, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
      //         scanType: ["qrCode","barCode"], // 可以指定扫二维码还是一维码，默认二者都有
      //         success: function (res) {
      //             var result = res.resultStr; // 当needResult 为 1 时，扫码返回的结果
      //         }
      //     });

  });

  wx.error(function () {
      alert('failure');
  });
</script>
</html>
