<?php
/**
 * Created by PhpStorm.
 * User: hqs <klinson@163.com>
 * Date: 2017/6/3
 * Time: 0:36
 */

namespace app\common\model;
use think\Model;

class Tag extends Model
{
    public function saveDo($tag)
    {
        $id = $this->where('title', $tag)->value('id');
        if (empty($id)) {
            return $this->insertGetId(['title' => $tag]);
        } else {
            return $id;
        }
    }

    /**
     * 获取本文下标签
     * @param $contentId
     * @author klinson <klinson@163.com>
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function getContentTag($contentId)
    {
        return $this->alias('tag')
            ->join('__WEB_CONTENT_TAG__ wct', 'wct.tag_id = tag.id')
            ->where('wct.content_id', $contentId)
            ->field('tag.id, tag.title as tag')
            ->select();
    }

    public function getTop($count = 10)
    {
        return $this->alias('tag')
            ->join('__WEB_CONTENT_TAG__ wct', 'wct.tag_id = tag.id')
            ->group('tag.id')
            ->field('tag.id, tag.title, count(tag.id) as count')
            ->order('count desc')
            ->limit($count)
            ->select();
    }
}