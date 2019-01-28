<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SwooleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole:action {event}';

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
        $this->ws = new \Swoole\WebSocket\Server('0.0.0.0', 5950);

        $this->ws->on('open', function ($ws, $request) {
            echo '连接成功';
        });
        //监听WebSocket消息事件
        $this->ws->on('message', function ($ws, $request) {
            echo '接收到消息：' . $request->data;
            $ws->push($ws->fd,'123');
        });
        $this->ws->on('close', function ($ws, $fd) {
            echo $fd . '已断开';
        });
        $this->ws->start();
    }
    /**
     * 停止websocket
     */
    private function stop()
    {
        $this->ws->stop(-1,false);
    }
    /**
     * 重启
     */
    private function restart()
    {
        $this->ws->reload(true);
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
