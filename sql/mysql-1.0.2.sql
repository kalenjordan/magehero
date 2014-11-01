CREATE TABLE posts (
  post_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT(11),
  subject VARCHAR(255),
  body TEXT,
  created_at DATETIME,
  updated_at DATETIME
);

CREATE TABLE post_tag (
  post_tag_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  user_id INT(11),
  post_id INT(11),
  tag_id INT(11),
  created_at DATETIME,
  updated_at DATETIME
);

CREATE TABLE tags (
  tag_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  tag_text VARCHAR(255),
  created_at DATETIME,
  updated_at DATETIME
);