
FROM node

COPY ./package.json /src/
COPY ./*.js /src/
RUN cd /src; npm install

ENTRYPOINT ["node", "/src/index.js"]
