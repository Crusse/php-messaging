<?php

namespace Crusse\Messaging;

abstract class Endpoint {

  abstract function getAddress();

  function receive( Message $message ) {
    return $message;
  }

  function __toString() {
    return $this->getAddress();
  }
}

class ProxyEndpoint extends Endpoint {

  function getAddress() {
    return 'proxy';
  }

  function receive( Message $message ) {

    echo $this .' received '. $message . PHP_EOL;

    return new Message( str_replace( 'Original', 'Modified', $message ) );
  }
}

class FinalEndpoint extends Endpoint {

  function getAddress() {
    return 'final';
  }

  function receive( Message $message ) {

    echo $this .' received '. $message . PHP_EOL;

    return $message;
  }
}

class Router {

  static private $endpoints;
  
  static function registerEndpoint( Endpoint $endpoint ) {
    static::$endpoints[ $endpoint->getAddress() ] = $endpoint;
  }
  
  static function registerEndpoints( array $endpoints ) {

    foreach ( $endpoints as $endpoint )
      static::registerEndpoint( $endpoint );
  }
  
  static function getEndpointForAddress( $endpointName ) {
    return @static::$endpoints[ $endpointName ];
  }
}

class Message {

  private $payload;

  function __construct( $payload ) {
    $this->payload = $payload;
  }

  function __toString() {
    return (string) $this->payload;
  }

  function __get( $endpointName ) { return $this->sendTo( $endpointName ); }
  function __isset( $endpointName ) { return $this->sendTo( $endpointName ); }

  function sendTo( $endpointName ) {

    $endpoint = Router::getEndpointForAddress( $endpointName );

    if ( !$endpoint )
      throw new \InvalidArgumentException( 'Tried to send the message to a non-endpoint' );

    $message = $endpoint->receive( $this );

    return $message;
  }
}

Router::registerEndpoints( array(
  new ProxyEndpoint(),
  new FinalEndpoint()
) );

$message = new Message( 'Original foo' );

$message -> proxy -> final;


