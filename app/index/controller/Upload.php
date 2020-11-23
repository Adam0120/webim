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
namespace app\index\controller;

use app\lib\controller\MyBaseController;
use think\response\Json;

class Upload extends MyBaseController
{

    //上传图片
    public function uploadImg()
    {
        $file = request()->file('file');
        if( $file->getSize() > 10240000){
            // 上传失败获取错误信息
            return json( ['code' => -2, 'msg' => '文件超过10M', 'data' => ''] );
        }
        $savename = \think\facade\Filesystem::disk('public')->putFile( 'layim_pic', $file);
        if($savename){
            $src =  'storage'. DS . $savename;
            return json( ['code' => 0, 'msg' => '', 'data' => ['src' => $src ] ] );
        }else{
            // 上传失败获取错误信息
            return json( ['code' => -1, 'msg' => $file->getError(), 'data' => ''] );
        }
    }

    //上传文件
    public function uploadFile()
    {
        $file = request()->file('file');
        if( $file->getSize() > 10240000){
            // 上传失败获取错误信息
            return json( ['code' => -2, 'msg' => '文件超过10M', 'data' => ''] );
        }
        $savename = \think\facade\Filesystem::disk('public')->putFile( 'layim_file', $file);
        if($savename){
            $src =  'storage'. DS . $savename;
            return json( ['code' => 0, 'msg' => '', 'data' => ['src' => $src ] ] );
        }else{
            // 上传失败获取错误信息
            return json( ['code' => -1, 'msg' => $file->getError(), 'data' => ''] );
        }
    }

    /**
     * 文件上传 serve
     * @return Json 文件传后回调
     * */
    public function uploadMp3(){
        $file = request()->file('file');
        if( $file->getSize() > 10240000){
            // 上传失败获取错误信息
            return json( ['code' => -2, 'msg' => '文件超过10M', 'data' => ''] );
        }
        // 移动到框架应用根目录/storage/ 目录下
        $info = $file->move( '../public/storage/layim_voice/'.date('Ymd').DS);

        if($info){
            // 成功上传后 获取上传信息
            return json(['code'=>0,'src'=> $info->getPathname()]);
        }else{
            // 上传失败获取错误信息
            return json(['code'=>1,'msg'=>$file->getExtension()]);
        }
    }
}
