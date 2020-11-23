<?php

namespace app\index\controller;

use app\lib\exception\BaseExcption;
use think\Exception;
use think\response\Json;

class XunFei
{
    const PREPARE_URL       = 'http://raasr.xfyun.cn/api/prepare';// 预处理 /prepare:
    const UPLOAD_URL        = 'http://raasr.xfyun.cn/api/upload';// 文件分片上传 /upload:
    const MERGE_URL         = 'http://raasr.xfyun.cn/api/merge';// 合并文件 /merge:
    const GET_PROGRESS_URL  = 'http://raasr.xfyun.cn/api/getProgress';// 查询处理进度 /getProgress:
    const GET_RESULT_URL    = 'http://raasr.xfyun.cn/api/getResult';// 获取结果 /getResult:

//      测试表单页面
//    public function index(){
//        return view();
//    }

    /**
     * 翻译接口
     *
     * @return Json
     * @throws BaseExcption
     */
    public function actionExec()
    {
        // 接收参数
        if (empty(request()->file('file'))) {
            throw new Exception('请求参数异常', 400);
        }
        //接收文件
        $file = request()->file('file');
        //获取文件类型信息
        $mime = $file->getOriginalMime();
        //文件上传兵返回文件名称
        $savename = \think\facade\Filesystem::disk('public')->putFile( 'xun_fei', $file);
        if(!$savename){
            throw new Exception('文件上传失败');
        }
        $file =  'storage/' . $savename;
        //讯飞语音转文字密钥
        $appId = env('xunfei.app_id');
        $secretKey = env('xunfei.app_key');

        $prepareData = static::prepare($appId, $secretKey, $file);
        if ($prepareData['ok'] != 0) {
            throw new Exception('prepare失败');
        }

        $taskId = $prepareData['data'];

        $uploadData = static::upload($appId, $secretKey, $file, $mime, $taskId);
        if ($uploadData['ok'] != 0) {
            throw new Exception('upload失败');
        }

        $mergeData = static::merge($appId, $secretKey, $taskId);

        if ($mergeData['ok'] != 0) {
            throw new Exception('merge失败');
        }

        $num = 1;

        //限定转写次数
        start:
        $getProgressData = static::getProgress($appId, $secretKey, $taskId);

        if ($getProgressData['ok'] != 0) {
            throw new Exception('getProgress失败');
        }
        $statusData = json_decode($getProgressData['data'], true);

        if ($statusData['status'] != 9) {
            if ($num >= 10) {
                throw new Exception('转写时间过长');
            }
            $num++;
            sleep(1);
            goto start;
        }

        $getResultData = static::getResult($appId, $secretKey, $taskId);
        if ($getResultData['ok'] != 0) {
            throw new Exception('getResult失败');
        }
        //分词数组
        $data = json_decode($getResultData['data'],true);
        //拼接分词
        $text = '';
        foreach ($data as $v) {
            $text.= $v['onebest'];
        }

        return json(['code'=>0,'msg'=>'ok','data'=>$text]);
    }


    /**
     * 预处理
     *
     * @param $appId
     * @param $secretKey
     * @param $file
     * @return mixed
     */
    public static function prepare($appId, $secretKey, $file)
    {
        $fileInfo = pathinfo($file);
        $ts = time();

        $data = [
            'app_id' => (string)$appId,
            'signa' => (string)static::getSinga($appId, $secretKey, $ts),
            'ts' => (string)$ts,
            'file_len' => (string)filesize($file),
            'file_name' => (string)$fileInfo['basename'],
            'slice_num' => 1,
            'has_participle' => (string)"false",//转写结果是否包含分词信息
        ];

        $data = http_build_query($data);

        $header = [
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8'
        ];

        $res = static::curlPost(static::PREPARE_URL, $data, $header);

        $resultData = json_decode($res, true);

        return $resultData;
    }

    /**
     * 上传文件
     *
     * @param $appId
     * @param $secretKey
     * @param $file
     * @param $taskId
     * @return mixed
     */
    public static function upload($appId, $secretKey, $file, $mime, $taskId)
    {
        $ts = time();
        $curlFile = curl_file_create(
            $file,
            $mime,
            pathinfo(
                $file,
                PATHINFO_BASENAME
            )
        );

        $data = [
            'app_id' => (string)$appId,
            'signa' => (string)static::getSinga($appId, $secretKey, $ts),
            'ts' => (string)$ts,
            'task_id' => $taskId,
            'slice_id' => "aaaaaaaaaa",
            'content' => $curlFile,
        ];

        $header = [
            "Content-Type: multipart/form-data"
        ];

        $res = static::curlPost(static::UPLOAD_URL, $data, $header);

        $resultData = json_decode($res, true);

        return $resultData;
    }

    /**
     * 合并文件
     *
     * @param $appId
     * @param $secretKey
     * @param $taskId
     * @return mixed
     */
    public static function merge($appId, $secretKey, $taskId)
    {
        $ts = time();

        $data = [
            'app_id' => (string)$appId,
            'signa' => (string)static::getSinga($appId, $secretKey, $ts),
            'ts' => (string)$ts,
            'task_id' => $taskId,
        ];
        $data = http_build_query($data);
        $header = [
            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8"
        ];

        $res = static::curlPost(static::MERGE_URL, $data, $header);

        $resultData = json_decode($res, true);

        return $resultData;

    }

    /**
     * 查询处理进度
     *
     * @param $appId
     * @param $secretKey
     * @param $taskId
     * @return mixed
     */
    public static function getProgress($appId, $secretKey, $taskId)
    {
        $ts = time();

        $data = [
            'app_id' => (string)$appId,
            'signa' => (string)static::getSinga($appId, $secretKey, $ts),
            'ts' => (string)$ts,
            'task_id' => $taskId,
        ];

        $data = http_build_query($data);

        $header = [
            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8"
        ];

        $res = static::curlPost(static::GET_PROGRESS_URL, $data, $header);

        $resultData = json_decode($res, true);

        return $resultData;
    }

    /**
     * 获取转写结果
     *
     * @param $appId
     * @param $secretKey
     * @param $taskId
     * @return mixed
     */
    public static function getResult($appId, $secretKey, $taskId)
    {
        $ts = time();

        $data = [
            'app_id' => (string)$appId,
            'signa' => (string)static::getSinga($appId, $secretKey, $ts),
            'ts' => (string)$ts,
            'task_id' => $taskId,
        ];

        $data = http_build_query($data);

        $header = [
            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8"
        ];

        $res = static::curlPost(static::GET_RESULT_URL, $data, $header);

        $resultData = json_decode($res, true);

        return $resultData;
    }


    /**
     * curl
     *
     * @param $url
     * @param string $postData
     * @param string $header
     * @return bool|string
     */
    public static function curlPost($url, $postData = '', $header = '')
    {
        //初始化
        $curl = curl_init(); //用curl发送数据给api
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $url);

        if (!empty($header)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        }

        if (!empty($postData)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
        }

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        $response = curl_exec($curl);
        //关闭URL请求
        curl_close($curl);
        //显示获得的数据
        return $response;
    }

    /**
     * 获取signa
     *
     * @param $appId
     * @param $secretKey
     * @param $ts
     * @return string
     */
    public static function getSinga($appId, $secretKey, $ts)
    {
        $md5Str = $appId . $ts;

        $md5 = MD5($md5Str);

        $md5 = mb_convert_encoding($md5, "UTF-8");

        // 相当于java的HmacSHA1方法
        $signa = base64_encode(hash_hmac("sha1", $md5, $secretKey, true));

        return $signa;
    }

}
