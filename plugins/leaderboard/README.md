# leaderboard

这是一个用于实现排行榜功能的插件

## 使用

访问 `http://{tpv地址}/leaderboard/{具体api}`

## Api文档

* add_score - 新增分数
  * 参数
    * board - 排行榜id
    * name - 新增分数的对应名字
    * score - 排行榜上的分数
  * 返回值
    * 根据新增分数重新排名之后的总榜，格式同`get_board`
  * 可能的错误
    * 如果设置中设置了不允许客户端创建排行榜，会用错误码5来代替创建一个数据库中不存在的排行榜
* get_board - 获取总榜
  * 参数
    * board - 排行榜id
  * 返回值
    * `[ { "name": "分数得主", "score": "分数" }, ... ]`

## 设置表

设置Id | 设置名 | 接受的值
-|-|-
leaderboard_allow_create | 允许客户端创建排行榜 | `true` 或 `false`，默认为`false`