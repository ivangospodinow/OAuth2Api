# Requirements
- php >=7.4
- mysql database
- composer
- git

# Installation
```sh
git clone https://github.com/ivangospodinow/OAuth2Api.git ./OAuth2Api
cd OAuth2Api
# change env variables as needed
cp .env.local.example .env.local
# will do composer install, database creation, migrations.
composer run-script appinit
# starts the backend server, timeout is 1 hour
# http://127.0.0.1:8888
composer run-script localhost
````

# Testing
```sh
# phpunit tests
composer run-script tests
# api consuming tests
composer run-script tests-localhost
```

## Commands
```sh
# will do composer install, database creation, migrations. 
composer run-script appinit
# starts the backend server, timeout is 1 hour
# http://127.0.0.1:8888
composer run-script localhost
# phpunit tests
composer run-script tests
# api consuming tests
composer run-script tests-localhost
````

# Api development plan
- [x] Have fun, it is a new project :)
- [X] Install Symfony, crate hello world
- [X] Install LexikJWTAuthenticationBundle, get to know the plugin, configure
- [X] Create project structure
- [X] Implement Service Locator and factory (library already provides it)
- [X] Database - custom ORM ot top of PDO, Entity / Repo, maybe some database migration tool?
- [X] Api Controller
- [ ] Users Controller
- [X] Projects Controller
- [X] Tasks Controller
- [X] Token generation and Authorisation
- [X] Recheck each point
- [ ] Reliase

# Api Task

Create RESTful API с OAuth 2 authentication with LexikJWTAuthenticationBundle for Symfony 5

- Token generation endpoint.
- Api to support crud operations for two models: Project and task with One to Many relation.
- Project fields: id (GUID), title, description, status, duration, client, company, tasks, deletedAt. Validation: client or company must be set.
- Task fields: id(GUID), name, project, deletedAt
- Error handling with status 200 and code passed as variable with the error.
- Api calls are allowed only for authorized users.
- Use vanilla php

