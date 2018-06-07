停止维护，请慎用。

# Ueditor
this is a ueditor

## 安装

> composer require "zhangvoice/ueditor:dev-master"

## 删除

> composer remove zhangvoice/ueditor

## 更新

> composer update zhangvoice/ueditor

## 使用

```
//引入类库
use think\ueditor\Ueditor;
```

```
//添加ueditor方法
public function ueditor()
{
    $data = new Ueditor();
    echo $data->output();
}
```
## 视图

```
<script>
    $(function(){
        var ue = UE.getEditor('container',{
            serverUrl :'{:url('模块/控制器/ueditor')}'
        });
    })
</script>
```

## 上传目录

默认上传至 public/uploads/ueditor 请确认目录存在。

目前仅支持TP5,不支持SAE平台。

## ueditor.zip

ueditor 1.4.3.3 版本 ,解压拷贝至静态资源存放目录

```

<script src="{:url('/')}static/ueditor/ueditor.config.js"></script>
<script src="{:url('/')}static/ueditor/ueditor.all.min.js"></script>
<script src="{:url('/')}static/ueditor/lang/zh-cn/zh-cn.js"></script>

```