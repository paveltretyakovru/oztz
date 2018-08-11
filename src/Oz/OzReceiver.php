<?php namespace Oz;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

use Oz\interfaces\OzReceiverInterface;

class OzReceiver implements OzReceiverInterface {
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
      [$this, 'callback']
    );


    while(count($channel->callbacks)) {
      $channel->wait();
    }

    $channel->close();
    $connection->close();
  }

  public function prepareUserMessage(String $value) {
    if(!empty($value)) {
      $sqlQuery = 'select * from `messages`  where `ozname` = :param';

      $db_host = $this->db_props['db_host'];
      $db_dbname = $this->db_props['db_dbname'];
      $db_username = $this->db_props['db_username'];
      $db_password = $this->db_props['db_password'];

      $dbDriver = \ByJG\AnyDataset\Factory::getDbRelationalInstance(
        "mysql://${db_username}:${db_password}@${db_host}/${db_dbname}"
      );

      $iterator = $dbDriver->getIterator($sqlQuery, ['param' => $value]);
      $arr = $iterator->toArray();

      if(count($arr) > 0) {
        return json_encode(
          ['status' => true, 'value' => strrev($arr[0]['ozname'])]
        );
      }
    }

    return json_encode(['status' => false, 'value' => '']);
  }

  public function callback(AMQPMessage $req) {
    // Routing configs
    $msgConfig = ['correlation_id' => $req->get('correlation_id')];
    $routingKey = $req->get('reply_to');
    
    // Preparing data to message
    $userMessage = json_decode($req->body, true)['message'];
    $preparedMessage = $this->prepareUserMessage($userMessage);

    // Create message object
    $msg = new AMQPMessage($preparedMessage, $msgConfig);

    // Publishing message
    $req->delivery_info['channel']
      ->basic_publish($msg, '', $routingKey);

    $req->delivery_info['channel']
      ->basic_ack($req->delivery_info['delivery_tag']);
  }
}