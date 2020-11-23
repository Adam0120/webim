<?php


namespace app\lib\controller;


use app\BaseController;
use think\App;
use think\exception\HttpResponseException;
use think\response\Json;

class MyBaseController extends BaseController
{

    public function redirect(...$args)
    {
        throw new HttpResponseException(redirect(...$args));
    }

    /**
     * layui返回格式
     * @param string $msg 提示信息
     * @param int $code  code码
     * @param array  $data  全部数据（数组）
     * @param int $count 总条数
     *
     * @return  Json
     */
    public function jsonLayui($msg = '',$data = [],$code = 0,$count = 0)
    {
        return json(['code' => $code,'count'=>$count,'msg'=>$msg,'data'=>$data]);
    }

    /**
     * 成功返回
     * @param string $msg 提示信息
     * @param int $code  code码
     * @param mixed  $data  全部数据
     *
     * @return  Json
     */
    public function success($data = [],$msg = '',$code = 200)
    {
        return json(['code' => $code,'msg'=>$msg,'data'=>$data]);
    }

    /**
     * 失败返回
     * @param string $msg 提示信息
     * @param int $code  code码
     * @param array  $data  全部数据（数组）
     *
     * @return  Json
     */
    public function fail($msg = '',$data = [],$code = 400)
    {
        return json(['code' => $code,'msg'=>$msg,'data'=>$data]);
    }
}
