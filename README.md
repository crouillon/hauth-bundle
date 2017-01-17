hauth-bundle
============

[![Build Status](https://travis-ci.org/Lp-digital/hauth-bundle.svg?branch=master)](https://travis-ci.org/Lp-digital/hauth-bundle)
[![Code Climate](https://codeclimate.com/github/Lp-digital/hauth-bundle/badges/gpa.svg)](https://codeclimate.com/github/Lp-digital/hauth-bundle)
[![Test Coverage](https://codeclimate.com/github/Lp-digital/hauth-bundle/badges/coverage.svg)](https://codeclimate.com/github/Lp-digital/hauth-bundle/coverage)

**hauth-bundle** enables to easily implement social signin on BackBee instances throw [hybridauth/hydridauth](http://hybridauth.sourceforge.net/) library.

Currently, only the firewall **rest_api_area** is supported (ie BackBee authentication to the toolbar).


Installation
------------
Edit the file `composer.json` of your BackBee project.

Add the new dependency to the bundle in the `require` section:
```json
# composer.json
...
    "require": {
        ...
        "lp-digitalhauth-bundle": "~0.1"
    },
...
```

Save and close the file.

Run a composer update on your project.


Activation
----------
Edit the file `repository/Config/bundles.yml` of your BackBee project.

Add the following line at the end of the file:
```yaml
# bundles configuration - repository/Config/bundles.yml
...
hauth: LpDigital\Bundle\HAuthBundle\HAuth
```

Save and close the file.

Then launch the command to update database:
```
./backbee bundle:update hauth --force
```

Depending on your configuration, cache may need to be clear.


Configuration
-------------
Create and edit the configuration file `repository/Config/bundle/hauth/config.yml` in your BackBee project.

The configuration mainly follow the configuration syntax of hybridauth, see [http://hybridauth.sourceforge.net/userguide.html](http://hybridauth.sourceforge.net/userguide.html).
```yaml
hybridauth:
    store_user_profile: true       # Is the social user's profile will be stored in db?        
    firewalls: ['rest_api_area']   # An array of BackBee firewalls for which hauth-bundle will propose hydrid authentication
    base_url: /hauth.html          # The entry point of the hybrid authentication
    debug_mode: false
    providers:
        {Provider name}:           # Provider name, see hybridauth documentation for supported list
            enabled: {true|false}  # Is the provider enabled?
            fa-icon:               # Optional, the Font Awesome icon class name for the provider
            scope:                 # The permissions requested to the provider
            keys:
                id:                # your provider application key
                secret:            # your provider secret token
        ...
```


---

*This project is supported by [Lp digital](http://www.lp-digital.fr/en/)*

**Lead Developer** : [@crouillon](https://github.com/crouillon)

Released under the GPL3 License
