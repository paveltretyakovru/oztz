<?php namespace Oz\interfaces;

use PhpAmqpLib\Message\AMQPMessage;

interface OzReceiverInterface {
  /**
   * Function to listen AMQP messages
   */
  public function listen();

  /**
   * Callback function to maked message
   * @param AMQPMessage $req
   */
  public function callback(AMQPMessage $req);

  /**
   * Function to prepare input user data
   * @param String $value
   */
  public function prepareUserMessage(String $value);
}