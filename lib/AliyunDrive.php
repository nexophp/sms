<?php


/**
 * 阿里云短信
 * https://dysms.console.aliyun.com/overview
 * @author sunkangchina <68103403@qq.com>
 * @license MIT <https://mit-license.org/>
 * @date 2025
 */

namespace modules\sms\lib;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Dysmsapi;
use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\SendSmsRequest;
use AlibabaCloud\Tea\Utils\Utils\RuntimeOptions;
use Darabonba\OpenApi\Models\Config;
use Exception;

class AliyunDrive
{
    public static $client;
    public static $req;
    public static function send($phone, $template_id,  $content, $data = [], $sign_name = null)
    {
        $template_id = trim($template_id);
        $client = self::getClient();
        $par = [
            "phoneNumbers" => $phone,
            "signName" => $sign_name ?: get_config('sms_default_sign'),
            'templateCode' => $template_id,
        ];
        if ($data) {
            $par['templateParam'] = json_encode($data);
        }
        $sendSmsRequest = new SendSmsRequest($par);
        try {
            $res = $client->sendSms($sendSmsRequest, new RuntimeOptions([]));
            if (!$res->body->bizId) {
                $err = $res->body->message ?: '';
                if ($err) {
                    add_log('阿里云短信发送失败', $err, 'error');
                }
                add_log('发送短信失败', [
                    'phone' => $phone, 
                    'msg'=>$err,
                    'sign' => $sign_name,
                ], 'error');
                return false;
            }
            add_log('发送短信成功', [
                'phone' => $phone,
                'content' => '***',
                'sign' => $sign_name,
            ]);
            return true;
        } catch (Exception $error) {
            $err = $error->getMessage();
            add_log('发送短信失败', [
                'phone' => $phone, 
                'msg'=>$err,
                'sign' => $sign_name,
            ], 'error');
        }
    }

    public static function less()
    {
        return false;
    }

    private static function getClient()
    {
        $config = new Config([
            "accessKeyId" => get_config('aliyun_accesskey_id'),
            "accessKeySecret" => get_config('aliyun_accesskey_secret'),
        ]);
        $config->endpoint = get_config('sms_aliyun_endpoint');
        return new Dysmsapi($config);
    }
}
