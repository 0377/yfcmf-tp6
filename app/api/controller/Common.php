<?php
/**
 * *
 *  * ============================================================================
 *  * Created by PhpStorm.
 *  * User: Ice
 *  * 邮箱: ice@sbing.vip
 *  * 网址: https://sbing.vip
 *  * Date: 2019/9/19 下午3:33
 *  * ============================================================================.
 */

namespace app\api\controller;

use think\facade\Config;
use app\common\model\Area;
use app\common\model\Version;
use app\common\controller\Api;
use app\common\model\Attachment;
use think\facade\Event;

/**
 * 公共接口.
 */
class Common extends Api
{
    protected $noNeedLogin = ['init'];
    protected $noNeedRight = '*';

    /**
     * 加载初始化.
     *
     * @param  string  $version  版本号
     * @param  string  $lng  经度
     * @param  string  $lat  纬度
     */
    public function init()
    {
        if ($version = $this->request->request('version')) {
            $lng = $this->request->request('lng');
            $lat = $this->request->request('lat');
            $content = [
                'citydata'    => Area::getCityFromLngLat($lng, $lat),
                'versiondata' => Version::check($version),
                'uploaddata'  => Config::get('upload'),
                'coverdata'   => Config::get('cover'),
            ];
            $this->success('', $content);
        } else {
            $this->error(__('Invalid parameters'));
        }
    }

    /**
     * 上传文件.
     * @ApiMethod (POST)
     *
     * @param  File  $file  文件流
     */
    public function upload()
    {
        $file = $this->request->file('file');
        if (empty($file)) {
            $this->error(__('No file upload or server upload limit exceeded'));
        }

        //判断是否已经存在附件
        $sha1 = $file->hash();

        $upload = Config::get('upload');

        preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
        $type = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size = (int) $upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
        $fileInfo['name'] = $file->getOriginalName(); //上传文件名
        $fileInfo['type'] = $file->getOriginalMime(); //上传文件类型信息
        $fileInfo['tmp_name'] = $file->getPathname();
        $fileInfo['size'] = $file->getSize();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix && preg_match('/^[a-zA-Z0-9]+$/', $suffix) ? $suffix : 'file';

        $mimetypeArr = explode(',', strtolower($upload['mimetype']));
        $typeArr = explode('/', $fileInfo['type']);

        //禁止上传PHP和HTML文件
        if (in_array($fileInfo['type'], ['text/x-php', 'text/html']) || in_array($suffix, ['php', 'html', 'htm', 'phar', 'phtml']) || preg_match("/^php(.*)/i", $suffix)) {
            $this->error(__('Uploaded file format is limited'));
        }

        //Mimetype值不正确
        if (stripos($fileInfo['type'], '/') === false) {
            $this->error(__('Uploaded file format is limited'));
        }

        //验证文件后缀
        if ($upload['mimetype'] !== '*' &&
            (
                !in_array($suffix, $mimetypeArr)
                || (stripos($typeArr[0] . '/', $upload['mimetype']) !== false && (!in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr)))
            )
        ) {
            $this->error(__('Uploaded file format is limited'));
        }

        //验证是否为图片文件
        $imagewidth = $imageheight = 0;
        if (in_array($fileInfo['type'],
                ['image/gif', 'image/jpg', 'image/jpeg', 'image/bmp', 'image/png', 'image/webp']) || in_array($suffix,
                ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp'])) {
            $imgInfo = getimagesize($fileInfo['tmp_name']);
            if (! $imgInfo || ! isset($imgInfo[0]) || ! isset($imgInfo[1])) {
                $this->error(__('Uploaded file is not a valid image'));
            }
            $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
            $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
        }

        $_validate[] = 'filesize:'.$size;
        if ($upload['mimetype']) {
            $_validate[] = 'fileExt:'.$upload['mimetype'];
        }
        $validate = implode('|', $_validate);

        $event_config = Event::trigger('upload_init', $upload,true);
        if($event_config){
            $upload = array_merge($upload, $event_config);
        }
        try {
            $savename = upload_file($file, $upload['driver'], 'uploads', $validate, $upload['cdnurl']);
        } catch (\Exception $e) {
            $savename = false;
            $this->error($e->getMessage());
        }
        if (! $savename) {
            $this->error('上传失败');
        }
        $category = request()->post('category');
        $category = array_key_exists($category, config('site.attachmentcategory') ?? []) ? $category : '';
        $params = [
            'admin_id'    => 0,
            'user_id'     => (int) $this->auth->id,
            'category'    => $category,
            'filename'    => mb_substr(htmlspecialchars(strip_tags($fileInfo['name'])), 0, 100),
            'filesize'    => $fileInfo['size'],
            'imagewidth'  => $imagewidth,
            'imageheight' => $imageheight,
            'imagetype'   => $suffix,
            'imageframes' => 0,
            'mimetype'    => $fileInfo['type'],
            'url'         => $savename,
            'uploadtime'  => time(),
            'storage'     => $upload['driver'],
            'sha1'        => $sha1,
        ];
        $attachment = new Attachment();
        $attachment->data(array_filter($params));
        $attachment->save();
        \think\facade\Event::trigger('upload_after', $attachment);
        $this->success(__('Upload successful'), [
            'url' => $savename,
        ]);
    }
}
