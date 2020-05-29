<?php

namespace app\model;

use think\Model;

/**
 * Class app\model\Attachment
 *
 * @property int    $admin_id    管理员ID
 * @property int    $filesize    文件大小
 * @property int    $id          ID
 * @property int    $imageframes 图片帧数
 * @property int    $uploadtime  上传时间
 * @property int    $user_id     会员ID
 * @property string $createtime  创建日期
 * @property string $extparam    透传数据
 * @property string $imageheight 高度
 * @property string $imagetype   图片类型
 * @property string $imagewidth  宽度
 * @property string $mimetype    mime类型
 * @property string $sha1        文件 sha1编码
 * @property string $storage     存储位置
 * @property string $updatetime  更新时间
 * @property string $url         物理路径
 */
class Attachment extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 定义字段类型
    protected $type = [
    ];

    public function setUploadtimeAttr($value)
    {
        return is_numeric($value) ? $value : strtotime($value);
    }

    protected static function onBeforeInsert($model)
    {
        // 如果已经上传该资源，则不再记录
        if (self::where('url', '=', $model['url'])->find()) {
            return false;
        }
    }
}
