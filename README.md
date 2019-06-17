# Tinywebdb (php-vue)

## 下载&构建

（需要提前安装docker）

```
git clone https://github.com/ColinTree/tinywebdb-php-vue.git
cd tinywebdb-php-vue
git submodule update --init
docker build -t tpv .
docker create --name tpv_temp_container tpv
docker cp tpv_temp_container:/usr/app/dist.tar.gz .
docker rm tpv_temp_container
```

执行完之后你就可以看到 `dist.tar.gz` 出现在你当前的文件夹中

注：
如果有需要可以通过`npm i -g tar-to-zip`安装tar.gz转换zip的模块，并运行`tar2zip dist.tar.gz`

## 生产环境的配置

上线生产环境之前请确保负责提供配置的 `config.php` 已经被创建并设置

常量 | 必须？ | 默认值 | 备注
-|-|-|-
DEBUG_MODE | 否 | false | 调试模式，如果发生错误会附带错误信息在result
ACCESS_CONTROL_ALLOW_ORIGIN | 否 | (未设置则不生效) | 是否允许浏览器跨域/其策略，`"*"`即为允许所有
MANAGE_LOGIN_TIMEOUT | 否 | 600 | 后台管理会话自动结束时长（秒）


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

* `/storeavalue`
  * 参数
    * tag
    * value
  * 返回值
    * [ "STORED", tag, value / 错误信息 ]

### 其他API

#### `Api.class.php` 中定义的状态码

状态名 | 分类 | 状态码 | http状态码 | 描述
-----------|----------|:----:|:---------:|------------
STATUS_SUCCEED | 全局 | 0 | 200 | API执行成功，具体细节返参见返回值
STATUS_API_NOT_FOUND | 全局 | 1 | 404 | 未能找到请求的API
STATUS_API_FAILED | 全局 | 2 | 200 | API在运行过程中出现错误
STATUS_INTERNAL_ERROR | 全局 | 3 | 500 | 服务器内部错误
STATUS_UNAUTHORIZED | 全局 | 4 | 401 | 请求未提供token或者token已失效
STATUS_KEY_NOT_FOUNT | 需要提供标签的地方 | 10 | 200 | 数据库中未能找到请求的标签
STATUS_KEY_RESERVED | 需要提供标签的地方 | 11 | 200 | 请求了管理系统的保留标签
STATUS_UNACCEPTED_LIMIT | 需要返回分页的地方 | 20 | 200 | 分页限制应该在1-100之间
STATUS_KEY_ALREADY_EXIST | 需要添加标签的地方 | 30 | 200 | 标签已存在
STATUS_PASSWORD_TOO_SHORT | 初始化 | 40 | 200 | 密码太短
STATUS_PASSWORD_INVALID  | 初始化 | 41 | 200 | 密码过于简单或者包含了不允许的字符
STATUS_SETTING_NOT_RECOGNISED | 更新设置 | 50 | 200 | 服务器不支持指定的设置
STATUS_EXPORT_UNACCEPTED_TYPE | 导出数据 | 60 | 200 | 服务器不支持指定的导出格式
STATUS_EXPORT_XLSX_UNSUPPORTED | 导出数据(仅限xlsx) | 61 | 200 | 服务器未安装xlsx相关的模块

#### 后台管理：

请求头 `X-TPV-Manage-Token` 大部分时候是必须的，是一串用于身份验证的token，只有token验证通过的操作才会被服务器执行

* add - 添加一个标签，标签不得存在
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 参数
    * `key` - 必需 - 默认为空 - 标签
    * `value` - 必需 - 默认为空 - 值
  * 返回值 - [ "status": 状态码, "result": 提示文本 ]
  * 可能返回的非全局状态码 - STATUS_KEY_RESERVED, STATUS_KEY_ALREADY_EXIST
* count - 统计数目
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 参数
    * `prefix` - 非必须 - 默认为空
  * 返回值 - [ "status": 状态码, "result": 标签数量 ]
* delete - 删除一个标签
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 参数
    * `key` - 必需 - 默认为空 - 标签
  * 返回值 - [ "status": 状态码, "result": 提示文本 ]
  * 可能返回的非全局状态码 - STATUS_KEY_RESERVED
* export - 导出数据
  * 参数
    * `token` - 必需 - 功能同`X-TPV-Manage-Token`，旨在方便通过链接直接下载
    * `type` - 必需 - 支持`json`, `xml`, `csv`, `xlsx`，具体导出格式自行尝试
    * `prefix` - 非必需 - 默认为空 - 标签前缀
    * `include_reserved` - 非必需 - 默认为`false` - 是否导出保留标签
  * 返回值 - 对应的文件下载 或者 [ "status": 状态码, "result": 错误信息 ]
  * 可能返回的非全局状态码 - STATUS_EXPORT_UNACCEPTED_TYPE, STATUS_EXPORT_XLSX_UNSUPPORTED(仅限xlsx导出)
* get - 请求标签的值
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 参数
    * `key` - 必需 - 默认为空 - 标签
  * 返回值 - [ "status": 状态码, "result": 标签对应的值 ]
  * 可能返回的非全局状态码 - STATUS_KEY_NOT_FOUNT
* has - 是否有某个标签的记录
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 参数
    * `key` - 必需 - 默认为空 - 标签
  * 返回值 - [ "status": 状态码, "result": 标签是否存在 ]
* import_json - 导入json格式数据
  * 参数
    * `file` - 必需 - FormData格式文件
  * 返回值 - [ 'status': 状态码, 'result': [ 'failed': 导入失败的标签（如保留标签）列表 ] ]
* init - 初始化
  * 参数
    * `pwd` - 必须 - **只接受POST** - 密码
  * 返回值 - [ 'status': 状态码, 'result': 初始化成功返回token，或者其他情况下返回提示文本 ]
  * 可能返回的非全局状态码 - STATUS_PASSWORD_TOO_SHORT, STATUS_PASSWORD_INVALID
* login - 登录
  * 参数
    * `pwd` - 必须 - **只接受POST** - 密码
  * 返回值 - [ "status": 状态码, "result": [ "succeed": 登录是否成功, "token": 用于登录验证的token（登陆成功才会返回） ] ]
* logout - 登出
  * 请求头
    * `X-TPV-Manage-Token` - 非必须
  * 返回值 - [ "status": 状态码, "result": "sure" ]
* mdelete - 批量删除制定标签
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 参数
    * `keys` - 必需 - 默认为空列表 - 标签列表，使用JSON样式传递（如 `["key1", "key2"]`）
  * 返回值 - [ "status": 状态码, "result": 结果列表，如`[ true（第一个值删除成功）, false（第二个值删除失败） ]` ]
* page - 获取指定页的标签
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 参数
    * `page` - 非必需 - 默认为1 - 要获取的页数
    * `perPage` - 非必需 - 默认为100 - 每页最大数量
    * `prefix` - 非必需 - 默认为空 - 标签前缀
  * 返回值 - [ "status": 状态码, "result": [ 'count' => 数据库有多少数据, 'actualPage' => 实际返回的是第几页, 'content' => 标签-值列表，如`{ ["key":"key1","value":"valueOfKey1"], ["key":"key2","value":"valueOfKey2"] }` ] ]
* ping - 检查状态
  * 请求头
    * `X-TPV-Manage-Token` - 非必须
  * 返回值 - [ "status": 状态码, "result": [ 'login': 是否已登录, 'initialized': 系统是否已经初始化 ] ]
* set - 设置标签的值
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 参数
    * `key` - 必需 - 默认为空 - 标签
    * `value` - 必需 - 默认为空 - 值
  * 返回值 - [ "status": 状态码, "result": 提示文本 ]
  * 可能返回的非全局状态码 - STATUS_KEY_RESERVED
* setting_update - 更新指定设置
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 参数
    * `settingId` - 必需 - 设置Id，详见设置列表
    * `value` - 必需 - 值，详见设置列表
  * 返回值 - [ "status": 状态码, "result": 提示文本 ]
  * 可能返回的非全局状态码 - STATUS_SETTING_NOT_RECOGNISED
* settings - 获取所有设置
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 返回值 - [ "status": 状态码, "result": 所有变更过的设置，如`{ "all_category": "#student_#teacher_" }` ]
* update - 更新标签的值，标签必须存在
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 参数
    * `key` - 必需 - 默认为空 - 标签
    * `value` - 必需 - 默认为空 - 值
  * 返回值 - [ "status": 状态码, "result": 提示文本 ]
  * 可能返回的非全局状态码 - STATUS_KEY_RESERVED, STATUS_KEY_NOT_FOUNT

##### 后台管理设置表

设置Id | 设置名 | 接受的值
-|-|-
all_category | 标签浏览页·分类列表 | 使用井号#分隔的文本
allow_browser | 允许来自浏览器的读写 | 不是`"false"`默认为`"true"`
