# Tinywebdb (php-vue)

TPV是一个数据库管理系统，默认支持 App Inventor 的 Tinywebdb，并以此得名。

[下载页](https://github.com/ColinTree/tinywebdb-php-vue/releases/latest)

一般会有四个下载选项

* `dist.tar.gz` - 自动构建结果（仅包含Tinywebdb插件）
* `dist.zip` - 自动构建结果（仅包含Tinywebdb插件）
* `Source code (zip)` - 源码
* `Source code (tar.gz)` - 源码

## 在新浪云SAE上部署

### 首次部署

1. 在[新浪云(SAE)](http://sae.sina.com.cn)创建 `php, 标准环境, 7.0, git` 应用
2. 前往左侧菜单`应用->代码管理`，创建版本，并选择`上传代码包`，将 [Release](https://github.com/ColinTree/tinywebdb-php-vue/releases/latest) 页面下载的 `dist.zip` 上传
3. 选择`上传代码包`旁边的`编辑代码`（编辑界面有时候是弹窗，会被浏览器拦截，请注意放行）
4. 根据[生产环境的配置](#%E7%94%9F%E4%BA%A7%E7%8E%AF%E5%A2%83%E7%9A%84%E9%85%8D%E7%BD%AE)配置 `config.php`
5. 前往你的app网址如 `http://{app-id}.applinzi.com/#/manage/init` ，设置后台密码
6. 开始正常使用！（AI2中填写的网络微数据库地址是 `http://{app-id}.applinzi.com`）

### 更新已有应用

只需要前往`应用->代码管理`，选择`上传代码包`，上传 [Release](https://github.com/ColinTree/tinywebdb-php-vue/releases/latest) 中的 `dist.zip` 文件即可

## 使用[宝塔服务器面板](https://bt.cn)

### 首次部署

1. 安装php7.0插件（其他php7版本没有测试，安装完有问题建议回退到7.0）
2. 安装mysql插件
3. 在网站一栏中选择添加站点，输入域名或公网ip，选择php7.0，并创建mysql数据库
4. 创建完毕记下数据库信息，或者稍后在数据库一栏中查询
5. 下载 [Release](https://github.com/ColinTree/tinywebdb-php-vue/releases/latest) 页面的 `dist.zip` 或者 `dist.tar.gz`
6. 点击站点列表中的根目录，前往文件管理页面，上传并解压dist包
7. 根据[生产环境的配置](#%E7%94%9F%E4%BA%A7%E7%8E%AF%E5%A2%83%E7%9A%84%E9%85%8D%E7%BD%AE)配置 `config.php`
8. 如果是nginx环境
  - 双击文件列表中解压出来的.htaccess，复制文件内容
  - 然后回到站点列表，点击站点名（打开设置），找到“伪静态”一栏，前往上面的[“Apache转Nginx”](https://www.bt.cn/Tools)链接（或者[这个](https://winginx.com/en/htaccess))
  - 将转换完的 Nginx rewrite 保存到宝塔的伪静态设置
9. 修改站点设置“默认文档”，将index.html移动到index.php上面，保存（按钮文字有可能是“添加”）

## 生产环境的配置

上线生产环境之前请确保负责提供配置的 `config.php` 已经被创建并设置

常量 | 必须？ | 默认值 | 备注
-|-|-|-
DEBUG_MODE | 否 | false | 调试模式，如果发生错误会附带错误信息在result
ACCESS_CONTROL_ALLOW_ORIGIN | 否 | (未设置则不生效) | 是否允许浏览器跨域/其策略，`"*"`即为允许所有
MANAGE_LOGIN_TIMEOUT | 否 | 600 | 后台管理会话自动结束时长（秒）


其他设置：

* DbProvider::setDb - **必须** - 提供数据库服务

样例参见 [config.sample.php](https://github.com/ColinTree/tinywebdb-php-vue/blob/master/backend/config.sample.php)

## 插件

TPV支持使用插件，目前内置插件有：

* [网络微数据库 (tinywebdb)](./plugins/tinywebdb/README.md)（默认开启）
* [排行榜 (leaderboard)](./plugins/leaderboard/README.md)

如需调整插件启用情况，则需要自行[构建TPV](#%E4%BB%8E%E6%BA%90%E7%A0%81%E6%9E%84%E5%BB%BA)

## 从源码构建

1. 下载源码

```
git clone https://github.com/ColinTree/tinywebdb-php-vue.git
cd tinywebdb-php-vue
git submodule update --init
npm i tar-to-zip -g
```

2. 配置`plugins.json`，格式参照源码中的样例 [plugins.sample.json](https://github.com/ColinTree/tinywebdb-php-vue/blob/master/plugins.sample.json)
3. 构建

```
npm run build
```

执行完之后你就可以看到 `dist.tar.gz` 和 `dist.zip` 出现在你当前的文件夹中

## API文档

### `Api.class.php` 中定义的状态码

状态名 | 分类 | 状态码 | http状态码 | 描述
-----------|----------|:----:|:---------:|------------
STATUS_SUCCEED | 全局 | 0 | 200 | API执行成功，具体细节返参见返回值
STATUS_API_NOT_FOUND | 全局 | 1 | 404 | 未能找到请求的API
STATUS_API_FAILED | 全局 | 2 | 200 | API在运行过程中出现错误
STATUS_INTERNAL_ERROR | 全局 | 3 | 500 | 服务器内部错误
STATUS_UNAUTHORIZED | 全局 | 4 | 401 | 请求未提供token或者token已失效
STATUS_PLUGIN_API_FAILED | 插件 | 5 | 200 | 插件通用错误码
STATUS_SYSTEM_NOT_CONFIGURED | 全局 | 6 | 200 | 系统尚未初始化（需要配置config.php）
STATUS_KEY_NOT_FOUNT | 需要提供标签的地方 | 10 | 200 | 数据库中未能找到请求的标签
STATUS_KEY_RESERVED | 需要提供标签的地方 | 11 | 200 | 请求了管理系统的保留标签
STATUS_UNACCEPTED_LIMIT | 需要返回分页的地方 | 20 | 200 | 分页限制应该在1-100之间
STATUS_KEY_ALREADY_EXIST | 需要添加标签的地方 | 30 | 200 | 标签已存在
STATUS_PASSWORD_TOO_SHORT | 初始化 | 40 | 200 | 密码太短
STATUS_PASSWORD_INVALID  | 初始化 | 41 | 200 | 密码过于简单或者包含了不允许的字符
STATUS_SETTING_NOT_RECOGNISED | 更新设置 | 50 | 200 | 服务器不支持指定的设置
STATUS_EXPORT_UNACCEPTED_TYPE | 导出数据 | 60 | 200 | 服务器不支持指定的导出格式
STATUS_EXPORT_XLSX_UNSUPPORTED | 导出数据(仅限xlsx) | 61 | 200 | 服务器未安装xlsx相关的模块

### 后台管理

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
* erase_all - 重置（初始化）整个TPV系统
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 返回值 - [ "status": 状态码, "result": 提示文本 ]
* erase_data - 清除所有数据（除了保留标签，如设置和密码）
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 返回值 - [ "status": 状态码, "result": 提示文本 ]
* erase_pwd - 删除密码
  * 请求头
    * `X-TPV-Manage-Token` - 必须
  * 返回值 - [ "status": 状态码, "result": 提示文本 ]
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
    * `settingId` - 必需 - 设置Id，详见[设置表](#%E5%90%8E%E5%8F%B0%E7%AE%A1%E7%90%86%E8%AE%BE%E7%BD%AE%E8%A1%A8)
    * `value` - 必需 - 值，详见[设置表](#%E5%90%8E%E5%8F%B0%E7%AE%A1%E7%90%86%E8%AE%BE%E7%BD%AE%E8%A1%A8)
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

#### 后台管理设置表

设置Id | 设置名 | 接受的值
-|-|-
all_category | 标签浏览页·分类列表 | 使用井号#分隔的文本
