<?php

namespace App\Console\Commands;

use handlers\SwooleHandler;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class SwooleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole:server {event}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'swoole command';

    /**
     * Swoole constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $event = $this->argument('event'); // Command命令事件
        switch($event){
            case 'start':
                $this->start();
                break;
            case 'restart':
                $this->restart();
                break;
            case 'stop':
                $this->stop();
                break;
            default:
                break;
        }
    }

    /**
     * 开启websocket
     */
    private function start()
    {
        $ws = new \swoole_websocket_server('0.0.0.0', 5950);

        $ws->set([
            'reactor_num' => 1, //线程数  cpu核数
            'worker_num' => 4,    //worker进程数 全异步非阻塞服务器 worker_num配置为CPU核数的1-4倍即可。同步阻塞服务器，worker_num配置为100或者更高，具体要看每次请求处理的耗时和操作系统负载状况
            'backlog' => 128,   //列队长度
            'max_request' => 50,//表示worker进程在处理完n次请求后结束运行。manager会重新创建一个worker进程。此选项用来防止worker进程内存溢出。
            'dispatch_mode' => 1,//进程数据包分配模式 1平均分配，2按FD取模固定分配，3抢占式分配，默认为取模(dispatch=2)
            'max_conn ' => 1000,//最大连接数
        ]);

        $hander = App::make('handlers\SwooleHandler');

        $ws->on('open', [$hander,'onOpen']);
        //监听WebSocket消息事件
        $ws->on('message', [$hander,'onMessage']);
        $ws->on('close', [$hander,'onClose']);
        $ws->start();
    }
    /**
     * 停止websocket
     */
    private function stop()
    {
        $ws = Cache::get('ws');
        $ws->stop(-1,false);
    }
    /**
     * 重启
     */
    private function restart()
    {
        $ws = Cache::get('ws');
        $ws->reload(true);
    }
    /**
     * @param $ws
     * @param $room_id
     * @param string $user_id
     * @param string $message
     * @param string $type
     * @return bool
     */
    private function sendAll($ws, $room_id, $user_id = null, $message = null, $type = 'message')
    {
//        $user = $this->user->find($user_id, ['id', 'name']);
//        if (!$user) {
//            return false;
//        }
//        $message = json_encode([
//            'message' => is_string($message) ? nl2br($message) : $message,
//            'type' => $type,
//            'user' => $user
//        ]);
//        $members = Redis::zrange("room:{$room_id}" , 0 , -1);
//        foreach ($members as $fd) {
//            $ws->push($fd, $message);
//        }
    }
}
