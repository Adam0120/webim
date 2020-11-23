<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{    
   /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     */
    public static function onMessage($client_id, $data) {
        global $db1;
        $message = json_decode($data, true);
        //var_export($message);
        $message_type = $message['type'];
        switch($message_type) {
            case 'init':
                // uid
                $uid = $message['id'];
                // 设置session
                $_SESSION = [
                    'username' => $message['username'],
                    'avatar'   => $message['avatar'],
                    'id'       => $uid,
                    'sign'     => $message['sign']
                ];

                // 将当前链接与uid绑定
                Gateway::bindUid($client_id, $uid);
                // 通知当前客户端初始化
                $init_message = array(
                    'message_type' => 'init',
                    'id'           => $uid,
                );
                Gateway::sendToClient($client_id, json_encode($init_message));

                //查询最近1周有无需要推送的离线信息
                $time = time() - 7 * 3600 * 24;
                $resMsg = $db1->select('id,send,fromname,fromavatar,receive,send_time,content')->from('yz_chat_recoed')
                    ->where("receive= {$uid} and send_time > {$time} and type = 'friend' and is_read = 0" )
                    ->query();
                //var_export($resMsg);
                if( !empty( $resMsg ) ){
                    foreach( $resMsg as $key=>$vo ){
                        $log_message = [
                            'message_type' => 'logMessage',
                            'data' => [
                                'username' => $vo['fromname'],
                                'avatar'   => $vo['fromavatar'],
                                'id'       => $vo['send'],
                                'type'     => 'friend',
                                'content'  => htmlspecialchars( $vo['content'] ),
                                'timestamp'=> $vo['send_time'] * 1000,
                            ]
                        ];
                        Gateway::sendToUid( $uid, json_encode($log_message) );

                        //设置推送状态为已经推送
                        $db1->query("UPDATE `yz_chat_recoed` SET `is_read` = '1' WHERE id=" . $vo['id']);
                    }
                }

                //更新登录状态
                $db1->update('yz_member')->cols(['status'=>1])
                    ->where("id=$uid")->query();
                //消息广播
                $groupIds = $db1->select('id')->from('yz_my_group')
                    ->where("member_id=$uid")->column();
                if ($groupIds){
                    $groupIds = implode(',',$groupIds);
                    $memberIds = $db1->select('member_id')->from('yz_my_friend')
                        ->where("group_id in ($groupIds)")->column();
                    if ($memberIds){
                        foreach ($memberIds as $id){
                            $res = Gateway::getClientIdByUid( $id );
                            if (!empty($res)){
                                $init_message = array(
                                    'message_type' => 'friend_login',
                                    'id'           => $uid,
                                );
                                Gateway::sendToClient($res[0], json_encode($init_message));
                            }
                        }
                    }
                }

                //查询当前的用户是在哪个分组中,将当前的链接加入该分组
                $ret = $db1->query("select `group_id` from `yz_group_member` where `member_id` = {$uid} group by `group_id`");
                if( !empty( $ret ) ){
                    foreach( $ret as $key=>$vo ){
                        Gateway::joinGroup($client_id, $vo['group_id']);  //将登录用户加入群组
                    }
                }
                unset( $ret );
                return;
                break;
            case 'addUser' :
                //添加用户
                $add_message = [
                    'message_type' => 'addUser',
                    'data' => [
                        'type' => 'friend',
                        'avatar'   => $message['data']['avatar'],
                        'username' => $message['data']['username'],
                        'groupid'  => $message['data']['groupid'],
                        'id'       => $message['data']['id'],
                        'sign'     => $message['data']['sign']
                    ]
                ];
                Gateway::sendToAll( json_encode($add_message), null, $client_id );
                return;
                break;
            case 'delUser' :
                //删除用户
                $del_message = [
                    'message_type' => 'delUser',
                    'data' => [
                        'type' => 'friend',
                        'id'       => $message['data']['id']
                    ]
                ];
                Gateway::sendToAll( json_encode($del_message), null, $client_id );
                return;
                break;
            case 'addGroup':
                //添加群组
                $uids = explode( ',', $message['data']['uids'] );
                $client_id_array = [];
                foreach( $uids as $vo ){
                    $ret = Gateway::getClientIdByUid( $vo );  //当前组中在线的client_id
                    if( !empty( $ret ) ){
                        $client_id_array[] = $ret['0'];
                        Gateway::joinGroup($ret['0'], $message['data']['id']);  //将这些用户加入群组
                    }
                }
                unset( $ret, $uids );
                $add_message = [
                    'message_type' => 'addGroup',
                    'data' => [
                        'type' => 'group',
                        'avatar'   => $message['data']['avatar'],
                        'id'       => $message['data']['id'],
                        'groupname'     => $message['data']['groupname']
                    ]
                ];
                Gateway::sendToAll( json_encode($add_message), $client_id_array, $client_id );
                return;
                break;
            case 'joinGroup':
                //加入群组
                $groupid = $message['to'];
                $res = $db1->select('owner_id,approval')->from('yz_group')->where('id='.$groupid)->query();
                if(isset($res[0]['approval'])){
                    if($res[0]['approval'] == 1){ //群需要验证
                        $dataMsg = [
                            'msg_type'=> 3,
                            'send'    => $message['from'],
                            'receive' => $res[0]['owner_id'],
                            'remark'  => $message['remark']."|".$groupid,
                            'send_time'=>time()
                        ];
                        $db1->insert('yz_message')->cols($dataMsg)->query();
                    }else{ //不需要验证直接加群
                        $mInfo = $db1->select('username,avatar,sign')->from('yz_member')->where('id='.$message['from'])->query();
                        //添加群组
                        $memberData = [
                            'group_id'=> $groupid,
                            'member_id'=>$message['from'],
                            'add_time'=>time(),
                            'type'=> 3,
                            'username'=>$mInfo[0]['username'],
                            'useravatar'=>$mInfo[0]['avatar'],
                            'usersign'=>$mInfo[0]['sign'],

                        ];
                        $db1->insert('yz_group_member')->cols($memberData)->query();

                        $dataMsg = [
                            'msg_type'=> 4,
                            'send'    => $message['from'],
                            'receive' => $res[0]['owner_id'],
                            'remark'  => $mInfo[0]['username']." 加入群聊 ".$message['groupname'],
                            'send_time'=>time()
                        ];

                        $db1->insert('yz_message')->cols($dataMsg)->query();
                        $mClientId = Gateway::getClientIdByUid($message['from']);
                        if (!empty($mClientId)){
                            Gateway::joinGroup($mClientId[0], $message['to']);  //将该用户加入群组
                            $add_message = [
                                'message_type' => 'addGroup',
                                'data' => [
                                    'type' => 'group',
                                    'avatar'   => $message['avatar'],
                                    'id'       => $message['to'],
                                    'groupname'  => $message['groupname']
                                ]
                            ];
                            var_export($mClientId);
                            Gateway::sendToUid($message['from'], json_encode($add_message) );  //推送群组信息
                        }
                    }
                    $oClientId = Gateway::getClientIdByUid($res[0]['owner_id']);
                    if (!empty($oClientId)){
                        Gateway::sendToUid($res[0]['owner_id'], json_encode([
                            'type'=>'friend',
                            'count'=>1
                        ]));
                    }
                }


                return;
                break;
            case 'addMember':
                //添加群组成员
                $uids = explode( ',', $message['data']['uid'] );
                $client_id_array = [];
                foreach( $uids as $vo ){
                    $ret = Gateway::getClientIdByUid( $vo );  //当前组中在线的client_id
                    if( !empty( $ret ) ){
                        $client_id_array[] = $ret[0];
                        Gateway::joinGroup($ret[0], $message['data']['id']);  //将这些用户加入群组
                    }
                }
                unset( $ret, $uids );

                $add_message = [
                    'message_type' => 'addGroup',
                    'data' => [
                        'type' => 'group',
                        'avatar'   => $message['data']['avatar'],
                        'id'       => $message['data']['id'],
                        'groupname'=> $message['data']['groupname']
                    ]
                ];
                Gateway::sendToAll( json_encode($add_message), $client_id_array, $client_id );  //推送群组信息
                return;
                break;
            case 'removeMember':
                //将移除群组的成员的群信息移除，并从讨论组移除
                $ret = Gateway::getClientIdByUid( $message['data']['uid'] );
                if( !empty( $ret ) ){

                    Gateway::leaveGroup($ret['0'], $message['data']['id']);

                    $del_message = [
                        'message_type' => 'delGroup',
                        'data' => [
                            'type' => 'group',
                            'id'       => $message['data']['id']
                        ]
                    ];
                    Gateway::sendToAll( json_encode($del_message), [$ret['0']], $client_id );
                }

                return;
                break;
            case 'delGroup':
                //删除群组
                $del_message = [
                    'message_type' => 'delGroup',
                    'data' => [
                        'type' => 'group',
                        'id'       => $message['data']['id']
                    ]
                ];
                Gateway::sendToAll( json_encode($del_message), null, $client_id );
                return;
                break;
            case 'chatMessage':
                $type = $message['data']['to']['type'];
                $to_id = $message['data']['to']['id'];
                $uid = $_SESSION['id'];
                $chat_message = [
                    'message_type' => 'chatMessage',
                    'data' => [
                        'username' => $_SESSION['username'],
                        'avatar'   => $_SESSION['avatar'],
                        'id'       => $type === 'friend' ? $uid : $to_id,
                        'type'     => $type,
                        'content'  => htmlspecialchars($message['data']['mine']['content']),
                        'timestamp'=> time()*1000,
                    ]
                ];
                //聊天记录数组
                $param = [
                    'send' => $uid,
                    'receive' => $to_id,
                    'fromname' => $_SESSION['username'],
                    'fromavatar' => $_SESSION['avatar'],
                    'content' => htmlspecialchars($message['data']['mine']['content']),
                    'send_time' => time(),
                    'is_read' => 1,
                    'type'=>$type
                ];
                switch ($type) {
                    // 私聊
                    case 'friend':
                        // 插入
                        if( empty( Gateway::getClientIdByUid( $to_id ) ) ){
                            $param['is_read'] = 0;  //用户不在线,标记此消息推送
                            $sendMessage = [
                                'system'=>true //系统消息
                                ,'id'=> $uid //聊天窗口ID
                                ,'type'=> "friend" //聊天窗口类型
                                ,'content'=> '对方不在线,上线时将推送次消息'
                            ];

                        Gateway::sendToUid($uid, json_encode([
                                'type'=>'chatMessage',
                                'data'=>$sendMessage
                            ]));
                        var_export($sendMessage);
                        }else{
                            Gateway::sendToUid($to_id, json_encode($chat_message));
                        }
                        $db1->insert('yz_chat_recoed')->cols( $param )->query();
                        break;
                    // 群聊
                    case 'group':
                        $db1->insert('yz_chat_recoed')->cols( $param )->query();
                        return Gateway::sendToGroup($to_id, json_encode($chat_message), $client_id);
                        break;
                }
                return;
                break;
            case 'hide':
            case 'online':
                $status_message = [
                    'message_type' => $message_type,
                    'id'           => $_SESSION['id'],
                ];
                $_SESSION['online'] = $message_type;
                Gateway::sendToAll(json_encode($status_message));
                return;
                break;
            case 'ping':
                return;
            case 'setAdminDo':
                $data = $message['data'];
                $gid = $message['data']['gid'];
                $to_id = $message['data']['uid'];
                $groupname = $db1->select('groupname')->from('yz_group')->where('id ='.$gid)->single();
                $dataMsg = [
                    'msg_type'=>2,
                    'send'=> $data['id'],
                    'receive'=>$to_id,
                    'remark'=> $data['username'].' 在 '.$groupname.' 将你'.$data['msg'],
                    'send_time'=>time(),
                    'my_group'=>$gid
                ];
                //var_export($dataMsg);
                $db1->insert('yz_message')->cols($dataMsg)->query();
                $init_message = array(
                    'message_type' => 'friend',
                    'count' => 1
                );
                Gateway::sendToUid($to_id, json_encode($init_message));
                break;
            case 'groupMessage':
                $dataMsg = $message['data'];
                $gid = $message['data']['id'];
                $list = Gateway::getClientIdListByGroup($gid);
                var_dump($list);
                Gateway::sendToAll(json_encode([
                    'type'=>'chatMessage',
                    'data'=>$dataMsg
                ]),$list);
                //var_export($message);
                break;
            case 'addFriend':
                $dataMsg = [
                  'msg_type'=>1,
                  'send'=> $message['from'],
                  'receive'=>$message['to'],
                  'remark'=> $message['remark'],
                  'send_time'=>time(),
                  'my_group'=>$message['group']
                ];
                $db1->insert('yz_message')->cols($dataMsg)->query();
                $init_message = array(
                    'message_type' => 'friend',
                    'count' => 1
                );
                Gateway::sendToUid($message['to'], json_encode($init_message));
                break;
            case 'msgHandle':
                //添加好友 or 群
                $category = $message['category'];
                if ($category == 'friend'){//添加好友
                    if ($message['active'] == 'agree'){//同意
                        $tmpmsg = ' 同意添加您为好友';
                        $db1->insert('yz_my_friend')->cols([
                            'group_id'=>$message['group'],
                            'member_id'=>$message['from'],
                            'username'=>$message['username']
                        ])->query();
                    }else{ //拒绝
                        $tmpmsg = ' 拒绝添加您为好友';
                    }
                    $dataMsg = [
                        'msg_type'=>2,
                        'send'=>$message['from'],
                        'receive'=>$message['to'],
                        'remark'=> $message['username'].$tmpmsg,
                        'send_time'=>time()
                    ];
                    $db1->insert('yz_message')->cols($dataMsg)->query();

                    if(Gateway::isUidOnline($message['to'])){
                        //添加用户
                        $add_message = [
                            'message_type' => 'addUser',
                            'data' => [
                                'type' => 'friend',
                                'avatar'   => $message['avatar'],
                                'username' => $message['username'],
                                'groupid'  => $message['group'],
                                'id'       => $message['from'],
                                'sign'     => $message['sign']
                            ]
                        ];
                        Gateway::sendToUid($message['to'], json_encode($add_message));
                    }

                }else{//加群
                    list($remark,$group_id) = explode('|', $message['remark']);
                    $groupInfo = $db1->select('groupname,avatar')->from('yz_group')->where('id='.$group_id)->query();                    if ($message['active'] == 'agree'){//同意加群
                        $tmpmsg = ' 同意您加入 ';
                        $ret = Gateway::getClientIdByUid($message['to']);
                        if ( !empty($ret)){
                            Gateway::joinGroup($ret[0], $group_id);  //将该用户加入群组
                            $add_message = [
                                'message_type' => 'addGroup',
                                'data' => [
                                    'type' => 'group',
                                    'avatar'   => $groupInfo[0]['avatar'],
                                    'id'       => $group_id,
                                    'groupname'  => $groupInfo[0]['groupname']
                                ]
                            ];
                            Gateway::sendToClient($ret[0], json_encode($add_message) );  //推送群组信息
                        }
                    }else{//拒绝结群
                        $tmpmsg = ' 拒绝您加入 ';
                    }
                    //添加消息记录
                    $dataMsg = [
                      'msg_type'=>4,
                      'send'=>$message['from'],
                      'receive'=>$message['to'],
                      'remark' => $message['username'].$tmpmsg.$groupInfo[0]['groupname'],
                      'send_time'=>time()
                    ];
                    $db1->insert('yz_message')->cols($dataMsg)->query();
                }

                //给请求者推送消息
                if (Gateway::isUidOnline($message['to'])){
                    Gateway::sendToUid($message['to'], json_encode([
                        'type'=>'friend',
                        'count'=>1
                    ]));
                }
                break;
            case "createMyGroup":
                    $uid = $message['data']['uid'];
                    Gateway::sendToUid($uid,json_encode($message));
                break;
            default:
                echo "unknown message $data" . PHP_EOL;
        }
    }

    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     */
    public static function onClose($client_id) {
        global $db1;
        $id = $_SESSION['id'];
        if ($id){
            $db1->update('yz_member')->cols(['status'=>0])->where('id='.$id)->query();
            $logout_message = [
                'message_type' => 'logout',
                'data'=>['id'=>$id]
            ];
            Gateway::sendToAll(json_encode($logout_message));
        }

    }
}
