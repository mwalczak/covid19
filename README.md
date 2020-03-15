## Simple Covid-19 data presentation

Made with Symfony 5

Feel free to extend or get in touch with any feature or co-work requests 

Data provided by: https://covid.ourworldindata.org/data/full_data.csv


## Setup env
Start docker with:
```
docker-compose build
docker-compose up -d
docker-compose exec php composer install
```
Setup permissions:
```
docker-compose exec php chmod u+x bin/console bin/phpunit
```
Frontend:
```
docker-compose exec frontend yarn install && yarn encore dev --watch
```