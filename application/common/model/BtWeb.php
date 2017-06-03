<?php
/**
 * Created by PhpStorm.
 * User: hqs <klinson@163.com>
 * Date: 2017/6/3
 * Time: 16:01
 */

namespace app\common\model;
use think\Model;

class BtWeb extends Model
{

    /**
     * 生成bt搜索地址
     * @param $id
     * @param $keyword
     * @author klinson <klinson@163.com>
     * @return string
     * @throws \Exception
     */
    public function getDownloadUrl($id, $keyword)
    {
        $info = $this->where('id', $id)->find();
        if (empty($info)) {
            throw new \Exception ('不存在该下载方式');
        }

        $head = $info['is_https'] ? 'https://' : 'http://';
        $page = 1;
        $host = $head . $info['host'] . str_replace(['{$keyword}', '{$page}'], [$keyword, $page], $info['search_rule']);
        //下载量加一操作
        $WebContent = new WebContent();
        $WebContent->downloadIncByAlias($keyword);
        $this->downloadInc($id);

        return $host;
    }

    public function downloadInc($id)
    {
        $this->where('id', $id)->setInc('download');
    }
}