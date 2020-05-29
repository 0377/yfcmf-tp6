<?php

namespace app\model;

use think\Model;

/**
 * 分类模型.
 *
 * @property int        $id
 * @property int        $pid         父ID
 * @property int        $weigh       权重
 * @property string     $createtime  创建时间
 * @property string     $description 描述
 * @property string     $diyname     自定义名称
 * @property string     $flag
 * @property string     $image       图片
 * @property string     $keywords    关键字
 * @property string     $name
 * @property string     $nickname
 * @property string     $status      状态
 * @property string     $type        栏目类型
 * @property string     $updatetime  更新时间
 * @property-read mixed $flag_text
 * @property-read mixed $type_text
 */
class Category extends Model
{
    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    // 追加属性
    protected $append = [
        'type_text',
        'flag_text',
    ];

    protected static function onAfterInsert($row)
    {
        $row->save(['weigh' => $row['id']]);
    }

    public function setFlagAttr($value, $data)
    {
        return is_array($value) ? implode(',', $value) : $value;
    }

    /**
     * 读取分类类型.
     *
     * @return array
     */
    public static function getTypeList()
    {
        $typeList = config('site.categorytype');
        foreach ($typeList as $k => &$v) {
            $v = __($v);
        }

        return $typeList;
    }

    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : $data['type'];
        $list  = $this->getTypeList();

        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getFlagList()
    {
        return ['hot' => __('Hot'), 'index' => __('Index'), 'recommend' => __('Recommend')];
    }

    public function getFlagTextAttr($value, $data)
    {
        $value    = $value ? $value : $data['flag'];
        $valueArr = explode(',', $value);
        $list     = $this->getFlagList();

        return implode(',', array_intersect_key($list, array_flip($valueArr)));
    }

    /**
     * 读取分类列表.
     *
     * @param  string  $type    指定类型
     * @param  string  $status  指定状态
     *
     * @return array
     */
    public static function getCategoryArray($type = null, $status = null)
    {
        $list = self::where(function ($query) use ($type, $status) {
            if (!is_null($type)) {
                $query->where('type', '=', $type);
            }
            if (!is_null($status)) {
                $query->where('status', '=', $status);
            }
        })->order('weigh', 'desc')->select()->toArray();

        return $list;
    }
}
