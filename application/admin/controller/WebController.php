<?php
/**
 * Created by PhpStorm.
 * User: hqs <klinson@163.com>
 * Date: 2017/5/25
 * Time: 23:57
 */

namespace app\admin\controller;
use app\common\model\Web;
use app\common\model\WebCategory;
use think\Cache;
use think\Db;
use think\Url;

class WebController extends _BaseController
{
    public function index()
    {
        $Web = new Web();
        $list = $Web->paginate(10);
        dump(collection($list)->toArray());
    }

    /**
     * 更新分类
     * @param $id
     * @param int $start
     * @param int $count
     * @author klinson <klinson@163.com>
     */
    public function go($id, $start = 0, $count = 20)
    {
//        $web_id = $this->request->param('id', 0);
        $Web = new Web();

//        $info = $Web->where('id', $web_id)->find();
//        if (empty($info)) {
//            $this->error('不存在该站点');
//        }
//        $res = $Web->updateWeb($web_id);
//        dump($res);
//        dump($info->toArray());

        $res = $Web->updateCategory($id, $start, $count);
        if ($res) {
            $this->success('go', Url::build('go', ['id'=> $id, 'start' => $start+$count]));
        } else {
            echo 'success';
        }
    }

    /**
     * 更新分类下内容标题内容
     * @param $id
     * @param int $key
     * @param int $page_path
     * @author klinson <klinson@163.com>
     */
    public function go2($id, $key = 0, $page_path = 0)
    {
        set_time_limit(0);
//        ini_set('memory_limit', '128M');
        $Web = new Web();
        $ids = $Web->getCategoryList($id);
        if (!isset($ids[$key]) || empty($ids[$key])) {
            echo 'success';
            return;
        }
        $res = $Web->updateCategoryList($ids[$key], $page_path);
        if ($res === false) {
            $key++;
            $page_path = 0;
        } else {
            $page_path = $res;
        }
        $this->success(
            'go',
            Url::build(
                'go2',
                [
                    'id' => $id,
                    'key' => $key,
                    'page_path' => $page_path
                ]
            )
        );
    }

    /**
     * 更新具体内容下详情
     * @param int $id
     * @author klinson <klinson@163.com>
     */
    public function go3($id = 1)
    {
        $Web = new Web();
        $maxId = Cache::get('web_content_max_id');
        if (empty($maxId)) {
            $maxId = Db::name('WebContent')->order('id', 'desc')->value('id');
            Cache::set('web_content_max_id', $maxId, 300);
        }
        if ($id > $maxId) {
            echo 'success';
            exit;
        } else {
            $Web->updateWebContent($id);
            $this->success(
                'go',
                Url::build(
                    'go3',
                    [
                        'id' => ++$id,
                    ]
                )
            );
        }
    }

    /**
     * 循环更新内容页
     * @author klinson <klinson@163.com>
     */
    public function go4()
    {
        $Web = new Web();
        $maxId = Cache::get('web_content_max_id');
        if (empty($maxId)) {
            $maxId = Db::name('WebContent')->order('id', 'desc')->value('id');
            Cache::set('web_content_max_id', $maxId, 300);
        }
        $id = 1468;
        while ($id <= $maxId) {
            $Web->updateWebContent($id);
            $id++;
        }
        echo 'success';
        return ;
    }

    /**
     * 统计分类下属数量
     * @author klinson <klinson@163.com>
     */
   public function go5()
   {
       $WebCategory = new WebCategory();
       $WebCategory->setCount();
   }
}