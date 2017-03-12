FROM php:7.1-alpine

ARG COMPOSER_OPTS

RUN apk --no-cache add curl git openssl graphviz

COPY . /app
WORKDIR /app

RUN /app/bin/install-composer.sh && composer update $COMPOSER_OPTS

CMD [""]
