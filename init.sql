/*如果存在web_news数据库就删除 
*/
DROP DATABASE IF EXISTS `web_news`;

/*如果存在web_news用户就删除
*/
DELETE FROM mysql.user WHERE user='web_news';

/*创建web_news数据库，编码格式为utf8mb4（可以支持emoji）
*/
CREATE DATABASE web_news DEFAULT CHARSET utf8mb4 COLLATE utf8mb4_bin;

/*创建和数据库名相同的用户，并赋予web_news数据库的所有权限
*/
GRANT ALL PRIVILEGES ON web_news.* TO "web_news"@'localhost' IDENTIFIED BY 'web_news';

/*使用web_news数据库
*/
USE web_news;

/*创建Channel表，存放新闻频道
*/
CREATE TABLE `Channel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,  /*Channel ID，自增·*/
  `channel` varchar(10) CHARACTER SET utf8mb4 NOT NULL,  /*Channel名称*/
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

/*创建News表，存放新闻条目
*/
CREATE TABLE `News` (
  `id` int(11) NOT NULL AUTO_INCREMENT,  /*News ID，自增·*/
  `title` varchar(1000) CHARACTER SET utf8mb4 NOT NULL,  /*新闻标题*/
  `publish_time` datetime DEFAULT NULL,  /*新闻发布时间*/
  `pic` varchar(1000) CHARACTER SET utf8mb4 NOT NULL,  /*新闻头图*/
  `url` varchar(1000) CHARACTER SET utf8mb4 NOT NULL,  /*新闻链接*/
  `channel` int(11) NOT NULL,  /*新闻关联的频道*/
  constraint `news_related_channel` foreign key (`channel`) references `Channel`(`id`) on delete cascade on update cascade,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

/*创建Reader表，存放用户
*/
CREATE TABLE `Reader` (
  `id` int(11) NOT NULL AUTO_INCREMENT,  /*Reader ID，自增·*/
  `email` varchar(50) CHARACTER SET utf8mb4 NOT NULL,  /*读者邮箱*/
  `password` varchar(32) CHARACTER SET utf8mb4 NOT NULL,  /*读者密码的哈希值*/
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

/*创建Favorite表，存放用户对某频道的喜爱度
*/
CREATE TABLE `Favorite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,  /*Favorite ID，自增·*/
  `reader` int(11) NOT NULL,  /*关联的读者*/
  `channel` int(11) NOT NULL,  /*关联的频道*/
  `times` int(11) NOT NULL DEFAULT 0,  /*读者对该频道的访问次数，以此个性化推荐新闻*/
  constraint `favorite_related_reader` foreign key (`reader`) references `Reader`(`id`) on delete cascade on update cascade,
  constraint `favorite_related_channel` foreign key (`channel`) references `Channel`(`id`) on delete cascade on update cascade,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
