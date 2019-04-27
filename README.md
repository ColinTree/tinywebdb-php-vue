# Tinywebdb (php-vue)

## 构建

（需要提前安装docker）

```
docker build -t tpv .
docker create --name tpv_temp_container tpv
docker cp tpv_temp_container:/usr/app/dist.tar.gz .
docker rm tpv_temp_container
```

执行完之后你就可以看到 `dist.tar.gz` 出现在你当前的文件夹中

## 生产环境的配置

上线生产环境之前请确保负责提供配置的 `config.php` 已经被创建并设置

常量 | 必须？ | 默认值
-|-|-|-
DEBUG_MODE | 否 | false
ACCESS_CONTROL_ALLOW_ORIGIN | 否 | (未设置则不会生效)


其他设置：

* DbProvider::setDb - **必须** - 提供数据库服务

样例参见 `config.sample.php` 文件。（源码中的位置：`backend/config.sample.php`）

## API文档

### Tinywebdb API

标准Tinywebdb API没有状态码

* `/getvalue`
  * 参数
    * tag
  * 返回值
    * [ "VALUE", tag, value / 错误信息 ]

* `/setvalue`
  * 参数
    * tag
    * value
  * 返回值
    * [ "STORED", tag, value / 错误信息 ]

### 其他API

#### `Api.class.php` 中定义的状态码

状态名 | 分类 | 状态码 | http状态码 | 描述
-----------|----------|:----:|:---------:|------------
STATE_SUCCEED | 全局 | 0 | 200 | API执行成功，具体细节返参见返回值
STATE_API_NOT_FOUND | 全局 | 1 | 404 | 未能找到请求的API
STATE_API_FAILED | 全局 | 2 | 200 | API在运行过程中出现错误
STATE_INTERNAL_ERROR | 全局 | 3 | 500 | 服务器内部错误
STATE_UNAUTHORIZED | 全局 | 4 | 401 | 请求未提供token或者token已失效
STATE_KEY_NOT_FOUNT | 需要提供标签的地方 | 10 | 200 | 数据库中未能找到请求的标签
STATE_KEY_RESERVED | 需要提供标签的地方 | 11 | 200 | 请求了管理系统的保留标签
STATE_UNACCEPTED_LIMIT | 需要返回分页的地方 | 20 | 200 | 分页限制应该在1-100之间
STATE_KEY_ALREADY_EXIST | 需要添加标签的地方 | 30 | 200 | 标签已存在

#### 后台管理：

请求头 `X-TPV-Manage-Token` 大部分时候是必须的，是一串用于身份验证的token，只有token验证通过的操作才会被服务器执行

* login - 登录
  * 参数
    * `pwd` - 必须 - **只接受POST** - 密码
  * 返回值 - [ "state": 状态码, "result": [ "succeed": 登录是否成功, "token": 用于登录验证的token（登陆成功才会返回） ] ]
* logout - 登出
  * 请求头
    * `X-TPV-Manage-Token` - 非必须
  * 返回值 - [ "state": 状态码, "result": "sure" ]
* count - 统计数目
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 参数
    * `prefix` - 非必须 - 默认为空
  * 返回值 - [ "state": 状态码, "result": 标签数量 ]
* has - 是否有某个标签的记录
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 参数
    * `key` - 必需 - 默认为空 - 标签
  * 返回值 - [ "state": 状态码, "result": 标签是否存在 ]
* get - 请求标签的值
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 参数
    * `key` - 必需 - 默认为空 - 标签
  * 返回值 - [ "state": 状态码, "result": 标签对应的值 ]
  * 可能返回的非全局状态码 - STATE_KEY_NOT_FOUNT
* set - 设置标签的值
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 参数
    * `key` - 必需 - 默认为空 - 标签
    * `value` - 必需 - 默认为空 - 值
  * 返回值 - [ "state": 状态码, "result": 提示文本 ]
  * 可能返回的非全局状态码 - STATE_KEY_RESERVED
* add - 添加一个标签，标签不得存在
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 参数
    * `key` - 必需 - 默认为空 - 标签
    * `value` - 必需 - 默认为空 - 值
  * 返回值 - [ "state": 状态码, "result": 提示文本 ]
  * 可能返回的非全局状态码 - STATE_KEY_RESERVED, STATE_KEY_ALREADY_EXIST
* update - 更新标签的值，标签必须存在
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 参数
    * `key` - 必需 - 默认为空 - 标签
    * `value` - 必需 - 默认为空 - 值
  * 返回值 - [ "state": 状态码, "result": 提示文本 ]
  * 可能返回的非全局状态码 - STATE_KEY_RESERVED, STATE_KEY_NOT_FOUNT
* delete - 删除一个标签
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 参数
    * `key` - 必需 - 默认为空 - 标签
  * 返回值 - [ "state": 状态码, "result": 提示文本 ]
  * 可能返回的非全局状态码 - STATE_KEY_RESERVED
* mdelete - 批量删除制定标签
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 参数
    * `keys` - 必需 - 默认为空列表 - 标签列表，使用JSON样式传递（如 `["key1", "key2"]`）
  * 返回值 - [ "state": 状态码, "result": 结果列表，如`[ true（第一个值删除成功）, false（第二个值删除失败） ]` ]
* page - 获取指定页的标签
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 参数
    * `page` - 非必需 - 默认为1 - 要获取的页数
    * `perPage` - 非必需 - 默认为100 - 每页最大数量
    * `prefix` - 非必需 - 默认为空 - 标签前缀
  * 返回值 - [ "state": 状态码, "result": 标签-值列表，如`{ ["key":"key1","value":"valueOfKey1"], ["key":"key2","value":"valueOfKey2"] }` ]