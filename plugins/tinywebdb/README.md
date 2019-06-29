# tinywebdb

本插件实现了标准Tinywebdb的功能，并增设了部分特殊标签（默认禁用）：

* count - 计数
* getall - 获取一个列表
* listget - 批量获取指定的值
* search - 搜索标签

## 使用

访问 `http://{tpv地址}/tinywebdb/{具体api}`

## Api文档

* getvalue - 获取值
  * 参数
    * tag
  * 返回值
    * [ "VALUE", tag, value / 错误信息 ]

* storeavalue - 储存值
  * 参数
    * tag
    * value
  * 返回值
    * [ "STORED", tag, value / 错误信息 ]

## 设置表

设置Id | 设置名 | 接受的值
-|-|-
tinywebdb_allow_browser | 允许来自浏览器的读写 | `true` 或 `false`，默认为`true`
tinywebdb_special_tags | 特殊标签设置 | JSON格式文本，如 `{"count":"disabled", "getall":"disabled", "listget":"disabled", "search":"disabled"}`，默认都是disabled