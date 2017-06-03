<?php
/**
 * Created by PhpStorm.
 * User: hqs <klinson@163.com>
 * Date: 2017/6/3
 * Time: 11:48
 */

namespace app\home\controller;
use app\common\model\BtWeb;
use app\common\model\Tag;
use app\common\model\WebCategory;
use app\common\model\WebContent;
use app\common\model\WebContentTag;

class WebController extends _BaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $WebCategory = new WebCategory();
        $Tag = new Tag();
        //左侧排行
        $topCategory = $WebCategory->getTop(15);
        $this->assign('topCategory', $topCategory);
        $topTag = $Tag->getTop(15);
        $this->assign('topTag', $topTag);
    }

    /**
     * 列表
     * @author klinson <klinson@163.com>
     * @return mixed
     */
    public function index()
    {
        $this->getList();
        return $this->fetch();
    }

    public function allCategory()
    {
        $WebCategory = new WebCategory();
        $map = [];
        $search = $this->request->param('search', '', 'trim');
        if (!empty($search)) {
            $map['title'] = ['like', '%'.$search.'%'];
        }
        $list = $WebCategory->where($map)->order('title')->paginate(20, false, ['query' => $this->request->param()]);
        $this->assign('list', $list);
        return $this->fetch('all_category');
    }

    /**
     * 分类
     * @author klinson <klinson@163.com>
     * @return mixed
     */
    public function category()
    {
        $category = $this->request->param('category', 0, 'trim');
        if (empty($category)) {
            $this->error('不存在该分类');
        }
        $map = [];
        if (is_numeric($category)) {
            $map['id'] = $category;
        } else {
            $map['title'] = $category;
        }
        $WebCategory = new WebCategory();
        $info = $WebCategory->where($map)->find();
        if (empty($info)) {
            $this->error('不存在该分类');
        }
        $this->assign('category', $info);

        $map = ['category_id' => $info['id']];
        $this->getList($map);

        $this->assign('categoryTags', $WebCategory->getTags($info['id']));
        return $this->fetch();
    }

    /**
     * 详情
     * @author klinson <klinson@163.com>
     */
    public function detail()
    {
        $detail = $this->request->param('detail', 0, 'trim');
        if (empty($detail)) {
            $this->error('不存在该分类');
        }
        $map = [];
        if (is_numeric($detail)) {
            $map['id'] = $detail;
        } else {
            $map['alias'] = $detail;
        }

        $WebContent = new WebContent();
        $info = $WebContent->where($map)->find();
        if (empty($info)) {
            $this->error('不存在该分类');
        }

        $this->assign('detail', $info);

        $bts = BtWeb::all();
        $this->assign('bts', $bts);
        return $this->fetch();
    }

    /**
     * 下载跳转
     * @author klinson <klinson@163.com>
     */
    public function download()
    {
        $id = $this->request->param('id', 0, 'trim');
        if (empty($id)) {
            $this->error('不存在该下载方式');
        }
        $detail = $this->request->param('detail', '', 'trim');
        if (empty($detail)) {
            $this->error('无下载内容');
        }
        try {
            $BtWeb = new BtWeb();
            $url = $BtWeb->getDownloadUrl($id, $detail);
            header('location: ' . $url);
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
    }

    private function getList($map = [])
    {
        $WebContent = new WebContent();
        $search = $this->request->param('search', '', 'trim');
        if (!empty($search)) {
            $map['alias'] = ['like', '%'.$search.'%'];
        }
        $tags = $this->request->param('tags', '', 'trim');
        if (!empty($tags)) {
            $tags = preg_split('/[\.\_\,\ ]/', $tags);
            $tags = array_filter($tags);
            $tags = array_flip(array_flip($tags));
            $WebContentTag = new WebContentTag();
            $ids = $WebContentTag->alias('wct')
                ->join('__TAG__ tag', 'tag.id = wct.tag_id')
                ->whereIn('tag.title', $tags)
                ->group('content_id')
                ->having('count(content_id) = '.count($tags))
                ->distinct('content_id')
                ->column('content_id');

            $map['id'] = ['in', $ids];
        }
        $field = ['id', 'time', 'title', 'alias', 'publish_time', 'category_id', 'img_path'];
        $list = $WebContent->field($field)->where($map)->order('create_time', 'asc')->paginate(12, false, ['query' => $this->request->param()]);
        $this->assign('list', $list);
    }
}