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
        $ws = new swoole_webSocket_server('0.0.0.0', 5950);

        $ws->on('open', function ($ws, $request) {
        });
        //监听WebSocket消息事件
        $ws->on('message', function ($ws, $frame) {
        });
        $ws->on('close', function ($ws, $fd) {
        });
        $ws->start();
    }
    /**
     * 停止websocket
     */
    private function stop()
    {
    }
    /**
     * 重启
     */
    private function restart()
    {
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
