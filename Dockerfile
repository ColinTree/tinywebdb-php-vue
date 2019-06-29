FROM node:8-alpine

RUN npm config set unsafe-perm true
RUN npm i -g @vue/cli \
             bestzip

WORKDIR /usr/app/frontend_temp

COPY package*.json ./
RUN npm i

COPY frontend/tinywebdb-php-vue frontend/tinywebdb-php-vue
COPY .postcssrc.js .
COPY plugins plugins
COPY plugins.json .
RUN npm run vue-build

WORKDIR /usr/app

RUN mv frontend_temp/frontend/tinywebdb-php-vue/dist/* . && \
    rm -rf frontend_temp

COPY backend .
COPY plugins plugins
COPY plugins.json .
COPY config.yaml .
COPY .htaccess .

RUN tar -zcvf dist.tar.gz * .htaccess