DROP TABLE IF EXISTS `posts`;
DROP TABLE IF EXISTS `judgments`;
DROP TABLE IF EXISTS `runs`;
DROP TABLE IF EXISTS `teams`;
DROP TABLE IF EXISTS `contests_divisions_problems`;
DROP TABLE IF EXISTS `contests_divisions`;
DROP TABLE IF EXISTS `problems`;
DROP TABLE IF EXISTS `divisions`;
DROP TABLE IF EXISTS `contests`;
DROP TABLE IF EXISTS `tags`;
DROP TABLE IF EXISTS `globals`;

CREATE TABLE `globals` (
  `curr_contest_id` int(11) NOT NULL,
  `next_judge_id` int(11) NOT NULL,
  `next_order_seq` int(11) NOT NULL
);

INSERT INTO `globals` (`curr_contest_id`, `next_judge_id`, `next_order_seq`) VALUES (0, 1, 1);

CREATE TABLE `tags` (
  `tag` varchar(32) NOT NULL,
  PRIMARY KEY (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Tags';

CREATE TABLE `contests` (
  `contest_id` int(11) NOT NULL AUTO_INCREMENT,
  `contest_type` varchar(32) NOT NULL,
  `contest_name` varchar(128) NOT NULL,
  `time_start` int(11) NOT NULL,
  `time_length` int(11) NOT NULL,
  `tag` varchar(32) NOT NULL,
  `metadata` longtext NOT NULL,
  `status` tinyint(3) NOT NULL,
  PRIMARY KEY (`contest_id`),
  FOREIGN KEY (`tag`) REFERENCES `tags` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contests';

CREATE TABLE `divisions` (
  `division_id` int(11) NOT NULL AUTO_INCREMENT,
  `division_name` varchar(50) NOT NULL,
  PRIMARY KEY (`division_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Divisions';

CREATE TABLE `problems` (
  `problem_id` int(11) NOT NULL AUTO_INCREMENT,
  `problem_type` varchar(32) NOT NULL DEFAULT 'default',
  `title` varchar(128) NOT NULL DEFAULT '',
  `order_seq` int(11) NOT NULL,
  `metadata` longtext NOT NULL,
  `status` tinyint(3) NOT NULL,
  PRIMARY KEY (`problem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Problems';

CREATE TABLE `contests_divisions` (
  `contest_id` int(11) NOT NULL,
  `division_id` int(11) NOT NULL,
  `metadata` longtext NOT NULL,
  FOREIGN KEY (`contest_id`) REFERENCES `contests` (`contest_id`),
  FOREIGN KEY (`division_id`) REFERENCES `divisions` (`division_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contests_Divisions';

CREATE TABLE `contests_divisions_problems` (
  `contest_id` int(11) NOT NULL,
  `division_id` int(11) NOT NULL,
  `problem_id` int(11) NOT NULL,
  `url` varchar(250) NOT NULL DEFAULT '',
  `alias` varchar(128) NOT NULL DEFAULT '',
  `division_metadata` longtext NOT NULL,
  PRIMARY KEY (`contest_id`, `division_id`, `problem_id`),
  FOREIGN KEY (`contest_id`) REFERENCES `contests` (`contest_id`),
  FOREIGN KEY (`division_id`) REFERENCES `divisions` (`division_id`),
  FOREIGN KEY (`problem_id`) REFERENCES `problems` (`problem_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Contests_Divisions_Problems';

CREATE TABLE `teams` (
  `team_id` int(11) NOT NULL AUTO_INCREMENT,
  `tag` varchar(32) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` char(40) NOT NULL,
  `alias` varchar(100) NOT NULL,
  `division_id` int(11) NOT NULL,
  `status` tinyint(3) NOT NULL,
  PRIMARY KEY (`team_id`),
  FOREIGN KEY (`division_id`) REFERENCES `divisions` (`division_id`),
  FOREIGN KEY (`tag`) REFERENCES `tags` (`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Teams';

CREATE TABLE `runs` (
  `run_id` int(11) NOT NULL AUTO_INCREMENT,
  `problem_id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `payload` longblob NOT NULL,
  `time_submitted` int(11) NOT NULL,
  `metadata` longtext NOT NULL,
  `status` tinyint(3) NOT NULL,
  PRIMARY KEY (`run_id`),
  FOREIGN KEY (`problem_id`) REFERENCES `problems` (`problem_id`),
  FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Runs';

CREATE TABLE `judgments` (
  `judgment_id` int(11) NOT NULL AUTO_INCREMENT,
  `run_id` int(11) NOT NULL,
  `time_updated` int(11) NOT NULL,
  `judge_id` int(11) NOT NULL,
  `metadata` longtext NOT NULL,
  `status` tinyint(3) NOT NULL,
  PRIMARY KEY (`judgment_id`),
  FOREIGN KEY (`run_id`) REFERENCES `runs` (`run_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Judgments';

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL AUTO_INCREMENT,
  `contest_id` int(11) NOT NULL,
  `team_id` int(11),
  `ref_id` int(11),
  `text` text NOT NULL,
  `time_posted` int(11) NOT NULL,
  `status` tinyint(3) NOT NULL,
  PRIMARY KEY (`post_id`),
  FOREIGN KEY (`contest_id`) REFERENCES `contests` (`contest_id`),
  FOREIGN KEY (`team_id`) REFERENCES `teams` (`team_id`),
  FOREIGN KEY (`ref_id`) REFERENCES `posts` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Posts';