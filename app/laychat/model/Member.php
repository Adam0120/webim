<?php
namespace app\laychat\model;

use app\models\BaseModel;

class Member extends BaseModel
{
    //修改登录状态
    public function getStatusAttr($value){
        $status = [0 =>'offline',1 =>'online'];
        return $status[$value];
    }

    /**
     *  获取个人信息
     * @param  string  $mid 会员id
     * @return array
     * */
    public static function getMemberInfo($mid){
        $info = self::field('id,username,status,sign,avatar')
            ->where('id',$mid)->find()->toArray();
        return $info;
    }
}
