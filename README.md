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

![Maestro in action](https://user-images.githubusercontent.com/530801/57580279-7c90d780-749f-11e9-88c3-5bc3fa74afd6.png)

*Monitoring progress*

![Console output](https://user-images.githubusercontent.com/530801/57580324-f7f28900-749f-11e9-8d41-1f773cf627b3.png)

*Executing arbitrary commands*

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
    "parameters": {
        "my-random-param1": "foobar",
        "php_versions": [ "3", "6.0", "7.4" ]
    }
    "repositories": {
        "dantleech/fink": {
            "parameters": {
                "package_specific_param": "will override global parameters above"
            }
            "manifest": {
                "initialize": {
                    "type": "checkout",
                    "parameters": {
                        "purge": true
                    }
                },
                "readme": {
                    "type": "template",
                    "parameters": {
                        "from": "template/README.md.twig",
                        "to": "README.md"
                    },
                    "depends": ["initialize"]
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
    "parameters": {
        "my-random-param1": "foobar",
        "php_versions": [ "3", "6.0", "7.4" ]
    }
    "prototypes": {
        "base": {
            "parameters": {
                "package_specific_param": "will override global parameters above"
            }
            "manifest": {
                "initialize": {
                    "type": "checkout",
                    "parameters": {
                        "purge": true
                    }
                },
                "readme": {
                    "type": "template",
                    "parameters": {
                        "from": "template/README.md.twig",
                        "to": "README.md"
                    },
                    "depends": ["initialize"]
                }
            }
        }
    }
    "repositories": {
        "dantleech/fink": {
            "prototype": "base"
        }
    }
}
```

Monitoring
----------

- Monitor progress using `--progress=someprogresshere`.
- Use `-v` to view each packages console output (like `docker-compose logs`
  for example).

Extensions
----------

Extensions are fixed and enabled by default. Extensions provide unit "types"
which are detailed below.

### Process

The process extension uses Amphp to execute external processes, including git
operations.

#### `checkout`

This item is responsible for checking out the repository. If the repository
is already checked out, then the default behavior is to ignore it.

Params:

- `purge`: If the repository should removed and re-installed on each request.

#### `process`

Use this item to execute an arbitrary process.

Params:

- `command`: Command to execute, e.g. `./vendor/bin/phpunit` or `composer
  install`

### Template

The template extension uses Twig to render and apply templates.

#### `template`

Apply a template.

Params

- `from`: Source Twig file, by default Twig will look for these files relative
  to the configuration file's directory.
- `to`: Copy the rendered template to the package using this relative path,
  e.g. `README.md`, `.travis/something`

Documentation
-------------

Nope.

Contribution
------------

All contributions are welcome, make a PR.
