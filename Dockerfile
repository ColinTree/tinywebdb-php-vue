FROM node:8-alpine

RUN npm config set unsafe-perm true
RUN npm i -g @vue/cli \
             bestzip

WORKDIR /usr/app/frontend_temp

COPY frontend/tinywebdb-php-vue/package*.json ./
RUN npm i

COPY frontend/tinywebdb-php-vue .
RUN npm run build

WORKDIR /usr/app

RUN mv frontend_temp/dist/* . && \
    rm -rf frontend_temp

COPY backend .
COPY .htaccess .

RUN tar -zcvf dist.tar.gz *