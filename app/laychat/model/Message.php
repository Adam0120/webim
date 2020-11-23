<?php
namespace app\laychat\model;

use app\models\BaseModel;

class Message extends BaseModel
{
    public function user(){
        return $this->belongsTo('member','send')
            ->field('id,username,status,avatar,sign');
    }
}
