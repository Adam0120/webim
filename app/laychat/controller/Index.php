<?php
declare (strict_types = 1);

namespace app\laychat\controller;

use app\laychat\model\Group;
use app\laychat\model\GroupMember;
use app\laychat\model\Member;
use app\laychat\model\MyGroup;
use app\laychat\model\Skin;
use app\lib\controller\MyBaseController;

class Index extends MyBaseController
{
    protected $memberInfo;

    public function initialize()
    {
        parent::initialize();
        $this->memberInfo = session('member_info');
        if(!$this->memberInfo) return $this->redirect('/index/chatUser/login');
    }

    public function index()
    {
        $mine = $this->memberInfo;
        $skin = Skin::where('is_user_upload = 0 and member_id='.session('id'))->find();
        return view('',[
            'uinfo' => $mine,
            'skin' => $skin?:'1.jpg'
        ]);
    }

    public function getList(){

        //个人信息
        $mine = Member::getMemberInfo(session('id'));
        //好友信息
        $friedList = MyGroup::getFriendList(session('id'));
        //群信息
        $groupList = GroupMember::getGroupList(session('id'));

        return $this->jsonLayui('ok',[
            'mine'=>$mine,
            'friend'=>$friedList,
            'group'=>$groupList
        ]);
    }

    //获取组员信息
    public function getMembers()
    {
        $id = input('param.id');

        //群主信息
        //$owner = Group::field('owner_name,owner_id,owner_avatar,owner_sign')->where('id = ' . $id)->find();
        //群成员信息
        $list = GroupMember::field('member_id id,username,useravatar avatar,usersign sign')
            ->where('group_id = ' . $id)->select();

        $return = [
            'code' => 0,
            'msg' => '',
            'data' => [
//                'owner' => [
//                    'username' => $owner['owner_name'],
//                    'id' => $owner['owner_id'],
//                    'owner_id' => $owner['owner_avatar'],
//                    'sign' => $owner['owner_sign']
//                ],

                'list' => $list
            ]
        ];

        return json( $return );
    }

}
