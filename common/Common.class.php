<?php
class Common{

    public static function slimParseUrl($url){
        $path = $url['path'];
        $pathArr = explode('/', $path);
        return empty($pathArr[0]) ? 'index' : $pathArr[0];
    }

    public static function autoSession(){
        session_start();
    }

    /**
     * 优化require_once
     * @param string $file_path 文件地址
     * @return bool
     */
    public static function jkimport($file_path){
        static $_requireFiles = array();
        if (!isset($_requireFiles[$file_path])){
            $file_path = SLIM_ROOT.'/core/'.$file_path;
            if (file_exists($file_path)){
                require $file_path;
                $_requireFiles[$file_path] = true;
            }else{
                $_requireFiles[$file_path] = false;
            }
        }
        return $_requireFiles[$file_path];
    }

     public static function JKS($service){
        static $services = array();
        if(!isset($services[$service])){
            $service = 'S'.$service;
            $services[$service] = new $service();
        }
        return $services[$service];
    }

    function JKU($type,$args = array()){
        unset($args['m'],$args['a']);
        $types = explode('/',$type);
        if(count($types) > 2){
            $app = $types[0];
            $module = $types[1];
            $action = $types[2];
        }elseif(count($types) == 2){
            $app = APP_GROUP;
            $module = $types[0];
            $action = $types[1];
        }else{
            $app = APP_GROUP;
            $module = $types[0];
            $action = 'index';
        }
        return CUrl::get($app,$module,$action,$args);
    }

    function getWxPayUrl($oid){
        return SITE_PATH."payment/wx.php?oid={$oid}&wxref=mp.weixin.qq.com&s=".$GLOBALS['seller_info']['api_key'];
    }


    /**
     *
     * @param String $route app:module#action
     * @param String $param id=1&p=2
     * @return string
     */
    function url($route="",$param=array()){
        $key = md5("URL_KEY_".$route.serialize($param));
        static $weixin;
        $is_rewrite = conf("URL_MODEL");
        $is_rewrite = 0;
        $weixin = $GLOBALS['weixin_name'];
        $url_array = explode(":",$route);
        $app = isset($url_array[0])?strtolower(trim($url_array[0])):"";
        if(isset($GLOBALS[$key])){
            $url = $GLOBALS[$key];
            $return_url = $url;
            if($app=="wei" || $app=="wap"){
                if($is_rewrite==1){
                    $return_url.="?wxref=mp.weixin.qq.com&s=".$GLOBALS['seller_info']['api_key'];
                }else{
                    $return_url.="&wxref=mp.weixin.qq.com&s=".$GLOBALS['seller_info']['api_key'];
                }
                if($GLOBALS['new_weixin_info'] && $GLOBALS['weixin_info']){
                    $ckey = new CKey();
                    $weixin = base64_encode(base64_encode($ckey->encrypt($GLOBALS['weixin_info'],$GLOBALS['seller_info']['api_key'])));
                    $return_url.="&w=".$weixin;
                }
                $adm = strim($_REQUEST['adm']);
                if($adm){
                    $return_url.="&adm=".$adm;
                }
            }
            return $return_url;
        }

        $url = loadDynamicCache($key);
        if($url!==false){
            $GLOBALS[$key] = $url;
            $return_url = $url;
            if($app=="wei" || $app=="wap"){
                if($is_rewrite==1){
                    $return_url.="?wxref=mp.weixin.qq.com&s=".$GLOBALS['seller_info']['api_key'];
                }else{
                    $return_url.="&wxref=mp.weixin.qq.com&s=".$GLOBALS['seller_info']['api_key'];
                }
                if($GLOBALS['new_weixin_info'] && $GLOBALS['weixin_info']){
                    $ckey = new CKey();
                    $weixin = base64_encode(base64_encode($ckey->encrypt($GLOBALS['weixin_info'],$GLOBALS['seller_info']['api_key'])));
                    $return_url.="&w=".$weixin;
                }
                $adm = strim($_REQUEST['adm']);
                if($adm){
                    $return_url.="&adm=".$adm;
                }
            }
            return $return_url;
        }

        $module_and_action = isset($url_array[1])?strtolower(trim($url_array[1])):"";
        $module_and_action = str_replace('#','/',$module_and_action);
        $route_array = explode("/",$module_and_action);
        if(isset($param)&&$param != '' && !is_array($param)){
            $param['id'] = $param;
        }
        $module = isset($route_array[0])?strtolower(trim($route_array[0])):"";
        $action = isset($route_array[1])?strtolower(trim($route_array[1])):"";
        if(!$app||$app=='')$app="index";
        if(!$module||$module=='index')$module="index";
        if(!$action||$action=='index')$action="";

        if($is_rewrite==0){
            //原始模式
            if($app=="wei" || $app=="wap"){
                $url = "wap.php";
            }else{
                $url = $app.".php";
            }
            if($module!=''||$action!=''||count($param)>0){
                $url.="?";
            }
            if($module&&$module!=''){
                $url .= "m=".$module."&";
            }
            if($action&&$action!=''){
                $url .= "a=".$action."&";
            }
            if(count($param)>0){
                foreach($param as $k=>$v){
                    if($k&&$v){
                        $url =$url.$k."=".urlencode($v)."&";
                    }
                }
            }
            if(substr($url,-1,1)=='&'||substr($url,-1,1)=='?') $url = substr($url,0,-1);
            $return_url = $url;
            if($app=="wei" || $app=="wap"){
                $return_url .= "&wxref=mp.weixin.qq.com&s=".$GLOBALS['seller_info']['api_key'];
                if($GLOBALS['new_weixin_info'] && $GLOBALS['weixin_info']){
                    $ckey = new CKey();
                    $weixin = base64_encode(base64_encode($ckey->encrypt($GLOBALS['weixin_info'],$GLOBALS['seller_info']['api_key'])));
                    $return_url.="&w=".$weixin;
                }
                $adm = strim($_REQUEST['adm']);
                if($adm){
                    $return_url.="&adm=".$adm;
                }
            }
            $GLOBALS[$key] = $url;
            setDynamicCache($key,$url);
            return $return_url;
        }else{
            //重写的默认
            if($app=="wei" || $app=="wap"){
                $url = 'wap';
            }else{
                $url = $app;
            }
            if($module && $module!=''){
                $url .= "/".$module;
            }
            if($action && $action!=''){
                $url .= "-".$action;
            }
            if(count($param)>0){
                $url.="/";
                foreach($param as $k=>$v){
                    $url =$url.$k."-".urlencode($v)."-";
                }
            }
            $route = $module."/".$action;
            switch ($route){
            case "xxx":
                break;
            default:
                break;
            }

            if(substr($url,-1,1)=='/'||substr($url,-1,1)=='-') $url = substr($url,0,-1);
            if($url!=''){
                if($module!=""||$action!=""){
                    $url.= REWRITER_EXT;
                }
            }
            $return_url = $url;
            if($app=="wei" || $app=="wap"){
                $return_url.="?wxref=mp.weixin.qq.com&s=".$GLOBALS['seller_info']['api_key'];
                if($GLOBALS['new_weixin_info'] && $GLOBALS['weixin_info']){
                    $ckey = new CKey();
                    $weixin = base64_encode(base64_encode($ckey->encrypt($GLOBALS['weixin_info'],$GLOBALS['seller_info']['api_key'])));
                    $return_url.="&w=".$weixin;
                }
                $adm = strim($_REQUEST['adm']);
                if($adm){
                    $return_url.="&adm=".$adm;
                }
            }
            $GLOBALS[$key] = $url;
            setDynamicCache($key,$url);
            return $return_url;
        }
    }

    //载入动态缓存数据
    function loadDynamicCache($name){
        if(isset($GLOBALS['dynamic_cache'][$name])){
            return $GLOBALS['dynamic_cache'][$name];
        }else{
            return false;
        }
    }

    function setDynamicCache($name,$value){
        if(!isset($GLOBALS['dynamic_cache'][$name])){
            if(isset($GLOBALS['dynamic_cache']) && count($GLOBALS['dynamic_cache']) > MAX_DYNAMIC_CACHE_SIZE){
                array_shift($GLOBALS['dynamic_cache']);
            }
            $GLOBALS['dynamic_cache'][$name] = $value;
        }
    }

    //utf8 字符串截取
    function msubstr($str, $start=0, $length=15, $charset="utf-8", $suffix=true)
    {
        if(function_exists("mb_substr"))
        {
            $slice =  mb_substr($str, $start, $length, $charset);
            if($suffix&$slice!=$str) return $slice."…";
            return $slice;
        }
        elseif(function_exists('iconv_substr')) {
            return iconv_substr($str,$start,$length,$charset);
        }
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("",array_slice($match[0], $start, $length));
        if($suffix&&$slice!=$str) return $slice."…";
        return $slice;
    }

    //解析URL标签
    // $str = u:index:acate#index|id=10&name=abc
    function parse_url_tag($str)
    {
        $str = substr($str,2);
        $str_array = explode("|",$str);
        $route = $str_array[0];
        $param_tmp = explode("&",$str_array[1]);
        $param = array();
        foreach($param_tmp as $item)
        {
            if($item!='')
                $item_arr = explode("=",$item);
            if(isset($item_arr[0])&&isset($item_arr[1]))
                $param[$item_arr[0]] = $item_arr[1];
        }
        $url = url($route,$param);

        return $url;
    }

    //编译生成css文件
    /**
     *
     * @param unknown_type $urls
     * @param unknown_type $type index|list|detail|""
     * @return string
     */
    function parse_css($urls, $type = "") {
        $url = md5(implode(',', $urls) . $type);
        $css_url = 'public/tpl/css/' . $url . '.css';
        $folder = JK_ROOT . 'public/tpl/css/';
        $url_path = JK_ROOT . $css_url;

        if (!file_exists($url_path) || IS_DEBUG) {
            if ($type != "index"  && $type != "channel"  && $type != "nav"   && $type != "page_nav"   && $type != "list" && $type != "detail" && $type != "catering" && $type != "market"&& $type != "coupon"&& $type != "greetcard")
                $type = "";
            if (!file_exists($folder))
                makeDir($folder, 0777);
            $tmpl_path = TPL_PATH;

            $css_content = '';

            foreach ($urls as $url) {
                if ($type == "") {
                    $url = TPL_ROOT . "common/" . $url;
                }elseif($type=="greetcard"){
                    $url = TPL_ROOT . $type . "/" . $url;
                }else {
                    $moban = $GLOBALS['seller_info'][$type . "_moban"];
                    $url = TPL_ROOT . $type . "/" . $moban . "/" . $url;
                }
                $css_content .= @file_get_contents($url);
            }
            $css_content = preg_replace("/[\r\n]/", '', $css_content);
            if ($type == "")
                $css_content = str_replace("../images/", $tmpl_path . "common/images/", $css_content);
            elseif($type=="greetcard"){
                $css_content = str_replace("../images/", $tmpl_path. $type . "/" . $moban . "/images/", $css_content);
            }
            else {
                $moban = $GLOBALS['seller_info'][$type . "_moban"];
                $css_content = str_replace("../images/", $tmpl_path. $type . "/" . $moban . "/images/", $css_content);
            }
            @file_put_contents($url_path, $css_content);
        }
        return RESOURCE_URL . $css_url;
    }

    /**
     *
     * @param $urls 载入的脚本
     * @param $encode_url 需加密的脚本
     */
    function parse_script($urls, $encode_url = array(), $type = "") {
        $url = md5(implode(',', $urls) . $type);
        $js_url = 'public/tpl/js/' . $url . '.js';
        $url_path = JK_ROOT . $js_url;
        $folder = JK_ROOT.'public/tpl/js/';
        if (!file_exists($url_path) || IS_DEBUG) {
            if ($type != "index" && $type != "channel" && $type != "nav"   && $type != "page_nav"  && $type != "list" && $type != "detail" && $type != "catering" && $type != "market"&& $type != "coupon"&& $type != "greetcard")
                $type = "";
            if (!file_exists($folder))
                makeDir($folder, 0777);

            $js_content = '';
            foreach ($urls as $url) {
                if ($type == "") {
                    $url = TPL_ROOT . "common/" . $url;
                }elseif($type=="greetcard") {
                    $url = TPL_ROOT . $type . "/" . $url;
                }
                else {
                    $moban = $GLOBALS['seller_info'][$type . "_moban"];
                    $url = TPL_ROOT . $type . "/" . $moban . "/" . $url;
                }

                $append_content = @file_get_contents($url) . "\r\n";
                if (in_array($url, $encode_url)) {
                    $packer = new CJavaScriptPacker($append_content);
                    $append_content = $packer->pack();
                }
                $js_content .= $append_content;
            }
            //		require_once APP_ROOT_PATH."system/libs/javascriptpacker.php";
            //	    $packer = new JavaScriptPacker($js_content);
            //		$js_content = $packer->pack();
            @file_put_contents($url_path, $js_content);
        }
        return RESOURCE_URL . $js_url;
    }

    function conf($name){
        return Jike::$conf[$name];
    }

    function lang($name=null,$value=null){
        static $_lang = array();
        if(empty($name)){
            return $_lang;
        }
        if(is_string($name)){
            $name = strtoupper($name);
            if (is_null($value)){
                return isset($_lang[$name]) ? $_lang[$name] : $name;
            }
            $_lang[$name] = $value;
            return;
        }
        if (is_array($name)){
            $_lang = array_merge($_lang, array_change_key_case($name, CASE_UPPER));
        }
        return;
    }

    /**
     * 字符串命名风格转换
     * type 0 将Java风格转换为C的风格 1 将C风格转换为Java的风格
     * @param string $name 字符串
     * @param integer $type 转换类型
     * @return string
     */
    function parse_name($name, $type=0) {
        if ($type) {
            return ucfirst(preg_replace("/_([a-zA-Z])/e", "strtoupper('\\1')", $name));
        } else {
            return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
        }
    }
    /**
     * 获取和设置配置参数 支持批量定义
     * @param string|array $name 配置变量
     * @param mixed $value 配置值
     * @return mixed
     */
    function C($name=null, $value=null) {
        static $_config = array();
        // 无参数时获取所有
        if (empty($name)) {
            if(!empty($value) && $array = S('c_'.$value)) {
                $_config = array_merge($_config, array_change_key_case($array));
            }
            return $_config;
        }
        // 优先执行设置获取或赋值
        if (is_string($name)) {
            if (!strpos($name, '.')) {
                $name = strtolower($name);
                if (is_null($value))
                    return isset($_config[$name]) ? $_config[$name] : null;
                $_config[$name] = $value;
                return;
            }
            // 二维数组设置和获取支持
            $name = explode('.', $name);
            $name[0]   =  strtolower($name[0]);
            if (is_null($value))
                return isset($_config[$name[0]][$name[1]]) ? $_config[$name[0]][$name[1]] : null;
            $_config[$name[0]][$name[1]] = $value;
            return;
        }
        // 批量设置
        if (is_array($name)){
            $_config = array_merge($_config, array_change_key_case($name));
            if(!empty($value)) {// 保存配置值
                S('c_'.$value,$_config);
            }
            return;
        }
        return null; // 避免非法参数
    }

    function echoFlush($str)
    {
        echo str_repeat(' ',4096).' ';
        echo $str;
    }


    function replacePublic($content){
        $content = trim($content);
        $content = preg_replace('/^'.preg_quote(SITE_URL,'/')."public\//","./public/",$content);
        $content = preg_replace('/^'.preg_quote(SITE_PATH,'/')."public\//","./public/",$content);
        return $content;
    }

    function getDirsById($id){
        $id = sprintf("%011d", $id);
        $dir1 = substr($id, 0, 3);
        $dir2 = substr($id, 3, 3);
        $dir3 = substr($id, 6, 3);
        $dir4 = substr($id, -2);
        return $dir1.'/'.$dir2.'/'.$dir3.'/'.$dir4;
    }

    //检查目标文件夹是否存在，如果不存在则自动创建该目录
    function makeDir($dir){
        do {
            $mdir = $dir;
            while(!is_dir($mdir)) {
                $basedir = dirname($mdir);
                if($basedir == '/' || is_dir($basedir)){
                    mkdir($mdir,0777);
                }else{
                    $mdir = $basedir;
                }
            }
        }while($mdir != $dir);
        return true;
    }

    /**
     * 写入文件内容
     * @param string $filepat 文件路径
     * @param string $content 写入内容
     * @param string $type 写入方式 w:将文件指针指向文件头并将文件大小截为零 a:将文件指针指向文件末尾
     * @return string
     */
    function writeFile($filepath,$content,$type='w'){
        if($fp = fopen($filepath,$type)){
            @flock($fp, LOCK_EX);
            fwrite($fp, $content);
            @flock($fp,LOCK_UN);
            @fclose($fp);
            @chmod($filepath, 0777);
        }
    }

    /**
     * 清除指定目录下的文件
     * @param string $dir 目录路径
     * @return void
     */
    function clearDir($dir,$is_del_dir = false){
        if(!file_exists($dir)){
            return;
        }
        $directory = dir($dir);
        while($entry = $directory->read()){
            if($entry != '.' && $entry != '..'){
                $filename = $dir.'/'.$entry;
                if(is_dir($filename)){
                    clearDir($filename,$is_del_dir);
                }
                if(is_file($filename)){
                    @unlink($filename);
                }
            }
        }
        $directory->close();
        if($is_del_dir){
            @rmdir($dir);
        }
    }

    function getPhpSelf(){
        $php_self = '';
        $script_name = basename($_SERVER['SCRIPT_FILENAME']);
        if(basename($_SERVER['SCRIPT_NAME']) === $script_name){
            $php_self = $_SERVER['SCRIPT_NAME'];
        }else if(basename($_SERVER['PHP_SELF']) === $script_name){
            $php_self = $_SERVER['PHP_SELF'];
        }else if(isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $script_name){
            $php_self = $_SERVER['ORIG_SCRIPT_NAME'];
        }else if(($pos = strpos($_SERVER['PHP_SELF'],'/'.$script_name)) !== false){
            $php_self = substr($_SERVER['SCRIPT_NAME'],0,$pos).'/'.$script_name;
        }else if(isset($_SERVER['DOCUMENT_ROOT']) && strpos($_SERVER['SCRIPT_FILENAME'],$_SERVER['DOCUMENT_ROOT']) === 0){
            $php_self = str_replace('\\','/',str_replace($_SERVER['DOCUMENT_ROOT'],'',$_SERVER['SCRIPT_FILENAME']));
        }else{
            return false;
        }
        return $php_self;
    }

    function getClientIp(){
        static $ip = null;
        if($ip === null){
            $ip = $_SERVER['REMOTE_ADDR'];
            if(isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])){
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            }elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)){
                foreach ($matches[0] as $xip){
                    if (!preg_match('#^(10|172\.16|192\.168)\.#',$xip)){
                        $ip = $xip;
                        break;
                    }
                }
            }
        }
        return $ip;
    }

    function random($length,$numeric = false){
        $seed = base_convert(md5(microtime().$_SERVER['HTTP_USER_AGENT'].getClientIp()),16,$numeric ? 10 : 35);
        $seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
        if($numeric){
            $hash = '';
        }else{
            $hash = chr(rand(1, 26) + rand(0, 1) * 32 + 64);
            $length--;
        }
        $max = strlen($seed) - 1;
        for($i = 0; $i < $length; $i++){
            $hash .= $seed{mt_rand(0, $max)};
        }
        return $hash;
    }

    function strim($str){
        return htmlspecialchars(trim($str));
    }

    function btrim($str){
        return trim($str);
    }

    function validTag($str){
        return preg_replace("/<(?!a|pre|table|tbody|tr|th|td|div|ol|ul|li|sup|sub|span|br|img|audio|video|p|h1|h2|h3|h4|h5|h6|b|strong|em|u|hr\/|hr \/|\/a|\/pre|\/table|\/tbody|\/tr|\/th|\/td|\/div|\/ol|\/ul|\/li|\/sup|\/sub|\/span|\/br|\/img|\/audio|\/video|\/p|\/h1|\/h2|\/h3|\/h4|\/h5|\/h6|\/b|\/strong|\/em|\/u|blockquote|\/blockquote|strike|\/strike|b|\/b|i|\/i|u|\/u)[^>]*>/i","",$str);
    }

    //邮件格式验证的函数
    function checkEmail($email)
    {
        if(!preg_match("/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/",$email))
        {
            return false;
        }
        else
            return true;
    }

    //验证手机号码
    function checkMobile($mobile)
    {
        if(!empty($mobile) && !preg_match("/^\d{6,}$/",$mobile))
        {
            return false;
        }
        else
            return true;
    }

    function jkHtmlDecode($str){
        return htmlspecialchars_decode(stripslashes($str));
    }

    function jkHtmlspecialchars($request){
        if(empty($request)){
            return $request;
        }
        if(is_array($request)){
            $keys = array_keys($request);
            foreach($keys as $key){
                $val = $request[$key];
                unset($request[$key]);
                $request[$key] = jkHtmlspecialchars($val);
            }
        }else{
            $request = htmlspecialchars($request);
        }
        return $request;
    }

    function jkHtmlspecialcharsDecode($request){
        if(empty($request)){
            return $request;
        }
        if(is_array($request)){
            $keys = array_keys($request);
            foreach($keys as $key){
                $val = $request[$key];
                unset($request[$key]);
                $request[$key] = jkHtmlspecialcharsDecode($val);
            }
        }else{
            $request = htmlspecialchars_decode($request);
        }
        return $request;
    }

    /**
     * 分页处理
     * @param int $total_count 总数
     * @param int $page 当前页
     * @param int $page_size 分页大小
     * @return array
     */
    function buildPageSimple($total_count,$page = 1,$page_size = 0){
        $pager['total_count'] = (int)$total_count;
        $pager['page'] = $page;
        $pager['page_size'] = ($page_size == 0) ? 20 : $page_size;
        /* page 总数 */
        $pager['page_count'] = ($pager['total_count'] > 0) ? ceil($pager['total_count'] / $pager['page_size']) : 1;

        /* 边界处理 */
        if ($pager['page'] > $pager['page_count'])
            $pager['page'] = $pager['page_count'];

        $pager['limit'] = ($pager['page'] - 1) * $pager['page_size'] . "," . $pager['page_size'];

        $page_prev  = ($pager['page'] > 1) ? $pager['page'] - 1 : 1;
        $page_next  = ($pager['page'] < $pager['page_count']) ? $pager['page'] + 1 : $pager['page_count'];
        $pager['prev_page'] = $page_prev;
        $pager['next_page'] = $page_next;
        return $pager;
    }

    /**
     * 分页处理
     * @param string $type 所在页面
     * @param array  $args 参数
     * @param int $total_count 总数
     * @param int $page 当前页
     * @param int $page_size 分页大小
     * @param string $url 自定义路径
     * @param int $offset 偏移量
     * @return array
     */
    function buildPage($type,$args,$total_count,$page = 1,$page_size = 0,$url='',$offset = 5){
        $pager['total_count'] = intval($total_count);
        $pager['page'] = $page;
        $pager['page_size'] = ($page_size == 0) ? 20 : $page_size;
        /* page 总数 */
        $pager['page_count'] = ($pager['total_count'] > 0) ? ceil($pager['total_count'] / $pager['page_size']) : 1;

        /* 边界处理 */
        if ($pager['page'] > $pager['page_count'])
            $pager['page'] = $pager['page_count'];

        $pager['limit'] = ($pager['page'] - 1) * $pager['page_size'] . "," . $pager['page_size'];
        $page_prev  = ($pager['page'] > 1) ? $pager['page'] - 1 : 1;
        $page_next  = ($pager['page'] < $pager['page_count']) ? $pager['page'] + 1 : $pager['page_count'];
        $pager['prev_page'] = $page_prev;
        $pager['next_page'] = $page_next;

        if (!empty($url)){
            $pager['page_first'] = $url . 1;
            $pager['page_prev']  = $url . $page_prev;
            $pager['page_next']  = $url . $page_next;
            $pager['page_last']  = $url . $pager['page_count'];
        }
        else{
            $args['page'] = '_page_';
            if(!empty($type)){
                if(strpos($type,'javascript:') === false){
                    $page_url = JKU($type,$args);
                }else{
                    $page_url = $type;
                }
            }else{
                $page_url = 'javascript:;';
            }
            $pager['page_first'] = str_replace('_page_',1,$page_url);
            $pager['page_prev']  = str_replace('_page_',$page_prev,$page_url);
            $pager['page_next']  = str_replace('_page_',$page_next,$page_url);
            $pager['page_last']  = str_replace('_page_',$pager['page_count'],$page_url);
        }
        $pager['page_nums'] = array();
        if($pager['page_count'] <= $offset * 2){
            for ($i=1; $i <= $pager['page_count']; $i++){
                $pager['page_nums'][] = array('name' => $i,'url' => empty($url) ? str_replace('_page_',$i,$page_url) : $url . $i);
            }
        }else{
            if($pager['page'] - $offset < 2){
                $temp = $offset * 2;
                for ($i=1; $i<=$temp; $i++){
                    $pager['page_nums'][] = array('name' => $i,'url' => empty($url) ? str_replace('_page_',$i,$page_url) : $url . $i);
                }
                $pager['page_nums'][] = array('name'=>'...');
                $pager['page_nums'][] = array('name' => $pager['page_count'],'url' => empty($url) ? str_replace('_page_',$pager['page_count'],$page_url) : $url . $pager['page_count']);
            }else{
                $pager['page_nums'][] = array('name' => 1,'url' => empty($url) ? str_replace('_page_',1,$page_url) : $url . 1);
                $pager['page_nums'][] = array('name'=>'...');
                $start = $pager['page'] - $offset + 1;
                $end = $pager['page'] + $offset - 1;
                if($pager['page_count'] - $end > 1){
                    for ($i=$start;$i<=$end;$i++){
                        $pager['page_nums'][] = array('name' => $i,'url' => empty($url) ? str_replace('_page_',$i,$page_url) : $url . $i);
                    }

                    $pager['page_nums'][] = array('name'=>'...');
                    $pager['page_nums'][] = array('name' => $pager['page_count'],'url' => empty($url) ? str_replace('_page_',$pager['page_count'],$page_url) : $url . $pager['page_count']);
                }else{
                    $start = $pager['page_count'] - $offset * 2 + 1;
                    $end = $pager['page_count'];
                    for ($i=$start;$i<=$end;$i++){
                        $pager['page_nums'][] = array('name' => $i,'url' => empty($url) ? str_replace('_page_',$i,$page_url) : $url . $i);
                    }
                }
            }
        }
        return $pager;
    }

    /**
     * 保存图片
     * @param array $upd_file  即上传的$_FILES数组
     * @param array $key $_FILES 中的键名 为空则保存 $_FILES 中的所有图片
     * @param string $dir 保存到的目录
     * @param array $whs
     可生成多个缩略图
     数组 参数1 为宽度，
     参数2为高度，
     参数3为处理方式:0(缩放,默认)，1(剪裁)，
     参数4为是否水印 默认为 0(不生成水印)
     array(
         'thumb1'=>array(300,300,0,0),
         'thumb2'=>array(100,100,0,0),
         'origin'=>array(0,0,0,0),  宽与高为0为直接上传
         ...
     )，
     * @param array $is_water 原图是否水印
     * @return array
     array(
         'key'=>array(
             'name'=>图片名称，
             'url'=>原图web路径，
             'path'=>原图物理路径，
             有略图时
             'thumb'=>array(
                 'thumb1'=>array('url'=>web路径,'path'=>物理路径),
                 'thumb2'=>array('url'=>web路径,'path'=>物理路径),
                 ...
             )
         )
         ....
     )
     */
    //$img = save_image_upload($_FILES,'avatar','temp',array('avatar'=>array(300,300,1,1)),1);
    function saveImageUpload($upd_file, $key='',$dir='temp', $whs=array(),$is_water=false,$need_return = false){
        $image = new CImage();
        $image->max_size = intval(conf("MAX_IMAGE_SIZE"));
        $list = array();
        if(empty($key)){
            foreach($upd_file as $fkey=>$file){
                $list[$fkey] = false;
                $image->init($file,$dir);
                if($image->save()){
                    $list[$fkey] = array();
                    $list[$fkey]['url'] = $image->file['target'];
                    $list[$fkey]['path'] = $image->file['local_target'];
                    $list[$fkey]['name'] = $image->file['prefix'];
                    $list[$fkey]['size'] = $image->file['size'];
                }else{
                    if($image->error_code==-105){
                        if($need_return){
                            return array('error'=>1,'message'=>'上传的图片太大');
                        }else{
                            echo "上传的图片太大";
                        }
                    }elseif($image->error_code==-104||$image->error_code==-103||$image->error_code==-102||$image->error_code==-101){
                        if($need_return){
                            return array('error'=>1,'message'=>'非法图像');
                        }else{
                            echo "非法图像";
                        }
                    }
                    exit;
                }
            }
        }else{
            $list[$key] = false;
            $image->init($upd_file[$key],$dir);
            if($image->save()){
                $list[$key] = array();
                $list[$key]['url'] = $image->file['target'];
                $list[$key]['path'] = $image->file['local_target'];
                $list[$key]['name'] = $image->file['prefix'];
                $list[$key]['size'] = $image->file['size'];
            }else{
                if($image->error_code==-105){
                    if($need_return){
                        return array('error'=>1,'message'=>'上传的图片太大');
                    }else{
                        echo "上传的图片太大";
                    }
                }elseif($image->error_code==-104||$image->error_code==-103||$image->error_code==-102||$image->error_code==-101){
                    if($need_return){
                        return array('error'=>1,'message'=>'非法图像');
                    }else{
                        echo "非法图像";
                    }
                }
                exit;
            }
        }
        return $list;
    }
    /**
     *
     */
    function compare_check($type,$value,$limit_type,$limit_value){

        if(($limit_type==$type&&$value==$limit_value)){
            return 'checked="checked"';
        }else{
            if($value==0&&$limit_type!=$type){
                return 'checked="checked"';
            }else{
                return '';
            }

        }
    }
}
