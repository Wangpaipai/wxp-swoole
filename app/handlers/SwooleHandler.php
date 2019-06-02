<?php
/**
 * Created by PhpStorm.
 * User: 54714
 * Date: 2019/1/28
 * Time: 16:52
 */

namespace handlers;


use RedisCache\RedisServer;

class SwooleHandler
{
    public function onStart()
    {
        swoole_set_process_name("swoole_service");
    }

	/**
	 * 监听连接事件
	 * Created by：Mp_Lxj
	 * @date 2019/1/28 16:58
	 * @param \swoole_websocket_server $ws
	 * @param $request
	 */
	public function onOpen(\swoole_websocket_server $ws, $request)
	{
		$Redis = new RedisServer();
		$Redis->rPush('connect',$request->fd);
		$arr = [
			'type' => 'setFd',
			'fd' => $request->fd
		];
		$ws->push($request->fd,json_encode($arr));
	}

	/**
	 * 监听接收消息
	 * Created by：Mp_Lxj
	 * @date 2019/1/28 16:59
	 * @param \swoole_websocket_server $ws
	 * @param $request
	 */
	public function onMessage(\swoole_websocket_server $ws, $request)
	{
	    echo $request->fd;
		$data = json_decode($request->data,true);
		if(is_array($data)){
			switch($data['type']){
				case 'msg':
					$this->sendMsg($ws,$data,$request->fd);
					break;
				case 'setData':
					$this->setData($ws,$data,$request->fd);
					break;
				case 'getUserAll':
					$this->getUserAll($ws,$request->fd);
					break;
				case 'swoole_stop':
					$ws->stop(-1);
					break;
				case 'swoole_restart':
					$ws->reload(false);
					break;
				default:
					$data = [
						'type' => 'error',
						'msg' => '数据类型错误'
					];
					$ws->push($request->fd,json_encode($data));
					break;
			}
		}
	}

	/**
	 * 获取当前在线的所有用户
	 * Created by：Mp_Lxj
	 * @date 2019/1/31 16:14
	 * @param $ws
	 * @param $fd
	 */
	public function getUserAll($ws,$fd)
	{
		$Redis = new RedisServer();
		$fdAll = $Redis->hGetAll('user_data');
		$data = [];
		foreach($fdAll as $key=>$value){
			$value['fd'] = $key;
			$data[] = $value;
		}
		$arr = [
			'type' => 'getUserAll',
			'data' => $data
		];
		$ws->push($fd,json_encode($arr));
	}

	/**
	 * 设置资料
	 * Created by：Mp_Lxj
	 * @date 2019/1/31 14:37
	 * @param $ws
	 * @param $data
	 * @param $fd
	 */
	public function setData($ws,$data,$fd)
	{
		$Redis = new RedisServer();
		$arr = [
			'name' => $data['name'] ?? '',
			'icon' => $data['icon'] ?? '',
			'uid' => $data['uid'] ?? ''
		];
		$Redis->hSet('user_data',$fd,$arr);

		//回执发送成功
		$send = [
			'type' => 'set_data_succ'
		];
		$ws->push($fd,json_encode($send));

		$fdAll = $Redis->hKeys('user_data');
		$arr['fd'] = $fd;
		$notice = [
			'type' => 'addUser',
			'data' => $arr
		];
		foreach($fdAll as $value){
			$ws->push($value,json_encode($notice));
		}
	}

	/**
	 * 发送消息
	 * Created by：Mp_Lxj
	 * @date 2019/1/31 9:18
	 * @param $ws
	 * @param $data
	 * @param $fd
	 */
	public function sendMsg($ws,$data,$fd)
	{
		$Redis = new RedisServer();
		$userData = $Redis->hGet('user_data',$fd);
//		$fdAll = $Redis->hKeys('user_data');
//		$connAll = $Redis->lRange('connect');

		//群发信息
		$arr = [
			'type' => 'msg',
			'fd' => $fd,
			'data' => [
				'user' => $userData,
				'msg' => $data['msg'],
				'time' => date('Y/m/d H:i')
			]
		];
		$ws->push($data['toFd'],json_encode($arr));
	}

	/**
	 * 监听关闭连接
	 * Created by：Mp_Lxj
	 * @date 2019/1/28 16:59
	 * @param $ws
	 * @param $fd
	 */
	public function onClose(\swoole_websocket_server $ws, $fd)
	{
		$Redis = new RedisServer();
		$Redis->hDel('user_data',$fd);
		$Redis->lRem('connect',$fd);

		//通知某某下线了
		$connAll = $Redis->lRange('connect');
		$arr = [
			'type' => 'close_notice',
			'fd' => $fd
		];

		foreach($connAll as $value){
			$ws->push($value,json_encode($arr));
		}
	}
}