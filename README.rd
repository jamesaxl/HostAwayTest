# Build a Simple REST API in PHP

## Exercise description

Please code a RESTFul API to store, retrieve, delete, update phone book items.
Each phone book item should have at least the following fields:
- First name (required)
- Last name (required)
- Phone number (required) - must be validated based on some standard, e.g. +12 223 444224455
- Country code - country code should be validated via https://api.hostaway.com/countries
- Timezone name - should be validated via https://api.hostaway.com/timezones
- insertedOn (required) - DateTime type
- updatedOn (required) - DateTime type
- In every insert or update, a call should be sent to the given API endpoints to get list of countries or timezones for validation, and proper error should be thrown if it’s invalid
Exceptions should be handled properly, specially upon validation or HTTP call issues
Different layers of application shall be separated when necessary
Proper design patterns shall be used when necessary
Results should be possible to be retrieved by ID, or as total results, or by searching parts of the name

### Install

```
cd /path/to/host_away_exam
composer install
```
### Setup database

Create database

```
mysql -uroot -p
CREATE DATABASE hostaway ;
quit
```

Note: You should change config.php

Create table phone_books

```
php migration.php
```

Setup php test server

```
cd web
php -S 127.0.0.1:8000
```

If you want to run with apache, I already put .htaccess

```
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.+)$ index.php [QSA,L]
```

For nginx

```
location / {
  try_files $uri $uri/ /index.php$is_args$args;
}
```

### Requests

- Get /get-all get all records.
- Get /get?id:int find a record using id
- Get /search?keyword:string find all records that contains keyword (all fields)
- Post /store insert record
```
{
    "firstName" : "Daim",
    "lastName" : "Bagdarrel",
    "phoneNumber" : "+12 223 444224457",
    "countryCode" : "US",
    "timezone" : "Pacific/Midway"
}
```

- Post /update?id=int  update a record
```
{
    "phoneNumber" : "+12 223 444224457"
}
```

- Delete /delete?id:int delete a record


## License

Copy left.
