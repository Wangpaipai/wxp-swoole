<?php
/**
 * Created by PhpStorm.
 * User: 54714
 * Date: 2019/1/28
 * Time: 16:52
 */

namespace App\handlers;


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
		echo "接收到消息 $request->data \n";
		$ws->push($request->fd,'接收成功:' . $request->data);
	}

	/**
	 * 监听关闭连接
	 * Created by：Mp_Lxj
	 * @date 2019/1/28 16:59
	 * @param $ws
	 * @param $fd
	 */
	public function onClose($ws, $fd)
	{
		echo "断开连接 $fd \n";
	}
}