# 

# One time configuration of the sample app

1. Create a copy of .env from .env.bak
```sh
cp .env.bak bak
```
2. then update .env with your api key and api url

```sh
vi .env
```

```sh
DB_CONNECTION=mysql
DB_HOST=propertyListing-db
DB_PORT=3306
DB_DATABASE=propertyListing
DB_USERNAME=root
DB_PASSWORD=password
API_KEY=myawesomekey
API_URL=http://www.example.com/api
```

# Starting the sample app

```sh
cd propertyListing
```

```sh
docker-compose up -d --force-recreate --build
```

```sh
docker exec -it propertyListing-app composer install
```

wait for around 2 minute after build success, to allowed MySql spin up.
Else will triggred ERROR 2002 (HY000): Can't connect to local MySQL server through socket '/var/run/mysqld/mysqld.sock'

# Executing the scrapper job

```sh
docker exec -it propertyListing-app php app/ConsoleScrapper.php
```

# Stop the sample app

```sh
docker-compose stop
```

# Totally remove the sample app

```sh
docker-compose down
docker-compose kill
```

#DB Stuff

##Access PHPMyAdmin at
http://localhost:8282/

## Access MySql console

```sh
docker exec -it mtc-db mysql -u root -ppassword
```

#Improvement suggestion

1. Country,town and county should be changed int id value and move settings table.
   As this will not only improved [erformance but also space saving.


   | id | mainId | title          |
   | ---- | -------- | ---------------- |
   | 1  | 0      | United Kingdom |
   | 2  | 1      | London         |
   | 3  | 2      | Surrey         |
2. The long running scrapper job to be ofloaded to a message-broker like RabbitMQ
