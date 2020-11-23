<?php
namespace app\laychat\model;

use app\models\BaseModel;

class MyFriend extends BaseModel
{
    //获取好友详细信息
    public function friendList(){
        return $this->belongsTo('member','member_id')
            ->field('id,username,status,sign,avatar');
    }
}
