FROM node:latest

WORKDIR /home/node/app

COPY ./ /home/node/app

RUN npm install -g nodemon && npm install

CMD npm run dev

EXPOSE 3000