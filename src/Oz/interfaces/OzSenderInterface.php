<?php namespace Oz\interfaces;

use PhpAmqpLib\Message\AMQPMessage;

interface OzSenderInterface {
  /**
   * Function to prepare AMQP message
   * @param String $message
   */
  public function execute(String $message);

  /**
   * Callback for completed queue
   * @param AMQPMessage $req
   */
  public function onResponse(AMQPMessage $rep);
}