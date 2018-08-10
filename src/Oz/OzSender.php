<?php namespace Oz;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

use Oz\interfaces\OzSenderInterface;

class OzSender implements OzSenderInterface {
  /**
   * @var corr_id
   * @var response
   */
  private $corr_id;
  private $response;

  public function __construct() {
    $this->corr_id = uniqid();
    $this->response = null;
  }

  public function execute(String $message) {
    // 1. Create connection
    $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    $channel = $connection->channel();

    // 2. Declare queue
    list($callback_queue, ,) = $channel->queue_declare("", false, false, true, false);    

    // 3. Set to listening queue messages
    $channel->basic_consume(
      $callback_queue, '', false, false, false, false,  // Options
      [$this, 'onResponse']                             // Callback
    );
    
    // 4. Create message object
    $msg = new AMQPMessage(
      json_encode(['message' => $message]),
      ['correlation_id' => $this->corr_id, 'reply_to' => $callback_queue]
    );

    // 5. Create publish
    $channel->basic_publish($msg, '', 'rpc_queue');

    // 6. Waiting result
    while(!$this->response) {
      $channel->wait();
    }

    // 7. Closing connection
    $channel->close();
    $connection->close();

    // 8. Return execute result to client
    echo $this->response;
  }

  public function onResponse(AMQPMessage $rep) {
    if($rep->get('correlation_id') == $this->corr_id) {
        $this->response = $rep->body;
    }
  }
}