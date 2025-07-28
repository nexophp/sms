<?php
/**
 * 短信模板
 * @author sunkangchina <68103403@qq.com>
 * @license MIT <https://mit-license.org/>
 * @date 2025
 */
namespace modules\sms\controller;

class TemplateController extends \core\AdminController
{
    /**
     * 模板列表
     * @permission 短信模板.管理 短信模板.查看
     */
    public function actionList()
    {
        $list = db_get_all('sms_template', '*', []);
        $driveTypes = \modules\sms\lib\Sms::getDriveTypes(); 
        foreach ($list as &$item) {
            $item['drive_type_label'] = $driveTypes[$item['drive_type']];
        }
        json_success(['data' => $list]);
    }
    /**
     * 添加模板
     * @permission 短信模板.管理 短信模板.添加
     */
    public function actionAdd()
    {
        $data = $this->post_data;
        // 检查 code 是否唯一
        if (db_get_one('sms_template', 'id', ['code' => $data['code']])) {
            json_error(['msg' => lang('模板代码已存在')]);
        }
        $data['created_at'] = time();
        $data['drive_type'] = $data['drive_type'] ?? get_config('sms_default_drive');
        $id = db_insert('sms_template', $data);
        if ($id) {
            json_success(['msg' => lang('添加成功')]);
        }
        json_error(['msg' => lang('添加失败')]);
    }
    /**
     * 更新模板
     * @permission 短信模板.管理 短信模板.更新
     */
    public function actionUpdate()
    {
        $data = $this->post_data;
        $id = $data['id'];
        // 检查 code 是否唯一（排除自身）
        $existing = db_get_one('sms_template', 'id', [
            'code' => $data['code'], 
            'drive_type' => $data['drive_type'],
            'subject' => $data['subject'],
            'id[!]' => $id]);
        if ($existing) {
            json_error(['msg' => lang('模板代码已存在')]);
        } 
        db_update('sms_template', $data, ['id' => $id]); 
        json_success(['msg' => lang('更新成功')]); 
    }
    /**
     * 删除模板
     * @permission 短信模板.管理 短信模板.删除
     */
    public function actionDelete()
    {
        $id = $this->post_data['id'];
        $res = db_delete('sms_template', ['id' => $id]);
        if ($res) {
            json_success(['msg' => lang('删除成功')]);
        }
        json_error(['msg' => lang('删除失败')]);
    }
}