<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Mockery\Exception;


class UserController extends Controller
{
    /**
     * 展示给定用户的信息。
     *
     * @param  int  $id
     * @return Response
     */
    public function login(Request $request)
    {
        $username = $request['username'];
        $password = $request['password'];
        if ($username === 'sjy' && $password === 'S@309824650s') {
            session(['username'=>$username]);
        }
        if ($request->session()->get('username')) {
            return redirect('/e2bf79ac-8395-11ea-b654-f7a4f600176a');
        }
        return view('login');
    }


    function bbb1()
    {
        // 设置一些基本的变量
        $host = "127.0.0.1";
        $port = 1234;
// 设置超时时间
        set_time_limit(0);
// 创建一个Socket
        $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not createsocket\n");
//绑定Socket到端口
        $result = socket_bind($socket, $host, $port) or die("Could not bind tosocket\n");
// 开始监听链接
        $result = socket_listen($socket, 3) or die("Could not set up socketlistener\n");
// accept incoming connections
// 另一个Socket来处理通信
        $spawn = socket_accept($socket) or die("Could not accept incomingconnection\n");
// 获得客户端的输入
        $input = socket_read($spawn, 1024) or die("Could not read input\n");
// 清空输入字符串
        $input = trim($input);
//处理客户端输入并返回结果
        $output = strrev($input) . "\n";
        socket_write($spawn, $output, strlen ($output)) or die("Could not write
output\n");
// 关闭sockets
        socket_close($spawn);
        socket_close($socket);
    }



    function socket_fuwu()
    {
        //确保在连接客户端时不会超时
         set_time_limit(0);

  $ip = '127.0.0.1';
  $port = 1940;

  /*
   +-------------------------------
  *    @socket通信整个过程
  +-------------------------------
  *    @socket_create
  *    @socket_bind
  *    @socket_listen
  *    @socket_accept
  *    @socket_read
  *    @socket_write
  *    @socket_close
  +--------------------------------
  */

 /*----------------    以下操作都是手册上的    -------------------*/
 if(($sock = socket_create(AF_INET,SOCK_STREAM,SOL_TCP)) < 0) {
             echo "socket_create() 失败的原因是:".socket_strerror($sock)."\n";
 }

 if(($ret = socket_bind($sock,$ip,$port)) < 0) {
             echo "socket_bind() 失败的原因是:".socket_strerror($ret)."\n";
 }

 if(($ret = socket_listen($sock,4)) < 0) {
             echo "socket_listen() 失败的原因是:".socket_strerror($ret)."\n";
 }

 $count = 0;

 do {
             if (($msgsock = socket_accept($sock)) < 0) {
                     echo "socket_accept() failed: reason: " . socket_strerror($msgsock) . "\n";
         break;
     } else {
         //发到客户端
         $msg ="测试成功！\n";
         socket_write($msgsock, $msg, strlen($msg));

         echo "测试成功了啊\n";
         $buf = socket_read($msgsock,8192);


         $talkback = "收到的信息:$buf\n";
         echo $talkback;

         if(++$count >= 5){
                             break;
         };


     }
     //echo $buf;
     socket_close($msgsock);

 } while (true);

socket_close($sock);
    }


    public function bbbs()
    {
        for ($i=0;$i<10;$i++){
            $this->bbb();
        }
    }


    public function bbb()
    {
        error_reporting(E_ALL);
        set_time_limit(0);
        echo "<h2>TCP/IP Connection</h2>\n";

        $ip = '127.0.0.1';
        $port = 1940;

        /*
         +-------------------------------
         *  @socket连接整个过程
         +-------------------------------
         *  @socket_create
         *  @socket_connect
         *  @socket_write
         *  @socket_read
         *  @socket_close
         +--------------------------------
         */

        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket < 0) {
            echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
        }else {
            echo "OK.\n";
        }

        echo "试图连接 '$ip' 端口 '$port'...\n";
        try{
            $result = socket_connect($socket, $ip, $port);
        }catch (Exception  $e){
            echo 111;die;
            $this->socket_fuwu();
            $this->bbb();
        }

        if ($result < 0) {
            echo "socket_connect() failed.\nReason: ($result) " . socket_strerror($result) . "\n";
        }else {
            echo "连接OK\n";
        }

        $in = "Ho\r\n";
        $in .= "first blood\r\n";
        $out = '';

        if(!socket_write($socket, $in, strlen($in))) {
            echo "socket_write() failed: reason: " . socket_strerror($socket) . "\n";
        }else {
            echo "发送到服务器信息成功！\n";
            echo "发送的内容为:<font color='red'>$in</font> <br>";
        }

        while($out = socket_read($socket, 8192)) {
            echo "接收服务器回传信息成功！\n";
            echo "接受的内容为:",$out;
        }


        echo "关闭SOCKET...\n";
//        socket_close($socket);
        echo "关闭OK\n";
    }
}


?>