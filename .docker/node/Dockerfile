FROM node:lts as node

WORKDIR /usr/src/app

COPY ./ /usr/src/app

RUN yarn --pure-lockfile
