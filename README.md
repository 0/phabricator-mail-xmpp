# phabricator-mail-xmpp

A [Phabricator](https://phacility.com/phabricator/) mail adapter to send messages through [XMPP](https://en.wikipedia.org/wiki/XMPP).


## Caveats

* This will be used _in place of_ other mail adapters.
  Your Phabricator instance will not send out any email when this is enabled and working.
* This is only for outgoing messages.
  It will not receive any replies sent through XMPP.
* This is a bit of a hack, so use it at your own peril.
  If something goes wrong, messages could get silently dropped.


## Requirements

In order for this mail adapter to be useful, you need:

* a Phabricator instance that you control, and
* an XMPP account that can send messages to your Phabricator users' XMPP accounts.


## Installation

1. Clone this repository alongside your Phabricator.
1. Configure your Phabricator instance:
    1. Run `bin/config set load-libraries '["phabricator-mail-xmpp/src"]'`.
        * If `load-libraries` is already set, you'll need to add to it instead of overwriting it.
    1. Set `cluster.mailers` as described below.
1. Ask your users to change their primary email addresses to their XMPP JIDs.


### Configuration

The `options` for this mailer in `cluster.mailers` are: `host`, `port`, `user`, `password`.

As recommended in [Configuring Outbound Email](https://secure.phabricator.com/book/phabricator/article/configuring_outbound_email/), it's easiest to make a `mailers.json` file and run
```
bin/config set cluster.mailers --stdin < mailers.json
```
The file should look something like
```
[
  {
    "key": "xmpp",
    "type": "xmpp",
    "options": {
      "host": "example.com",
      "port": "5222",
      "user": "xmppuser",
      "password": "xmpppass"
    }
  }
]
```

It should in principle be possible to configure other mailers as well, for failover or load balancing, but those concepts don't really jibe with the spirit of this mail adapter.


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
