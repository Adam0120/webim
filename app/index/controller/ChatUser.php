<?php
declare (strict_types = 1);

namespace app\index\controller;

use app\laychat\model\Member;
use app\lib\controller\MyBaseController;
use app\lib\validate\CreateValidate;
use app\lib\validate\LoginValidate;

class ChatUser extends MyBaseController
{
    //登录聊天室
    public function login()
    {
        if($this->request->isPost()){

            $res = (new LoginValidate())->goCheck();
            if($res) return alert_error($res);

            $data = $this->request->postMore([
                ['account', ''],
                ['password', ''],
            ]);
            $memberInfo = Member::where('account',$data['account'])->find();
            if($memberInfo){
                //获取用户密钥及密码
                if (md5($data['password'].$memberInfo['salt']) == $memberInfo['password']){
                    //用户信息存储session
                    session('id',$memberInfo['id']);
                    session('member_info',[
                        'id'=>$memberInfo['id'],
                        'avatar'=>$memberInfo['avatar'],
                        'username'=>$memberInfo['username'],
                        'sign'=>$memberInfo['sign']
                    ]);
                    Member::upData($memberInfo['id'], [
                        'login_time'=>time(),
                        'status'=>1
                    ]);
                    return alert_success('登录成功','/laychat/index');
                }
            }
            return alert_error('用户名或密码错误!');
        }
        return view();
    }

    public function create(){
        return view();
    }
    public function save(){
        $res = (new CreateValidate())->goCheck();
        if($res) return alert_error($res);
        $data = $this->request->postMore([
                ['account',''],
                ['password',''],
                ['avatar',''],
                ['username',''],
                ['sign',''],
                ['sex',''],
            ]);
        $data['salt'] = substr(md5(microtime()), 0,6);
        $data['password'] = md5($data['password'].$data['salt']);
        $data['create_time'] = time();
        if (Member::insert($data)){
            $id = Member::getLastInsID();
            session('id',$id);
            session('member_info',[
                'id'=>$id,
                'avatar'=>$data['avatar'],
                'sign'=>$data['sign'],
                'username'=>$data['username'],
            ]);
            Member::upData($id, [
                'login_time'=>time(),
                'status'=>1
            ]);
            return alert_success('注册成功','/laychat/index');
        }else{
            return alert_error('注册失败了');
        }
    }
}
