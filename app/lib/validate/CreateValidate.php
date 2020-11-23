<?php

namespace app\lib\validate;

class CreateValidate extends BaseValidate
{
    /**
     * 定义验证规则
     * 格式：'字段名'	=>	['规则1','规则2'...]
     *
     * @var array
     */	
	protected $rule = [
	    'account'=>'require|token',
	    'username'=>'require',
        'password'=>'require',
        'captcha'=>'require|captcha',
    ];

}
