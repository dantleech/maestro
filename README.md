Maestro
=======

[![Build Status](https://travis-ci.org/dantleech/maestro.svg?branch=master)](https://travis-ci.org/dantleech/maestro)

**This project is still in an experimental phase and has not been used to  orchestrate anything at this stage**

Maestro will be a micro-package orchestration and management tool.

Use it, for many packages in parallel, to:

- Apply file templates.
- Execute arbitrary commands(e.g. git commit, push etc).
- Run QA, fixer and migration tools (e.g. phpunit, phpcs, rector, whatevver).

It may also, in the future:

- Be able to create new packages (including github repo creation, possibly publishing to packagist)
- Perform releases
- Run as a local CI server

![out](https://user-images.githubusercontent.com/530801/58695020-3d61f200-838c-11e9-8ca0-de086cb7450a.png)

*Task Graph*

![Untitled](https://user-images.githubusercontent.com/530801/58994540-c643b800-87e8-11e9-8c83-83e8347298f9.png)

*Progress*

Background
----------

Maestro reads a configuration file in which you define the packages that you
wish to manage.

It can then clone these packages into a workspace directory (e.g.
`.local/share/maestro/123412312-my-project`).

Usage
-----

Maestro depends on a `maestro.json` configuration file such as:

```javascript
{
    "vars": {
        "php_versions": [ 7.1, 7.2, 7.3 ],
        "phpstan_level": 7
    },
    "packages": {
        "dantleech/fink": {
            "tasks": {
                "vcs": {
                    "type": "git",
                    "args": {
                        "url": "git@github.com:$PACKAGE_NAME"
                    }
                },
                "composer install": {
                    "type": "script",
                    "args": {
                        "script": "composer install"
                    },
                    "depends": "vcs"
                },
                "phpunit": {
                    "type": "script",
                    "args": {
                        "script": "./vendor/bin/phpunit"
                    },
                    "depends": "composer install"
                },
                "php-cs-fixer": {
                    "type": "script",
                    "args": {
                        "script": "./vendor/bin/php-cs-fixer fix lib --dry-run"
                    },
                    "depends": "composer install"
                }
            }
        }
    }
}
```

You can use `prototypes` as a base configuration, for example the above could
be re-written as:

```javascript
{
    "vars": {
        "my-random-param1": "foobar",
        "php_versions": [ "3", "6.0", "7.4" ]
    }
    "prototypes": {
        "base": {
            "tasks": {
                "vcs": {
                    "type": "git",
                    "args": {
                        "url": "git@github.com:$PACKAGE_NAME"
                    }
                },
                "composer install": {
                    "type": "script",
                    "args": {
                        "script": "composer install"
                    },
                    "depends": "vcs"
                },
                "phpunit": {
                    "type": "script",
                    "args": {
                        "script": "./vendor/bin/phpunit"
                    },
                    "depends": "composer install"
                },
                "php-cs-fixer": {
                    "type": "script",
                    "args": {
                        "script": "./vendor/bin/php-cs-fixer fix lib --dry-run"
                    },
                    "depends": "composer install"
                }
            }
        }
    }
    "packages": {
        "dantleech/fink": {
            "prototype": "base"
        }
    }
}
```

Extensions
----------

Extensions are fixed and enabled by default. Extensions provide unit "types"
which are detailed below.

### Maestro

The process extension uses Amphp to execute external processes, including git
operations.

#### Tasks

##### `script`

Execute an arbitrary script.

e.g.

```javascript
{
    "type": "script",
    "args": {
        "script": "echo 'This is my package '$PACKAGE_NAME"
    }
}
```

###### `json_file`

Create or manipulate a JSON file

e.g.

```javascript
{
    "type": "json",
    "args": {
        "targetPath": "composer.json",
        "merge": {
            "require-dev": {
                 "phpunit": "~7.0"
            },
            "extra": {
                "branch-alias": {
                    "dev-master": "0.1.x-dev"
                }
            }
        }
    }
}
```

##### `git`

Use this to clone a GIT repository for the package.

```javascript
{
    "type": "git",
    "args": {
        "url": "git@github.com:$PACKAGE_NAME"
    }
}
```

### Schedules

Schedules determine how often and/or when a task will be executed. They are
defined at the task level as follows:

```javascript
{
    "type": "git",
    "args": {
        "url": "git@github.com:$PACKAGE_NAME"
    },
    "schedule": {
        "type": "repeat",
        "args": {
            "delay": 10
        }
    }
}
```

If omitted the default is the `asap` schedule.

#### `asap`

The ASAP schedule will execute the task once as soon as possible.  This is the
default schedule.

```javascript
"schedule": {
    "type": "asap"
}
```

#### `repeat`

The ASAP schedule will execute the task once as soon as possible.  This is the
default schedule.

```javascript
"schedule": {
    "type": "repeat",
    "args": {
        "delay": 10
    }
}
```

### Twig

This extension allows you to apply templates to packages.

#### `template`

Apply a template:

e.g.

```javascript
{
    "type": "template",
    "args": {
        "path": "templates/README.md.twig"
        "targetPath": "README.md"
    }
}
```

- **path**: Where the template is located, relative to the Manifest file.
- **targetPath**: Where to put the rendered template, relative to the package path.

### Tmux

Automatically setup and switch to [Tmux](https://github.com/tmux/tmux/wiki)
sessions for package workspaces [tmux]

```bash
$ maestro tmux phpactor/worse-reflection
```

- If there is an existing Tmux session called `phpactor/worse-reflection`,
  then switch to it.
- If no session, exists create one.
- Switch the current tmux session to the new or existing one.

This is very useful to quicly create an environment where you can
manipulate the package after maestro has executed.

Note that this only works if you are already inside a Tmux session.

Documentation
-------------

Nope.

Contribution
------------

All contributions are welcome, make a PR.
