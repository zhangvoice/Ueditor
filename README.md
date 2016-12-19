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
            serverUrl :'{:U('模块/控制器/ueditor')}'
        });
    })
</script>
```

## 上传目录

默认上传至 public/uploads/ueditor 请确认目录存在。

目前仅支持TP5,不支持SAE平台。