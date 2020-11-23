/*
Navicat MySQL Data Transfer

Source Server         : 127.0.0.1
Source Server Version : 80012
Source Host           : 127.0.0.1:3306
Source Database       : tp_layim

Target Server Type    : MYSQL
Target Server Version : 80012
File Encoding         : 65001

Date: 2020-11-21 09:46:02
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for yz_chat_recoed
-- ----------------------------
DROP TABLE IF EXISTS `yz_chat_recoed`;
CREATE TABLE `yz_chat_recoed` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `fromname` varchar(65) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '发送人昵称',
  `fromavatar` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '发送人头像',
  `send` int(10) NOT NULL COMMENT '发送者',
  `receive` int(10) NOT NULL COMMENT '接收者',
  `content` varchar(1024) COLLATE utf8mb4_general_ci NOT NULL COMMENT '发送内容',
  `send_time` int(11) NOT NULL COMMENT '发送时间',
  `type` enum('group','friend') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'friend' COMMENT '聊天类型',
  `is_read` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 未读 1 以读',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=203 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='聊天记录表';

-- ----------------------------
-- Table structure for yz_group
-- ----------------------------
DROP TABLE IF EXISTS `yz_group`;
CREATE TABLE `yz_group` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `accunt` varchar(20) COLLATE utf8mb4_general_ci NOT NULL COMMENT '群号',
  `groupname` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '群名称',
  `des` varchar(200) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '群描述',
  `number` smallint(3) DEFAULT '0' COMMENT '人数',
  `approval` tinyint(1) NOT NULL DEFAULT '1' COMMENT '0 无需验证 1 需要验证',
  `group_status` tinyint(1) DEFAULT '1' COMMENT '1 正常 2全员禁言',
  `avatar` varchar(128) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '/static/images/defult_image.png' COMMENT '群头像',
  `owner_name` varchar(32) COLLATE utf8mb4_general_ci NOT NULL COMMENT '群主名称',
  `owner_id` int(10) NOT NULL COMMENT '群主id',
  `owner_avatar` varchar(32) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '群主头像',
  `owner_sign` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '群主签名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='群聊天表';

-- ----------------------------
-- Table structure for yz_group_member
-- ----------------------------
DROP TABLE IF EXISTS `yz_group_member`;
CREATE TABLE `yz_group_member` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL COMMENT '群id',
  `member_id` int(11) NOT NULL COMMENT '用户id',
  `status` tinyint(1) DEFAULT '1' COMMENT '1 正常 2 为该群黑名单',
  `add_time` int(11) DEFAULT NULL COMMENT '加群时间',
  `type` tinyint(1) DEFAULT '3' COMMENT '1群主 2管理员 3会员',
  `forbidden_speech_time` int(11) DEFAULT '0' COMMENT '禁言到某个时间',
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '群员昵称',
  `useravatar` varchar(120) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '群主头像',
  `usersign` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '用户签名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='群员表';

-- ----------------------------
-- Table structure for yz_member
-- ----------------------------
DROP TABLE IF EXISTS `yz_member`;
CREATE TABLE `yz_member` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `account` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '账号',
  `password` char(32) NOT NULL COMMENT '密码',
  `salt` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '秘钥',
  `birthday` int(11) DEFAULT NULL COMMENT '生日',
  `username` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '匿名' COMMENT '昵称',
  `sex` tinyint(4) DEFAULT '3' COMMENT '性别1男2女3保密',
  `status` tinyint(4) DEFAULT '0' COMMENT '在线状态0不在线1在线',
  `sign` varchar(65) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '签名',
  `email` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '邮箱',
  `phone` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT '电话',
  `blood_type` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '其他血型' COMMENT 'A型 B型 AB型 0型 其他血型',
  `job` tinyint(4) DEFAULT '0' COMMENT '1计算机/互联网/通信 2生产/工艺/制造 3医疗/护理/制药 4金融/银行/投资/保险 5商业/服务业/个体经营 6文化/广告/传媒 7娱乐/艺术/表演 8律师/法务 9教育/培训 10共五月/行政/事业单位 11 模特 12空姐 13 学生 14 其他',
  `avatar` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '/static/ images/default_ image.png' COMMENT '头像',
  `qq` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT '' COMMENT 'qq号',
  `create_time` int(11) unsigned DEFAULT NULL COMMENT '创建时间',
  `update_time` int(11) unsigned DEFAULT NULL COMMENT '更新时间',
  `login_time` int(11) unsigned DEFAULT NULL COMMENT '上一次登录时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='会员表';

-- ----------------------------
-- Table structure for yz_message
-- ----------------------------
DROP TABLE IF EXISTS `yz_message`;
CREATE TABLE `yz_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `msg_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1 请求添加用户 2系统(加好友) 3 请求加群 4 系统(加群) 5全体会员消息',
  `send` int(11) NOT NULL COMMENT '消息发送者',
  `receive` int(11) NOT NULL COMMENT '消息接收者',
  `msg_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1未读 2同意 3拒绝 4 同意且返回消息以读 5拒绝且返回消息以读 6 全体消息以读',
  `remark` varchar(128) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '附加消息',
  `send_time` int(11) DEFAULT NULL COMMENT '发送消息时间',
  `read_time` int(11) DEFAULT NULL COMMENT '接收消息时间',
  `receive_group` int(11) DEFAULT NULL COMMENT '接收消息的群主',
  `andle_group` int(11) DEFAULT NULL COMMENT '处理该请求的群主',
  `my_group` int(11) DEFAULT NULL COMMENT '好友分组',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='通知表';

-- ----------------------------
-- Table structure for yz_my_friend
-- ----------------------------
DROP TABLE IF EXISTS `yz_my_friend`;
CREATE TABLE `yz_my_friend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL COMMENT '分组id',
  `member_id` int(11) NOT NULL COMMENT '好友id',
  `username` varchar(65) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT '好友昵称',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='会员分组下好友列表';

-- ----------------------------
-- Table structure for yz_my_group
-- ----------------------------
DROP TABLE IF EXISTS `yz_my_group`;
CREATE TABLE `yz_my_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主健ID',
  `member_id` int(11) NOT NULL COMMENT '会员ID',
  `group_name` varchar(128) NOT NULL COMMENT '分组名称',
  `sort` tinyint(4) DEFAULT '1' COMMENT '好友分组的排列顺序越小越靠前',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COMMENT='会员好友分组表';

-- ----------------------------
-- Table structure for yz_skin
-- ----------------------------
DROP TABLE IF EXISTS `yz_skin`;
CREATE TABLE `yz_skin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `member_id` int(10) unsigned NOT NULL COMMENT '会员ID',
  `url` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '皮肤地址',
  `is_user_upload` tinyint(1) DEFAULT '0' COMMENT '1用户自定义 0默认',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='皮肤表';
