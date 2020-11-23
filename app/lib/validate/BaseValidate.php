<?php

namespace app\lib\validate;
use think\Validate;

/**
 * 自定义验证提示和方法类
 */
class BaseValidate extends Validate
{
    /**
     * 是否批量验证
     * @var bool
     */
    protected $batch = false;

    /**
     * 验证失败是否抛出异常
     * @var bool
     */
    protected $failException = false;

    /**
     * 场景需要验证的规则
     * @var array
     */
    protected $only = [];

    /**
     * 场景需要移除的验证规则
     * @var array
     */
    protected $remove = [];

    /**
     * 场景需要追加的验证规则
     * @var array
     */
    protected $append = [];
    /**
     * 默认规则提示
     * @var array
     */
    protected $typeMsg = [
        'accepted' => '您必须接受 :attribute。',
        'after' => ':attribute 必须要晚于 :rule。',
        'alpha' => ':attribute 只能由字母组成。',
        'array' => ':attribute 必须是一个数组。',
        'before' => ':attribute 必须要早于 :rule。',
        'between' => ':attribute 必须介于 :1 - :2 之间。',
        'boolean' => ':attribute 必须为布尔值。',
        'date' => ':attribute 不是一个有效的日期。',
        'different' => ':attribute 和 :rule 必须不同。',
        'email' => ':attribute 不是一个合法的邮箱。',
        'file' => ':attribute 必须是文件。',
        'gt' => ':attribute 必须大于 :rule。',
        'image' => ':attribute 必须是图片。',
        'in' => '已选的属性 :attribute 非法。',
        'integer' => ':attribute 必须是整数。',
        'lt' => ':attribute 必须小于 :rule。',
        'max' => ':attribute 不能大于 :rule。',
        'min' => ':attribute 必须大于等于 :rule。',
        'regex' => ':attribute 格式不正确。',
        'require' => ':attribute 不能为空。',
        'unique' => ':attribute 已经存在。',
        'url' => ':attribute 格式不正确。',
        'must' => ':attribute must',
        'number' => ':attribute 必须为整数',
        'float' => ':attribute 必须为浮点数',
        'mobile' => ':attribute 手机号错误',
        'alphaNum' => ':attribute must be alpha-numeric',
        'alphaDash' => ':attribute must be alpha-numeric, dash, underscore',
        'activeUrl' => ':attribute not a valid domain or ip',
        'chs' => ':attribute must be chinese',
        'chsAlpha' => ':attribute must be chinese or alpha',
        'chsAlphaNum' => ':attribute must be chinese,alpha-numeric',
        'chsDash' => ':attribute must be chinese,alpha-numeric,underscore, dash',
        'ip' => ':attribute 不是一个合法的ip地址',
        'dateFormat' => ':attribute must be dateFormat of :rule',
        'notIn' => ':attribute 不存在 :rule 中',
        'notBetween' => ':attribute 介于 :1 - :2 之间',
        'length' => 'size of :attribute must be :rule',
        'afterWith' => ':attribute cannot be less than :rule',
        'beforeWith' => ':attribute cannot exceed :rule',
        'expire' => ':attribute not within :rule',
        'allowIp' => 'access IP is not allowed',
        'denyIp' => 'access IP denied',
        'confirm' => ':attribute out of accord with :2',
        'egt' => ':attribute must greater than or equal :rule',
        'elt' => ':attribute must less than or equal :rule',
        'eq' => ':attribute must equal :rule',
        'method' => 'invalid Request method',
        'token' => 'invalid token',
        'fileSize' => 'filesize not match',
        'fileExt' => 'extensions to upload is not allowed',
        'fileMime' => 'mimetype to upload is not allowed',
    ];
    /**
     * 验证字段描述
     * @var array
     */
    protected $field = [
        'name' => '姓名',
        'account' => '账号',
        'username' => '用户名',
        'email' => '邮箱',
        'first_name' => '名',
        'last_name' => '姓',
        'password' => '密码',
        'password_confirm' => '确认密码',
        'city' => '城市',
        'country' => '国家',
        'address' => '地址',
        'phone' => '电话',
        'mobile' => '手机',
        'age' => '年龄',
        'sex' => '性别',
        'gender' => '性别',
        'day' => '天',
        'month' => '月',
        'year' => '年',
        'hour' => '时',
        'minute' => '分',
        'second' => '秒',
        'title' => '标题',
        'content' => '内容',
        'description' => '描述',
        'excerpt' => '摘要',
        'date' => '日期',
        'time' => '时间',
        'available' => '可用的',
        'size' => '大小',
        'captcha' => '验证码',
    ];

    /**
     * 验证是否正整数信息
     * @param string $id 字段值
     * @param string $rule  规则
     * @param array  $data  全部数据（数组）
     * @param string $field 字段名
     * @param string $msg 字段描述
     *
     * @return  string
     */
    protected function isPosInt($id, $rule = '', $data = [], $field = '', $msg = '')
    {
        if (is_numeric($id) && is_int($id + 0) && ($id + 0) > 0) {
            return true;
        } else {
            return $rule . ' 必须是正整数';
        }
    }
    /**
     * 验证请求信息
     *
     * @return  string
     */
    public function goCheck(){
        //  获取http参数
        $params = request()->param();
        if(!$this->check($params)){
            return $this->getError();
        }
        return false;
    }
}
