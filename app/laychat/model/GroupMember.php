<?php
namespace app\laychat\model;

use app\models\BaseModel;
use think\helper\Arr;

class GroupMember extends BaseModel
{
    public function group(){
        return $this->belongsTo('group','group_id')
            ->field('id,groupname,avatar');
    }
    /**
     *  获取群组列表
     * @param  string  $mid 会员id
     * @return array
     * */
    public static function getGroupList($mid){
        $groupList = self::where('member_id',$mid)
            ->field('group_id')
            ->with('group')
            ->select()
            ->toArray();
        return array_column($groupList, 'group');
    }
}
