# API Server with Symfony
The aim of this repo is to provide a skelton made with symfony 6.4 and Lexik JWTAuthenticationBundle

## Installation
clone this repo.

The __main__ branch is a simple symfony repository with no dependencies.

The __install__ branch has dependencies installed

The __entities__ branch has datas for tests (books, authors and users). Fixtures are presents.

First start with __install__ or __entities__ branch. Run ``` composer install ``` to install dependencies.

Then, you have to create keys for JWT generation : ``` php bin/console lexik:jwt:generate-keypair ```

Then start server with ``` symfony server:start```

In  __entities__ branch, all routes are unprotected.

if you want to test JWT generation then run an API tester like Bruno or PostMan and POST to this adress : https://localhost:8000/api/login_check with this JSON in body :

```
{
  "username": "admin@example.com", 
  "password": "password"
}
```

The API will return your token