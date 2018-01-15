<?php
//博客引擎
namespace writings\controller;
class index{
    //主页
    function home(){
        if (!file_exists('./config.php')) jp(__URL__."index.php/api/install/start", 0);
        //初始化博客文章的数据库
        $dblist = M("writings");
        //搜索功能 文章关键词
        $search = get("search");
        //1热门  2推荐
        $frozen = get("frozen");
        //分类ID
        $sort = get('sort');
        
        $where = null;
        $And = null;
        //分类ID查看
        if (!empty($sort)){
            $where = "writings_rid={$sort}";
            $And = 'and';
        }
        
        if (!empty($frozen)){
            //热门
            if ($frozen == 1){
                //初始化博客配置
                $configwritnum = M('configwrit')->select("id=1")[0]['configwrit_hotnum'];
                $where = "writings_view > {$configwritnum} $And $where";
            }
            //推荐
            if ($frozen == 2){
                $where = "writings_groom = 2 $And $where";
            }
        }
        //关键词搜索
        if (!empty($search)){
           $where = "writings_title like '%{$search}%'"; 
        }

        //翻页功能
        $page = get("page");
        $pageNum = 8;//初始化每页显示数量15
        //为了防止冲突，这里重新定义where
        if ($where != null){
            $countwhere = "where ". $where;
        }
        $queryCount = $dblist->sql("select count(id) as c from god_writings {$countwhere}")[0]['c'];//所有记录数量
        $pageCount = ceil($queryCount / $pageNum);//总页数
        if ($pageCount <= $page) $page = $pageCount;//计算当前页面大于总页数
        if ($page <= 1) $page = 1;//计算当前页面小于1
        $pageMy = ($page-1) * $pageNum;//计算当前页码
        $arrayPage = array("count"=>$pageCount,"page"=>$page,"num"=>$queryCount);
        $query = $dblist->select($where,null,"id","$pageMy,$pageNum");
        //获取博客配置
        $set = M('configwrit')->select('id=1')[0];
        $writingsrt = M('writingsrt')->select();
        display("index.html",array("data"=>$query,"arrayPage"=>$arrayPage,"set"=>$set,"writingsrt"=>$writingsrt));
    }
    
    //文章页
    function view(){
        $id = get('id');
        $dblist = M("writings");
        $data = $dblist->select("id={$id}")[0];
        if (!is_array($data)) exit('该文章不存在或者已经被删除!');
        //获取博客配置
        $set = M('configwrit')->select('id=1')[0];
        $writingsrt = M('writingsrt')->select();
        $dblist->update(array("writings_view"=>$data['writings_view']+1),"id={$id}");
        display('view.html',array("data"=>$data,"set"=>$set,"writingsrt"=>$writingsrt));
    }
    
    //h5
    function h5(){
        $dblist = M("advimage");
        $data = $dblist->select();

        display('h5.html',array('data'=>$data));
    }
}


?>