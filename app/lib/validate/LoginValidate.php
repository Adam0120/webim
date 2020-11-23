<?php

namespace app\lib\validate;

class LoginValidate extends BaseValidate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'account'=>'require|token',
        'password'=>'require',
        'captcha'=>'require|captcha',
    ];

}
