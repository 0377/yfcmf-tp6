<?php
/**
 * *
 *  * ============================================================================
 *  * Created by PhpStorm.
 *  * User: Ice
 *  * 邮箱: ice@sbing.vip
 *  * 网址: https://sbing.vip
 *  * Date: 2019/9/19 下午3:20
 *  * ============================================================================.
 */

// 公共助手函数
use think\Model;
use think\facade\Lang;
use think\facade\Event;
use think\facade\Config;

if (! function_exists('tp5ControllerToTp6Controller')) {
    /**
     * TP5二级目录转TP6二级目录.
     *
     * @param  string  $class
     *
     * @return string
     */
    function tp5ControllerToTp6Controller($class = '')
    {
        $_arr = explode('/', $class);
        $route = $class;
        if (count($_arr) >= 3) {
            $route = '';
            foreach ($_arr as $_k => $_v) {
                $route .= $_v;
                ($_k == 0) ? $route .= '.' : $route .= '/';
            }
            $route = rtrim($route, '/');
        } elseif (count($_arr) == 2) {
            $route = implode('.', $_arr).'/index';
        }

        return $route;
    }
}
if (! function_exists('model')) {
    /**
     * 实例化Model.
     *
     * @param  string  $name
     * @param  string  $layer
     * @param  bool  $appendSuffix
     *
     * @throws \think\Exception
     * @return Model
     */
    function model($name = '', $layer = 'model', $appendSuffix = false)
    {
        if (class_exists($name)) {
            return new $name();
        }
        $class = app()->getNamespace().'\\'.$layer.'\\'.$name;
        if (class_exists($class)) {
            return new $class();
        }
        $class = 'app\\common\\'.$layer.'\\'.$name;
        if (class_exists($class)) {
            return new $class();
        } else {
            throw new \think\Exception('model not found');
        }
    }
}

/**
 * 处理插件钩子.
 *
 * @param  string  $event  钩子名称
 * @param  array|null  $params  传入参数
 * @param  bool  $once
 *
 * @return mixed
 */
function hook($event, $params = null, bool $once = false)
{
    return Event::trigger($event, $params, $once);
}

/**
 * 获得插件列表.
 *
 * @return array
 */
function get_addon_list()
{
    $results = scandir(ADDON_PATH);
    $list = [];
    foreach ($results as $name) {
        if ($name === '.' or $name === '..') {
            continue;
        }
        if (is_file(ADDON_PATH.$name)) {
            continue;
        }
        $addonDir = ADDON_PATH.$name.DIRECTORY_SEPARATOR;
        if (! is_dir($addonDir)) {
            continue;
        }

        if (! is_file($addonDir.ucfirst($name).'.php')) {
            continue;
        }

        //这里不采用get_addon_info是因为会有缓存
        //$info = get_addon_info($name);
        $info_file = $addonDir.'info.ini';
        if (! is_file($info_file)) {
            continue;
        }
        $info = parse_ini_file($info_file, true, INI_SCANNER_TYPED) ?: [];
        //$info = Config::parse($info_file, '', "addon-info-{$name}");
        if (! isset($info['name'])) {
            continue;
        }
        $info['url'] = addon_url($name);
        $list[$name] = $info;
    }

    return $list;
}

/**
 * 获得插件内的服务类.
 *
 * @return array
 */
function get_addon_service()
{
    $addons = get_addon_list();
    $list = [];
    foreach ($addons as $name => $addon) {
        if (! $addon['state']) {
            continue;
        }
        $addonServiceDir = ADDON_PATH.$name.DIRECTORY_SEPARATOR.'service'.DIRECTORY_SEPARATOR;

        if (! is_dir($addonServiceDir)) {
            continue;
        }

        $service_files = is_dir($addonServiceDir) ? scandir($addonServiceDir) : [];
        $namespace = 'addons\\'.$name.'\\service\\';
        foreach ($service_files as $file) {
            if (strpos($file, '.php')) {
                $className = str_replace('.php', '', $file);
                $class = $namespace.$className;
                if (class_exists($class)) {
                    $list[] = $class;
                }

            }
        }
    }

    return $list;
}

/**
 * 获得插件自动加载的配置.
 *
 * @param  bool  $truncate  是否清除手动配置的钩子
 *
 * @return array
 */
function get_addon_autoload_config($truncate = false)
{
    // 读取addons的配置
    $config = (array) Config::get('addons');
    if ($truncate) {
        // 清空手动配置的钩子
        $config['hooks'] = [];
    }
    $route = [];
    // 读取插件目录及钩子列表
    $base = get_class_methods('\\think\\Addons');
    $base = array_merge($base, ['install', 'uninstall', 'enable', 'disable']);
    $url_domain_deploy = false;
    $addons = get_addon_list();
    $domain = [];
    foreach ($addons as $name => $addon) {
        if (! $addon['state']) {
            continue;
        }

        // 读取出所有公共方法
        $methods = (array) get_class_methods('\\addons\\'.$name.'\\'.ucfirst($name));
        // 跟插件基类方法做比对，得到差异结果
        $hooks = array_diff($methods, $base);
        // 循环将钩子方法写入配置中
        foreach ($hooks as $hook) {
            $hook = parseName($hook, 0, false);
            if (! isset($config['hooks'][$hook])) {
                $config['hooks'][$hook] = [];
            }
            // 兼容手动配置项
            if (is_string($config['hooks'][$hook])) {
                $config['hooks'][$hook] = explode(',', $config['hooks'][$hook]);
            }
            if (! in_array($name, $config['hooks'][$hook])) {
                $config['hooks'][$hook][] = $name;
            }
        }
        $conf = get_addon_config($addon['name']);
        if ($conf) {
            $conf['rewrite'] = isset($conf['rewrite']) && is_array($conf['rewrite']) ? $conf['rewrite'] : [];
            $rule = array_map(function ($value) use ($addon) {
                return "{$addon['name']}/{$value}";
            }, array_flip($conf['rewrite']));
            if (isset($conf['domain']) && $conf['domain']) {
                $domain[] = [
                    'addon'  => $addon['name'],
                    'domain' => $conf['domain'],
                    'rule'   => $rule,
                ];
            } else {
                $route = array_merge($route, $rule);
            }
        }
    }
    $config['service'] = get_addon_service();
    $config['route'] = $route;
    $config['route'] = array_merge($config['route'], $domain);

    return $config;
}

/**
 * 获取插件类的类名.
 *
 * @param  string  $name  插件名
 * @param  string  $type  返回命名空间类型
 * @param  string  $class  当前类名
 *
 * @return string
 */
function get_addon_class($name, $type = 'hook', $class = null)
{
    $name = parseName($name);
    // 处理多级控制器情况
    if (! is_null($class) && strpos($class, '.')) {
        $class = explode('.', $class);

        $class[count($class) - 1] = parseName(end($class), 1);
        $class = implode('\\', $class);
    } else {
        $class = parseName(is_null($class) ? $name : $class, 1);
    }
    switch ($type) {
        case 'controller':
            $namespace = '\\addons\\'.$name.'\\controller\\'.$class;
            break;
        default:
            $namespace = '\\addons\\'.$name.'\\'.$class;
    }

    return class_exists($namespace) ? $namespace : '';
}

/**
 * 读取插件的基础信息.
 *
 * @param  string  $name  插件名
 *
 * @return array
 */
function get_addon_info($name)
{
    $addon = get_addon_instance($name);
    if (! $addon) {
        return [];
    }

    return $addon->getInfo($name);
}

/**
 * 获取插件类的配置数组.
 *
 * @param  string  $name  插件名
 *
 * @return array
 */
function get_addon_fullconfig($name)
{
    $addon = get_addon_instance($name);
    if (! $addon) {
        return [];
    }

    return $addon->getFullConfig($name);
}

/**
 * 获取插件类的配置值值
 *
 * @param  string  $name  插件名
 *
 * @return array
 */
function get_addon_config($name)
{
    $addon = get_addon_instance($name);
    if (! $addon) {
        return [];
    }

    return $addon->getConfig($name);
}

/**
 * 获取插件的单例.
 *
 * @param  string  $name  插件名
 *
 * @return mixed|null
 */
function get_addon_instance($name)
{
    static $_addons = [];
    if (isset($_addons[$name])) {
        return $_addons[$name];
    }
    $class = get_addon_class($name);
    if (class_exists($class)) {
        $_addons[$name] = new $class();

        return $_addons[$name];
    } else {
        return;
    }
}

if (! function_exists('remove_empty_folder')) {
    /**
     * 移除空目录
     * @param string $dir 目录
     */
    function remove_empty_folder($dir)
    {
        try {
            $isDirEmpty = !(new \FilesystemIterator($dir))->valid();
            if ($isDirEmpty) {
                @rmdir($dir);
                remove_empty_folder(dirname($dir));
            }
        } catch (\UnexpectedValueException $e) {

        } catch (\Exception $e) {

        }
    }
}

if (! function_exists('get_addon_tables')) {
    /**
     * 获取插件创建的表
     * @param string $name 插件名
     * @return array
     */
    function get_addon_tables($name)
    {
        $addonInfo = get_addon_info($name);
        if (!$addonInfo) {
            return [];
        }
        $regex = "/^CREATE\s+TABLE\s+(IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z_]+)`?/mi";
        $sqlFile = ADDON_PATH . $name . DS . 'install.sql';
        $tables = [];
        if (is_file($sqlFile)) {
            preg_match_all($regex, file_get_contents($sqlFile), $matches);
            if ($matches && isset($matches[2]) && $matches[2]) {
                $prefix = env('database.prefix');
                $tables = array_map(function ($item) use ($prefix) {
                    return str_replace("__PREFIX__", $prefix, $item);
                }, $matches[2]);
            }
        }
        return $tables;
    }
}


/**
 * 插件显示内容里生成访问插件的url.
 *
 * @param  string  $url  地址 格式：插件名/控制器/方法
 * @param  array  $vars  变量参数
 * @param  bool|string  $suffix  生成的URL后缀
 * @param  bool|string  $domain  域名
 *
 * @return bool|string
 */
function addon_url($url, $vars = [], $suffix = true, $domain = false)
{
    $url = ltrim($url, '/');
    $addon = substr($url, 0, stripos($url, '/'));
    if (! is_array($vars)) {
        parse_str($vars, $params);
        $vars = $params;
    }
    $params = [];
    foreach ($vars as $k => $v) {
        if (substr($k, 0, 1) === ':') {
            $params[$k] = $v;
            unset($vars[$k]);
        }
    }
    $val = "@addons/{$url}";
    $config = get_addon_config($addon);

    $rewrite = $config && isset($config['rewrite']) && $config['rewrite'] ? $config['rewrite'] : [];

    if ($rewrite) {
        $path = substr($url, stripos($url, '/') + 1);
        if (isset($rewrite[$path]) && $rewrite[$path]) {
            $val = $rewrite[$path];
            array_walk($params, function ($value, $key) use (&$val) {
                $val = str_replace("[{$key}]", $value, $val);
            });
            $val = str_replace(['^', '$'], '', $val);
            if (substr($val, -1) === '/') {
                $suffix = false;
            }
        } else {
            // 如果采用了域名部署,则需要去掉前两段
            /*if ($indomain && $domainprefix) {
                $arr = explode("/", $val);
                $val = implode("/", array_slice($arr, 2));
            }*/
        }
    } else {
        // 如果采用了域名部署,则需要去掉前两段
        /*if ($indomain && $domainprefix) {
            $arr = explode("/", $val);
            $val = implode("/", array_slice($arr, 2));
        }*/
        foreach ($params as $k => $v) {
            $vars[substr($k, 1)] = $v;
        }
    }
    $url = url($val, [], $suffix, $domain).($vars ? '?'.http_build_query($vars) : '');
    $url = preg_replace("/\/((?!index)[\w]+)\.php\//i", '/', $url);

    return $url;
}

/**
 * 设置基础配置信息.
 *
 * @param  string  $name  插件名
 * @param  array  $array  配置数据
 *
 * @throws Exception
 * @return bool
 */
function set_addon_info($name, $array)
{
    $file = ADDON_PATH.$name.DIRECTORY_SEPARATOR.'info.ini';
    $addon = get_addon_instance($name);
    $array = $addon->setInfo($name, $array);
    if (! isset($array['name']) || ! isset($array['title']) || ! isset($array['version'])) {
        throw new Exception('插件配置写入失败');
    }
    $res = [];
    foreach ($array as $key => $val) {
        if (is_array($val)) {
            $res[] = "[$key]";
            foreach ($val as $skey => $sval) {
                $res[] = "$skey = ".(is_numeric($sval) ? $sval : $sval);
            }
        } else {
            $res[] = "$key = ".(is_numeric($val) ? $val : $val);
        }
    }
    if ($handle = fopen($file, 'w')) {
        fwrite($handle, implode("\n", $res)."\n");
        fclose($handle);
        //清空当前配置缓存
        Config::set([$name => null], 'addoninfo');
    } else {
        throw new Exception('文件没有写入权限');
    }

    return true;
}

/**
 * 写入配置文件.
 *
 * @param  string  $name  插件名
 * @param  array  $config  配置数据
 * @param  bool  $writefile  是否写入配置文件
 *
 * @throws Exception
 * @return bool
 */
function set_addon_config($name, $config, $writefile = true)
{
    $addon = get_addon_instance($name);
    $addon->setConfig($name, $config);
    $fullconfig = get_addon_fullconfig($name);
    foreach ($fullconfig as $k => &$v) {
        if (isset($config[$v['name']])) {
            $value = $v['type'] !== 'array' && is_array($config[$v['name']]) ? implode(',',
                $config[$v['name']]) : $config[$v['name']];
            $v['value'] = $value;
        }
    }
    if ($writefile) {
        // 写入配置文件
        set_addon_fullconfig($name, $fullconfig);
    }

    return true;
}

/**
 * 写入配置文件.
 *
 * @param  string  $name  插件名
 * @param  array  $array  配置数据
 *
 * @throws Exception
 * @return bool
 */
function set_addon_fullconfig($name, $array)
{
    $file = ADDON_PATH.$name.DIRECTORY_SEPARATOR.'config.php';
    if (! is_really_writable($file)) {
        throw new Exception('文件没有写入权限');
    }
    if ($handle = fopen($file, 'w')) {
        fwrite($handle, "<?php\n\n".'return '.varexport($array, true).";\n");
        fclose($handle);
    } else {
        throw new Exception('文件没有写入权限');
    }

    return true;
}

if (! function_exists('input_token')) {
    /**
     * 生成表单令牌.
     *
     * @param  string  $name  令牌名称
     *
     * @return string
     */
    function input_token($name = '__token__')
    {
        return '<input type="hidden" name="'.$name.'" value="'.token($name).'" />';
    }
}
if (! function_exists('parseName')) {
    function parseName($name, $type = 0, $ucfirst = true)
    {
        if ($type) {
            $name = preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name);

            return $ucfirst ? ucfirst($name) : lcfirst($name);
        }

        return strtolower(trim(preg_replace('/[A-Z]/', '_\\0', $name), '_'));
    }
}
if (! function_exists('__')) {

    /**
     * 获取语言变量值
     *
     * @param  string  $name  语言变量名
     * @param  array  $vars  动态变量值
     * @param  string  $lang  语言
     *
     * @return mixed
     */
    function __($name, $vars = [], $lang = '')
    {
        if (is_numeric($name) || ! $name) {
            return $name;
        }
        if (! is_array($vars)) {
            $vars = func_get_args();
            array_shift($vars);
            $lang = '';
        }

        return Lang::get($name, $vars, $lang);
    }
}

if (! function_exists('format_bytes')) {

    /**
     * 将字节转换为可读文本.
     *
     * @param  int  $size  大小
     * @param  string  $delimiter  分隔符
     *
     * @return string
     */
    function format_bytes($size, $delimiter = '')
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        for ($i = 0; $size >= 1024 && $i < 6; $i++) {
            $size /= 1024;
        }

        return round($size, 2).$delimiter.$units[$i];
    }
}

if (! function_exists('datetime')) {

    /**
     * 将时间戳转换为日期时间.
     *
     * @param  int  $time  时间戳
     * @param  string  $format  日期时间格式
     *
     * @return string
     */
    function datetime($time, $format = 'Y-m-d H:i:s')
    {
        $time = is_numeric($time) ? $time : strtotime($time);

        return date($format, $time);
    }
}

if (! function_exists('human_date')) {

    /**
     * 获取语义化时间.
     *
     * @param  int  $time  时间
     * @param  int  $local  本地时间
     *
     * @return string
     */
    function human_date($time, $local = null)
    {
        return \fast\Date::human($time, $local);
    }
}

if (! function_exists('cdnurl')) {

    /**
     * 获取上传资源的CDN的地址
     *
     * @param  string  $url  资源相对地址
     * @param  bool  $domain  是否显示域名 或者直接传入域名
     *
     * @return string
     */
    function cdnurl($url, $domain = false)
    {
        $regex = "/^((?:[a-z]+:)?\/\/|data:image\/)(.*)/i";
        $url = preg_match($regex, $url) ? $url : \think\facade\Config::get('upload.cdnurl').$url;
        if ($domain && ! preg_match($regex, $url)) {
            $domain = is_bool($domain) ? request()->domain() : $domain;
            $url = $domain.$url;
        }

        return $url;
    }
}

if (! function_exists('is_really_writable')) {

    /**
     * 判断文件或文件夹是否可写.
     *
     * @param  string  $file  文件或目录
     *
     * @return bool
     */
    function is_really_writable($file)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return is_writable($file);
        }
        if (is_dir($file)) {
            $file = rtrim($file, '/').'/'.md5(mt_rand());
            if (($fp = @fopen($file, 'ab')) === false) {
                return false;
            }
            fclose($fp);
            @chmod($file, 0777);
            @unlink($file);

            return true;
        } elseif (! is_file($file) or ($fp = @fopen($file, 'ab')) === false) {
            return false;
        }
        fclose($fp);

        return true;
    }
}

if (! function_exists('rmdirs')) {

    /**
     * 删除文件夹.
     *
     * @param  string  $dirname  目录
     * @param  bool  $withself  是否删除自身
     *
     * @return bool
     */
    function rmdirs($dirname, $withself = true)
    {
        if (! is_dir($dirname)) {
            return false;
        }
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirname, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        if ($withself) {
            @rmdir($dirname);
        }

        return true;
    }
}

if (! function_exists('copydirs')) {

    /**
     * 复制文件夹.
     *
     * @param  string  $source  源文件夹
     * @param  string  $dest  目标文件夹
     */
    function copydirs($source, $dest)
    {
        if (! is_dir($dest)) {
            mkdir($dest, 0755, true);
        }
        foreach (
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            ) as $item
        ) {
            if ($item->isDir()) {
                $sontDir = $dest.DIRECTORY_SEPARATOR.$iterator->getSubPathName();
                if (! is_dir($sontDir)) {
                    mkdir($sontDir, 0755, true);
                }
            } else {
                copy($item, $dest.DIRECTORY_SEPARATOR.$iterator->getSubPathName());
            }
        }
    }
}

if (! function_exists('mb_ucfirst')) {
    function mb_ucfirst($string)
    {
        return mb_strtoupper(mb_substr($string, 0, 1)).mb_strtolower(mb_substr($string, 1));
    }
}

if (! function_exists('addtion')) {

    /**
     * 附加关联字段数据.
     *
     * @param  array  $items  数据列表
     * @param  mixed  $fields  渲染的来源字段
     *
     * @return array
     */
    function addtion($items, $fields)
    {
        if (! $items || ! $fields) {
            return $items;
        }
        $fieldsArr = [];
        if (! is_array($fields)) {
            $arr = explode(',', $fields);
            foreach ($arr as $k => $v) {
                $fieldsArr[$v] = ['field' => $v];
            }
        } else {
            foreach ($fields as $k => $v) {
                if (is_array($v)) {
                    $v['field'] = isset($v['field']) ? $v['field'] : $k;
                } else {
                    $v = ['field' => $v];
                }
                $fieldsArr[$v['field']] = $v;
            }
        }
        foreach ($fieldsArr as $k => &$v) {
            $v = is_array($v) ? $v : ['field' => $v];
            $v['display'] = isset($v['display']) ? $v['display'] : str_replace(['_ids', '_id'], ['_names', '_name'],
                $v['field']);
            $v['primary'] = isset($v['primary']) ? $v['primary'] : '';
            $v['column'] = isset($v['column']) ? $v['column'] : 'name';
            $v['model'] = isset($v['model']) ? $v['model'] : '';
            $v['table'] = isset($v['table']) ? $v['table'] : '';
            $v['name'] = isset($v['name']) ? $v['name'] : str_replace(['_ids', '_id'], '', $v['field']);
        }
        unset($v);
        $ids = [];
        $fields = array_keys($fieldsArr);
        foreach ($items as $k => $v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $ids[$n] = array_merge(isset($ids[$n]) && is_array($ids[$n]) ? $ids[$n] : [], explode(',', $v[$n]));
                }
            }
        }
        $result = [];
        foreach ($fieldsArr as $k => $v) {
            if ($v['model']) {
                $model = new $v['model']();
            } else {
                $model = $v['name'] ? \think\facade\Db::name($v['name']) : \think\facade\Db::table($v['table']);
            }
            $primary = $v['primary'] ? $v['primary'] : $model->getPk();
            $result[$v['field']] = $model->where($primary, 'in',
                $ids[$v['field']])->column("{$primary},{$v['column']}");
        }

        foreach ($items as $k => &$v) {
            foreach ($fields as $m => $n) {
                if (isset($v[$n])) {
                    $curr = array_flip(explode(',', $v[$n]));

                    $v[$fieldsArr[$n]['display']] = implode(',', array_intersect_key($result[$n], $curr));
                }
            }
        }

        return $items;
    }
}

if (! function_exists('var_export_short')) {

    /**
     * 返回打印数组结构.
     *
     * @param  string  $var  数组
     * @param  string  $indent  缩进字符
     *
     * @return string
     */
    function var_export_short($var, $indent = '')
    {
        switch (gettype($var)) {
            case 'string':
                return '"'.addcslashes($var, "\\\$\"\r\n\t\v\f").'"';
            case 'array':
                $indexed = array_keys($var) === range(0, count($var) - 1);
                $r = [];
                foreach ($var as $key => $value) {
                    $r[] = "$indent    "
                        .($indexed ? '' : var_export_short($key).' => ')
                        .var_export_short($value, "$indent    ");
                }

                return "[\n".implode(",\n", $r)."\n".$indent.']';
            case 'boolean':
                return $var ? 'TRUE' : 'FALSE';
            default:
                return var_export($var, true);
        }
    }
}

if (! function_exists('letter_avatar')) {
    /**
     * 首字母头像.
     *
     * @param $text
     *
     * @return string
     */
    function letter_avatar($text)
    {
        $total = unpack('L', hash('adler32', $text, true))[1];
        $hue = $total % 360;
        [$r, $g, $b] = hsv2rgb($hue / 360, 0.3, 0.9);

        $bg = "rgb({$r},{$g},{$b})";
        $color = '#ffffff';
        $first = mb_strtoupper(mb_substr($text, 0, 1));
        $src = base64_encode('<svg xmlns="http://www.w3.org/2000/svg" version="1.1" height="100" width="100"><rect fill="'.$bg.'" x="0" y="0" width="100" height="100"></rect><text x="50" y="50" font-size="50" text-copy="fast" fill="'.$color.'" text-anchor="middle" text-rights="admin" alignment-baseline="central">'.$first.'</text></svg>');
        $value = 'data:image/svg+xml;base64,'.$src;

        return $value;
    }
}

if (! function_exists('hsv2rgb')) {
    function hsv2rgb($h, $s, $v)
    {
        $r = $g = $b = 0;

        $i = floor($h * 6);
        $f = $h * 6 - $i;
        $p = $v * (1 - $s);
        $q = $v * (1 - $f * $s);
        $t = $v * (1 - (1 - $f) * $s);

        switch ($i % 6) {
            case 0:
                $r = $v;
                $g = $t;
                $b = $p;
                break;
            case 1:
                $r = $q;
                $g = $v;
                $b = $p;
                break;
            case 2:
                $r = $p;
                $g = $v;
                $b = $t;
                break;
            case 3:
                $r = $p;
                $g = $q;
                $b = $v;
                break;
            case 4:
                $r = $t;
                $g = $p;
                $b = $v;
                break;
            case 5:
                $r = $v;
                $g = $p;
                $b = $q;
                break;
        }

        return [
            floor($r * 255),
            floor($g * 255),
            floor($b * 255),
        ];
    }
}

if (! function_exists('list_to_tree')) {
    /**
     * 把返回的数据集转换成Tree.
     *
     * @param  array  $list  要转换的数据集
     * @param  string  $pid  parent标记字段
     * @param  string  $level  level标记字段
     *
     * @return array
     */
    function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0)
    {
        // 创建Tree
        $tree = [];
        if (is_array($list)) {
            // 创建基于主键的数组引用
            $refer = [];
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] = &$list[$key];
            }
            foreach ($list as $key => $data) {
                // 判断是否存在parent
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] = &$list[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = &$refer[$parentId];
                        $parent[$child][] = &$list[$key];
                    }
                }
            }
        }

        return $tree;
    }
}

if (! function_exists('tree_to_list')) {
    /**
     * 将list_to_tree的树还原成列表.
     *
     * @param  array  $tree  原来的树
     * @param  string  $child  孩子节点的键
     * @param  string  $order  排序显示的键，一般是主键 升序排列
     * @param  array  $list  过渡用的中间数组，
     *
     * @return array        返回排过序的列表数组
     */
    function tree_to_list($tree, $child = '_child', $order = 'id', &$list = [])
    {
        if (is_array($tree)) {
            foreach ($tree as $key => $value) {
                $reffer = $value;
                if (isset($reffer[$child])) {
                    unset($reffer[$child]);
                    tree_to_list($value[$child], $child, $order, $list);
                }
                $list[] = $reffer;
            }
            $list = list_sort_by($list, $order, $sortby = 'asc');
        }

        return $list;
    }
}

if (! function_exists('list_sort_by')) {
    /**
     * 对查询结果集进行排序.
     *
     * @param  array  $list  查询结果
     * @param  string  $field  排序的字段名
     * @param  string  $sortby  排序类型 asc正向排序 desc逆向排序 nat自然排序
     *
     * @return array|bool
     */
    function list_sort_by($list, $field, $sortby = 'asc')
    {
        if (is_array($list)) {
            $refer = $resultSet = [];
            foreach ($list as $i => $data) {
                $refer[$i] = &$data[$field];
            }
            switch ($sortby) {
                case 'asc': // 正向排序
                    asort($refer);
                    break;
                case 'desc':// 逆向排序
                    arsort($refer);
                    break;
                case 'nat': // 自然排序
                    natcasesort($refer);
                    break;
            }
            foreach ($refer as $key => $val) {
                $resultSet[] = &$list[$key];
            }

            return $resultSet;
        }

        return false;
    }
}

if (! function_exists('upload_file')) {
    /**
     * 上传文件.
     *
     * @param  string  $file  上传的文件
     * @param  string  $name  上传的位置
     * @param  string  $path  上传的文件夹
     * @param  string  $validate  规则验证
     * @param  string  $url  前缀
     *
     * @return string|bool
     * @author niu
     */
    function upload_file($file = null, $name = 'local', $path = '', $validate = '', $url = '/')
    {
        //文件
        if (! $file) {
            return false;
        }
        //上传配置
        $config_name = 'filesystem.disks.'.$name;
        $filesystem = config($config_name);
        if (! $filesystem) {
            return false;
        }
        //上传文件
        if ($validate) {
            validate(['file' => $validate])->check(['file' => $file]);
        }
        $savename = \think\facade\Filesystem::disk($name)->putFile($path, $file, function ($file) {
            //重命名
            return date('Ymd').'/'.md5((string) microtime(true));
        });
        if(empty($url)){
            $url = '/';
        }
        $savename = $url.$savename;

        return $savename;
    }
}

if (! function_exists('varexport')) {
    /**
     * var_export方法array转[]
     *
     * @param $expression
     * @param  bool  $return
     *
     * @return mixed|string|string[]|null
     */
    function varexport($expression, $return = false)
    {
        $export = var_export($expression, true);
        $export = preg_replace("/^([ ]*)(.*)/m", '$1$1$2', $export);
        $array = preg_split("/\r\n|\n|\r/", $export);
        $array = preg_replace(["/\s*array\s\($/", "/\)(,)?$/", "/\s=>\s$/"], [null, ']$1', ' => ['], $array);
        $export = join(PHP_EOL, array_filter(["["] + $array));
        if ((bool) $return) {

            return $export;
        } else {
            echo $export;
        }
    }
}
