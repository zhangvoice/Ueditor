<?php
/**
 * Ueditor插件
 * @author Nintendov
 */
namespace think\ueditor;
class Ueditor{
	
	private $output;//要输出的数据
	
	private $rootpath = '/uploads/ueditor';//上传地址
	
	public function __construct(){

		//导入设置
		$CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents(dirname(__FILE__)."/config.json")), true);
		
		$action = htmlspecialchars($_GET['action']);
		
		switch($action){
			case 'config':
		        $result = json_encode($CONFIG);
		        break;
		        
		    case 'uploadimage':
				$config = array(
		            "pathFormat" => $this->rootpath . "/image",
		            "maxSize" => $CONFIG['imageMaxSize'],
		            "allowFiles" => $CONFIG['extimg']
				);
				$fieldName = $CONFIG['imageFieldName'];
				$result = $this->uploadFile($config, $fieldName);
				break;
				
			case 'uploadscrawl':
				$config = array(
		            "pathFormat" => $this->rootpath . "/crawl",
		            "maxSize" => $CONFIG['scrawlMaxSize'],
		            "oriName" => "scrawl.png"
		            );
		            $fieldName = $CONFIG['scrawlFieldName'];
		            $result=$this->uploadBase64($config,$fieldName);
		            break;
		            
		    case 'uploadvideo':
				$config = array(
		            "pathFormat" => $this->rootpath . "/video",
		            "maxSize" => $CONFIG['videoMaxSize'],
		            "allowFiles" => $CONFIG['extvideo']
				);
				$fieldName = $CONFIG['videoFieldName'];
				$result=$this->uploadFile($config, $fieldName);
				break;
				
			case 'uploadfile':
				// default:
				$config = array(
		            "pathFormat" => $this->rootpath . "/file",
		            "maxSize" => $CONFIG['fileMaxSize'],
		            "allowFiles" => $CONFIG['extfile']
				);
				$fieldName = $CONFIG['fileFieldName'];
				$result=$this->uploadFile($config, $fieldName);
				break;
				
			case 'listfile':
				$config=array(
					'allowFiles' => $CONFIG['fileManagerAllowFiles'],
					'listSize' => $CONFIG['fileManagerListSize'],
					'path' => $this->rootpath . "/file",
				);
				$result = $this->listFile($config);
				break;
				
			case 'listimage':
				$config=array(
					'allowFiles' => $CONFIG['imageManagerAllowFiles'],
					'listSize' => $CONFIG['imageManagerListSize'],
					'path' => $this->rootpath . "/image",
				);
				$result = $this->listFile($config);
				break;
	
			default:
		        $result = json_encode(array(
		            'state'=> 'wrong require'
		        ));
		        break;
			
		}
		
		if (isset($_GET["callback"])) {
			if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
				$this->output = htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
			} else {
				$this->output = json_encode(array(
		            'state'=> 'callback参数不合法'
		            ));
			}
		} else {
			$this->output = $result;
		}
	}
	
	
	/**
	 * 
	 * 输出结果
	 * @param data 数组数据
	 * @return 组合后json格式的结果
	 */
	public function output(){
		return $this->output;
	}
	
	/**
	 * 上传文件方法
	 * 
	 */
	private function uploadFile($config,$fieldName){
		$file = request()->file('upfile');
		$info = $file->validate($config['allowFiles'])->move(ROOT_PATH . 'public' . $config['pathFormat']);
		if($info){
			$data = array(
				'state'=>	"SUCCESS",
				'url'=>		url('/') . $config['pathFormat'] . '/' . $info->getSaveName(),
				'title'=>	$info->getFilename(),
				'original'=>$info->getFilename(),
				'type'=>	$file->getType(),
				'size'=>	$file->getSize(),
			);

		}else{
			$data = array(
				"state"=>	$file->getError()
			);
    	}
		return json_encode($data);
	}
	
	/**
	 * 
	 * Enter description here ...
	 */
	private function uploadBase64($config,$fieldName){
		$data = array();
		
		$base64Data = $_POST[$fieldName];

        $img = base64_decode($base64Data);
        $path = $this->getFullPath($config['pathFormat']);

        if(strlen($img)>$config['maxSize']){
        	$data['states'] = 'too large';
        	return json_encode($data);
        }
        
        $rootpath = $this->rootpath;
        // p($path);
        //替换随机字符串
        $imgname = uniqid().'.png';
        $filename = $path."/".$imgname;
        $this->createDir(ROOT_PATH ."public".$path);
        $flg = file_put_contents('.'.$filename,$img);
		if($flg){
			$data=array(
        		'state'=>'SUCCESS',
        		'url'=>url('/') . $filename,
        		'title'=>$imgname,
        		'original'=>'scrawl.png',
        		'type'=>'.png',
        		'size'=>strlen($img),
        	
        	);
        }else{
			$data=array(
        		'state'=>'cant write',
        	);
        }
        return json_encode($data);
	}
	
	/**
	 * 循环检测目录是否存在，并创建目录
	 */
	private function createDir($path){
	    if (!file_exists($path)){
	        $this->createDir(dirname($path));
	        mkdir($path, 0777);
	    }
	}

	/**
	 * 列出文件夹下所有文件，如果是目录则向下
	 */
	private function listFile($config){

		// p($config);
		$allowFiles = substr(str_replace(".", "|", join("", $config['allowFiles'])), 1);
		$size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $config['listSize'];
		$start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
		$end = $start + $size;
		
		$rootpath = $this->rootpath;
		
		$path = $_SERVER['DOCUMENT_ROOT'] . url('/') . $config['path'];

		$files = $this->getfiles($path, $allowFiles);

		if (!count($files)) {
		    return json_encode(array(
		        "state" => "no match file",
		        "list" => array(),
		        "start" => $start,
		        "total" => count($files)
		    ));
		}
		
		/* 获取指定范围的列表 */
		$len = count($files);
		for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
		    $list[] = $files[$i];
		}
		//倒序
		//for ($i = $end, $list = array(); $i < $len && $i < $end; $i++){
		//    $list[] = $files[$i];
		//}
		
		/* 返回数据 */
		$result = json_encode(array(
		    "state" => "SUCCESS",
		    "list" => $list,
		    "start" => $start,
		    "total" => count($files)
		));
		
		return $result;
	}
	
	/**
     * 规则替换命名文件
     * @param $path
     * @return string
     */
    private function getFullPath($path)
    {
        //替换日期事件
        $t = time();
        $d = explode('-', date("Y-y-m-d-H-i-s"));
        $format = $path;
        $format = str_replace("{yyyy}", $d[0], $format);
        $format = str_replace("{yy}", $d[1], $format);
        $format = str_replace("{mm}", $d[2], $format);
        $format = str_replace("{dd}", $d[3], $format);
        $format = str_replace("{hh}", $d[4], $format);
        $format = str_replace("{ii}", $d[5], $format);
        $format = str_replace("{ss}", $d[6], $format);
        
        return $format;
    }
    
	/**
	 * 遍历获取目录下的指定类型的文件
	 * @param $path
	 * @param array $files
	 * @return array
	 */
	private function getfiles($path, $allowFiles, &$files = array())
	{
	    if (!is_dir($path)) return null;
	    if(substr($path, strlen($path) - 1) != '/') $path .= '/';
	    $handle = opendir($path);
	    while (false !== ($file = readdir($handle))) {
	        if ($file != '.' && $file != '..') {
	            $path2 = $path . $file;
	            if (is_dir($path2)) {
	                $this->getfiles($path2, $allowFiles, $files);
	            } else {
	                if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
	                    $files[] = array(
	                        'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
	                        'mtime'=> filemtime($path2)
	                    );
	                }
	            }
	        }
	    }
	    return $files;
	}
	
}