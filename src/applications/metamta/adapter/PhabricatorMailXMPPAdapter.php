<?php

/**
 * Mail adapter that uses XMPP to send messages.
 */
final class PhabricatorMailXMPPAdapter
  extends PhabricatorMailAdapter {

  const ADAPTERTYPE = 'xmpp';

  public function getSupportedMessageTypes() {
    return array(
      PhabricatorMailEmailMessage::MESSAGETYPE,
    );
  }

  public function supportsMessageIDHeader() {
    return false;
  }

  protected function validateOptions(array $options) {
    PhutilTypeSpec::checkMap(
      $options,
      array(
        'host' => 'string',
        'port' => 'string',
        'user' => 'string',
        'password' => 'string',
      ));
  }

  public function newDefaultOptions() {
    return array(
      'host' => null,
      'port' => null,
      'user' => null,
      'password' => null,
    );
  }

  public function newLegacyOptions() {
    return $this->newDefaultOptions();
  }

  /**
   * @phutil-external-symbol class Fabiang\Xmpp\Client
   * @phutil-external-symbol class Fabiang\Xmpp\Options
   * @phutil-external-symbol class Fabiang\Xmpp\Protocol\Message
   */
  public function sendMessage(PhabricatorMailExternalMessage $message) {
    $root = dirname(phutil_get_library_root('phabricator-mail-xmpp'));
    require_once $root.'/externals/xmpp/vendor/autoload.php';

    $host = $this->getOption('host');
    $port = $this->getOption('port');
    $user = $this->getOption('user');
    $password = $this->getOption('password');

    if (!$host || !$port || !$user || !$password) {
      throw new Exception(
        pht(
          "Configure '%s', '%s', '%s', and '%s' to use XMPP to send messages.",
          'xmpp.host',
          'xmpp.port',
          'xmpp.user',
          'xmpp.password'));
    }

    $subject = $message->getSubject();
    if ($subject === null) {
      $subject = "";
    }
    $body = $message->getTextBody();
    if ($body === null) {
      $body = "";
    }
    $msg = $subject."\n\n".$body;

    $tos = array();
    foreach ($message->getToAddresses() as $address) {
      $tos[] = (string)$address;
    }
    foreach ($message->getCCAddresses() as $address) {
      $tos[] = (string)$address;
    }

    $options = new Fabiang\Xmpp\Options('tcp://'.$host.':'.$port);
    $options->setUsername($user)
      ->setPassword($password);

    $client = new Fabiang\Xmpp\Client($options);
    $client->connect();

    // The interface that we're implementing is designed around sending a
    // single email, the outcome of which can either be success, temporary
    // failure, or permanent failure. Hence, we're able to signal exactly one
    // of these scenarios.
    //
    // This adapter sends multiple messages, each with one recipient. These
    // messages may succeed and fail independently, so it's not always clear
    // what to signal. For example, we might signal failure if any messages at
    // all fail, but this could cause those that succeeded to be resent,
    // generating extra noise.
    //
    // Lacking a guiding principle, we choose the simplest option for error
    // handling: we close our eyes and hope for the best. We let exceptions
    // bubble up, but otherwise always claim success.

    try {
      foreach ($tos as $to) {
        $message = new Fabiang\Xmpp\Protocol\Message();
        $message->setTo($to)
          ->setMessage($msg);
        $client->send($message);
      }
    } finally {
        $client->disconnect();
    }

    return true;
  }
}
