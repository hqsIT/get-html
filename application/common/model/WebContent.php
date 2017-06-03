<?php
/**
 * Created by PhpStorm.
 * User: hqs <klinson@163.com>
 * Date: 2017/5/27
 * Time: 2:16
 */

namespace app\common\model;
use think\Db;
use think\Model;

class WebContent extends Model
{
    protected $autoWriteTimestamp = 'int';
    protected $insert  = [
        'status' => 1
    ];

    public function getTagsAttr($value, $data)
    {
        $Tag = new Tag();
        return $Tag->getContentTag($data['id']);
    }

    public function getCategoryAttr($value, $data)
    {
        return Db::name('WebCategory')->where('id', $data['category_id'])->value('title');
    }

    /**
     * 保存数据
     * @param array $data
     * @author klinson <klinson@163.com>
     * @return false|int
     */
    public function saveDo($data = [])
    {
        $id = $this->where('path', $data['path'])->value('id');
        if (empty($id)) {
            return $this->isUpdate(false)->save($data);
        } else {
            return $this->isUpdate(true)->save($data, ['id' => $id]);
        }
    }

    public function saveById($id, $data)
    {
        return $this->isUpdate(true)->save($data, ['id' => $id]);
    }

    public function saveTag($id, $tags = [])
    {
        $Tag = new Tag();
        $WebContentTag = new WebContentTag();
        $WebContentTag->where('content_id', $id)->delete();
        $data = [
            'content_id' => $id
        ];
        foreach ($tags as $item) {
            if (empty($item)) {
                continue;
            }
            $data['tag_id'] = $Tag->saveDo($item);
            $WebContentTag->isUpdate(false)->data($data)->save();
        }
    }
}