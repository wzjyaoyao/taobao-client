<?php
namespace Hoowu\Taobao;
// Response的Rest输出
class TopClient {
    private static $config = [
        'app_key'=>'',
        'app_secrect'=>'',
        'app_url'=>''
    ];
    public static function setConfig($config) {
        self::$config = $config;
    }
    public static function send($method,$paramArr=[],$sessionkey="") {
        // 检测配置
        if (empty(self::$config['app_key'])
            || empty(self::$config['app_secrect'])
            || empty(self::$config['app_url'])) {
            // 缺少必要配置
            return false;
        }
        $sendParams = array(
            'app_key' => self::$config['app_key'],
            'method' => $method,
            'format' => 'json',
            'v' => '2.0',
            'sign_method' => 'md5',
            'timestamp' => date('Y-m-d H:i:s'),
        );
        if($paramArr) {
            $sendParams = array_merge($sendParams,$paramArr);
        }
        if($sessionkey) {
            $sendParams['session'] = $sessionkey;
        }
        //生成签名
        $sign = self::createSign($sendParams);
        //组织参数
        $strParam = self::createStrParam($sendParams);
        $strParam .= 'sign='.$sign;
        //访问服务
        $url = self::$config['app_url'].'?'.$strParam; //沙箱环境调用地址
        try{
            $result = file_get_contents($url);
            $result = json_decode($result,true);
        }
        catch (\Exception $e) {
            return false;
        }
        return $result;
    }

    private static function createSign($paramArr) {
        $sign = self::$config['app_secrect'];
        ksort($paramArr);
        foreach ($paramArr as $key => $val) {
            if ($key != '' && $val != '') {
                $sign .= $key . $val;
            }
        }
        $sign .= self::$config['app_secrect'];
        $sign = strtoupper(md5($sign));
        return $sign;
    }

    //组参函数
    private static function createStrParam($paramArr) {
        $strParam = '';
        foreach ($paramArr as $key => $val) {
            if ($key != '' && $val != '') {
                $strParam .= $key . '=' . urlencode($val) . '&';
            }
        }
        return $strParam;
    }
}