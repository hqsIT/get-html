<?php

/**
 * Created by PhpStorm.
 * User: hqs <klinson@163.com>
 * Date: 2017/5/25
 * Time: 23:59
 */
namespace app\common\model;
use think\Db;
use think\Model;

class WebCategory extends Model
{
    protected $autoWriteTimestamp = 'int';

    protected $insert = [
        'status' => 1
    ];

    /**
     * 保存数据
     * @param $web_id
     * @param array $data
     * @author klinson <klinson@163.com>
     * @return false|int
     */
    public function saveDo($web_id, $data = [])
    {
        $id = $this->where('path', $data['path'])->where('web_id', $web_id)->value('id');
        if (empty($id)) {
            $data['web_id'] = $web_id;
            return $this->isUpdate(false)->save($data);
        } else {
            return $this->isUpdate(true)->save($data, ['id' => $id]);
        }
    }

    public function getTop($count = 10)
    {
        return $this->field('id, title, count')->order('count', 'desc')->limit($count)->select();
    }

    /**
     * 统计条数
     * @author klinson <klinson@163.com>
     */
    public function setCount()
    {
        $maxId = $this->order('id', 'desc')->value('id');
        $id = 1;
        while ($id <= $maxId) {
            $res = $this->where('id', $id)->field('id')->find();
            if (!empty($res)) {
                $count = Db::name('WebContent')->where('category_id', $id)->count('id');
                $this->where('id', $id)->setField('count', $count);
            }

            $id++;
        }
    }

    /**
     * 分类下所有作品标签
     * @author klinson <klinson@163.com>
     */
    public function getTags($id)
    {
        $join = [
            ['__WEB_CONTENT__ content', 'content.category_id = category.id'],
            ['__WEB_CONTENT_TAG__ wct', 'wct.content_id = content.id'],
            ['__TAG__ tag', 'tag.id = wct.tag_id'],
        ];
        return $this->alias('category')->where('category.id', $id)->join($join)->distinct('tag.id')->field('tag.id, tag.title')->select();
    }
}