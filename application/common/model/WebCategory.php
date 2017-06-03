<?php

/**
 * Created by PhpStorm.
 * User: hqs <klinson@163.com>
 * Date: 2017/5/25
 * Time: 23:59
 */
namespace app\common\model;
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
}