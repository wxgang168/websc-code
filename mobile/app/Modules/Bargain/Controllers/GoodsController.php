<?php

namespace App\Modules\Bargain\Controllers;

use App\Modules\Base\Controllers\FrontendController;

class GoodsController extends FrontendController
{
    private $user_id = 0;
    private $goods_id = 0;
    private $region_id = 0;
    private $area_info = [];

    /**
     * 构造函数
     */
    public function __construct()
    {
        parent::__construct();
        L(require(LANG_PATH . C('shop.lang') . '/team.php'));
        $files = [
            'order',
            'clips',
            'payment',
            'transaction'
        ];
        $this->load_helper($files);

        $this->user_id = !empty($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
        $this->keywords = I('request.keywords');
        $this->bargain_id = I('id', 0, 'intval');//活动id
        $this->bs_id = I('bs_id', 0, 'intval');//参与活动id

        $this->page = 1;
        $this->size = 10;
    }


    /**
     *砍价商品详情
     */
    public function actionIndex()
    {
        if ($this->bargain_id == 0) {
            /* 如果没有传入id则跳回到首页 */
            ecs_header("Location: ./\n");
            exit;
        }
        //商品信息
        $goods = get_bargain_goods_info($this->bargain_id);
        if(empty($goods)){
            show_message('活动审核中或者已关闭，去查看新的活动吧', '查看新的活动', url('bargain/index/index'), 'success');
        }
        //验证砍价活动是否结束
        if ($this->bargain_id) {
            $bargain_info = bargain_is_failure($this->bargain_id);
            if ($bargain_info['status'] == 1 || gmtime() > $bargain_info['end_time']) {
                show_message('该砍价活动已结束，去查看新的活动吧', '查看新的活动', url('bargain/index/index'), 'success');
            }
        }

        //验证是否参与当前活动跳转url && $this->bs_id
        if ($this->bargain_id) {
            $add_bargain = is_add_bargain($this->bargain_id,$this->user_id);
            if ($add_bargain && $add_bargain['id'] != $this->bs_id) {
                $this->redirect('bargain/goods/index', ['id' => $this->bargain_id,'bs_id' =>$add_bargain['id']]);
            }
        }

        //验证是否参与当前活动标示
        $add_bargain = is_add_bargain($this->bargain_id,$this->user_id);
        if($add_bargain){
            $this->assign('add_bargain', 1);//已参加
        }

        /* --验证是否砍价-- */
        if(!empty($this->bs_id)){
            $bargain_info = is_bargain_join($this->bs_id,$this->user_id);
            if ($bargain_info) {
                $this->assign('bargain_join', 1);//已砍价标示
                $this->assign('bargain_info', $bargain_info); //砍价记录
            }

            //亲友帮
            $bargain_list = get_bargain_statistics($this->bs_id);
            $this->assign('bargain_list', $bargain_list);//已砍价

            //曲线图
            $graph_list = get_bargain_graph_list($this->bs_id);
            $this->assign('graph', $graph_list);//曲线图

            //砍后价格,选择属性
            $bargain_log = $this->db->table('bargain_statistics_log')->field('goods_attr_id,final_price')->where(['id' =>$this->bs_id])->find();
            $this->assign('final_price', $bargain_log['final_price']);//已砍价

            //获取选中活动属性原价，底价
            if($bargain_log['goods_attr_id']){
                $spec = explode(",", $bargain_log['goods_attr_id']);
                //$goods['goods_price'] = bargain_goods_price($this->bargain_id,$goods['goods_id'],$spec);//原价
                $goods['goods_price'] = get_final_price($goods['goods_id'], 1, true, $spec, $warehouse_id, $area_id);//原价
                $goods['target_price'] = bargain_target_price($this->bargain_id,$goods['goods_id'],$spec);//底价
            }

            //进度条
            $surplus = $goods['goods_price'] - $goods['target_price'];//差价
            //已砍价
            $subtract = $this->db->table('bargain_statistics')->where(['bs_id' =>$this->bs_id])->sum('subtract_price');
            $bargain_bar = round($subtract * 100 / $surplus, 0);//百分比
            $this->assign('bargain_bar', $bargain_bar);//进度条
        }
        //排行榜
        $bargain_ranking = get_bargain_goods_ranking($this->bargain_id);
        $rank = copy_array_column($bargain_ranking, 'user_id');
        $rank = array_search($this->user_id, $rank);
        $rank = $rank+1;
        $this->assign('rank', $rank);//排行名次
        $this->assign('bargain_ranking', $bargain_ranking);//排行榜

        //砍价爆款
        $bargain_hot = get_bargain_goods_hot();

        if ($goods['user_id'] > 0) {
            //商家所有商品评分类型汇总
            $merchants_goods_comment = get_merchants_goods_comment($goods['user_id']);
            $this->assign('merch_cmt', $merchants_goods_comment);
        }

        //店铺关注人数
        $sql = "SELECT count(*) FROM " . $this->ecs->table('collect_store') . " WHERE ru_id = " . $goods['user_id'];
        $collect_number = $this->db->getOne($sql);
        $this->assign('collect_number', $collect_number ? $collect_number : 0);
        //评分 end
        $sql = "select b.is_IM,a.ru_id,a.province, a.city, a.kf_type, a.kf_ww, a.kf_qq, a.meiqia, a.shop_name, a.kf_appkey,kf_secretkey from {pre}seller_shopinfo as a left join {pre}merchants_shop_information as b on a.ru_id=b.user_id where a.ru_id='" . $goods['user_id'] . "' ";
        $basic_info = $this->db->getRow($sql);

        $info_ww = $basic_info['kf_ww'] ? explode("\r\n", $basic_info['kf_ww']) : '';
        $info_qq = $basic_info['kf_qq'] ? explode("\r\n", $basic_info['kf_qq']) : '';
        $kf_ww = $info_ww ? $info_ww[0] : '';
        $kf_qq = $info_qq ? $info_qq[0] : '';
        $basic_ww = $kf_ww ? explode('|', $kf_ww) : '';
        $basic_qq = $kf_qq ? explode('|', $kf_qq) : '';
        $basic_info['kf_ww'] = $basic_ww ? $basic_ww[1] : '';
        $basic_info['kf_qq'] = $basic_qq ? $basic_qq[1] : '';

        if (($basic_info['is_im'] == 1 || $basic_info['ru_id'] == 0) && !empty($basic_info['kf_appkey'])) {
            $basic_info['kf_appkey'] = $basic_info['kf_appkey'];
        } else {
            $basic_info['kf_appkey'] = '';
        }

        $basic_date = ['region_name'];
        $basic_info['province'] = get_table_date('region', "region_id = '" . $basic_info['province'] . "'", $basic_date, 2);
        $basic_info['city'] = get_table_date('region', "region_id= '" . $basic_info['city'] . "'", $basic_date, 2) . "市";
        $this->assign('basic_info', $basic_info);

        //生成二维码链接
        $shopurl = __HOST__ . $_SERVER['REQUEST_URI'];
        $this->assign('shopurl', $shopurl);

        // 获得商品的规格和属性
        $properties = get_goods_properties($goods['goods_id'], $this->region_id, $this->area_info['region_id']);
        //默认选中的商品规格
        $default_spe = '';
        if ($properties['spe']) {
            foreach ($properties['spe'] as $k => $v) {
                if ($v['attr_type'] == 1) {
                    if ($v['is_checked'] > 0) {
                        foreach ($v['values'] as $key => $val) {
                            $default_spe .= $val['checked'] ? $val['label'] . '、' : '';
                        }
                    } else {
                        foreach ($v['values'] as $key => $val) {
                            if ($key == 0) {
                                $default_spe .= $val['label'] . '、';
                            }
                        }
                    }
                }
            }
        }
        //查询商品描述
        $info = $this->db->table('goods')->field('goods_desc,desc_mobile')->where(array('goods_id' => $goods['goods_id']))->find();
        // 查询关联商品描述
        $sql = "SELECT ld.goods_desc FROM {pre}link_desc_goodsid AS dg, {pre}link_goods_desc AS ld WHERE dg.goods_id = {$goods['goods_id']}  AND dg.d_id = ld.id AND ld.review_status > 2";
        $link_desc = $this->db->getOne($sql);
        if (!empty($info['desc_mobile'])) {
            // 处理手机端商品详情 图片（手机相册图） data/gallery_album/
            if (C('shop.open_oss') == 1) {
                $bucket_info = get_bucket_info();
                $bucket_info['endpoint'] = empty($bucket_info['endpoint']) ? $bucket_info['outside_site'] : $bucket_info['endpoint'];
                $desc_preg = get_goods_desc_images_preg($bucket_info['endpoint'], $info['desc_mobile'], 'desc_mobile');
                $goods_desc = preg_replace('/<div[^>]*(tools)[^>]*>(.*?)<\/div>(.*?)<\/div>/is', '', $desc_preg['desc_mobile']);
            } else {
                $goods_desc = preg_replace('/<div[^>]*(tools)[^>]*>(.*?)<\/div>(.*?)<\/div>/is', '', $info['desc_mobile']);
            }
        }

        if (empty($info['desc_mobile']) && !empty($info['goods_desc'])) {
            if (C('shop.open_oss') == 1) {
                $bucket_info = get_bucket_info();
                $bucket_info['endpoint'] = empty($bucket_info['endpoint']) ? $bucket_info['outside_site'] : $bucket_info['endpoint'];
                $goods_desc = str_replace(['src="/images/upload', 'src="images/upload'], 'src="' . $bucket_info['endpoint'] . 'images/upload', $info['goods_desc']);

                // $desc_preg = get_goods_desc_images_preg($bucket_info['endpoint'], $info['goods_desc']);
                // $goods_desc = $desc_preg['goods_desc'];
            } else {
                $goods_desc = str_replace(['src="/images/upload', 'src="images/upload'], 'src="' . __STATIC__ . '/images/upload', $info['goods_desc']);
            }
        }
        if (empty($info['desc_mobile']) && empty($info['goods_desc'])) {
            $goods_desc = $link_desc;
        }
        /** 如果样式错位，直接去添加手机端内容。
         * $goods_desc = preg_replace("/height\=\"[0-9]+?\"/", "", $goods_desc);
         * $goods_desc = preg_replace("/width\=\"[0-9]+?\"/", "", $goods_desc);
         * $goods_desc = preg_replace("/style=.+?[*|\"]/i", "", $goods_desc);
         */
        $this->assign('goods_desc', $goods_desc);//商品详情

        $this->assign('default_spe', $default_spe);
        $this->assign('properties', $properties['pro']);                                 // 商品规格参数
        $this->assign('specification', $properties['spe']);                        // 商品规格属性

        $this->assign('pictures', get_goods_gallery($goods['goods_id']));     // 商品相册
        $this->assign('bargain_id', $goods['id']);
        $this->assign('goods_id', $goods['goods_id']);
        $this->assign('bs_id', $this->bs_id);
        $this->assign('goods', $goods);
        $this->assign('bargain_hot', $bargain_hot);

        // 微信JSSDK分享
        $share_data = [
            'title' => '砍价商品_' . $goods['goods_name'],
            'desc' => $goods['goods_brief'],
            'link' => '',
            'img' => $goods['goods_img'],
        ];
        $this->assign('share_data', $this->get_wechat_share_content($share_data));

        /** 判断客服目录 */
        if (is_dir(dirname(ROOT_PATH) . '/kefu')) {
            $this->assign('kefu', 1);
        }

        $this->assign('page_title', $goods['goods_name']);
        $this->display();
    }


    /**
     * 我要参与
     */
    public function actionAddbargain()
    {
        $this->check_login();
        $goods_id = I('goods_id', 0, 'intval');
        $bargain_id = I('id', 0, 'intval');
        $number = 1;
        $warehouse_id = I('request.warehouse_id', 0, 'intval');
        $area_id = I('request.area_id', 0, 'intval'); //仓库管理的地区ID
        //选中活动商品属性
        $specs = '';
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'spec_') !== false) {
                $specs .= ',' . intval($value);
            }
        }
        $specs = trim($specs, ',');
        $spec = explode(",", $specs);
        //获取砍价商品价格
        $bargain_goods = $this->db->table('bargain_goods')->field('goods_price,total_num')->where(['id' =>$bargain_id])->find();

        //获取砍价商品属性价格
        if($specs){
            //$goods_price = bargain_goods_price($bargain_id,$goods_id,$spec);//原价
            $goods_price = get_final_price($goods_id, $number, true, $spec, $warehouse_id, $area_id);
        }else{
            $goods_price = $bargain_goods['goods_price'];
        }

        //插入参与活动记录表
        $data['bargain_id'] = $bargain_id;
        $data['goods_attr_id'] = $specs;
        $data['user_id'] = $this->user_id;
        $data['final_price'] = $goods_price;
        $data['add_time'] = gmtime();
        $new_bargain = $this->db->filter_field('bargain_statistics_log', $data);
        $bargain_log_id = $this->db->table('bargain_statistics_log')->data($new_bargain)->add();

        //更新活动参与人数
        $total_num = $bargain_goods['total_num'] +1;
        $this->db->table('bargain_goods')->data(['total_num' => $total_num])->where(['id' =>$bargain_id])->save();

        $this->redirect('bargain/goods/index', ['id' => $bargain_id,'bs_id' =>$bargain_log_id]);
    }


     /**
      * 去砍价
      */
    public function actionGobargain()
    {
        $result = [
            'error' => '',
            'message' => ''
        ];
        $bs_id = I('bs_id', 0, 'intval');//活动id
        $bargain_id = I('id', 0, 'intval');//发起活动id
        $goods_id =  I('goods_id', 0, 'intval');//商品id

        if (!isset($this->user_id) || $this->user_id == 0) {
            $result['error'] = 1;
            $result['message'] = L('login_please');
            die(json_encode($result));
        }

        //砍掉价格计算模式

        //活动砍价信息
        $bargain = $this->db->table('bargain_goods')->field('goods_price,target_price,min_price,max_price')->where(['id' =>$bargain_id])->find();
        //参与活动记录
        $bs_log = $this->db->table('bargain_statistics_log')->field('goods_attr_id,final_price,count_num')->where(['id' =>$bs_id])->find();
        //获取选中活动属性原价，底价
        if($bs_log['goods_attr_id']){
            $spec = explode(",", $bs_log['goods_attr_id']);
            $bargain['target_price'] = bargain_target_price($bargain_id,$goods_id,$spec);//底价
        }
        //验证是否重复参与砍价
        $number = $this->db->table('bargain_statistics')->where(array('user_id' =>$this->user_id,'bs_id' =>$bs_id))->count();
        if($number){
            $result = [
                'error' => 3,
                'message' => '您已参与砍价，参加新的活动吧！'
            ];
            die(json_encode($result));
        }
        //砍价规则
        if($bargain['target_price'] == $bs_log['final_price']){
            $result = [
                'error' => 3,
                'message' => '已砍至最低价格，参加新的活动吧！'
            ];
            die(json_encode($result));
        }else{
            $subtract_price = rand($bargain['min_price'], $bargain['max_price']);//砍掉价格区间
            $subtract = $bs_log['final_price'] - $subtract_price;//已砍价到

            if($subtract < $bargain['target_price']){
                $subtract_price = $bs_log['final_price'] - $bargain['target_price'];
            }
        }
        $data['bs_id'] = $bs_id;
        $data['user_id'] = $this->user_id;
        $data['subtract_price'] = $subtract_price;//砍掉价格
        $data['add_time'] = gmtime();
        $subtract_price =price_format($data['subtract_price']);
        if($this->db->table('bargain_statistics')->data($data)->add() === false){
            $result = [
                'error' => 2,
                'message' => '砍价失败'
            ];
        }else{
            //更新参与砍价人数 和砍后最终购买价
            $count_num = $bs_log['count_num']+1;
            $final_price = $bs_log['final_price'] - $data['subtract_price']; //砍后价格
            $this->db->table('bargain_statistics_log')->data(['final_price' => $final_price,'count_num' => $count_num])->where(['id' =>$bs_id])->save();
            $result = [
                'error' => 4,
                'subtract_price' => $subtract_price,
                'message' => '砍价成功'
            ];
        }
        die(json_encode($result));

    }

    /**
     * 砍价改变属性、数量时重新计算商品价格
     */
    public function actionBargainprice()
    {
        $res = ['err_msg' => '', 'result' => '', 'qty' => 1];
        $attr = I('attr');
        $bargain_id = I('bargain_id', 1, 'intval');//活动ID
        $number = I('number', 1, 'intval');
        $attr_id = !empty($attr) ? explode(',', $attr) : [];//属性
        $warehouse_id = I('request.warehouse_id', 0, 'intval');
        $area_id = I('request.area_id', 0, 'intval'); //仓库管理的地区ID
        $onload = I('request.onload', '', 'trim');

        //商品信息
        $goods = get_bargain_goods_info($bargain_id);

        $goods_attr = isset($_REQUEST['goods_attr']) ? explode(',', $_REQUEST['goods_attr']) : [];
        $attr_ajax = get_goods_attr_ajax($goods['goods_id'], $goods_attr, $attr_id);

        if ($bargain_id == 0) {
            $res['err_msg'] = L('err_change_attr');
            $res['err_no'] = 1;
        } else {
            if ($number == 0) {
                $res['qty'] = $number = 1;
            } else {
                $res['qty'] = $number;
            }
            //ecmoban模板堂 --zhuo start
            $products = get_warehouse_id_attr_number($goods['goods_id'], $_REQUEST['attr'], $goods['user_id'], $warehouse_id, $area_id);
            $attr_number = $products['product_number'];
            if ($goods['model_attr'] == 1) {
                $table_products = "products_warehouse";
                $type_files = " and warehouse_id = '$warehouse_id'";
            } elseif ($goods['model_attr'] == 2) {
                $table_products = "products_area";
                $type_files = " and area_id = '$area_id'";
            } else {
                $table_products = "products";
                $type_files = "";
            }

            $sql = "SELECT * FROM " . $GLOBALS['ecs']->table($table_products) . " WHERE goods_id = '".$goods['goods_id']."'" . $type_files . " LIMIT 0, 1";
            $prod = $GLOBALS['db']->getRow($sql);

            if ($goods['goods_type'] == 0) {
                $attr_number = $goods['goods_number'];
            } else {
                if (empty($prod)) { //当商品没有属性库存时
                    $attr_number = $goods['goods_number'];
                }

                if (!empty($prod) && $GLOBALS['_CFG']['add_shop_price'] == 0) { //当商品没有属性库存时
                    if (empty($attr_number)) {
                        $attr_number = $goods['goods_number'];
                    }
                }
            }

            $attr_number = !empty($attr_number) ? $attr_number : 0;
            $res['attr_number'] = $attr_number;

            //限制用户购买的数量 bywanglu
            if($attr_id){
                $shop_price = get_final_price($goods['goods_id'], $number, true, $attr_id, $warehouse_id, $area_id);
                //ecmoban模板堂 --zhuo end
                $res['shop_price'] = price_format($shop_price);
                $res['market_price'] = $goods['market_price'];

                $res['show_goods'] = 0;

                if ($goods_attr && $GLOBALS['_CFG']['add_shop_price'] == 0) {
                    if (count($goods_attr) == count($attr_ajax['attr_id'])) {
                        $res['show_goods'] = 1;
                    }
                }
                //属性价格
                $spec_price = get_final_price($goods['goods_id'], $number, true, $attr_id, $warehouse_id, $area_id, 1, 0, 0, $res['show_goods']);

                if ($GLOBALS['_CFG']['add_shop_price'] == 0 && empty($spec_price)) {
                    $spec_price = $shop_price;
                }
                $res['spec_price'] = price_format($spec_price);
                $martetprice_amount = $spec_price + $goods['marketPrice'];
                $res['marketPrice_amount'] = price_format($spec_price + $goods['marketPrice']);

                //切换属性后的价格折扣 by wanglu
                $res['discount'] = round($shop_price / $martetprice_amount, 2) * 10;
                $res['result'] = price_format($shop_price);//商品价格不跟随增加数量而增加，之前代码 $res['result'] = price_format($shop_price * $number);
                //砍价最低价
                $target_price =  bargain_target_price($bargain_id,$goods['goods_id'],$attr_id, $warehouse_id, $area_id);
                $res['target_price'] = price_format($target_price);
            }else{
                $res['result'] = price_format($goods['goods_price']);
                $res['target_price'] = price_format($goods['target_price']);
            }

        }
        $goods_fittings = get_goods_fittings_info($goods['goods_id'], $warehouse_id, $area_id, '', 1);
        $fittings_list = get_goods_fittings([$goods['goods_id']], $warehouse_id, $area_id);

        if ($fittings_list) {
            if (is_array($fittings_list)) {
                foreach ($fittings_list as $vo) {
                    $fittings_index[$vo['group_id']] = $vo['group_id'];//关联数组
                }
            }
            ksort($fittings_index);//重新排序

            $merge_fittings = get_merge_fittings_array($fittings_index, $fittings_list); //配件商品重新分组
            $fitts = get_fittings_array_list($merge_fittings, $goods_fittings);

            for ($i = 0; $i < count($fitts); $i++) {
                $fittings_interval = $fitts[$i]['fittings_interval'];

                $res['fittings_interval'][$i]['fittings_minMax'] = price_format($fittings_interval['fittings_min']) . "-" . number_format($fittings_interval['fittings_max'], 2, '.', '');
                $res['fittings_interval'][$i]['market_minMax'] = price_format($fittings_interval['market_min']) . "-" . number_format($fittings_interval['market_max'], 2, '.', '');

                if ($fittings_interval['save_minPrice'] == $fittings_interval['save_maxPrice']) {
                    $res['fittings_interval'][$i]['save_minMaxPrice'] = price_format($fittings_interval['save_minPrice']);
                } else {
                    $res['fittings_interval'][$i]['save_minMaxPrice'] = price_format($fittings_interval['save_minPrice']) . "-" . number_format($fittings_interval['save_maxPrice'], 2, '.', '');
                }

                $res['fittings_interval'][$i]['groupId'] = $fittings_interval['groupId'];
            }
        }


        if ($GLOBALS['_CFG']['open_area_goods'] == 1) {
            $area_list = get_goods_link_area_list($goods['goods_id'], $goods['user_id']);
            if ($area_list['goods_area']) {
                if (!in_array($area_id, $area_list['goods_area'])) {
                    $res['err_no'] = 2;
                }
            } else {
                $res['err_no'] = 2;
            }
        }
        $attr_info = get_attr_value($goods['goods_id'], $attr_id[0]);
        if (!empty($attr_info['attr_img_flie'])) {
            $res['attr_img'] = get_image_path($attr_info['attr_img_flie']);
        }
        $res['onload'] = $onload;
        die(json_encode($res));
    }

     /*
    *拼团商品 --> 购买
    */

    public function actionBargainbuy()
    {
        $result = [
            'error' => '',
            'message' => ''
        ];
        if (!isset($this->user_id) || $this->user_id == 0) {
            $result = [
                'error' => 1,
                'message' => L('login_please')
            ];
            die(json_encode($result));
        }
        /* 查询：取得数量 */
        $goods_id = I('goods_id', 0, 'intval'); //商品id
        $bargain_id = I('bargain_id', 0, 'intval');//活动id
        $bs_id = I('bs_id', 0, 'intval');//参与活动id
        $number = I('number', 1, 'intval');

        //砍价商品信息
        $goods = get_bargain_goods_info($bargain_id);
        //参与活动记录
        $bs_log = $this->db->table('bargain_statistics_log')->field('goods_attr_id,final_price,count_num')->where(['id' =>$bs_id])->find();
        $specs = $bs_log['goods_attr_id'];

        $products = get_warehouse_id_attr_number($goods_id, $specs , $goods['user_id'], $warehouse_id, $area_id);
        $attr_number = $products['product_number'];
        if ($goods['model_attr'] == 1) {
            $table_products = "products_warehouse";
            $type_files = " and warehouse_id = '$warehouse_id'";
        } elseif ($goods['model_attr'] == 2) {
            $table_products = "products_area";
            $type_files = " and area_id = '$area_id'";
        } else {
            $table_products = "products";
            $type_files = "";
        }

        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table($table_products) . " WHERE goods_id = '$goods_id'" . $type_files . " LIMIT 0, 1";
        $prod = $GLOBALS['db']->getRow($sql);

        if ($goods['goods_type'] == 0) {
            $attr_number = $goods['goods_number'];
        } else {
            if (empty($prod)) { //当商品没有属性库存时
                $attr_number = $goods['goods_number'];
            }

            if (!empty($prod) && $GLOBALS['_CFG']['add_shop_price'] == 0 && $onload == 'onload') {
                if (empty($attr_number)) {
                    $attr_number = $goods['goods_number'];
                }
            }
        }

        $attr_number = !empty($attr_number) ? $attr_number : 0;
        /* 查询：判断数量是否足够 */
        if ($number > $attr_number) {
            $result = [
                'error' => 3,
                'message' => '当前库存不足'
            ];
            die(json_encode($result));
        }

        /* 查询：查询规格名称和值，不考虑价格 */
        $attr_list = [];
        $sql = "SELECT a.attr_name, g.attr_value " .
            "FROM " . $GLOBALS['ecs']->table('goods_attr') . " AS g, " .
            $GLOBALS['ecs']->table('attribute') . " AS a " .
            "WHERE g.attr_id = a.attr_id " .
            "AND g.goods_attr_id " . db_create_in($specs);
        $res = $GLOBALS['db']->query($sql);
        foreach ($res as $row) {
            $attr_list[] = $row['attr_name'] . ': ' . $row['attr_value'];
        }
        $goods_attr = join(chr(13) . chr(10), $attr_list);
        /* 更新：清空购物车中所有团购商品 */
        clear_cart(CART_BARGAIN_GOODS);

        //ecmoban模板堂 --zhuo start

        $area_info = get_area_info($this->province_id);
        $this->area_id = $area_info['region_id'];

        $where = "regionId = '$this->province_id'";
        $date = ['parent_id'];
        $this->region_id = get_table_date('region_warehouse', $where, $date, 2);

        if (!empty($_SESSION['user_id'])) {
            $sess = "";
        } else {
            $sess = real_cart_mac_ip();
        }

        //购买最终价格
        $bs_log =  dao('bargain_statistics_log')->field('final_price')->where(['id' =>$bs_id])->find();//参与活动记录
        $goods_price = $bs_log['final_price'];

        /* 更新：加入购物车 */
        $cart = [
            'user_id' => $_SESSION['user_id'],
            'session_id' => $sess,
            'goods_id' => $goods['goods_id'],
            'product_id' => $product_info['product_id'],
            'goods_sn' => addslashes($goods['goods_sn']),
            'goods_name' => addslashes($goods['goods_name']),
            'market_price' => $goods['market_price'],
            'goods_price' => $goods_price,
            'goods_number' => $number,
            'goods_attr' => addslashes($goods_attr),
            'goods_attr_id' => $specs,
            //ecmoban模板堂 --zhuo start
            'ru_id' => $goods['user_id'],
            'warehouse_id' => $this->region_id,
            'area_id' => $this->area_id,
            'add_time' => gmtime(),
            //ecmoban模板堂 --zhuo end
            'is_real' => $goods['is_real'],
            'extension_code' => addslashes($goods['extension_code']),
            'parent_id' => 0,
            'rec_type' => CART_BARGAIN_GOODS,
            'is_gift' => 0,
            'is_shipping' => $goods['is_shipping'],
            'is_checked' => 1
        ];
        $this->db->autoExecute($GLOBALS['ecs']->table('cart'), $cart, 'INSERT');
        /* 更新：记录购物流程类型：团购 */
        $_SESSION['flow_type'] = CART_BARGAIN_GOODS;
        $_SESSION['extension_code'] = 'bargain_buy';
        $_SESSION['extension_id'] = '';
        $_SESSION['bs_id'] = $bs_id;

        $result['error'] = 2;
        $result['message'] = '添加成功';
        die(json_encode($result));

    }

    /**
     * 验证是否登录
     */
    private function check_login()
    {
        if (!($_SESSION['user_id'] > 0)) {
            $url = urlencode(__HOST__ . $_SERVER['REQUEST_URI']);
            if (IS_POST) {
                $url = urlencode($_SERVER['HTTP_REFERER']);
            }
            ecs_header("Location: " . url('user/login/index', ['back_act' => $url]));
            exit;
        }
    }
}
