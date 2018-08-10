<?php namespace Oz;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class OzReceiver {
  private $db_props;

  public function __construct(Array $db_props) {
    $this->db_props = $db_props;
  }
  
  public function listen() {
    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    $channel = $connection->channel();

    $channel->queue_declare('rpc_queue', false, false, false, false);
    $channel->basic_qos(null, 1, null);

    $channel->basic_consume (
      'rpc_queue', '', false, false, false, false,
      function (AMQPMessage $req) {
        $userMessage = json_decode($req->body, true)['message'];
        $preparedMessage = $this->prepareUserMessage($userMessage);
        
        echo "Result: ";
        print_r($preparedMessage);

        $msg = new AMQPMessage(
          json_encode($preparedMessage),    	                         #message
          ['correlation_id' => $req->get('correlation_id')]  #options
        );

        $req->delivery_info['channel']->basic_publish(
          $msg,               	#message
          '',                 	#exchange
          $req->get('reply_to')   #routing key
        );

        $req->delivery_info['channel']->basic_ack(
          $req->delivery_info['delivery_tag'] #delivery tag
        );
      }
    );


    while(count($channel->callbacks)) {
      $channel->wait();
    }

    $channel->close();
    $connection->close();
  }

  public function prepareUserMessage(String $value) {
    if(!empty($value)) {
      $db_host = $this->db_props['db_host'];
      $db_dbname = $this->db_props['db_dbname'];
      $db_username = $this->db_props['db_username'];
      $db_password = $this->db_props['db_password'];

      $dbDriver = \ByJG\AnyDataset\Factory::getDbRelationalInstance(
        "mysql://${db_username}:${db_password}@${db_host}/${db_dbname}"
      );

      $iterator = $dbDriver->getIterator('select * from `messages`  where `ozname` = :param', ['param' => $value]);
      $arr = $iterator->toArray();

      return (count($arr) > 0)
        ? ['status' => true, 'value' => strrev($arr[0]['ozname'])]
        : ['status' => false, 'value' => ''];
    } else {
      return ['status' => false, 'value' => ''];
    }
  }
}