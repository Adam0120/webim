<?php
// +----------------------------------------------------------------------
// | layerIM + Workerman + ThinkPHP5 即时通讯
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: NickBai <1902822973@qq.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace app\laychat\controller;

use app\laychat\model\ChatRecoed;
use app\laychat\model\Message;
use app\lib\controller\MyBaseController;

class Chatlog extends MyBaseController
{
    public function initialize()
    {
        parent::initialize();
        if(!session('member_info')) return $this->redirect('/index/chatUser/login');
    }
    //聊天记录
    public function index()
    {
        $id = input('id');
        $type = input('type');

        return view('',[
            'id' => $id,
            'type' => $type
        ]);
    }

    //聊天记录详情
    public function detail()
    {
        $id = input('id');
        $type = input('type');
        $uid = session('id');
        if( 'friend' == $type ){
            $result = ChatRecoed::where("((send={$uid} and receive={$id}) or (send={$id} and receive={$uid})) and type='friend'")
                ->order('send_time desc')
                ->select();
            if( empty($result) ){
                return json( ['code' => -1, 'data' => '', 'msg' => '没有记录'] );
            }

            return json( ['code' => 1, 'data' => $result, 'msg' => 'success'] );

        }else if('group' == $type){

            $result = ChatRecoed::where("send={$id} and type='group'")
                ->order('send_time desc')
                ->select();
            
            if( empty($result) ){
                return json( ['code' => -1, 'data' => '', 'msg' => '没有记录'] );
            }

            return json( ['code' => 1, 'data' => $result, 'msg' => 'success'] );
        }
    }

    //获取盒子消息记录
    public function getMsg(){
        $page = (input('page') -1)* 10;
        //获取当前用户未读消息
        $msg = Message::field('id,msg_type,send,receive,msg_status,remark,send_time,my_group')
        ->where([
            ['receive','=',session('id')],
            ['msg_status','=',1]
        ])->with(['user'])->limit($page* 10,10)->select()->toArray();
        if (!$msg){
            return json([
                'code'=>1,
                'msg'=>'暂时没有未读消息',
                'data'=> []
            ]);
        }
        //消息区分
        $common = []; //添加好友或群
        $system = []; //系统消息
        foreach ($msg as $v){
            if ($v['msg_type'] == 1 || $v['msg_type'] == 3){
                $common[] = $v;
            }else{
                $system[] = $v;
            }
        }
        //拼接消息
        $commonMsg = []; //普通消息
        foreach ($common as $v){
            $commonMsg[] = [
              'id'=>$v['id'],
              'content'=>$v['msg_type'] == 1 ? "申请添加您为好友" : "申请加入群",
                'uid' => session('id'),
                'from'=> $v['send'],
                'from_group'=>$v['my_group'],
                'type'=>$v['msg_type'] == 1?'friend':'group',
                'remark'=>$v['remark'],
                'href'=>'',
                'read'=>$v['msg_status'],
                'time'=>date("Y-m-d H:i:s",$v['send_time']),
                'user'=>$v['user']
            ];
        }
        $systemMsg = []; //系统
        foreach ($system as $v){
            $systemMsg[] = [
                'content'=> $v['remark'],
                'time'=>date("Y-m-d H:i:s",$v['send_time']),
            ];
        }
        return json([
            'code'=>0,
            'msg'=>'',
            'pages'=>$page,
            'data'=> array_merge($commonMsg,$systemMsg)
        ]);

    }
    //设置消息为以读
    public function setRead(){
        Message::whereRaw("(msg_type = 2 or msg_type = 4) and receive=".session('id'))
            ->update(['msg_status'=>6]);
        return $this->jsonLayui();
    }

    //获取系统消息的数量
    public function getSystem(){
        return Message::where("msg_status = 1 and receive=".session('id'))->count();
    }
}
