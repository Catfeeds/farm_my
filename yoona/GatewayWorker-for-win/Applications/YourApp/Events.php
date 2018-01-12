<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

use \GatewayWorker\Lib\Gateway;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {
        $_SESSION['myid']=$client_id;
        // 向当前client_id发送数据
//        Gateway::sendToClient($client_id, "Hello $client_id\r\n");
//        // 向所有人发送
//        Gateway::sendToAll("$client_id login\r\n");

    }
    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message)
   {
       $message_data=json_decode($message,true);
       $redis=new Redis();
       $redis->connect('127.0.0.1',6379);

       if(isset($message_data['room_id']))   //如果存在就将这个id加入这个组
       {
           switch ($message_data['room_id']){
               case 15:
                   $data=$message_data['room_id'];
                   $back_data=$redis->lgetrange('water',0,$redis->lsize('water'));

                  //  Gateway::joinGroup($client_id, $data);  //加入15 大厅聊天

                   if ($back_data!=""){
                       Gateway::sendToClient($client_id, json_encode($back_data));
                   }

                   break;
               case 17:
                   $redis->rpush('rg',$client_id); //将id存入缓存，进行排队,放到最后
                   $data=$message_data['room_id'];
                   Gateway::joinGroup($client_id, $data); //加入17 人工客服排队
                   break;
               case 16:
                   $data=$message_data['room_id'];
                   Gateway::joinGroup($client_id, $data); //加入工作人员端
                   break;
           }
       }

//water

       if (isset($message_data['my_room_id'])){
           switch ($message_data['my_room_id']){
               case 15:
                   $redis->rpush('water',$message); //加入聊天记录
                   if ($redis->lsize('water')>40){    //如果大于40就排除，保持40个
                       $redis->lpop('water');
                   }

                   Gateway::sendToGroup(15,$message);//将消息发给大厅
                   Gateway::sendToClient($client_id,$message);
                   break;
               case 17:
                   $i=$redis->get('toket');

                   //如果小于1就说明工作人员不在线
                   if (Gateway::getClientCountByGroup(16)<1){
                       Gateway::sendToClient($client_id,json_encode('系统维护中！'));
					   exit();
                    }

                   //如果等于空说明没有人，拿出第一个
                   if ($i==""){
                       $i=$redis->lpop('rg');
                       $redis->set('toket',$i);
                   }

                   //如果缓存中的id是自己就将消息发给人工客服
                   if ($i==$client_id){
                       Gateway::sendToGroup(16,json_encode($message_data['data']));//将消息发给客服

                   }else{
                       Gateway::sendToClient($client_id,json_encode('当前人数太多！请稍后！'));
                   }
                   break;
               case 16:          //工作人员发送至缓存中的id

                   $i=$redis->get('toket');   //判断toket

                   if ($i==""){
                       if ($redis->lsize("rg")>0){    //日过i>0那么就取出来1个
                           $i=$redis->lpop('rg');
                       }else{
                           Gateway::sendToClient($client_id,json_encode("无在线用户"));
                           exit();
                       }
                   }
                   $p=self::ifuid($i, $redis);   //判断该用户是否已经下线，如果是，就取下一个出来，如果缓存中每有了返回false

                    if ($p){
                        Gateway::sendToClient($p,json_encode($message_data['data']));
                    }else{
                        Gateway::sendToClient($client_id,json_encode("无在线用户"));
                    }
                   break;
               case 20:          //关闭当前链接，到下一个客户去
                   $i=$redis->get('toket');
                   Gateway::sendToClient($i,json_encode("人工服务已结束"));
                   Gateway::closeClient($i); //断开链接
                   $redis->set('toket',"");
                   if ($redis->lsize("rg")>0){
                       $i=$redis->lpop('rg');  //拿一个出来
                       $redis->set('toket',$i);
                       Gateway::sendToClient($client_id,json_encode('链接已建立！'));
                       Gateway::sendToClient($i,json_encode('区块链客服为您服务！'));
                   }else{
                       Gateway::sendToClient($client_id,json_encode("无在线用户"));
                   }

                   break;
           }
       }
        // 向所有人发送
//        Gateway::sendToAll("$client_id said $message\r\n");
//       if (isset($message_data['data'])){
//           Gateway::sendToAll(json_encode($message_data['data']));
//       }else{
//           Gateway::sendToClient($client_id, "Hello $client_id\r\n");
//       }


   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */

   public static function onClose($client_id)
   {
       $redis=new Redis();
       $redis->connect('127.0.0.1',6379);
       $toket=$redis->get('toket');

      if ($client_id==$toket){     //如果用户断开连接，那么就清空toket，并告诉工作人员，该用户已下线
          $redis->set("toket","");
          Gateway::sendToGroup(16,json_encode("用户已下线"));
      }
       // 向所有人发送
      // GateWay::sendToAll("$client_id logout\r\n");
   }




    public  static function ifuid($i,$redis){
//        $i=$redis->get('toket');
        if (Gateway::isOnline($i)){
            $redis->set("toket",$i);
            return $i;
            exit();
        };

        if ($redis->lsize("rg")>0){   //如果>0继续取出
            $i=$redis->lpop('rg');
        }else{
            $redis->set('toket',"");
            return false;
            exit();
        }
        self::ifuid($i, $redis);
    }

}
