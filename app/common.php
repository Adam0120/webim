<?php
// 应用公共文件
/**
 * $msg 待提示的消息
 * $url 待跳转的链接
 * $time 弹出维持时间（单位秒）
 */
function alert_error($msg='',$url='',$time=2){
    $str ='<script src="/static/admin/js/jquery.min.js"></script>
            <script src="/static/layer/layer.js"></script>';//加载jquery和layer
    $str .= <<<script
        <style>
            span{
                color: #eaeaea;
                font-weight: 600;
                font-size: 18px;
            }
            h2{
                width: 350px;
                height: 40px;
                text-align: center;
                line-height: 40px;
                color: brown;
            }
            img{
                width: 200px;
                height: 200px;
                margin: 0 75px 30px;
            }
        </style>
        <script>
            var img = "<img src='/static/images/err.jpg'>";
                layer.open({
                      type: 1,
                      title:"<span>图不重要,看字!</span>",
                      skin: "layui-layer-lan", //样式类名
                      closeBtn: 0, //不显示关闭按钮
                      anim: 1,
                      shadeClose: true, //开启遮罩关闭
                      content: "<h2>$msg</h2>" + img
                    });
                setTimeout(function(){
                    if("$url" !== ''){
                        self.location.href = "$url"
                    }else{
                        self.history.go(-1)
                    }
                    },"$time"*1000)
        </script>
script;

    return $str;
}

function alert_success($msg='',$url='',$time=2){
    $str ='<script src="/static/admin/js/jquery.min.js"></script>
            <script src="/static/layer/layer.js"></script>';//加载jquery和layer
    $str .= <<<script
        <style>
            *{
                background: ;
            }
            span{
                color: #eaeaea;
                font-weight: 600;
                font-size: 18px;
            }
            h2{
                width: 350px;
                height: 40px;
                text-align: center;
                line-height: 40px;
                color: brown;
            }
            img{
                width: 200px;
                height: 200px;
                margin: 0 75px 30px;
            }
        </style>
        <script>
        var img = "<img src='/static/images/ok2.jpg'>";
            layer.open({
                  type: 1,
                  title:"<span>图不重要,看字!</span>",
                  skin: "layui-layer-lan", //样式类名
                  closeBtn: 0, //不显示关闭按钮
                  anim: 1,
                  shadeClose: true, //开启遮罩关闭
                  content: "<h2>$msg</h2>" + img
                });
            setTimeout(function(){
                self.location.href = "$url"
                },"$time"*1000)
        </script>
script;
    return $str;
}
