FROM node:6.9.1

WORKDIR /usr/src/app
COPY package.json ./
RUN npm install

COPY test ./test
ENTRYPOINT ./node_modules/.bin/mocha
