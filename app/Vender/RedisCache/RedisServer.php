<?php
namespace RedisCache;

use Illuminate\Support\Facades\Redis;
class RedisServer
{
	/**
	 * Created by：Mp_Lxj
	 * @date 2018/11/29 13:49
	 * +----------------------------------------------------------------------------------
	 * + redis无法直接存入数组或对象，需手动转换，此处已封装
	 * +----------------------------------------------------------------------------------
	 * + 每个键此处设置了一个前缀，方法名为setKey
	 * +----------------------------------------------------------------------------------
	 * + 存值之前先转换值，setVal方法，方法判断是否是数组或对象，自动序列化数组或对象
	 * + 取值时需转换值，getVal方法，判断是否是数组或对象，若是则反序列化
	 * +----------------------------------------------------------------------------------
	 */
	private $prefix = 'swoole_data:';//redis前缀  谨慎修改，修改后之前缓存将全部无法使用

	/**
	 * 构造函数，实例化时可传入自定义前缀
	 * RedisServer constructor.
	 * @param string $prefix
	 */
	public function __construct($prefix = '')
	{
		if($prefix){
			$this->prefix = $prefix;
		}
	}

	/**
	 * 设置redis缓存
	 * Created by：Mp_Lxj
	 * @date 2018/11/29 10:07
	 * @param $key
	 * @param $val
	 * @param $timeout-过期时间，单位秒
	 */
	public function set($key,$val,$timeout = 0)
	{
		$key = $this->setKey($key);
		$val = $this->setVal($val);
		if($timeout){
			Redis::setex($key,$timeout,$val);
		}else{
			Redis::set($key,$val);
		}
	}

	/**
	 * 获取缓存内容
	 * Created by：Mp_Lxj
	 * @date 2018/11/29 10:22
	 * @param $key
	 * @return mixed
	 */
	public function get($key)
	{
		$key = $this->setKey($key);
		$val = Redis::get($key);
		return $this->getVal($val);
	}

	/**
	 * 删除Redis缓存
	 * Created by：Mp_Lxj
	 * @date 2018/11/30 9:17
	 * @param $key
	 */
	public function del($key)
	{
		$key = $this->setKey($key);
		Redis::del($key);
	}

	/**
	 * 当且仅当键不存在时写入
	 * Created by：Mp_Lxj
	 * @date 2018/11/30 9:36
	 * @param $key
	 * @param $val
	 * @param int $timeout-过期时间，不传默认永久
	 */
	public function add($key,$val,$timeout = 0)
	{
		$key = $this->setKey($key);
		$val = $this->setVal($val);
		Redis::setnx($key,$val);
		if($timeout){
			Redis::expire($key,$timeout);
		}
	}

	/**
	 * 取出并删除
	 * Created by：Mp_Lxj
	 * @date 2018/11/30 9:47
	 * @param $key
	 * @return mixed
	 */
	public function pull($key)
	{
		$value = $this->get($key);
		$this->del($key);
		return $value;
	}

	/**
	 * 自增
	 * Created by：Mp_Lxj
	 * @date 2018/11/30 9:39
	 * @param $key
	 * @param int $num--默认增长为1
	 */
	public function increment($key,int $num = 1)
	{
		$key = $this->setKey($key);
		Redis::incrby($key,$num);
	}

	/**
	 * 自减
	 * Created by：Mp_Lxj
	 * @date 2018/11/30 9:40
	 * @param $key
	 * @param int $num--默认减1
	 */
	public function decrement($key,int $num = 1)
	{
		$key = $this->setKey($key);
		Redis::decrby($key,$num);
	}

	/**
	 * 设置键
	 * Created by：Mp_Lxj
	 * @date 2018/11/29 10:12
	 * @param $key
	 * @return string
	 */
	private function setKey($key)
	{
		return $this->prefix . $key;
	}

	/**
	 * 设置值---redis的数组和object需要手动转字符
	 * Created by：Mp_Lxj
	 * @date 2018/11/29 10:14
	 * @param $val
	 * @return string
	 */
	private function setVal($val)
	{
		if(is_array($val) || is_object($val)){
			$val = $this->prefix . serialize($val);
		}
		return $val;
	}

	/**
	 * 重新反序列化值--若是数组或对象这反序列化   不是则原样返回
	 * Created by：Mp_Lxj
	 * @date 2018/11/29 10:20
	 * @param $val
	 * @return mixed
	 */
	private function getVal($val)
	{
		if(strpos($val,$this->prefix) === 0){
			$value = substr($val,strlen($this->prefix));
			$val = unserialize($value);
		}
		return $val;
	}

	/**
	 * 哈希 set
	 * Created by：Mp_Lxj
	 * @date 2019/1/31 14:35
	 * @param $key1
	 * @param $key2
	 * @param $val
	 */
	public function hSet($key1,$key2,$val)
	{
		$key = $this->setKey($key1);
		$val = $this->setVal($val);
		Redis::hset($key,$key2,$val);
	}

	/**
	 * 获取hash表的值
	 * Created by：Mp_Lxj
	 * @date 2019/1/31 14:43
	 * @param $key1
	 * @param $key2
	 * @return mixed
	 */
	public function hGet($key1,$key2)
	{
		$key = $this->setKey($key1);
		$val = Redis::hget($key,$key2);
		return $this->getVal($val);
	}

	/**
	 * 返回哈希列表中所有的keys
	 * Created by：Mp_Lxj
	 * @date 2019/1/31 14:38
	 * @param $key
	 * @return mixed
	 */
	public function hKeys($key)
	{
		$key = $this->setKey($key);
		return Redis::hkeys($key);
	}

	/**
	 * 获取hash表所有的内容  key=>val
	 * Created by：Mp_Lxj
	 * @date 2019/1/31 16:26
	 * @param $key
	 * @return mixed
	 */
	public function hGetAll($key)
	{
		$key = $this->setKey($key);
		return Redis::hgetall($key);
	}

	/**
	 * 删除哈希列表的键
	 * Created by：Mp_Lxj
	 * @date 2019/1/31 14:40
	 * @param $key1
	 * @param $key2
	 */
	public function hDel($key1,$key2)
	{
		$key = $this->setKey($key1);
		Redis::hdel($key, $key2) ;
	}

	/**
	 * 写入链表----在链表前面写入
	 * Created by：Mp_Lxj
	 * @date 2019/1/31 14:55
	 * @param $key
	 * @param $value
	 */
	public function lPush($key,$value)
	{
		$key = $this->setKey($key);
		$val = $this->setVal($value);
		Redis::lpush($key,$val);
	}

	/**
	 * 写入链表  在链表后面写入
	 * Created by：Mp_Lxj
	 * @date 2019/1/31 14:56
	 * @param $key
	 * @param $value
	 */
	public function rPush($key,$value)
	{
		$key = $this->setKey($key);
		$val = $this->setVal($value);
		Redis::rpush($key,$val);
	}

	/**
	 * 获取链表的值
	 * Created by：Mp_Lxj
	 * @date 2019/1/31 15:02
	 * @param $key
	 * @param int $start
	 * @param int $end
	 * @return mixed
	 */
	public function lRange($key,$start = 0,$end = -1)
	{
		$key = $this->setKey($key);
		$res = Redis::lrange($key,$start,$end);
		foreach($res as &$value){
			$value = $this->getVal($value);
		}
		return $res;
	}

	/**
	 * 删除链表指定内容的值
	 * Created by：Mp_Lxj
	 * @date 2019/1/31 15:10
	 * @param $key
	 * @param $val
	 * @param int $num
	 */
	public function lRem($key,$val,$num = 1)
	{
		$key = $this->setKey($key);
		$val = $this->setVal($val);
		Redis::lrem($key,$num,$val);
	}
}