# Backend of TPV

## State codes

### Tinywebdb APIs have no state codes

### Other apis

Defined in `Api.class.php`

state name | category | code | http code | description
-----------|----------|:----:|:---------:|------------
STATE_SUCCEED | general | 0 | 200 | api execute succeed, the result is its returning value of succeed message
STATE_API_NOT_FOUND | general | 1 | 404 | api cannot be found
STATE_API_FAILED | general | 2 | 200 | api can be found but failed executing
STATE_INTERNAL_ERROR | general | 3 | 500 | unexpected error occured
STATE_UNAUTHORIZED | general | 4 | 401 | unauthorized
STATE_KEY_NOT_FOUNT | any key-related | 10 | 200 | key cannot be found in the database
STATE_KEY_RESERVED | any key-related | 11 | 200 | key cannot be operated since it is reserved
STATE_UNACCEPTED_LIMIT | getPage | 20 | 200 | limit should between 1-100
STATE_KEY_ALREADY_EXIST | any add | 30 | 200 | key already exist

## production

Ensure you had set up `config.php`, which would produce config & decide database service.

See `config.sample.php`.

## dev

run

1. `docker build -t tpv-backend .`
2. `docker run --rm -it -v $PWD/src:/var/www/html -p 8888:80 tpv-backend`
3. Go VirtualBoxIp:8888

### Note that this may not work for Windows

* Some times `//var/www/html` would fix `invalid mode` Error
* For mounting directory issue in ToolBox, add share directory in VirtualBox like `d` -> `d:\`

See https://my.oschina.net/u/575836/blog/3015221

docker run --rm -it -v /d/OneDrive/GitHub/ColinTree/tinywebdb-php-vue/backend:/var/www/html -p 8088:80 tpv-backend