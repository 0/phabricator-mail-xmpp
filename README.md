# phabricator-mail-xmpp

A [Phabricator](https://phacility.com/phabricator/) mail adapter to send messages through [XMPP](https://en.wikipedia.org/wiki/XMPP).


## Caveats

* This will be used _in place of_ other mail adapters.
  Your Phabricator instance will not send out any email when this is enabled.
* This is only for outgoing messages.
  It will not receive any replies sent through XMPP.
* This is a bit of a hack, so use it at your own peril.
  If something goes wrong, messages could get sliently dropped.


## Requirements

In order for this mail adapter to be useful, you need:

* a Phabricator instance that you control, and
* an XMPP account that can send messages to your Phabricator users' XMPP accounts.


## Installation

1. Clone this repository alongside your Phabricator.
1. Configure your Phabricator instance:
    1. Run `bin/config set load-libraries '["phabricator-mail-xmpp/src"]'`.
        * If `load-libraries` is already set, you'll need to add to it instead of overwriting it.
    1. Set the configuration values in the "XMPP" section (`https://example.com/config/group/xmpp/`).
    1. Set `metamta.mail-adapter` to `PhabricatorMailImplementationXMPPAdapter`.
1. Ask your users to change their primary email addresses to their XMPP JIDs.


## Tips

The Phabricator `bin/mail` utility is very useful for testing.

If you don't need the information they produce, you can set

* `metamta.herald.show-hints`,
* `metamta.recipients.show-hints`, and
* `metamta.email-preferences`

all to `false` to make the sent messages shorter.


## License

This software is available under the same license as Phabricator itself (Apache 2.0).
See `NOTICE` for more information.

This software is distributed with other open source packages, found in `externals/`.
These packages are available under their own licenses:

* `externals/xmpp/vendor/composer/`: [Composer](https://github.com/composer/composer) 1.5.1 generated autoloader (MIT license)
* `externals/xmpp/vendor/fabiang/xmpp/`: [Fabian Grutschus's xmpp library](https://github.com/fabiang/xmpp) 0.7.0 (2-clause BSD license)
* `externals/xmpp/vendor/psr/log/`: [PSR Log](https://github.com/php-fig/log) 1.0.2 (MIT license)
