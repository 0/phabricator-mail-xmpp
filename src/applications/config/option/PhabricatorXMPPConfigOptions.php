<?php

final class PhabricatorXMPPConfigOptions
  extends PhabricatorApplicationConfigOptions {

  public function getName() {
    return pht('XMPP');
  }

  public function getDescription() {
    return pht('Configure XMPP.');
  }

  public function getIcon() {
    return 'fa-send-o';
  }

  public function getGroup() {
    return 'core';
  }

  public function getOptions() {
    return array(
      $this->newOption('xmpp.host', 'string', null)
        ->setLocked(true)
        ->setDescription(pht('XMPP server hostname.')),
      $this->newOption('xmpp.port', 'string', null)
        ->setLocked(true)
        ->setDescription(pht('XMPP server port.')),
      $this->newOption('xmpp.user', 'string', null)
        ->setLocked(true)
        ->setDescription(pht('XMPP account username.')),
      $this->newOption('xmpp.pass', 'string', null)
        ->setHidden(true)
        ->setDescription(pht('XMPP account password.')),
    );
  }

}
