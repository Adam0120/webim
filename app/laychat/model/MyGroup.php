<?php
namespace app\laychat\model;

use app\lib\exception\ApiExcption;
use app\models\BaseModel;

class MyGroup extends BaseModel
{
    //获取分组并获取分组中好友信息
    public function friend(){
        return $this->hasMany('my_friend','group_id')
            ->with('friend_list');
    }

    /**
     *  获取好友列表
     * @param  string  $mid 会员id
     * @return array
     * */
    public static function getFriendList($mid){
        $list = self::where('member_id',$mid)
            ->order('sort','asc')
            ->with('friend')
            ->select()->toArray();
        if(!$list) return [];
        $friend = [];
        foreach ($list as $k=>$v){
            $friend[$k]['groupname'] = $v['group_name'];
            $friend[$k]['id'] = $v['id'];
            if(isset($v['friend'])){
                $friend[$k]['list'] = array_column($v['friend'],'friend_list');
            }
        }
        return $friend;
    }

    /**
     *  管理好友列表
     * @param  int  $mid 会员id
     * @param  int  $groupId 好友分组id
     * @return array
     * */
    public static function getMyFriend($mid,$groupId = 0){
        $myGroup = [];
        $friend = [];
        $list = self::where('member_id',$mid)
            ->with('friend')
            ->order('sort','asc')
            ->select()->toArray();

        if(!$list) return [[],[]];
        foreach ($list as $k=>$v){
            $myGroup[$k]['id'] = $v['id'];
            $myGroup[$k]['groupname'] = $v['group_name'];
            $friend[$v['id']] = array_column($v['friend'],'friend_list');
        }
        if($groupId === 0){
            return [$myGroup,array_shift($friend)];
        }
        if(!isset($friend[$groupId])){
            return [$myGroup,[]];
        }
        return [$myGroup,$friend[$groupId]];
    }
}
