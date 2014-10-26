CREATE TABLE users (
  user_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100),
  name VARCHAR(255),
  details_json TEXT,
  created_at DATETIME,
  updated_at DATETIME
);

CREATE TABLE user_vote (
  user_vote_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  voting_user_id INT(11),
  elected_user_id INT(11),
  created_at DATETIME
);