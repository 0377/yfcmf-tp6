<?php

namespace think;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use PhpZip\Exception\ZipException;
use PhpZip\ZipFile;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use think\addons\AddonException;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Env;
use think\facade\Filesystem;

/**
 * 插件服务
 * @package think\addons
 */
class AddonService
{

    /**
     * 远程下载插件
     *
     * @param string $name   插件名称
     * @param array  $extend 扩展参数
     * @return  string
     */
    public static function download($name, $extend = [])
    {
        $addonsTempDir = self::getAddonsBackupDir();
        $tmpFile = $addonsTempDir . $name . ".zip";
        try {
            $client = self::getClient();
            $response = $client->get('/addon/download', ['query' => array_merge(['name' => $name], $extend)]);
            $body = $response->getBody();
            $content = $body->getContents();
            if (substr($content, 0, 1) === '{') {
                $json = (array)json_decode($content, true);
                //如果传回的是一个下载链接,则再次下载
                if ($json['data'] && isset($json['data']['url'])) {
                    $response = $client->get($json['data']['url']);
                    $body = $response->getBody();
                    $content = $body->getContents();
                } else {
                    //下载返回错误，抛出异常
                    throw new AddonException($json['msg'], $json['code'], $json['data']);
                }
            }
        } catch (TransferException $e) {
            throw new Exception("Addon package download failed");
        }

        if ($write = fopen($tmpFile, 'w')) {
            fwrite($write, $content);
            fclose($write);
            return $tmpFile;
        }
        throw new Exception("No permission to write temporary files");
    }

    /**
     * 解压插件
     *
     * @param string $name 插件名称
     * @return  string
     * @throws  Exception
     */
    public static function unzip($name)
    {
        if (!$name) {
            throw new Exception('Invalid parameters');
        }
        $addonsBackupDir = self::getAddonsBackupDir();
        $file = $addonsBackupDir . $name . '.zip';

        // 打开插件压缩包
        $zip = new ZipFile();
        try {
            $zip->openFile($file);
        } catch (ZipException $e) {
            $zip->close();
            throw new Exception('Unable to open the zip file');
        }

        $dir = self::getAddonDir($name);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755);
        }

        // 解压插件压缩包
        try {
            $zip->extractTo($dir);
        } catch (ZipException $e) {
            throw new Exception('Unable to extract the file');
        } finally {
            $zip->close();
        }
        return $dir;
    }

    /**
     * 离线安装
     * @param string $file 插件压缩包
     * @param array  $extend
     */
    public static function local($file, $extend = [])
    {
        //$addonsTempDir = self::getAddonsBackupDir();
        $addonsTempDir = app()->getRootPath().'runtime'.DIRECTORY_SEPARATOR;
        if (!$file || !$file instanceof \think\File) {
            throw new Exception('No file upload or server upload limit exceeded');
        }

        $validate = validate(['zip' => 'filesize:102400000|fileExt:zip,fastaddon'], [], false, false);
        if (! $validate->check(['zip' => $file])) {
            // 文件验证错误
            throw new Exception(__($validate->getError()));
        }

        $uploadFile = Filesystem::disk('runtime')->putFile('addons', $file, function ($file) {
            return str_replace('.'.$file->getOriginalExtension(), '', $file->getOriginalName());
        });

        //$uploadFile = $file->rule('uniqid')->validate(['size' => 102400000, 'ext' => 'zip,fastaddon'])->move($addonsTempDir);
        if (!$uploadFile) {
            // 上传失败获取错误信息
            throw new Exception(__('文件上传失败'));
        }
        $tmpFile = $addonsTempDir . $uploadFile;

        $info = [];
        $zip = new ZipFile();
        try {

            // 打开插件压缩包
            try {
                $zip->openFile($tmpFile);
            } catch (ZipException $e) {
                @unlink($tmpFile);
                throw new Exception('Unable to open the zip file');
            }

            $config = self::getInfoIni($zip);

            // 判断插件标识
            $name = isset($config['name']) ? $config['name'] : '';
            if (!$name) {
                throw new Exception('Addon info file data incorrect');
            }

            // 判断插件是否存在
            if (!preg_match("/^[a-zA-Z0-9]+$/", $name)) {
                throw new Exception('Addon name incorrect');
            }

            // 判断新插件是否存在
            $newAddonDir = self::getAddonDir($name);
            if (is_dir($newAddonDir)) {
                throw new Exception('Addon already exists');
            }

            // 追加MD5和Data数据
            $extend['md5'] = md5_file($tmpFile);
            $extend['data'] = $zip->getArchiveComment();
            $extend['unknownsources'] = Env::get('app_debug') && config('fastadmin.unknownsources');
            $extend['faversion'] = config('fastadmin.version');

            $params = array_merge($config, $extend);

            // 压缩包验证、版本依赖判断
            self::valid($params);

            //创建插件目录
            @mkdir($newAddonDir, 0755, true);

            // 解压到插件目录
            try {
                $zip->extractTo($newAddonDir);
            } catch (ZipException $e) {
                @unlink($newAddonDir);
                throw new Exception('Unable to extract the file');
            }

            Db::startTrans();
            try {
                //默认禁用该插件
                $info = get_addon_info($name);
                if ($info['state']) {
                    $info['state'] = 0;
                    set_addon_info($name, $info);
                }

                //执行插件的安装方法
                $class = get_addon_class($name);
                if (class_exists($class)) {
                    $addon = new $class();
                    $addon->install();
                }
                Db::commit();
            } catch (\Exception $e) {
                Db::rollback();
                @rmdirs($newAddonDir);
                throw new Exception(__($e->getMessage()));
            }

            //导入SQL
            self::importsql($name);
        } catch (AddonException $e) {
            throw new AddonException($e->getMessage(), $e->getCode(), $e->getData());
        } catch (Exception $e) {
            throw new Exception(__($e->getMessage()));
        } finally {
            $zip->close();
            unset($uploadFile);
            @unlink($tmpFile);
        }

        $info['config'] = get_addon_config($name) ? 1 : 0;
        $info['bootstrap'] = is_file(self::getBootstrapFile($name));
        return $info;
    }

    /**
     * 验证压缩包、依赖验证
     * @param array $params
     * @return bool
     * @throws Exception
     */
    public static function valid($params = [])
    {
        $client = self::getClient();
        $multipart = [];
        foreach ($params as $name => $value) {
            $multipart[] = ['name' => $name, 'contents' => $value];
        }
        try {
            $response = $client->post('/addon/valid', ['multipart' => $multipart]);
            $content = $response->getBody()->getContents();
        } catch (TransferException $e) {
            throw new Exception("Network error");
        }
        $json = (array)json_decode($content, true);
        if ($json && isset($json['code'])) {
            if ($json['code']) {
                return true;
            } else {
                throw new Exception($json['msg'] ?? "Invalid addon package");
            }
        } else {
            throw new Exception("Unknown data format");
        }
    }

    /**
     * 备份插件
     * @param string $name 插件名称
     * @return bool
     * @throws Exception
     */
    public static function backup($name)
    {
        $addonsBackupDir = self::getAddonsBackupDir();
        $file = $addonsBackupDir . $name . '-backup-' . date("YmdHis") . '.zip';
        $zipFile = new ZipFile();
        try {
            $zipFile
                ->addDirRecursive(self::getAddonDir($name))
                ->saveAsFile($file)
                ->close();
        } catch (ZipException $e) {

        } finally {
            $zipFile->close();
        }


        return true;
    }

    /**
     * 检测插件是否完整
     *
     * @param string $name 插件名称
     * @return  boolean
     * @throws  Exception
     */
    public static function check($name)
    {
        if (!$name || !is_dir(ADDON_PATH . $name)) {
            throw new Exception('Addon not exists');
        }
        $addonClass = get_addon_class($name);

        if (!$addonClass) {
            throw new Exception("The addon file does not exist");
        }
        $addon = new $addonClass();
        if (!$addon->checkInfo()) {
            throw new Exception("The configuration file content is incorrect");
        }
        return true;
    }

    /**
     * 是否有冲突
     *
     * @param string $name 插件名称
     * @return  boolean
     * @throws  AddonException
     */
    public static function noconflict($name)
    {
        // 检测冲突文件
        $list = self::getGlobalFiles($name, true);
        if ($list) {
            //发现冲突文件，抛出异常
            throw new AddonException(__("Conflicting file found"), -3, ['conflictlist' => $list]);
        }
        return true;
    }

    /**
     * 导入SQL
     *
     * @param string $name 插件名称
     * @return  boolean
     */
    public static function importsql($name)
    {
        $sqlFile = self::getAddonDir($name) . 'install.sql';
        if (is_file($sqlFile)) {
            $lines = file($sqlFile);
            $templine = '';
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || $line == '' || substr($line, 0, 2) == '/*') {
                    continue;
                }

                $templine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    $templine = str_ireplace('__PREFIX__', Env::get('database.prefix'), $templine);
                    $templine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $templine);
                    try {
                        Db::execute($templine);
                    } catch (\PDOException $e) {
                        //$e->getMessage();
                    }
                    $templine = '';
                }
            }
        }
        return true;
    }

    /**
     * 刷新插件缓存文件
     *
     * @return  boolean
     * @throws  Exception
     */
    public static function refresh()
    {
        //刷新addons.js
        $addons = get_addon_list();
        $bootstrapArr = [];
        foreach ($addons as $name => $addon) {
            $bootstrapFile = self::getBootstrapFile($name);
            if ($addon['state'] && is_file($bootstrapFile)) {
                $bootstrapArr[] = file_get_contents($bootstrapFile);
            }
        }
        $addonsFile = app()->getRootPath() . str_replace("/", DIRECTORY_SEPARATOR, "public/assets/js/addons.js");
        if ($handle = fopen($addonsFile, 'w')) {
            $tpl = <<<EOD
define([], function () {
    {__JS__}
});
EOD;
            fwrite($handle, str_replace("{__JS__}", implode("\n", $bootstrapArr), $tpl));
            fclose($handle);
        } else {
            throw new Exception(__("Unable to open file '%s' for writing", "addons.js"));
        }

        Cache::delete("addons");
        Cache::delete("hooks");

        $file = self::getExtraAddonsFile();

        $config = get_addon_autoload_config(true);
        if ($config['autoload']) {
            return;
        }

        if (!is_really_writable($file)) {
            throw new Exception(__("Unable to open file '%s' for writing", "addons.php"));
        }

        file_put_contents($file, "<?php\n\n" . "return " . varexport($config, true) . ";\n", LOCK_EX);
        return true;
    }

    /**
     * 安装插件
     *
     * @param string  $name   插件名称
     * @param boolean $force  是否覆盖
     * @param array   $extend 扩展参数
     * @return  boolean
     * @throws  Exception
     * @throws  AddonException
     */
    public static function install($name, $force = false, $extend = [])
    {
        if (!$name || (is_dir(ADDON_PATH . $name) && !$force)) {
            throw new Exception('Addon already exists');
        }

        // 远程下载插件
        $tmpFile = self::download($name, $extend);

        $addonDir = self::getAddonDir($name);

        try {
            // 解压插件压缩包到插件目录
            self::unzip($name);

            // 检查插件是否完整
            self::check($name);

            if (!$force) {
                // 是否有冲突
                self::noconflict($name);
            }
        } catch (AddonException $e) {
           //@rmdirs($addonDir);
            throw new AddonException($e->getMessage(), $e->getCode(), $e->getData());
        } catch (Exception $e) {
            //@rmdirs($addonDir);
            throw new Exception($e->getMessage());
        } finally {
            // 移除临时文件
            @unlink($tmpFile);
        }

        // 默认启用该插件
        $info = get_addon_info($name);

        Db::startTrans();
        try {
            if (!$info['state']) {
                $info['state'] = 1;
                set_addon_info($name, $info);
            }

            // 执行安装脚本
            $class = get_addon_class($name);
            if (class_exists($class)) {
                $addon = new $class();
                $addon->install();
            }
            Db::commit();
        } catch (Exception $e) {
            @rmdirs($addonDir);
            Db::rollback();
            throw new Exception($e->getMessage());
        }

        // 导入
        self::importsql($name);

        // 启用插件
        self::enable($name, true);

        $info['config'] = get_addon_config($name) ? 1 : 0;
        $info['bootstrap'] = is_file(self::getBootstrapFile($name));
        return $info;
    }

    /**
     * 卸载插件
     *
     * @param string  $name
     * @param boolean $force 是否强制卸载
     * @return  boolean
     * @throws  Exception
     */
    public static function uninstall($name, $force = false)
    {
        if (!$name || !is_dir(ADDON_PATH . $name)) {
            throw new Exception('Addon not exists');
        }

        if (!$force) {
            self::noconflict($name);
        }

        // 移除插件全局资源文件
        if ($force) {
            $list = self::getGlobalFiles($name);
            foreach ($list as $k => $v) {
                @unlink(app()->getRootPath() . $v);
            }
        }

        // 执行卸载脚本
        try {
            $class = get_addon_class($name);
            if (class_exists($class)) {
                $addon = new $class();
                $addon->uninstall();
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // 移除插件目录
        rmdirs(ADDON_PATH . $name);

        // 刷新
        self::refresh();
        return true;
    }

    /**
     * 启用
     * @param string  $name  插件名称
     * @param boolean $force 是否强制覆盖
     * @return  boolean
     */
    public static function enable($name, $force = false)
    {
        if (!$name || !is_dir(ADDON_PATH . $name)) {
            throw new Exception('Addon not exists');
        }

        if (!$force) {
            self::noconflict($name);
        }

        //备份冲突文件
        if (config('fastadmin.backup_global_files')) {
            $conflictFiles = self::getGlobalFiles($name, true);
            if ($conflictFiles) {
                $zip = new ZipFile();
                try {
                    foreach ($conflictFiles as $k => $v) {
                        $zip->addFile(app()->getRootPath() . $k, $v);
                    }
                    $addonsBackupDir = self::getAddonsBackupDir();
                    $zip->saveAsFile($addonsBackupDir . $name . "-conflict-enable-" . date("YmdHis") . ".zip");
                } catch (Exception $e) {

                } finally {
                    $zip->close();
                }
            }
        }

        $addonDir = self::getAddonDir($name);
        $sourceAssetsDir = self::getSourceAssetsDir($name);
        $destAssetsDir = self::getDestAssetsDir($name);

        $files = self::getGlobalFiles($name);
        if ($files) {
            //刷新插件配置缓存
            self::config($name, ['files' => $files]);
        }

        // 复制文件
        if (is_dir($sourceAssetsDir)) {
            copydirs($sourceAssetsDir, $destAssetsDir);
        }

        // 复制application和public到全局
        foreach (self::getCheckDirs() as $k => $dir) {
            if (is_dir($addonDir . $k)) {
                copydirs($addonDir . $k, app()->getRootPath() . $dir);
            }
        }

        //插件纯净模式时将插件目录下的application、public和assets删除
        if (config('fastadmin.addon_pure_mode')) {
            // 删除插件目录已复制到全局的文件
            @rmdirs($sourceAssetsDir);
            foreach (self::getCheckDirs() as $k => $dir) {
                @rmdirs($addonDir . $k);
            }
        }

        //执行启用脚本
        try {
            $class = get_addon_class($name);
            if (class_exists($class)) {
                $addon = new $class();
                if (method_exists($class, "enable")) {
                    $addon->enable();
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        $info = get_addon_info($name);
        $info['state'] = 1;
        unset($info['url']);

        set_addon_info($name, $info);

        // 刷新
        self::refresh();
        return true;
    }

    /**
     * 禁用
     *
     * @param string  $name  插件名称
     * @param boolean $force 是否强制禁用
     * @return  boolean
     * @throws  Exception
     */
    public static function disable($name, $force = false)
    {
        if (!$name || !is_dir(ADDON_PATH . $name)) {
            throw new Exception('Addon not exists');
        }

        $file = self::getExtraAddonsFile();
        if (!is_really_writable($file)) {
            throw new Exception(__("Unable to open file '%s' for writing", "addons.php"));
        }

        if (!$force) {
            self::noconflict($name);
        }

        if (config('fastadmin.backup_global_files')) {
            //仅备份修改过的文件
            $conflictFiles = self::getGlobalFiles($name, true);
            if ($conflictFiles) {
                $zip = new ZipFile();
                try {
                    foreach ($conflictFiles as $k => $v) {
                        $zip->addFile(app()->getRootPath() . $v, $v);
                    }
                    $addonsBackupDir = self::getAddonsBackupDir();
                    $zip->saveAsFile($addonsBackupDir . $name . "-conflict-disable-" . date("YmdHis") . ".zip");
                } catch (Exception $e) {

                } finally {
                    $zip->close();
                }
            }
        }

        $config = self::config($name);

        $addonDir = self::getAddonDir($name);
        //插件资源目录
        $destAssetsDir = self::getDestAssetsDir($name);

        // 移除插件全局文件
        $list = self::getGlobalFiles($name);


        //插件纯净模式时将原有的文件复制回插件目录
        //当无法获取全局文件列表时也将列表复制回插件目录
        if (config('fastadmin.addon_pure_mode') || !$list) {
            if ($config && isset($config['files']) && is_array($config['files'])) {
                foreach ($config['files'] as $index => $item) {
                    //避免切换不同服务器后导致路径不一致
                    $item = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $item);
                    $item_tp5 = str_replace('app'.DIRECTORY_SEPARATOR, 'application'.DIRECTORY_SEPARATOR, $item);

                    //插件资源目录，无需重复复制
                    if (stripos($item, str_replace(app()->getRootPath(), '', $destAssetsDir)) === 0) {
                        continue;
                    }
                    //检查目录是否存在，不存在则创建
                    $itemBaseDir = dirname($addonDir . $item_tp5);
                    if (!is_dir($itemBaseDir)) {
                        @mkdir($itemBaseDir, 0755, true);
                    }
                    if (is_file(app()->getRootPath() . $item)) {
                        @copy(app()->getRootPath() . $item, $addonDir . $item_tp5);
                    }
                }
                $list = $config['files'];
            }

            //复制插件目录资源
            if (is_dir($destAssetsDir)) {
                @copydirs($destAssetsDir, $addonDir . 'assets' . DIRECTORY_SEPARATOR);
            }
        }

        $dirs = [];
        foreach ($list as $k => $v) {
            $v = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $v);
            $v_tp6 = str_replace('application'.DIRECTORY_SEPARATOR, 'app'.DIRECTORY_SEPARATOR, $v);
            $file = app()->getRootPath() . $v_tp6;
            $dirs[] = dirname($file);
            @unlink($file);
        }

        // 移除插件空目录
        $dirs = array_filter(array_unique($dirs));
        foreach ($dirs as $k => $v) {
            remove_empty_folder($v);
        }

        $info = get_addon_info($name);
        $info['state'] = 0;
        unset($info['url']);

        set_addon_info($name, $info);

        // 执行禁用脚本
        try {
            $class = get_addon_class($name);
            if (class_exists($class)) {
                $addon = new $class();

                if (method_exists($class, "disable")) {
                    $addon->disable();
                }
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // 刷新
        self::refresh();
        return true;
    }

    /**
     * 升级插件
     *
     * @param string $name   插件名称
     * @param array  $extend 扩展参数
     */
    public static function upgrade($name, $extend = [])
    {
        $info = get_addon_info($name);
        if ($info['state']) {
            throw new Exception(__('Please disable addon first'));
        }
        $config = get_addon_config($name);
        if ($config) {
            //备份配置
        }

        // 远程下载插件
        $tmpFile = self::download($name, $extend);

        // 备份插件文件
        self::backup($name);

        $addonDir = self::getAddonDir($name);

        // 删除插件目录下的application和public
        $files = self::getCheckDirs();
        foreach ($files as $index => $file) {
            @rmdirs($addonDir . $index);
        }

        try {
            // 解压插件
            self::unzip($name);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        } finally {
            // 移除临时文件
            @unlink($tmpFile);
        }

        if ($config) {
            // 还原配置
            set_addon_config($name, $config);
        }

        // 导入
        self::importsql($name);

        // 执行升级脚本
        try {
            $addonName = ucfirst($name);
            //创建临时类用于调用升级的方法
            $sourceFile = $addonDir . $addonName . ".php";
            $destFile = $addonDir . $addonName . "Upgrade.php";

            $classContent = str_replace("class {$addonName} extends", "class {$addonName}Upgrade extends", file_get_contents($sourceFile));

            //创建临时的类文件
            file_put_contents($destFile, $classContent);

            $className = "\\addons\\" . $name . "\\" . $addonName . "Upgrade";
            $addon = new $className($name);

            //调用升级的方法
            if (method_exists($addon, "upgrade")) {
                $addon->upgrade();
            }

            //移除临时文件
            @unlink($destFile);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // 刷新
        self::refresh();

        //必须变更版本号
        $info['version'] = isset($extend['version']) ? $extend['version'] : $info['version'];

        $info['config'] = get_addon_config($name) ? 1 : 0;
        $info['bootstrap'] = is_file(self::getBootstrapFile($name));
        return $info;
    }

    /**
     * 读取或修改插件配置
     * @param string $name
     * @param array  $changed
     * @return array
     */
    public static function config($name, $changed = [])
    {
        $addonDir = self::getAddonDir($name);
        $addonConfigFile = $addonDir . '.addonrc';
        $config = [];
        if (is_file($addonConfigFile)) {
            $config = (array)json_decode(file_get_contents($addonConfigFile), true);
        }
        $config = array_merge($config, $changed);
        if ($changed) {
            file_put_contents($addonConfigFile, json_encode($config, JSON_UNESCAPED_UNICODE));
        }
        return $config;
    }

    /**
     * 获取插件在全局的文件
     *
     * @param string  $name         插件名称
     * @param boolean $onlyconflict 是否只返回冲突文件
     * @return  array
     */
    public static function getGlobalFiles($name, $onlyconflict = false)
    {
        $list = [];
        $addonDir = self::getAddonDir($name);
        $checkDirList = self::getCheckDirs();
        $checkDirList = array_merge($checkDirList, ['assets'=>'assets']);

        $assetDir = self::getDestAssetsDir($name);

        // 扫描插件目录是否有覆盖的文件
        foreach ($checkDirList as $dirName => $dirName_tp6) {
            //检测目录是否存在
            if (!is_dir($addonDir . $dirName)) {
                continue;
            }
            //匹配出所有的文件
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($addonDir . $dirName, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($files as $fileinfo) {

                if ($fileinfo->isFile()) {
                    $filePath = $fileinfo->getPathName();
                    //如果名称为assets需要做特殊处理
                    if ($dirName === 'assets') {
                        $path = str_replace(app()->getRootPath(), '', $assetDir) . str_replace($addonDir . $dirName . DIRECTORY_SEPARATOR, '', $filePath);
                    } else {
                        $path = str_replace($addonDir, '', $filePath);
                        // tp6
                        $path = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
                        $path = str_replace('application'.DIRECTORY_SEPARATOR, 'app'.DIRECTORY_SEPARATOR, $path);
                    }

                    if ($onlyconflict) {
                        $destPath = app()->getRootPath() . $path;
                        if (is_file($destPath)) {
                            if (filesize($filePath) != filesize($destPath) || md5_file($filePath) != md5_file($destPath)) {
                                $list[] = $path;
                            }
                        }
                    } else {
                        $list[] = $path;
                    }
                }
            }
        }
        $list = array_filter(array_unique($list));
        return $list;
    }

    /**
     * 获取插件行为、路由配置文件
     * @return string
     */
    public static function getExtraAddonsFile()
    {
        return app()->getConfigPath() . 'addons.php';
    }

    /**
     * 获取bootstrap.js路径
     * @return string
     */
    public static function getBootstrapFile($name)
    {
        return ADDON_PATH . $name . DIRECTORY_SEPARATOR . 'bootstrap.js';
    }

    /**
     * 获取指定插件的目录
     */
    public static function getAddonDir($name)
    {
        $dir = ADDON_PATH . $name . DIRECTORY_SEPARATOR;
        return $dir;
    }

    /**
     * 获取插件备份目录
     */
    public static function getAddonsBackupDir()
    {
        $dir = app()->getRuntimePath() . 'addons' . DIRECTORY_SEPARATOR;
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * 获取插件源资源文件夹
     * @param string $name 插件名称
     * @return  string
     */
    protected static function getSourceAssetsDir($name)
    {
        return ADDON_PATH . $name . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取插件目标资源文件夹
     * @param string $name 插件名称
     * @return  string
     */
    protected static function getDestAssetsDir($name)
    {
        $assetsDir = app()->getRootPath() . str_replace("/", DIRECTORY_SEPARATOR, "public/assets/addons/{$name}/");
        return $assetsDir;
    }

    /**
     * 获取远程服务器
     * @return  string
     */
    protected static function getServerUrl()
    {
        return config('fastadmin.api_url');
    }

    /**
     * 获取检测的全局文件夹目录
     * @return  array
     */
    protected static function getCheckDirs()
    {
        return [
            'application'=> 'app',
            'public'     => 'public',
        ];
    }

    /**
     * 获取请求对象
     * @return Client
     */
    protected static function getClient()
    {
        $options = [
            'base_uri'        => self::getServerUrl(),
            'timeout'         => 30,
            'connect_timeout' => 30,
            'verify'          => false,
            'http_errors'     => false,
            'headers'         => [
                'X-REQUESTED-WITH' => 'XMLHttpRequest',
                'Referer'          => dirname(request()->root(true)),
                'User-Agent'       => 'FastAddon',
            ]
        ];
        static $client;
        if (empty($client)) {
            $client = new Client($options);
        }
        return $client;
    }

    /**
     * 匹配配置文件中info信息
     * @param ZipFile $zip
     * @return array|false
     * @throws Exception
     */
    protected static function getInfoIni($zip)
    {
        $config = [];
        // 读取插件信息
        try {
            $info = $zip->getEntryContents('info.ini');
            $config = parse_ini_string($info);
        } catch (ZipException $e) {
            throw new Exception('Unable to extract the file');
        }
        return $config;
    }

}
