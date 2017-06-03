<?php

/**
 * Created by PhpStorm.
 * User: hqs <klinson@163.com>
 * Date: 2017/5/25
 * Time: 23:59
 */
namespace app\common\model;
use think\Cache;
use think\Db;
use think\Model;

class Web extends Model
{
    public function updateWeb($id)
    {
        set_time_limit(0);
        ini_set('memory_limit', '512M');

        $res = $this->updateCategory($id);
        return $res;
    }

    /**
     * 更新分类
     * @param $web_id
     * @param int $start
     * @param int $count
     * @author klinson <klinson@163.com>
     * @return bool
     */
    public function updateCategory($web_id, $start = 0, $count = 20)
    {
        $category_content = Cache::get('category_content_'.$web_id);
        $info = $this->get(['id' => $web_id]);
        $host = $this->getHost($web_id);
        //获取列表页列表区
        if (empty($category_content)) {
            $url = $host . '/' . $info['category_path'];
            $html_content = $this->load($url);

            $p = '/<div class=\"tab-content\".*?>(.*?)<\/div><\/div>/ism';
            $res = preg_match($p, $html_content, $match);
            if (!$res) {
                return false;
            }
            $category_content = $match[0];
            Cache::set('category_content_'.$web_id, $category_content, 600);
        }

        //取出具体列表
        $category_content_match = Cache::get('category_content_match_'.$web_id);
        if (empty($category_content_match)) {
            $p = '/<li><a href=\"(.*?)\"><img data-original=\"(.*?)\" \/><br \/>(.*?)<\/a><\/li>/ism';
            $res = preg_match_all($p, $category_content, $match);
            if (!$res) {
                return false;
            }
            $category_content_match = $match;
//            dump($category_content_match);
            Cache::set('category_content_match_'.$web_id, $category_content_match, 600);
        }

//        dump($category_content_match);exit;
        unset($category_content_match[0]);


        $all = count($category_content_match[1]);
        $end = $start + $count;
        $go = true;
        if ($end > $all) {
            $end = $all;
            $go = false;
        }
        for ($i = $start; $i < $end; $i++) {
            $item = $category_content_match[1][$i];
            if (empty($item)) {
                continue;
            }
            $data = [
                'path' => $item,
                'img_path' => $this->savePic(
                    $host.$category_content_match[2][$i],
                    '/downloads/picture/category/',
                    substr($item, 0, strlen($item)-1)
                ),
                'title' => $category_content_match[3][$i]
            ];

            $WebCategory = new WebCategory();
            $WebCategory->saveDo($web_id, $data);
        }
        return $go;
//        foreach ($category_content_match[1] as $key => $item) {
//            if (empty($item)) {
//                continue;
//            }
//            $data = [
//                'path' => $item,
//                'img_path' => $this->savePic(
//                    $host.$category_content_match[2][$key],
//                    '/downloads/picture/category/',
//                    substr($item, 0, strlen($item)-1)
//                ),
//                'title' => $category_content_match[3][$key]
//            ];
//            $WebCategory->saveDo($web_id, $data);
//        }
    }

    /**
     * 更新分类列表
     * @param $cate_id
     * @param null $page_path
     * @author klinson <klinson@163.com>
     * @return bool|string
     */
    public function updateCategoryList($cate_id, $page_path = null)
    {
        $cate_info = WebCategory::get(['id' => $cate_id]);
        if (empty($cate_info)) {
            return false;
        }
        $web_info = $this->get(['id' => $cate_info['web_id']]);
        if (empty($web_info)) {
            return false;
        }

        $host = $this->getHost($cate_info['web_id']);

        if (empty($page_path)) {
            $url = $host . '/' . $cate_info['path'];
        } else {
            $url = $host . '/' . base64_decode($page_path);
        }
        $category_page = $this->load($url);
        if (empty($category_page)) {
            return false;
        }
//        dump($category_page);exit;


        if (empty($page_path)) {
            //保存分类说明
            $p = '/<p class=\"avms\".*?>(.*?)<\/p>/ism';
            $res = preg_match($p, $category_page, $match);
            if (!$res) {
                return false;
            }
            $category_desc_content = $match[1];
            $WebCategory = new WebCategory();
            $WebCategory->isUpdate(true)->save(['desc'=> $category_desc_content], ['id' => $cate_id]);
        }

        //获取分类下列表
        $p = '/<span class=\"list_img\"><a href=\"(.*?)\" target="_blank"><img data-original=\"(.*?)\"><\/a><\/span><div class=\"list_text\"><span><date><a href=\".*?\"><b>(.*?)<\/b><\/a><\/date>\/<date>(.*?)<\/date><p>(.*?)<\/p><\/span><\/div>/ism';
        $res = preg_match_all($p, $category_page, $match);
        if (!$res) {
            return false;
        }
        $category_list_content = $match;
//        dump($category_list_content);
//exit;
        //保存
        $data = [
            'web_id' => $web_info['id'],
            'category_id' => $cate_info['id']
        ];
        foreach ($category_list_content[1] as $key => $item) {
            $data['title'] = $category_list_content[5][$key];
            $data['alias'] = $category_list_content[3][$key];
            $data['publish_time'] = $category_list_content[4][$key];
            $data['path'] = $item;
            $data['img_path'] = $this->savePic(
                $host.$category_list_content[2][$key],
                '/downloads/picture/content/',
                $category_list_content[3][$key]
            );
            $WebContent = new WebContent();
            $WebContent->saveDo($data);
        }

        //获取分类下分页
        $p = '/<div class=\"dede_pages\".*?>(.*?)<\/div>/ism';
        $res = preg_match($p, $category_page, $match);
        if (!$res) {
            return false;
        }
        $page_content = $match[1];

        $p = '/<li class=\'thisclass\'>(.*?)<\/li>/ism';
        $res = preg_match($p, $page_content, $match);
        if (!$res) {
            return false;
        }
        $now_page = $match[1];
        $next_page = $now_page + 1;

        $p = '/<li class=\'thisclass\'>'.$now_page.'<\/li>\&nbsp;<li><a href=\"(.*?)\">'.$next_page.'<\/a><\/li>/ism';
        $res = preg_match($p, $page_content, $match);
        if (!$res) {
            return false;
        }
        $next_page_path = $match[1];
        if (empty($next_page_path)) {
            return false;
        } else {
            return base64_encode($next_page_path);
        }
//        dump($next_page_path);
    }

    /**
     * 分类列表缓存
     * @param $web_id
     * @author klinson <klinson@163.com>
     * @return array|mixed
     */
    public function getCategoryList($web_id)
    {
        $ids = Cache::get('web_category_ids_'.$web_id);
        if (empty($ids)) {
            $ids = Db::name('WebCategory')->where('web_id', $web_id)->column('id');
            Cache::set('web_category_ids_'.$web_id, $ids, 600);
        }
        return $ids;
    }

    /**
     * 更新详细内容
     * @param $contentId
     * @author klinson <klinson@163.com>
     * @return bool
     */
    public function updateWebContent($contentId)
    {
        $WebContent = new WebContent();
        $info = $WebContent->where('id', $contentId)->field('id, web_id, alias, path')->find();
        if (empty($info)) {
            return false;
        }
        $host = $this->getHost($info['web_id']);
        $page_content = $this->load($host . $info['path']);
//        dump($page_content);

        $p = '/<div.*?class=\"artCon\".*?>(.*?)<\/div>/ism';
        $res = preg_match($p, $page_content, $match);
//        dump($match);
//        exit;

        if (!$res) {
            return false;
        }
        $content = $match[1];

        $p = '/时长(\d+)分钟.*分类定义于：(.*)，.*正式发片日期是([\d-]+).*<img.*src=\"([^\"]*)\".*/ism';
        $res = preg_match($p, $content, $match);
//        dump($match);exit;
        if (!$res) {
            return false;
        }
        $data = [
            'publish_time' => $match[3],
            'time'  => $match[1],
            'big_img_path' => $this->savePic(
                $host.$match[4],
                '/downloads/picture/content_hd/',
                $info['alias']
            )
        ];
        $WebContent->saveById($contentId, $data);

        $tags = explode(',', $match[2]);
        $WebContent->saveTag($contentId, $tags);

        return true;
    }

    /**
     * 加载url
     * @param $url
     * @author klinson <klinson@163.com>
     * @return bool|string
     */
    protected function load($url)
    {
        $handle = fopen($url, "r");
        if ($handle) {
            $content = stream_get_contents($handle, 1024 * 1024);//file_get_contents 读取资源流到一个字符串,第二个参数需要读取的最大的字节数。默认是-1（读取全部的缓冲数据）
            return $content;
        } else {
            return false;
        }
    }

    /**
     * 保存图片
     * @param $url
     * @author klinson <klinson@163.com>
     * @return bool|string
     */
    protected function savePic($url, $path, $saveName = false)
    {
        $ext = substr($url, strrpos($url, '.'));
        if ($saveName === false) {
            $saveName = time();
        }
        $path = $path.$saveName.$ext;
        if (file_exists($path)) {
            return $path;
        } else {
            try {
                return file_put_contents('.'.$path, file_get_contents($url)) === false
                    ? false : $path;
            } catch (\Exception $e) {
                return false;
            }

        }
    }

    /**
     * 获取带http域名
     * @param $web_id
     * @author klinson <klinson@163.com>
     * @return string
     */
    public function getHost($web_id)
    {
        $info = $this->get(['id' => $web_id]);
        $head = $info['is_https'] ? 'https://' : 'http://';
        $host = $head . $info['host'];
        return $host;
    }
}