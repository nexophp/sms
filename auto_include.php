<?php


/**
 * 发送短信
 * @param string $template_code 代码
 * @param string $phone 接收手机号
 * @param array $replace 替换内容
 * @return bool
 */
function send_sms($template_code, $phone, $replace = [])
{
    return \modules\sms\lib\Sms::send($phone, $template_code, $replace);
}

/**
 * 添加模板
 * @param string $code 代码
 * @param string $name 模板名称
 * @param string $title 模板标题
 * @param string $content 模板内容
 */
function add_sms_template($code, $name, $title, $content)
{
    $drive_type = 'Default';
    $template = db_get_one('sms_template', "*", [
        'code' => $code,
        'drive_type' => $drive_type,
    ]);
    if ($template) {
        db_update('sms_template', [
            'name' => $name,
            'code' => $code,
            'subject' => $title,
            'content' => $content,
            'drive_type' => $drive_type,
        ], [
            'id' => $template['id'],
        ]);
    } else {
        db_insert('sms_template', [
            'name' => $name,
            'code' => $code,
            'subject' => $title,
            'content' => $content,
            'drive_type' => $drive_type,

        ]);
    }
}

/**
 * 获取模板
 */
function get_sms_template($drive_type, $subject)
{
    return db_get_one('sms_template', "content", [
        'drive_type' => $drive_type,
        'subject' => $subject,
    ]);
}
/**
 * 短信内容替换
 */
function sms_content_replace($content, $replace)
{
    foreach ($replace as $key => $value) {
        $content = str_replace('{' . $key . '}', $value, $content);
    }
    return $content;
}


add_action('admin.setting.form', function () {
?>
    <div class="mb-4">
        <h6 class="fw-bold mb-3 border-bottom pb-2">
            <i class="bi bi-phone me-2"></i><?= lang('短信服务') ?>
            <button type="button" class="btn btn-sm btn-primary ms-2" @click="showSmsTestDrawer = true">
                <i class="bi bi-check-circle me-1"></i> <?= lang('测试服务') ?>
            </button>
            <button type="button" class="btn btn-sm btn-secondary ms-2" @click="showSmsTemplateDrawer = true">
                <i class="bi bi-file-earmark-text me-1"></i> <?= lang('短信模板') ?>
            </button>
        </h6>

        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('短信驱动') ?>
                </label>
                <select v-model="form.sms_default_drive" class="form-select">
                    <?php
                    $driveTypes = \modules\sms\lib\Sms::getDriveTypes();
                    foreach ($driveTypes as $key => $name) {
                        echo "<option value=\"$key\">" . lang($name) . "</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">
                    <?= lang('短信签名') ?>
                </label>
                <input v-model="form.sms_default_sign" class="form-control" placeholder="">
            </div>

            <!-- 默认驱动配置 -->
            <div class="col-12" v-if="form.sms_default_drive === 'Default'">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">
                            <?= lang('短信API地址') ?>
                        </label>
                        <input v-model="form.sms_default_ip" class="form-control" placeholder="121.196.204.71">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <?= lang('短信用户名') ?>
                        </label>
                        <input v-model="form.sms_default_user" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <?= lang('短信密码') ?>
                        </label>
                        <input v-model="form.sms_default_pwd" class="form-control" placeholder="" type="password">
                    </div>
                </div>
            </div>

            <!-- 阿里云驱动配置 -->
            <div class="col-12" v-if="form.sms_default_drive === 'Aliyun'">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">
                            <?= lang('AccessKey ID') ?>
                        </label>
                        <input v-model="form.aliyun_accesskey_id" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <?= lang('AccessKey Secret') ?>
                        </label>
                        <input v-model="form.aliyun_accesskey_secret" class="form-control" placeholder="" type="password">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <?= lang('Endpoint') ?>
                        </label>
                        <input v-model="form.sms_aliyun_endpoint" class="form-control" placeholder="dysmsapi.aliyuncs.com">
                    </div>
                </div>
            </div>



            <!-- 腾讯云驱动配置 -->
            <div class="col-12" v-if="form.sms_default_drive === 'Tencent'">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">
                            <?= lang('Secret ID') ?>
                        </label>
                        <input v-model="form.tencent_secret_id" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <?= lang('Secret Key') ?>
                        </label>
                        <input v-model="form.tencent_secret_key" class="form-control" placeholder="" type="password">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <?= lang('SDK App ID') ?>
                        </label>
                        <input v-model="form.tencent_sdk_app_id" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">
                            <?= lang('地区') ?>
                        </label>
                        <input v-model="form.tencent_support_area" class="form-control" placeholder="ap-guangzhou">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 测试服务抽屉 -->
    <el-drawer
        title="<?= lang('测试短信服务') ?>"
        :visible.sync="showSmsTestDrawer"
        direction="rtl"
        size="30%">
        <div class="p-3">
            <el-form label-width="120px">
                <el-form-item label="<?= lang('测试手机号') ?>" required>
                    <el-input v-model="smsTestForm.phone" placeholder="<?= lang('请输入测试手机号') ?>"></el-input>
                </el-form-item>
                <el-form-item>
                    <el-button type="primary" @click="sendTestSms()"> <?= lang('发送测试') ?> </el-button>
                </el-form-item>
            </el-form>
        </div>
    </el-drawer>

    <!-- 短信模板抽屉 -->
    <el-drawer
        title="<?= lang('短信模板管理') ?>"
        :visible.sync="showSmsTemplateDrawer"
        direction="rtl"
        size="60%">
        <div class="p-3">
            <el-button type="primary" size="small" @click="showAddSmsTemplate = true"> <?= lang('添加模板') ?> </el-button>
            <el-table :data="smsTemplateList" style="width: 100%" class="mt-3">
                <el-table-column prop="drive_type_label" width="130px" label="<?= lang('驱动类型') ?>"></el-table-column>
                <el-table-column prop="code" show-overflow-tooltip label="<?= lang('代码') ?>"></el-table-column>
                <el-table-column prop="name" label="<?= lang('模板名称') ?>"></el-table-column>
                <el-table-column label="<?= lang('操作') ?>" width="170">
                    <template slot-scope="scope">
                        <el-button size="mini" @click="editSmsTemplate(scope.row)"> <?= lang('编辑') ?> </el-button>
                        <el-button size="mini" type="danger" @click="deleteSmsTemplate(scope.row)"> <?= lang('删除') ?> </el-button>
                    </template>
                </el-table-column>
            </el-table>

            <!-- 添加/编辑模板对话框 -->
            <el-dialog :title="smsTemplateDialogTitle" :visible.sync="showAddSmsTemplate" width="50%" :append-to-body="true" :modal-append-to-body="true">
                <el-form label-width="120px">
                    <el-form-item label="<?= lang('代码') ?>" required>
                        <el-input v-model="smsTemplateForm.code" placeholder="<?= lang('一般为英文') ?>"></el-input>
                    </el-form-item>
                    <el-form-item label="<?= lang('模板名称') ?>" required>
                        <el-input v-model="smsTemplateForm.name"></el-input>
                    </el-form-item>
                    <el-form-item label="<?= lang('驱动类型') ?>" required>
                        <el-select v-model="smsTemplateForm.drive_type" placeholder="<?= lang('请选择驱动类型') ?>">
                            <el-option label="<?= lang('默认驱动') ?>" value="Default"></el-option>
                            <el-option label="<?= lang('阿里云') ?>" value="Aliyun"></el-option>
                            <el-option label="<?= lang('腾讯云') ?>" value="Tencent"></el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="<?= lang('模板ID') ?>">
                        <el-input v-model="smsTemplateForm.subject"></el-input>
                    </el-form-item>
                    <el-form-item label="<?= lang('短信内容') ?>" required>
                        <el-input type="textarea" :rows="6" v-model="smsTemplateForm.content" placeholder="<?= lang('请输入短信内容, 变量{name} {code}') ?>"></el-input>
                    </el-form-item>
                </el-form>
                <span slot="footer">
                    <el-button @click="showAddSmsTemplate = false"> <?= lang('取消') ?> </el-button>
                    <el-button type="primary" @click="saveSmsTemplate()"> <?= lang('保存') ?> </el-button>
                </span>
            </el-dialog>
        </div>
    </el-drawer>
<?php

    global $vue;

    $vue->data('showSmsTestDrawer', false);
    $vue->data('showSmsTemplateDrawer', false);
    $vue->data('smsTestForm', ['phone' => '', 'subject' => '', 'content' => '']);
    $vue->data('smsTemplateList', []);
    $vue->data('showAddSmsTemplate', false);
    $vue->data('smsTemplateDialogTitle', lang('添加模板'));
    $vue->data('smsTemplateForm', ['id' => '', 'code' => '', 'name' => '', 'subject' => '', 'content' => '']);

    $vue->method('editSmsTemplate(row)', "
        this.smsTemplateForm = {
            id:row.id,
            code:row.code,
            name:row.name,
            subject:row.subject,
            content:row.content,
            drive_type:row.drive_type,

        };
        this.smsTemplateDialogTitle = '" . lang('编辑模板') . "';
        this.isEditSmsTemplate = true;
        this.showAddSmsTemplate = true;
    ");

    $vue->method('deleteSmsTemplate(row)', "
        this.\$confirm('" . lang('确认删除该模板？') . "', '" . lang('提示') . "', {type: 'warning'}).then(() => {
            ajax('/sms/template/delete', {id: row.id}, function(res) {
                if (res.code === 0) {
                    _this.\$message.success('" . lang('删除成功') . "');
                    _this.loadSmsTemplates();
                }
            });
        });
    ");

    $vue->method('saveSmsTemplate()', "
        var url = this.isEditSmsTemplate ? '/sms/template/update' : '/sms/template/add';
        ajax(url, this.smsTemplateForm, function(res) {
            " . vue_message() . "
            if (res.code === 0) { 
                _this.showAddSmsTemplate = false;
                _this.loadSmsTemplates();
            }
        });
    ");

    $vue->method('loadSmsTemplates()', "
        ajax('/sms/template/list', {}, function(res) {
            if (res.code === 0) {
                _this.smsTemplateList = res.data;
            }
        });
    ");

    $vue->watch('showSmsTemplateDrawer', "
        handler(val,old_val){  
           this.loadSmsTemplates(); 
        }
    ");

    $vue->method('sendTestSms()', "
            if (!this.smsTestForm.phone) {
                this.\$message.error('" . lang('请填写完整信息') . "');
                return;
            }
            ajax('/sms/site/test', this.smsTestForm, function(res) {
                " . vue_message() . "
                if (res.code === 0) { 
                    _this.showSmsTestDrawer = false;
                }
            });
        ");
    },500);
