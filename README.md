# Commands
```sh
# run localhost server
composer run-script localhost-backend
```

# Api development plan
- [x] Have fun, it is a new project :)
- [X] Install Symfony, crate hello world
- [X] Install LexikJWTAuthenticationBundle, get to know the plugin, configure
- [X] Create project structure
- [ ] Implement Service Locator and factory (library already provides it)
- [ ] Database - custom ORM ot top of PDO, Entity / Repo, maybe some database migration tool?
- [ ] Api Controller
- [ ] Users Controller
- [ ] Projects Controller
- [ ] Tasks Controller
- [X] Token generation and Authorisation
- [ ] Recheck each point
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

