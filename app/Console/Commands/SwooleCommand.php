<?php

namespace App\Console\Commands;

use handlers\SwooleHandler;
use Illuminate\Console\Command;

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
            'backlog' => 128,   //列队长度
            'max_request' => 50,//表示worker进程在处理完n次请求后结束运行。manager会重新创建一个worker进程。此选项用来防止worker进程内存溢出。
            'max_conn ' => 1000,//最大连接数
            'heartbeat_check_interval' => 5,//心跳检测间隔时间
            'heartbeat_idle_time' => 10000,//心跳检测等待时间  超过10秒格杀勿论
        ]);

        $hander = app(SwooleHandler::class);

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
        $cli = new \swoole_http_client('127.0.0.1', 5950);
        $cli->set([
            'websocket_mask' => true,
            'ssl_host_name' => 'www.lxj520.xyz',
        ]);
        $cli->setHeaders([
            'Host' =>  'www.lxj520.xyz',
            'UserAgent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36'
        ]);

        $cli->on('message', function ($cli, $frame) {
            var_dump($frame);
        });

        $cli->upgrade('/', function ($cli) {
            $data = [
                'type' => 'swoole_stop'
            ];
            echo $cli->body;
            $cli->push(json_encode($data));
            $cli->close();
        });
    }
    /**
     * 重启
     */
    private function restart()
    {
        $cli = new \swoole_http_client('127.0.0.1', 5950);
        $cli->set([
            'websocket_mask' => true,
            'ssl_host_name' => 'www.lxj520.xyz',
        ]);
        $cli->setHeaders([
            'Host' =>  'www.lxj520.xyz',
            'UserAgent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/63.0.3239.132 Safari/537.36'
        ]);

        $cli->on('message', function ($cli, $frame) {
            var_dump($frame);
        });

        $cli->upgrade('/', function ($cli) {
            $data = [
                'type' => 'swoole_restart'
            ];
            echo $cli->body;
            $cli->push(json_encode($data));
            $cli->close();
        });
    }
}
