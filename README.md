Simple, secure, betting API. 

This API will process bet requests from users, ensuring that the request comes from a valid user with sufficient balance. 

Based on the user's bet, the system will generate a secure random number between 0 and 13 and calculate the payout based on this number.

## Commands to start the app

To start the app you need to install docker, and clone the repo to your local machine.

After that, go to the root project directory and run this to start docker containers:

```
docker-compose up --build
```

This will install mysql, php8.3, apache2, composer, create the database, all the tables, and then it will run the app seeder (app/commands/seed.php) which will put test data into the users and balances tables.

## Execution

To make a bet request to the app, you will need to authenticate a user. The seeded data contains these two users (and their passwords):
1. bob (password: bobpassword)
2. james (james: jamespassword)

Bob has two "wallets" (or balances): 100.00 USD, and 200.00 CAD. James has only one wallet (or balance): 5.00 GBP

All balances are stored and processed in cents. All bet amounts are accepted in cents. All money amounts accepted and returned are in cents. 
This is done to ensure performance and precision. 

To authenticate a user, make a POST request to `/auth` endpoint like this:

```
curl -X POST http://localhost:8090/auth -d "username=bob" -d "password=bobpassword"
```

where `username` and `password` are required input parameters.

The output will contain an auth token, or an error if the authetication fails (wrong password, or wrong username).

Use the token provided, and include it into your request to `/bet` endpoint. 

```
curl -X POST http://localhost:8090/bet -d "token=18d276ccf126d01385c2e4d1d8395f58" -d "amount=1500" -d "currency=USD"
```


where `token`, `currency`, and `amount` are required input parameters.
Currency must be a 3-letter currency code, and amount must be an integer value of the bet amount in cents.

With user bob you can place bets in CAD or USD currency. The amounts will be reducted accordingly. There is not such thing as a combined wallet with automatic currency conversion.

## Examples

Example of successful authentication:
```
curl -X POST http://localhost:8090/auth -d "username=bob" -d "password=bobpassword"
{"success":true,"auth-token":"5ae2dba2945e8882794fd86436eb7e13","auth-token-expires-at":"2024-08-19 18:39:01"}
```


Example of failed authentication:
```
curl -X POST http://localhost:8090/auth -d "username=bob" -d "password=somthingelse"
{"success":false,"message":"Authentication failed"}
```

Example of wrong token usage:
```
curl -X POST http://localhost:8090/bet -d "token=328d9005190a199fe5901f1a94e2aac0" -d "amount=2000" -d "currency=USD"
{"success":false,"message":"Authentication failed (token)"}
```

Example of successful bet placement:
```
curl -X POST http://localhost:8090/bet -d "token=328d9005190a199fe5901f1a94e2aac0" -d "amount=2000" -d "currency=USD"
{"success":true, "bet":{"bet_id":"45","bet_amount":2000,"bet_currency":"USD","generated_number":6,"payout":13736},"balances":{"USD":3672,"CAD":20000}}
```

Example of the insufficient balance:
```
curl -X POST http://localhost:8090/bet -d "token=328d9005190a199fe5901f1a94e2aac0" -d "amount=2000" -d "currency=USD"
{"success":false,"message":"Insufficient balance"}
```

## Other comments

There is an `.env` file with database connection configuration, and of cource it should not be in the git index, but I've added it for simplicity of starting and testing the app.

There are a couple of useful scripts under `app/commands/`:
1. `migrate.php` allows to tear down and start up the whole database structure (with removing all data and tables, and creating them from scratch);
2. `seed.php` inserts seeding data (two users and their balances).

To run the commands you need to enter the web container terminal, and while being in the /var/www directory run this commands:
```
php app/commands/migrate.php

... or ...

php app/commands/seed.php
```

Composer is the only external thing in the app. The reason for having it is to be able to sinmply autoload the namespaces, models, controllers.