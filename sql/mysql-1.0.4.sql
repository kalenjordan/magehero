CREATE TABLE post_vote (
  post_vote_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  voting_user_id INT(11),
  post_id INT(11),
  created_at DATETIME
);