<?php
/**
 * Created by PhpStorm.
 * User: 54714
 * Date: 2019/1/28
 * Time: 16:52
 */

namespace handlers;


class SwooleHandler
{
	/**
	 * 监听连接事件
	 * Created by：Mp_Lxj
	 * @date 2019/1/28 16:58
	 * @param \swoole_websocket_server $ws
	 * @param $request
	 */
	public function onOpen(\swoole_websocket_server $ws, $request)
	{
		echo "$request->fd 连接成功\n";
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
		$data = json_decode($request->data,true);
		if(is_array($data)){
			switch($data['type']){
				case 'msg':
					$this->sendMsg($ws,$data,$request->fd);
					break;
				case 'swoole_stop':
					$ws->stop(-1);
					echo '关闭成功' . "\n";
					break;
				case 'swoole_restart':
					$ws->reload(false);
					echo '重启成功' . "\n";
					break;
				default:
					$data = [
						'type' => 'msg',
						'msg' => '数据类型错误'
					];
					$ws->push($request->fd,json_encode($data));
					break;
			}
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
		echo '接收到消息:' .json_encode($data). "\n";
		$ws->push($fd,'接收成功:' . $data['msg']);
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
		echo "$fd 断开连接  \n";
	}
}