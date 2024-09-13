FROM docker/whalesay:latest
LABEL Name=bitsler1 Version=0.0.1
RUN apt-get -y update && apt-get install -y fortunes
CMD ["sh", "-c", "/usr/games/fortune -a | cowsay"]

FROM php:8.3-apache
RUN apt-get update

WORKDIR /var/www
COPY . /var/www

# dowload composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
