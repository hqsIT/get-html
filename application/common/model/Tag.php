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
}