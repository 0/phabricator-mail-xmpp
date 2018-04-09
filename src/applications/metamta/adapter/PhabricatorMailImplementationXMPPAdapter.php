<?php

/**
 * Mail adapter that uses XMPP to send messages.
 */
final class PhabricatorMailImplementationXMPPAdapter
  extends PhabricatorMailImplementationAdapter {

  private $params = array();

  public function setFrom($email, $name = '') {
    return $this;
  }

  public function addReplyTo($email, $name = '') {
    return $this;
  }

  public function addTos(array $emails) {
    foreach ($emails as $email) {
      $this->params['tos'][] = $email;
    }
    return $this;
  }

  public function addCCs(array $emails) {
    foreach ($emails as $email) {
      $this->params['tos'][] = $email;
    }
    return $this;
  }

  public function addAttachment($data, $filename, $mimetype) {}

  public function addHeader($header_name, $header_value) {
    return $this;
  }

  public function setBody($body) {
    $this->params['body'] = $body;
    return $this;
  }

  public function setHTMLBody($body) {
    return $this;
  }

  public function setSubject($subject) {
    $this->params['subject'] = $subject;
    return $this;
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
    return array(
      'host' => PhabricatorEnv::getEnvConfig('xmpp.host'),
      'port' => PhabricatorEnv::getEnvConfig('xmpp.port'),
      'user' => PhabricatorEnv::getEnvConfig('xmpp.user'),
      'password' => PhabricatorEnv::getEnvConfig('xmpp.password'),
    );
  }

  /**
   * @phutil-external-symbol class Fabiang\Xmpp\Client
   * @phutil-external-symbol class Fabiang\Xmpp\Options
   * @phutil-external-symbol class Fabiang\Xmpp\Protocol\Message
   */
  public function send() {
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

    $subject = idx($this->params, 'subject');
    $body = idx($this->params, 'body');
    $msg = $subject."\n\n".$body;

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
      foreach (idx($this->params, 'tos', array()) as $to) {
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
