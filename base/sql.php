<?php
	require "conn.php";
	
	class Channel {
		public $id = -1;
		public $channel = "";
	}
	
	class News {
		public $id = -1;
		public $title = "";
		public $publish_time = null;
		public $pic = "";
		public $url = "";
		public $channel = -1;
	}
	
	class Reader {
		public $id = -1;
		public $email = "";
		public $password = "";
	}
	
	class Favorite {
		public $id = -1;
		public $reader = -1;
		public $channel = -1;
		public $times = 0;
	}
	
	class news_sql {
		static function select_channel_list() {
			$mysqli = $GLOBALS["mysqli"];
			$channel_list = Array();
			$Channel = new Channel();
			try {
				$prepared_sql = "SELECT id, channel FROM Channel";
				if ($stmt = $mysqli->prepare($prepared_sql)) {
					$stmt->execute();
					$stmt->store_result();
					$stmt->bind_result($Channel->id, $Channel->channel);
					while ($stmt->fetch()) {
						array_push($channel_list, $Channel);
						$Channel = new Channel();
						$stmt->bind_result($Channel->id, $Channel->channel);
					};
					$stmt->close();
					return $channel_list;
				}
			}
			catch (Exception $e) {}
			
			return null;
		}
		
		static function select_channel($channel) {
			$mysqli = $GLOBALS["mysqli"];
			$Channel = new Channel();
			try {
				$prepared_sql = "SELECT id, channel FROM Channel WHERE channel = ?";
				if ($stmt = $mysqli->prepare($prepared_sql)) {
					$stmt->bind_param("s", $channel);
					$stmt->execute();
					$stmt->bind_result($Channel->id, $Channel->channel);
					$stmt->store_result();
					$num = $stmt->num_rows;
					$stmt->fetch();
					$stmt->close();
					if ($num > 0)
						return $Channel;
				}
			}
			catch (Exception $e) {}
			
			return null;
		}
		
		static function insert_channel($channel) {
			$mysqli = $GLOBALS["mysqli"];
			try {
				$prepared_sql = "INSERT INTO Channel(channel) VALUES(?)";
				if ($stmt = $mysqli->prepare($prepared_sql)) {
					$stmt->bind_param('s', $channel);
					$stmt->execute();
					$stmt->close();
					return true;
				}
			}
			catch (Exception $e) {}
			
			return false;
		}
		
		static function select_news($title) {
			$mysqli = $GLOBALS["mysqli"];
			$News = new News();
			try {
				$prepared_sql = "SELECT id, title, publish_time, pic, url, channel FROM News WHERE title = ?";
				if ($stmt = $mysqli->prepare($prepared_sql)) {
					$stmt->bind_param("s", $title);
					$stmt->execute();
					$stmt->bind_result($News->id, $News->title, $News->publish_time, $News->pic, $News->url, $News->channel_id);
					$stmt->store_result();
					$num = $stmt->num_rows;
					$stmt->fetch();
					$stmt->close();
					if ($num > 0)
						return $News;
				}
			}
			catch (Exception $e) {}
			
			return null;
		}
		
		static function insert_news($title, $publish_time, $pic, $url, $channel_id) {
			$mysqli = $GLOBALS["mysqli"];
			try {
				$prepared_sql = "INSERT INTO News(title, publish_time, pic, url, channel) VALUES(?, ?, ?, ?, ?)";
				if ($stmt = $mysqli->prepare($prepared_sql)) {
					$stmt->bind_param('ssssi', $title, $publish_time, $pic, $url, $channel_id);
					$stmt->execute();
					$stmt->close();
					return true;
				}
			}
			catch (Exception $e) {}
			
			return false;
		}
		
		static function select_reader($email) {
			$mysqli = $GLOBALS["mysqli"];
			$Reader = new Reader();
			try {
				$prepared_sql = "SELECT id, email, password FROM Reader WHERE email = ?";
				if ($stmt = $mysqli->prepare($prepared_sql)) {
					$stmt->bind_param("s", $email);
					$stmt->execute();
					$stmt->bind_result($Reader->id, $Reader->email, $Reader->password);
					$stmt->store_result();
					$num = $stmt->num_rows;
					$stmt->fetch();
					$stmt->close();
					if ($num > 0)
						return $Reader;
				}
			}
			catch (Exception $e) {}
			
			return null;
		}
		
		static function insert_reader($email, $password) {
			$mysqli = $GLOBALS["mysqli"];
			$password = md5($password);
			try {
				$prepared_sql = "INSERT INTO Reader(email, password) VALUES(?, ?)";
				if ($stmt = $mysqli->prepare($prepared_sql)) {
					$stmt->bind_param('ss', $email, $password);
					$stmt->execute();
					$stmt->close();
					return true;
				}
			}
			catch (Exception $e) {}
			
			return false;
		}
		
		static function select_favorite_list($reader) {
			$mysqli = $GLOBALS["mysqli"];
			$favorite_list = Array();
			$Favorite = new Favorite();
			try {
				$prepared_sql = "SELECT id, reader, channel, times FROM Favorite WHERE reader = ?";
				if ($stmt = $mysqli->prepare($prepared_sql)) {
					$stmt->bind_param('i', $reader);
					$stmt->execute();
					$stmt->store_result();
					$stmt->bind_result($Favorite->id, $Favorite->reader, $Favorite->channel, $Favorite->times);
					while ($stmt->fetch()) {
						array_push($favorite_list, $Favorite);
						$Favorite = new Favorite();
						$stmt->bind_result($Favorite->id, $Favorite->reader, $Favorite->channel, $Favorite->times);
					};
					$stmt->close();
					return $favorite_list;
				}
			}
			catch (Exception $e) {}
			
			return null;
		}
		
		static function select_favorite($reader, $channel) {
			$mysqli = $GLOBALS["mysqli"];
			$Favorite = new Favorite();
			try {
				$prepared_sql = "SELECT id, reader, channel, times FROM Favorite WHERE reader = ? AND channel = ?";
				if ($stmt = $mysqli->prepare($prepared_sql)) {
					$stmt->bind_param("ii", $reader, $channel);
					$stmt->execute();
					$stmt->bind_result($Favorite->id, $Favorite->reader, $Favorite->channel, $Favorite->times);
					$stmt->store_result();
					$num = $stmt->num_rows;
					$stmt->fetch();
					$stmt->close();
					if ($num > 0)
						return $Favorite;
				}
			}
			catch (Exception $e) {}
			
			return null;
		}
		
		static function insert_favorite($reader, $channel, $times) {
			$mysqli = $GLOBALS["mysqli"];
			try {
				$prepared_sql = "INSERT INTO Favorite(reader, channel, times) VALUES(?, ?, ?)";
				if ($stmt = $mysqli->prepare($prepared_sql)) {
					$stmt->bind_param('iii', $reader, $channel, $times);
					$stmt->execute();
					$stmt->close();
					return true;
				}
			}
			catch (Exception $e) {}
			
			return false;
		}
		
		static function update_favorite($reader, $channel, $times) {
			$mysqli = $GLOBALS["mysqli"];
			try {
				$prepared_sql = "UPDATE Favorite SET times = ? WHERE reader = ? AND channel = ?";
				if ($stmt = $mysqli->prepare($prepared_sql)) {
					$stmt->bind_param('iii', $times, $reader, $channel);
					$stmt->execute();
					$stmt->close();
					return true;
				}
			}
			catch (Exception $e) {}
			
			return false;
		}
		
		static function select_news_list($channel, $start, $count) {
			$mysqli = $GLOBALS["mysqli"];
			$News = new News();
			$news_list = Array();
			try {
				$prepared_sql = "SELECT id, title, publish_time, pic, url FROM News WHERE channel = ? AND id < ? ORDER BY id DESC LIMIT ?";
				if ($stmt = $mysqli->prepare($prepared_sql)) {
					$stmt->bind_param("iii", $channel, $start, $count);
					$stmt->execute();
					$stmt->bind_result($News->id, $News->title, $News->publish_time, $News->pic, $News->url);
					while ($stmt->fetch()) {
						$News->publish_time = strtotime($News->publish_time);
						$News->channel = $channel;
						array_push($news_list, $News);
						$News = new News();
						$stmt->bind_result($News->id, $News->title, $News->publish_time, $News->pic, $News->url);
					};
					$stmt->close();;
					return $news_list;
				}
			}
			catch (Exception $e) {}
			
			return null;
		}
		
		static function search_news($keyword) {
			$mysqli = $GLOBALS["mysqli"];
			$News = new News();
			$news_list = Array();
			$keyword = "%{$keyword}%";
			try {
				$prepared_sql = "SELECT id, channel, title, publish_time, pic, url FROM News WHERE title LIKE ? ORDER BY id DESC LIMIT 60";
				if ($stmt = $mysqli->prepare($prepared_sql)) {
					$stmt->bind_param("s", $keyword);
					$stmt->execute();
					$stmt->bind_result($News->id, $News->channel, $News->title, $News->publish_time, $News->pic, $News->url);
					while ($stmt->fetch()) {
						$News->publish_time = strtotime($News->publish_time);
						array_push($news_list, $News);
						$News = new News();
						$stmt->bind_result($News->id, $News->channel, $News->title, $News->publish_time, $News->pic, $News->url);
					};
					$stmt->close();;
					return $news_list;
				}
			}
			catch (Exception $e) {}
			
			return null;
		}
	}
?>