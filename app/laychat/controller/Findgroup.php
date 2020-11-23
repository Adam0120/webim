<?php
declare (strict_types = 1);

namespace app\laychat\controller;


use app\laychat\model\Group;
use app\laychat\model\GroupMember;
use app\laychat\model\Member;
use app\laychat\model\Message;
use app\laychat\model\MyFriend;
use app\laychat\model\MyGroup;
use app\laychat\model\Skin;
use app\lib\controller\MyBaseController;

class Findgroup extends MyBaseController
{

    public function initialize()
    {
        parent::initialize();
        if(!session('member_info')) return alert_error('请先登录~','/index/chatUser/login');
    }
    //显示查询 / 添加 分组的页面
    public function index()
    {
        $groupArr = Group::order('id desc')->limit(4)->select();
        $usersArr = Member::field('id,avatar,username,sign')->where("id != ".session('id'))->order('id desc')->limit(4)->select();

        return view('',[
            'group' => $groupArr,
            'users' => $usersArr
        ]);
    }

    //搜索查询群组 或 用户
    public function search()
    {
        $wq = input('param.search_txt');
        $type = input('param.type');
        if($type == 'group'){
            $find = Group::where("groupname like '%" . $wq . "%'")->select();
        }else{
            $find = Member::field('id,avatar,username,sign')->where("username like '%" . $wq . "%'")->select();
        }
        if( empty($find) ){
            return json( ['code' => -1, 'data' => '', 'msg' => '没有找到您需要的数据呦 ~' ] );
        }
        return json( ['code' => 1, 'data' => $find, 'msg' => 'success' ] );
    }


    //添加好友或群组
    public function addFriendGroup(){
        $data = $this->request->param();
        if ($data['active'] == 'agree'){//同意
            Message::upData($data['msgid'],['msg_status'=>2]);
            if($data['categroup'] == 'friend'){
                //添加好友
                $res = MyFriend::insert([
                    'group_id'=>$data['group'],
                    'member_id'=>$data['uid'],
                    'username'=>$data['username']
                ]);
                if (!$res) return $this->fail('添加好友失败');
            }else{//添加群
                list($remark,$group_id) = explode('|', $data['remark']);
                $mInfo = Member::field('avatar,sign')->where('id='.$data['uid'])->find();
                $res = GroupMember::insert([
                    'group_id'=> $group_id,
                    'member_id'=>$data['uid'],
                    'add_time'=>time(),
                    'type'=> 3,
                    'username'=>$data['username'],
                    'useravatar'=>$mInfo['avatar'],
                    'usersign'=>$mInfo['sign'],
                ]);
                if (!$res) return $this->fail('添加群失败');
            }
        }else{//拒绝
            Message::upData($data['msgid'],['msg_status'=>3]);
        }
        return $this->jsonLayui();
    }


    //添加群组
    public function addGroup()
    {
    	if( empty(session('id')) ){
    		$this->redirect( url('index/index') );
    	}
    	if( request()->isPost() ){
    		$param = input('post.');
    		$ids = $param['ids'];
    		unset( $param['ids'] );
    		if( empty($param['groupname']) ){
    			return json( ['code' => -1, 'data' => '', 'msg' => '群组名不能为空' ] );
    		}
    		if( empty( $ids ) ){
    			return json( ['code' => -2, 'data' => '', 'msg' => '请添加成员' ] );
    		}
    		$this->_getUpFile( $param );
    		$param['owner_id'] = session('id');
    		$param['owner_avatar'] = session('member_info')['avatar'];
    		$param['owner_sign'] = session('member_info')['sign'];
    		$param['owner_name'] = session('member_info')['username'];
    		$flag = Group::insert( $param );
    		if( empty( $flag ) ){
    			return json( ['code' => -3, 'data' => '', 'msg' => '添加群组失败' ] );
    		}
    		//unset( $param );
    		//拼装上自己
    		$ids .= "," . session('id');
    		$groupid = Group::getLastInsID();

    		$users = Member::where("id in($ids)")->select();
    		if( !empty( $users ) ){
                $params = [];
    			foreach( $users as $key=>$vo ){
    				$params[] = [
    						'member_id' => $vo['id'],
    						'username' => $vo['username'],
    						'usersign' => $vo['sign'],
    						'useravatar' => $vo['avatar'],
    						'add_time' => time(),
    						'group_id' => $groupid
    				];
    			}
                GroupMember::insertAll( $params );
                unset( $params );
    		}

			//socket data
			$add_data = '{"type":"addGroup", "data" : {"avatar":"' . $param['avatar'] . '","groupname":"' . $param['groupname'] . '",';
			$add_data .= '"id":"' . $groupid. '", "uids":"' . $ids . '"}}';
    		
    		return json( ['code' => 1, 'data' => $add_data, 'msg' => '创建群组 成功' ] );
    	}
    	
        return view();
    }

    //管理我的好友
    public function myFriend()
    {
        if( request()->isAjax() ){
            $groupid = input('param.id');
            list($myGroup,$friend) = MyGroup::getMyFriend(session('id'),$groupid);
            return json( ['code' => 1, 'data' => $friend, 'msg' => 'success'] );
        }
        list($myGroup,$friend) = MyGroup::getMyFriend(session('id'));
        return view('',[
            'friend' => $friend,
            'myGroup' => $myGroup
        ]);
    }
    //移动好友分组
    public function removeFriend(){
        $gid = input('param.gid');
        $mid = input('param.mid');
        if (MyFriend::where('member_id = '.$mid)->update(['group_id'=>$gid])){
            return $this->success([],'修改成功',1);
        }else{
            return $this->fail('修改失败了');
        }
    }
    //删除好友
    public function delFriend(){
        $mid = input('param.mid');
        if (MyFriend::where('member_id = '.$mid)->delete()){
            return $this->success([],'删除成功',1);
        }else{
            return $this->fail('删除失败了');
        }
    }
    
    //管理我的群组
    public function myGroup()
    {
    	if( request()->isAjax() ){
    		$groupid = input('param.id');
    		$users = GroupMember::field('username,member_id,useravatar,group_id,type')
                ->where('group_id', $groupid)
                ->order('type')->select();
    		if (!$users) return $this->fail('您还没有创建群聊,快去创建吧');

    		return json( ['code' => 1, 'data' => $users, 'msg' => 'success'] );
    	}
    	$users = [];
    	$group = Group::field('id,groupname')->where('owner_id', session('id'))->select();
    	if( !empty($group) ){
    		$users = GroupMember::field('username,member_id,useravatar,group_id,type')
                ->where('group_id', $group['0']['id'])
                ->order('type')->select();
    	}
    	return view('',[
            'group' => $group,
            'users' => $users
        ]);
    }
    
    //追加群组人员
    public function addMembers()
    {
    	$groupid = input('param.gid');
    	$ids = input('param.ids');
    	$users = Member::where("id in($ids)")->select();
    	if( !empty( $users ) ){
    		foreach( $users as $key=>$vo ){
                $param = [
                    'member_id' => $vo['id'],
                    'add_time' => time(),
                    'username' => $vo['username'],
                    'group_id' => $groupid,
                    'useravatar'=>$vo['avatar']
                ];
                GroupMember::insert( $param );
    			unset( $param );
    		}
    	}

        $group = Group::field('avatar,groupname')->where('id', $groupid)->find();
        //socket data
        $add_data = '{"type":"addMember", "data" : {"avatar":"' . $group['avatar'] . '","groupname":"' . $group['groupname'] . '",';
        $add_data .= '"id":"' . $groupid. '", "uid":"' . $ids . '"}}';
    	
    	return json( ['code' => 1, 'data' => $add_data, 'msg' => '加入群组 成功' ] );
    }

    public function setAdmin(){
        $uid = input('param.uid');
        $groupid = input('param.gid');
        $type = input('param.type') == 2 ? 3 : 2;
        GroupMember::where("member_id=$uid and group_id=$groupid")->update(["type"=>$type]);
        $msg = $type == 2?'设为管理员':'撤销管理员';
        //socket data
        return json( ['code' => 1, 'id' => session('id'),'username'=>session('member_info')['username'], 'msg' => $msg] );
    }
    
    //移出成员出组
    public function removeMembers()
    {
    	$uid = input('param.uid');
    	$groupid = input('param.gid');
    	
    	$cannot = Group::field('id')->where('owner_id = ' . $uid . ' and id = ' . $groupid)->find();

    	if( !empty( $cannot ) ){
    		return json( ['code' => -1, 'data' => '', 'msg' => '不可移除群主'] );
    	}

        GroupMember::where('member_id = ' . $uid . ' and group_id = ' .$groupid)->delete();
    	
    	return json( ['code' => 1, 'data' => '', 'msg' => '移除成功'] );
    }
    //创建好友分组
    public function createMyGroup()
    {
        $groupname = input('param.groupname');
        $sort = input('param.sort');
        $ret = MyGroup::where(['group_name'=>$groupname])->find();
        if ($ret) return $this->fail('分组名不能重复');
        //删除群组
        $res = MyGroup::insert([
            'member_id'=>session('id'),
            'group_name'=>$groupname,
            'sort'=>$sort
        ]);
        if ($res){
            $groupid = MyGroup::getLastInsID();
            $add_data = [
                'type'=>'createMyGroup',
                'data'=>[
                    'uid'=>session('id'),
                    'groupname'=>$groupname,
                    'groupid'=>$groupid
                ]
            ];
            return $this->success(json_encode($add_data),'创建分组成功',1);
        }

        return $this->fail('创建分组失败');
    }

    //解散好友分组
    public function removeMyGroup()
    {
        $groupid = input('param.gid');
        //删除群组
        MyGroup::where('id', $groupid)->delete();

        //删除群成员
        MyFriend::where('group_id', $groupid)->delete();

        return json( ['code' => 1, 'data' => '', 'msg' => '成功解散好友分组'] );
    }
    
    //解散群组
    public function removeGroup()
    {
    	$groupid = input('param.gid');
    	//删除群组
        Group::where('id', $groupid)->delete();
    	
    	//删除群成员
        GroupMember::where('group_id', $groupid)->delete();
    	
    	return json( ['code' => 1, 'data' => '', 'msg' => '成功解散该群'] );
    }
    
    //获取所有的用户
    public function getUsers()
    {
    	$result = Member::field('id,username')
    	->where('id != ' . session('id'))
    	->select();
    	
    	if( empty($result) ){
    		return json( ['code' => -1, 'data' => '', 'msg' => '暂无其他成员'] );
    	}
    	
    	$str = "";
        $idsArr = [];
    	$flag = input('param.flag');
    	$flag = empty( $flag ) ? false : true;
    	if( $flag ){
    		//查询该分组中的成员id
    		$groupid = input('param.gid');
    		$ids = GroupMember::field('member_id')->where('group_id', $groupid)->select();
    		
    		if( !empty( $ids ) ){
    			foreach( $ids as $key=>$vo ){
    				$idsArr[] = $vo['member_id'];
    			}
    			unset( $ids );
    		}
    		
    		foreach( $result as $key=>$vo ){
    			if( in_array( $vo['id'], $idsArr ) ){
    				unset( $result[$key] );
    			}
    		}
    	}
    	
    	if( empty($result) ){
    		return json( ['code' => -2, 'data' => '', 'msg' => '该群组已经包含了全部成员'] );
    	}
    	
    	$group = config('user_group');
    	//先将默认分组拼装好
    	foreach( $group as $key=>$vo ){
    		$str .= '{ "id": "-' . $key . '", "pId":0, "name":"' . $vo .'"},';
    	}
    	
    	foreach($result as $key=>$vo){
    		$str .= '{ "id": "' . $vo['id'] . '", "pId":"-' . $vo['group_id'] . '", "name":"' . $vo['username'].'"},';
    	}
    	
    	$str = "[" . substr($str, 0, -1) . "]";
    	
    	return json( ['code' => 1, 'data' => $str, 'msg' => 'success'] );
    }
    //更改签名
    public function setSign(){
        $sign = input('param.sign');
        $mid = session('id');
        if(Member::upData($mid, ['sign'=>$sign])){
            return json( ['code' => 1, 'msg' => '修改签名成功'] );
        }else{
            return json( ['code' => 1, 'msg' => '失败了'] );
        }
    }
    //保存更该的皮肤
    public function setSkin(){
        $src = explode('/', input('param.src'));
        $src = end($src);
        $listen = input('param.listen');
        switch ($listen){
            case 'setSkin':
            $skin = Skin::where('member_id = '.session('id'))->where(['url'=>$src])->find();
            if ($skin){ //如果有,更新为 默认背景
                Skin::upData($skin['id'], ['is_user_upload'=>0]);
                $defaultId = $skin['id'];
            }else{
                Skin::insert([ //没有则插入数据
                    'member_id'=>session('id'),
                    'url'=>$src,
                    'is_user_upload'=>0
                ]);//修改其他皮肤状态
                $defaultId = Skin::getLastInsID();

            }
                Skin::where('member_id = '.session('id')." and id != $defaultId")->update(['is_user_upload'=>1]);
            break;
        }

    }

    public function outLogin(){
        session('member_info','');
        return $this->redirect('/index/chatUser/login');
    }
    
    /**
     * 上传图片方法
     * @param $param
     */
    private function _getUpFile(&$param)
    {
        $file = request()->file('avatar');
        if( !is_null( $file ) ) {
            if ($file->getSize() > 1024000) {
                // 上传失败获取错误信息
                return json(['code' => -2, 'msg' => '文件超过1M', 'data' => '']);
            }
            $savename = \think\facade\Filesystem::disk('public')->putFile('layim_pic', $file);
            if ($savename) {
                $param['avatar'] = '/storage/' . $savename;
            } else {
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }else{
            unset( $param['avatar'] );
        }
    }
}
