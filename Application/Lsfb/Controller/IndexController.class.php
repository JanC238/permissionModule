<?php
namespace Lsfb\Controller;

use Think\Controller;

class IndexController extends PublicController
{
    public function index()
    {
        $uid = $_SESSION["APPuserid"];
//    	if(empty($uid)){
//    		$this->redirect('/Login/login');
//    	}else{
        //空间限制
        $size = D("Size");
        $result = $size->sizefile();
        if ($result['num'] == 2) {
            $this->redirect('/Login/login');
        }
        //空间配额
        $this->namespacesize = $result['size'];//剩余容量
        $this->totalsize = C("DB_SIZE");//总共
        $this->usesize = $result['ysize'];//使用容量
        $this->fb = round($result['ysize'] / C("DB_SIZE") * 100);
        $this->assign('lastLogin', user()['reg_time']);
        //
        $smsApi = D('Sms', 'Api');
        $Interantional = $smsApi->gmSms();
        //$China = $smsApi->sms();
        $this->assign('International', $Interantional);
        $this->assign('China', $China);
//    	}

        //获取最新订单
        $orderModel = M('UserOrder');
        $orders = $orderModel->where(array('payway' => 1))->order('id desc')->select();
        //商家名
        $businModel = M('BusinUser');
        foreach ($orders as $key => $order) {
            $businRow = $businModel->find($order['bid']);
            $orders[$key]['businName'] = $businRow['name'];
        }
        $this->assign('orders', $orders);
        $row['name'] = user()['account'];
        $this->assign('userRow', $row);
//统计订单
        $today = strtotime('00:00');
        $tomorrow = $today + 86400;
        $yesterday = $today - 86400;
        $week = $today - 7 * 86400;
        $todayCond = array(
            'odtime' => array(array('egt', $today), array('lt', $tomorrow)),
        );

        $todayCount = $this->getOrder($todayCond);
        $this->assign('todayCount', $todayCount);

        $yesterdayCond = array(
            'odtime' => array(array('egt', $yesterday), array('lt', $today)),
        );
        $yesterdayCount = $this->getOrder($yesterdayCond);
        $this->assign('yesterdayCount', $yesterdayCount);

        $weekCond = array(
            'odtime' => array(array('egt', $week), array('lt', $today)),
        );
        $weekCount = $this->getOrder($weekCond);
        $this->assign('weekCount', $weekCount);

        $allCount = $this->getOrder(array());
        $this->assign('allCount', $allCount);
        //统计访客
        $this->counts = $this->counts();
        $this->display();
    }

    /**
     * 获取指定条件的订单数
     * @param $cond 查询条件
     * @return int  订单数
     */
    public function getOrder($cond)
    {
        $userOrderModel = M('UserOrder');
        $orders = $userOrderModel->where($cond)->select();
        return count($orders);
    }

    //退出登录
    public function exits()
    {
        if ($_SESSION['APPuserid']) {
            unset($_SESSION['APPuserid']);
        }
        $this->redirect('/Login/login');
    }
}