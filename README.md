Horde Git Tools
===============

Toolchain for dealing with the split repository structure of
[Horde](https://github.com/horde)

Installation
------------

Use Composer for dependency management. For instructions how to install
Composer, see https://getcomposer.org/download/.

Make sure to install Composer version 1.x as Composer version 2 dropped
PEAR support.

Hint:
```sh
php composer-setup.php --1
```

Then clone this repository and install the dependencies. If you are developing
horde and using PHP 8, you need to set the flag `--ignore-platform-reqs`.


```sh
git clone https://github.com/horde/git-tools.git horde-git-tools
cd horde-git-tools
composer.phar install
```

Configuration
-------------

The main configuration is done in the horde-git-tools/config/ directory:

```sh
cp config/conf.php.dist config/conf.php
```

Then review config/conf.php and set values accordingly.

Per-directory configurations are possible too. Just add a .horde-git-tools.php
file in the base directory of a (planned) Git checkout and set any
configuration items there that you like to overwrite from the default global
configuration. E.g. to use a different webroot to run different Horde versions
in parallel:

```php
<?php
// Secondary directory for git checkouts.
$conf['git_base'] = '/home/user/horde5';

// Secondary target webroot for the installation.
$conf['web_base'] = '/var/www/horde5';
```

Usage
-----

Also see the --help text.

```sh
# The horde-git-tools command can be found at the horde-git-tools/bin directory.

# Options can also be given on command line. See usage for information.
horde-git-tools --help

# Clones all repositories locally to the configured git_base directory.
horde-git-tools git clone

# Links (or copies) to a web accessible directory (replacement for old
# install_dev script).
horde-git-tools dev install

# List available repositories on remote.
# Providing the --verbose flag will output full response from GitHub.
horde-git-tools --verbose git list

# Attempt to checkout a specific branch on all repositories.
horde-git-tools git checkout FRAMEWORK_5_2

# Attempt to git pull --rebase all repositories.
# Still need to add options like ability to ensure repo is on a specific
# branch before pulling, option to automatically stash/pop if repository is
# not clean etc...
horde-git-tools git pull

# Attempt to perform arbitrary git command on all repositories.
horde-git-tools git run "reset HEAD"

# Do the same, but only for imp and ansel.
horde-git-tools git run --repositories=imp,ansel "reset HEAD"

# Report on status of each repository.
# Still need to tweak and add options, better display etc...
horde-git-tools git status

# Perform a "component" action
horde-git-tools component /path/to/repository update
horde-git-tools component /path/to/repository changed '[mjr] Some change'
horde-git-tools component /path/to/repository release
```

Still to do
-----------

-  Create install action that will perform a full pear install of the webmail or
   groupware bundle (or optionally a specified list of applications).


