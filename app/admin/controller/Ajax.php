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

namespace app\admin\controller;

use think\facade\Db;
use think\facade\Event;
use think\facade\Lang;
use think\AddonService;
use think\facade\Cache;
use think\facade\Config;
use app\common\model\Attachment;
use app\common\controller\Backend;
use think\facade\Validate;

/**
 * Ajax异步请求接口.
 *
 * @internal
 */
class Ajax extends Backend
{
    protected $noNeedLogin = ['lang'];
    protected $noNeedRight = ['*'];
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();

        //设置过滤方法
        $this->request->filter(['strip_tags', 'htmlspecialchars']);
    }

    /**
     * 加载语言包.
     */
    public function lang()
    {
        header('Content-Type: application/javascript');
        $controllername = request()->param('controllername');
        //默认只加载了控制器对应的语言名，你还根据控制器名来加载额外的语言包
        $this->loadlang($controllername);

        return jsonp(Lang::get(), 200, [], ['json_encode_param' => JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE]);
    }

    /**
     * 上传文件.
     *
     * @param string $file 上传的文件
     *
     * @return json
     *
     * @author niu
     */
    public function upload()
    {
        //Config::set('default_return_type', 'json');
        Config::set(['default_return_type'=> 'json'], 'app');
        $file = $this->request->file('file');
        if (empty($file)) {
            $this->error(__('No file upload or server upload limit exceeded'));
        }

        //判断是否已经存在附件
        $sha1 = $file->hash();
        $extparam = $this->request->post();

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

        //上传图片
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
            'admin_id'    => (int) $this->auth->id,
            'user_id'     => 0,
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
            'extparam'    => json_encode($extparam),
        ];

        $attachment = new Attachment();
        $attachment->data(array_filter($params));
        $attachment->save();
        \think\facade\Event::listen('upload_after', $attachment);
        $this->success(__('Upload successful'), null, [
            'url' => $savename,
        ]);
    }

    /**
     * 通用排序.
     */
    public function weigh()
    {
        //排序的数组
        $ids = $this->request->post('ids');
        //拖动的记录ID
        $changeid = $this->request->post('changeid');
        //操作字段
        $field = $this->request->post('field');
        //操作的数据表
        $table = $this->request->post('table');
        if (!Validate::is($table, "alphaDash")) {
            $this->error();
        }
        //主键
        $pk = $this->request->post('pk');
        //排序的方式
        $orderway = strtolower($this->request->post("orderway", ""));

        $orderway = $orderway == 'asc' ? 'ASC' : 'DESC';
        $sour = $weighdata = [];
        $ids = explode(',', $ids);
        $prikey = $pk ? $pk : (Db::name($table)->getPk() ?: 'id');
        $pid = $this->request->post('pid');
        //限制更新的字段
        $field = in_array($field, ['weigh']) ? $field : 'weigh';

        // 如果设定了pid的值,此时只匹配满足条件的ID,其它忽略
        if ($pid !== '') {
            $hasids = [];
            $list = Db::name($table)->where($prikey, 'in', $ids)->where('pid', 'in',
                $pid)->field("{$prikey},pid")->select();
            foreach ($list as $k => $v) {
                $hasids[] = $v[$prikey];
            }
            $ids = array_values(array_intersect($ids, $hasids));
        }

        $list = Db::name($table)->field("$prikey,$field")->where($prikey, 'in', $ids)->order($field,
            $orderway)->select();
        foreach ($list as $k => $v) {
            $sour[] = $v[$prikey];
            $weighdata[$v[$prikey]] = $v[$field];
        }
        $position = array_search($changeid, $ids);
        $desc_id = $sour[$position];    //移动到目标的ID值,取出所处改变前位置的值
        $sour_id = $changeid;
        $weighids = [];
        $temp = array_values(array_diff_assoc($ids, $sour));
        foreach ($temp as $m => $n) {
            if ($n == $sour_id) {
                $offset = $desc_id;
            } else {
                if ($sour_id == $temp[0]) {
                    $offset = isset($temp[$m + 1]) ? $temp[$m + 1] : $sour_id;
                } else {
                    $offset = isset($temp[$m - 1]) ? $temp[$m - 1] : $sour_id;
                }
            }
            $weighids[$n] = $weighdata[$offset];
            Db::name($table)->where($prikey, $n)->update([$field => $weighdata[$offset]]);
        }
        $this->success();
    }

    /**
     * 清空系统缓存.
     */
    public function wipecache()
    {
        $type = $this->request->request('type');
        switch ($type) {
            case 'all':
            case 'content':
                rmdirs(app()->getRootPath().'runtime'.DIRECTORY_SEPARATOR, false);
                Cache::clear();
                if ($type == 'content') {
                    break;
                }
            case 'template':
                rmdirs(app()->getRootPath().'runtime'.DIRECTORY_SEPARATOR, false);
                if ($type == 'template') {
                    break;
                }
            case 'addons':
                AddonService::refresh();
                if ($type == 'addons') {
                    break;
                }
        }

        \think\facade\Event::trigger('wipecache_after');
        $this->success();
    }

    /**
     * 读取分类数据,联动列表.
     */
    public function category()
    {
        $type = $this->request->get('type');
        $pid = $this->request->get('pid');
        $where = ['status' => 'normal'];
        $categorylist = null;
        if ($pid !== '') {
            if ($type) {
                $where['type'] = $type;
            }
            if ($pid) {
                $where['pid'] = $pid;
            }

            $categorylist = Db::name('category')->where($where)->field('id as value,name')->order('weigh desc,id desc')->select();
        }
        $this->success('', null, $categorylist);
    }

    /**
     * 读取省市区数据,联动列表.
     */
    public function area()
    {
        $params = $this->request->get('row/a');
        if (! empty($params)) {
            $province = isset($params['province']) ? $params['province'] : '';
            $city = isset($params['city']) ? $params['city'] : null;
        } else {
            $province = $this->request->get('province');
            $city = $this->request->get('city');
        }
        $where = ['pid' => 0, 'level' => 1];
        $provincelist = null;
        if ($province !== '') {
            if ($province) {
                $where['pid'] = $province;
                $where['level'] = 2;
            }
            if ($city !== '') {
                if ($city) {
                    $where['pid'] = $city;
                    $where['level'] = 3;
                }
                $provincelist = Db::name('area')->where($where)->field('id as value,name')->select();
            }
        }
        $this->success('', null, $provincelist);
    }

    /**
     * 生成后缀图标
     */
    public function icon()
    {
        $suffix = $this->request->request("suffix");
        header('Content-type: image/svg+xml');
        $suffix = $suffix ? $suffix : "FILE";
        echo build_suffix_image($suffix);
        exit;
    }
}
