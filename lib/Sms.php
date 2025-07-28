<?php
/**
 * 短信
 * @author sunkangchina <68103403@qq.com>
 * @license MIT <https://mit-license.org/>
 * @date 2025
 */
namespace modules\sms\lib;
use lib\DataMasker;
class Sms
{
    /**
     * 获取短信驱动
     */
    public static function getDrive()
    {
        $name = get_config('sms_default_drive') ?: 'Default';
        $class = "\modules\sms\lib\\" . $name . "Drive"; 
        return $class;
    }

    /**
     * 获取所有驱动类型
     * @return array 驱动类型数组 [类名 => 显示名称]
     */
    public static function getDriveTypes()
    {
        $types = [];

        // 默认驱动
        $types['Default'] = '默认驱动';

        // 查找lib目录下的所有Drive文件
        $libPath = __DIR__;
        $files = scandir($libPath);

        foreach ($files as $file) {
            if (strpos($file, 'Drive.php') !== false) {
                $className = str_replace('Drive.php', '', $file);
                $filePath = $libPath . '/' . $file;

                // 读取文件内容
                $content = file_get_contents($filePath);

                // 提取第一个注释
                if (preg_match('/\/\*\*\s*\n\s*\*\s*(.+?)\s*\n/s', $content, $matches)) {
                    $comment = trim($matches[1]);
                    $types[$className] = $comment;
                } else {
                    $types[$className] = $className . '驱动';
                }
            }
        }

        return $types;
    }

    /**
     * 发送模板短信
     */
    public static function send($phone,  $code, $replace = [])
    {
        $drive = self::getDrive();
        $template = db_get_one('sms_template', "*", [
            'code' => $code,
            'drive_type' => get_config('sms_default_drive') ?: 'Default',
        ]);
        if (!$template) {
            add_log('短信模板不存在', [
                'code' => $code,
                'phone' => $phone,
            ], 'error');
            return false;
        }
        $template_id = $template['subject'];
        try {
            return $drive::send($phone, $template_id, $template['content'], $replace);
        } catch (\Exception $e) {
            add_log('短信发送失败', [
                'code' => DataMasker::auto($code),
                'phone' => $phone,
                'error' => $e->getMessage(),
            ], 'error');
            return false;
        }
    }
}
