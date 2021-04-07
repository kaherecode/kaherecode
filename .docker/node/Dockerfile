FROM node:14.16.0-alpine as node

WORKDIR /usr/src/app

COPY ./ /usr/src/app

RUN yarn --pure-lockfile

# RUN mkdir -m 777 /.npm
# RUN mkdir -m 777 /.yarn
# RUN touch /.yarnrc && chmod 777 /.yarnrc
