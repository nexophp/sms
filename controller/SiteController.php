<?php
/**
 * 短信
 * @author sunkangchina <68103403@qq.com>
 * @license MIT <https://mit-license.org/>
 * @date 2025
 */
namespace modules\sms\controller;

use \modules\sms\lib\Sms;

class SiteController extends \core\AdminController
{
    /**
     * 发送测试短信
     */
    public function actionTest()
    {
        if (!$this->uid || $this->user_info['tag'] != 'admin') {
            json_error(['msg' => lang('无法测试短信发送功能，如有疑问请联系管理员')]);
        }

        $data = $this->post_data;
        $to = $data['phone'] ?? '';
        $code = $data['code'] ?? 'login';
        $content = ['code'=>123456];

        if (empty($to) || empty($code) ) {
            json_error(['msg' => lang('请填写完整信息')]);
        } 
        try {
            $result = Sms::send($to, $code, $content);
            if ($result) {
                json_success(['msg' => lang('测试短信发送成功')]);
            } else {
                json_error(['msg' => lang('测试短信发送失败')]);
            }
        } catch (\Exception $e) {
            json_error(['msg' => lang('测试短信发送失败') . ': ' . $e->getMessage()]);
        }
    }

    /**
     * 发送模板测试短信
     */
    public function actionCode()
    {
        if (!$this->uid || $this->user_info['tag'] != 'admin') {
            json_error(['msg' => lang('无法测试短信发送功能，如有疑问请联系管理员')]);
        }
        $code = 'login';
        $phone = g('phone');
        if (!$phone) {
            json_error(['msg' => lang('请填写手机号')]);
        }
        add_sms_template('code', "登录", '登录验证', '您的验证码是{code},5分钟有效，如非本人请求请忽略。');
        
        try {
            $result = Sms::sendByTemplate('code', $phone, ['code' => rand(100000, 999999)]);
            if ($result) {
                json_success(['msg' => lang('测试短信发送成功')]);
            } else {
                json_error(['msg' => lang('测试短信发送失败')]);
            }
        } catch (\Exception $e) {
            json_error(['msg' => lang('测试短信发送失败') . ': ' . $e->getMessage()]);
        }
    }
}