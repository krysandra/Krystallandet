<?php
/*
 * Functionality for executing statements on the database
 */
class dbFunctions 
{
	private $prefix;
	private $db_host;
	private $db_username;
	private $db_password;
	private $db_name;
	private $conn;
	
    function __construct() {
 	    //parent::__construct();
		$this->db_host = "krystallandet.dk.mysql";
		$this->db_username = "krystallandet_d";
		$this->db_password = "qRXVhc4j";
		$this->db_name = "krystallandet_d";
		$this->prefix = "krystallandet";
		$this->conn = new mysqli($this->db_host, $this->db_username, $this->db_password, $this->db_name);		
		if ($this->conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}
		$this->conn->set_charset("utf8");
		
		/* Default timezone is set for use of SQL datetime */
		date_default_timezone_set('Europe/Copenhagen');
    }	
	
	
	/* USERS AND CHARACTERS */	
	
	public function get_all_superusers() 
	{
		$query = $this->conn->prepare("SELECT superuser_ID, name, color, date_joined, last_active FROM ".$this->prefix."_superusers ORDER BY name");
		$query->execute();			
		/* Store the result (to get properties) */
		//$query ->store_result();		
		/* Get the number of rows */
		//$num_of_rows = $query ->num_rows;						
		/* Get the result */
		return $query->get_result();			
	}	
	
	public function count_all_superusers() 
	{
		$query = $this->conn->prepare("SELECT COUNT(superuser_ID) AS res FROM ".$this->prefix."_superusers");
		$query->execute();			
		return $query->get_result();						
	}	
	
	public function get_superuser($id)
	{
		$query = $this->conn->prepare("SELECT superuser_ID, name, color, date_joined, last_active, email, skype, title, signature, avatar, chatavatar, chattitle, chatlink, color,
		birthday, reference, website, facebook, geography, profiletext, fk_role_ID FROM ".$this->prefix."_superusers WHERE superuser_ID = ?");		
		$query->bind_param('i', $id);
		$query->execute();	
		/* Get the result */
		return $query->get_result();		
	}			
	public function get_superuser_by_name($name)
	{
		$query = $this->conn->prepare("SELECT superuser_ID, password, name, color, date_joined, last_active, email, skype, title, signature, avatar, chatavatar, chattitle, chatlink, color,
		birthday, reference, website, facebook, geography, profiletext, fk_role_ID FROM ".$this->prefix."_superusers WHERE name = ?");
		$query->bind_param('s', $name);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	public function get_superuser_by_email($email)
	{
		$query = $this->conn->prepare("SELECT superuser_ID, name, email FROM ".$this->prefix."_superusers WHERE email = ? LIMIT 1");
		$query->bind_param('s', $email);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function check_for_existing_superuser_name($name)
	{
		$this->conn->real_escape_string($name);
		$query = $this->conn->prepare("SELECT COUNT(superuser_ID) AS res FROM ".$this->prefix."_superusers WHERE name = ?");
		$query->bind_param('s', $name);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}	
	
	public function check_for_existing_superuser($id)
	{
		$query = $this->conn->prepare("SELECT COUNT(superuser_ID) AS res FROM ".$this->prefix."_superusers WHERE superuser_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function check_for_existing_superuser_email($email)
	{
		$query = $this->conn->prepare("SELECT COUNT(superuser_ID) AS res FROM ".$this->prefix."_superusers WHERE email = ?");
		$query->bind_param('s', $email);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}	
	
	public function get_online_users() 
	{
		$datetime_after = date('Y-m-d H:i:s', strtotime(' - 30 minutes'));
		$query = $this->conn->prepare("SELECT superuser_ID, name, color FROM ".$this->prefix."_superusers WHERE last_active > ?");
		$query->bind_param('s', $datetime_after);
		$query->execute();		
		return $query->get_result();
	}	
	
	public function count_online_users() 
	{
		$datetime_after = date('Y-m-d H:i:s', strtotime(' - 30 minutes'));
		$query = $this->conn->prepare("SELECT COUNT(superuser_ID) AS res FROM ".$this->prefix."_superusers WHERE last_active > ?");
		$query->bind_param('s', $datetime_after);
		$query->execute();		
		return $query->get_result();
	}
	
	public function count_online_users_today() 
	{
		$datetime_after = date('Y-m-d 00:00:00');
		$query = $this->conn->prepare("SELECT COUNT(superuser_ID) AS res FROM ".$this->prefix."_superusers WHERE last_active > ?");
		$query->bind_param('s', $datetime_after);
		$query->execute();		
		return $query->get_result();
	}
	
	public function get_newest_character()
	{
		$this->conn->real_escape_string($name);
		$query = $this->conn->prepare("SELECT character_ID, name, color FROM ".$this->prefix."_usercharacters WHERE accepted = 1 ORDER BY date_created DESC LIMIT 1");
		$query->bind_param('s', $name);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}		
	
	public function get_newest_superuser()
	{
		$query = $this->conn->prepare("SELECT superuser_ID, name, color FROM ".$this->prefix."_superusers ORDER BY date_joined DESC LIMIT 1");
		$query->bind_param('s', $name);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}	
	
	public function get_admins()
	{
		$query = $this->conn->prepare("SELECT superuser_ID, name, color, avatar FROM ".$this->prefix."_superusers WHERE fk_role_ID = 3");		
		$query->bind_param('i', $id);
		$query->execute();	
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function get_character_by_name($name)
	{
		$query = $this->conn->prepare("SELECT character_ID, name, date_created, signature, avatar, accepted, active, dead, maxskill, color, fk_superuser_ID 
		FROM ".$this->prefix."_usercharacters WHERE name = ?");
		$query->bind_param('s', $name);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}	
	
	public function try_find_character_by_name($name)
	{
		$query = $this->conn->prepare("SELECT COUNT(character_ID) AS res FROM ".$this->prefix."_usercharacters WHERE name = ? AND accepted = 1");
		$query->bind_param('s', $name);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}	
	
	public function check_for_existing_character_name($name)
	{
		$query = $this->conn->prepare("SELECT COUNT(character_ID) AS res FROM ".$this->prefix."_usercharacters WHERE name = ?");
		$query->bind_param('s', $name);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}	
	
	public function check_for_existing_character($id)
	{
		$query = $this->conn->prepare("SELECT COUNT(character_ID) AS res FROM ".$this->prefix."_usercharacters WHERE character_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}	
		
	public function get_all_accepted_characters_simple() 
	{
		$query = $this->conn->prepare("SELECT character_ID, name, date_created, signature, avatar, accepted, active, dead, maxskill, color, fk_superuser_ID FROM ".$this->prefix."_usercharacters WHERE accepted = 1");
		$query->execute();	
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function get_all_accepted_active_characters_simple() 
	{
		$query = $this->conn->prepare("SELECT character_ID, name, date_created, signature, avatar, accepted, active, dead, maxskill, color, fk_superuser_ID FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND active = 1");
		$query->execute();	
		/* Get the result */
		return $query->get_result();		
	}
	
	public function count_all_accepted_characters()
	{
		$query = $this->conn->prepare("SELECT COUNT(character_ID) AS res FROM ".$this->prefix."_usercharacters WHERE accepted = 1");
		$query->execute();	
		return $query->get_result();		
	}
	
	public function count_all_active_characters()
	{
		$query = $this->conn->prepare("SELECT COUNT(character_ID) AS res FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND active = 1");
		$query->execute();	
		return $query->get_result();		
	}
	
	public function get_all_characters_from_superuser($id) 
	{
		$query = $this->conn->prepare("SELECT character_ID, name, date_created, signature, avatar, accepted, active, dead, maxskill, color, fk_superuser_ID FROM ".$this->prefix."_usercharacters WHERE fk_superuser_ID = ? ORDER BY name");
		$query->bind_param('i', $id);
		$query->execute();	
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function count_all_characters_from_superuser($id) 
	{
		$query = $this->conn->prepare("SELECT COUNT(character_ID) AS res FROM ".$this->prefix."_usercharacters WHERE fk_superuser_ID = ? ORDER BY name");
		$query->bind_param('i', $id);
		$query->execute();	
		/* Get the result */
		return $query->get_result();		
	}
	
	public function count_all_accepted_alive_characters_from_superuser($id) 
	{
		$query = $this->conn->prepare("SELECT COUNT(character_ID) AS res FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND dead = 0 AND fk_superuser_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		/* Get the result */
		return $query->get_result();
	}	
	
	public function count_all_accepted_active_characters_from_superuser($id) 
	{
		$query = $this->conn->prepare("SELECT COUNT(character_ID) AS res FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND active = 1 AND fk_superuser_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		/* Get the result */
		return $query->get_result();		
	}
	
	/*
	public function count_all_accepted_active_characters() 
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND active = 1 AND dead = 0");
		$query->execute();	
		return $query->get_result();
	}		
	
	public function count_all_accepted_characters() 
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND dead = 0");
		$query->execute();	
		return $query->get_result();
	}	
	
	public function count_all_accepted_inactive_characters() 
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND active = 0 AND dead = 0");
		$query->execute();	
		return $query->get_result();
	}			
	
	public function count_all_accepted_active_characters_beginswith($l) 
	{
		$this->conn->real_escape_string($l);
		$l = $l."%";
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND active = 1 AND dead = 0 AND name LIKE ?");
		$query->bind_param('s', $l);
		$query->execute();		
		return $query->get_result();	
	}		
	
	public function count_all_accepted_characters_beginswith($l)
	{
		$this->conn->real_escape_string($l);
		$l = $l."%";
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND dead = 0 AND name LIKE ?");
		$query->bind_param('s', $l);
		$query->execute();		
		return $query->get_result();	
	}	
	
	public function count_all_accepted_inactive_characters_beginswith($l)
	{
		$this->conn->real_escape_string($l);
		$l = $l."%";
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND dead = 0 AND active = 0 AND name LIKE ?");
		$query->bind_param('s', $l);
		$query->execute();		
		return $query->get_result();	
	}				
	
	public function get_all_accepted_active_characters($offset, $limit) 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND dead = 0 AND active = 1 ORDER BY name LIMIT ? , ?");
		$query->bind_param('ii', $offset, $limit);
		$query->execute();		
		return $query->get_result();		
	}		
	
	public function get_all_accepted_characters($offset, $limit) 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND dead = 0 ORDER BY name LIMIT ? , ?");
		$query->bind_param('ii', $offset, $limit);
		$query->execute();		
		return $query->get_result();		
	}	
	
	public function get_all_accepted_inactive_characters($offset, $limit) 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND dead = 0 AND active = 0 ORDER BY name LIMIT ? , ?");
		$query->bind_param('ii', $offset, $limit);
		$query->execute();		
		return $query->get_result();		
	}		
	
	public function get_all_accepted_active_characters_beginswith($l, $offset, $limit) 
	{
		$this->conn->real_escape_string($l);	
		$l = $l."%";	
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND dead = 0 AND active = 1 AND name LIKE ? ORDER BY name LIMIT ? , ?");
		$query->bind_param('sii', $l, $offset, $limit);
		$query->execute();		
		return $query->get_result();		
	}		
	
	public function get_all_accepted_characters_beginswith($l, $offset, $limit) 
	{
		$this->conn->real_escape_string($l);
		$l = $l."%";	
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND dead = 0 AND name LIKE ? ORDER BY name LIMIT ? , ?");
		$query->bind_param('sii', $l, $offset, $limit);
		$query->execute();		
		return $query->get_result();		
	}	
	
	public function get_all_accepted_inactive_characters_beginswith($l, $offset, $limit) 
	{
		$this->conn->real_escape_string($l);
		$l = $l."%";
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND dead = 0 AND active = 0 AND name LIKE ? ORDER BY name LIMIT ? , ?");
		$query->bind_param('sii', $l, $offset, $limit);
		$query->execute();		
		return $query->get_result();		
	}	
	*/
	
	public function count_character_search_all($like) 
	{
		$this->conn->real_escape_string($like);
		$like = $like."%";
		$query = $this->conn->prepare("SELECT COUNT(character_ID) AS res FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND dead = 0 AND name LIKE ?");
		$query->bind_param('s', $like);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	public function count_character_search($active, $like) 
	{
		$this->conn->real_escape_string($like);
		$like = $like."%";
		$query = $this->conn->prepare("SELECT COUNT(character_ID) AS res FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND active = ? AND dead = 0 AND name LIKE ?");
		$query->bind_param('is', $active, $like);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	public function count_character_search_all_include_dead($like) 
	{
		$this->conn->real_escape_string($like);
		$like = $like."%";
		$query = $this->conn->prepare("SELECT COUNT(character_ID) AS res FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND name LIKE ?");
		$query->bind_param('s', $like);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	
	public function count_character_search_all_specialchars() 
	{
		$query = $this->conn->prepare("SELECT COUNT(character_ID) AS res FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND dead = 0 AND name regexp '[^a-zA-Z0-9]'");
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	public function count_character_search_specialchars($active) 
	{
		$query = $this->conn->prepare("SELECT COUNT(character_ID) AS res FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND active = ? AND dead = 0 AND name regexp '[^a-zA-Z0-9]'");
		$query->bind_param('i', $active);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	public function count_character_search_all_specialchars_include_dead() 
	{
		$query = $this->conn->prepare("SELECT COUNT(character_ID) AS res FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND name regexp '[^a-zA-Z0-9]'");
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	
	public function get_character_search_all($like, $offset, $limit) 
	{
		$this->conn->real_escape_string($like);
		$like = $like."%";
		$query = $this->conn->prepare("SELECT character_ID, name, accepted, active, dead, color, date_created, fk_superuser_ID FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND dead = 0 AND name LIKE ? ORDER BY name LIMIT ? , ?");
		$query->bind_param('sii', $like, $offset, $limit);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	
	public function get_character_search($active, $like, $offset, $limit) 
	{
		$this->conn->real_escape_string($like);
		$like = $like."%";
		$query = $this->conn->prepare("SELECT character_ID, name, accepted, active, dead, color, date_created, fk_superuser_ID FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND active = ? AND dead = 0 AND name LIKE ? ORDER BY name LIMIT ? , ?");
		$query->bind_param('isii', $active, $like, $offset, $limit);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}	
	
	public function get_character_search_all_include_dead($like) 
	{
		$this->conn->real_escape_string($like);
		$like = $like."%";
		$query = $this->conn->prepare("SELECT character_ID, name, accepted, active, dead, color, date_created, fk_superuser_ID FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND name LIKE ? ORDER BY name");
		$query->bind_param('s', $like);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}	
	
	public function get_character_search_all_specialchars($offset, $limit) 
	{
		$query = $this->conn->prepare("SELECT character_ID, name, accepted, active, dead, color, date_created, fk_superuser_ID FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND dead = 0 AND name regexp '[^a-zA-Z0-9]' ORDER BY name LIMIT ? , ?");
		$query->bind_param('ii', $offset, $limit);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	
	public function get_character_search_specialchars($active, $offset, $limit) 
	{
		$query = $this->conn->prepare("SELECT character_ID, name, accepted, active, dead, color, date_created, fk_superuser_ID FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND active = ? AND dead = 0 AND name regexp '[^a-zA-Z0-9]' ORDER BY name LIMIT ? , ?");
		$query->bind_param('iii', $active, $offset, $limit);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	
	public function get_character_search_all_specialchars_include_dead() 
	{
		$query = $this->conn->prepare("SELECT character_ID, name, accepted, active, dead, color, date_created, fk_superuser_ID FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND name regexp '[^a-zA-Z]' ORDER BY name");
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}	
	
	public function count_characters_from_superuser($id)
	{
		$query = $this->conn->prepare("SELECT COUNT(character_ID) AS res FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND dead = 0 AND fk_superuser_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		/* Get the result */
		return $query->get_result();					
	}	
	
	public function count_active_characters_from_superuser($id)
	{
		$query = $this->conn->prepare("SELECT COUNT(character_ID) AS res FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND active = 1 AND fk_superuser_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		/* Get the result */
		return $query->get_result();						
	}
	
	public function count_accepted_characters_from_superuser($id)
	{
		$query = $this->conn->prepare("SELECT COUNT(character_ID) AS res FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND fk_superuser_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		/* Get the result */
		return $query->get_result();					
	}	
						
	public function get_characters_from_superuser($id)
	{
		$query = $this->conn->prepare("SELECT character_ID, name, accepted, active, dead, color, avatar, fk_superuser_ID FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND dead = 0 AND fk_superuser_ID = ? ORDER BY name");
		$query->bind_param('i', $id);
		$query->execute();	
		/* Get the result */
		return $query->get_result();					
	}
	
	public function get_characters_from_superuser_to_profile($id)
	{
		$query = $this->conn->prepare("SELECT character_ID, name, date_created, accepted, active, dead, color, avatar, fk_superuser_ID FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND fk_superuser_ID = ? AND dead = 0 ORDER BY active DESC, name");
		$query->bind_param('i', $id);
		$query->execute();	
		/* Get the result */
		return $query->get_result();					
	}	
	
	public function get_characters_from_superuser_to_profile_edit($id, $active, $dead, $accepted)
	{
		$query = $this->conn->prepare("SELECT character_ID, name, date_created, avatar, signature, maxskill, accepted, active, dead, color, fk_superuser_ID FROM ".$this->prefix."_usercharacters WHERE fk_superuser_ID = ? AND active = ? AND dead = ? AND accepted = ? ORDER BY name");
		$query->bind_param('iiii', $id, $active, $dead, $accepted);
		$query->execute();	
		/* Get the result */
		return $query->get_result();					
	}	
	
	public function get_first_character_from_superuser_to_profile_edit($id, $active, $dead, $accepted)
	{
		$query = $this->conn->prepare("SELECT character_ID, name, date_created, avatar, signature, maxskill, accepted, active, dead, color, fk_superuser_ID FROM ".$this->prefix."_usercharacters WHERE fk_superuser_ID = ? AND active = ? AND dead = ? AND accepted = ? ORDER BY name LIMIT 1");
		$query->bind_param('iiii', $id, $active, $dead, $accepted);
		$query->execute();	
		/* Get the result */
		return $query->get_result();					
	}
	
	public function count_characters_from_superuser_to_profile_edit($id, $active, $dead, $accepted)
	{
		$query = $this->conn->prepare("SELECT COUNT(character_ID) AS res FROM ".$this->prefix."_usercharacters WHERE fk_superuser_ID = ? AND active = ? AND dead = ? AND accepted = ? ORDER BY name");
		$query->bind_param('iiii', $id, $active, $dead, $accepted);
		$query->execute();	
		/* Get the result */
		return $query->get_result();					
	}				
				
	
	public function get_character($id)
	{
		$query = $this->conn->prepare("SELECT character_ID, name, date_created, avatar, signature, maxskill, accepted, active, dead, color, fk_superuser_ID  FROM ".$this->prefix."_usercharacters WHERE character_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();
		/* Get the result */
		return $query->get_result();		
	}		
	
	public function get_most_active_character_from_user($id)
	{
		$query = $this->conn->prepare("SELECT c.name,c.character_ID,c.color,COUNT(p.post_ID) AS NumberOfPosts FROM ".$this->prefix."_posts p
		LEFT JOIN ".$this->prefix."_usercharacters c
		ON p.fk_character_ID=c.character_ID
		WHERE c.fk_superuser_ID = ? AND p.official = 1
		GROUP BY c.name
		ORDER BY NumberOfPosts DESC
		LIMIT 1"
		);
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function get_first_character_from_user($id) 
	{
		$query = $this->conn->prepare("SELECT character_ID, name, date_created, accepted, active, dead, color, fk_superuser_ID FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND fk_superuser_ID = ? ORDER BY date_created LIMIT 1");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function get_newest_character_from_user($id) 
	{
		$query = $this->conn->prepare("SELECT character_ID, name, date_created, accepted, active, dead, color, fk_superuser_ID FROM ".$this->prefix."_usercharacters WHERE accepted = 1 AND fk_superuser_ID = ? ORDER BY date_created DESC LIMIT 1");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function get_most_active_topic_from_user($id)
	{
		$query = $this->conn->prepare("SELECT t.title, t.topic_ID, COUNT(p.post_ID) AS NumberOfPosts FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_topics t
		ON p.fk_topic_ID=t.topic_ID
		WHERE p.fk_superuser_ID = ? 
		GROUP BY t.topic_ID
		ORDER BY NumberOfPosts DESC
		LIMIT 1"
		);
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function get_most_active_topic_from_char($id)
	{
		$query = $this->conn->prepare("SELECT t.title, t.topic_ID, COUNT(p.post_ID) AS NumberOfPosts FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_topics t
		ON p.fk_topic_ID=t.topic_ID
		WHERE p.ingame = 1 AND p.fk_character_ID = ? 
		GROUP BY t.topic_ID
		ORDER BY NumberOfPosts DESC
		LIMIT 1"
		);
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function get_most_active_forum_from_user($id)
	{
		$query = $this->conn->prepare("SELECT f.title, f.forum_ID, COUNT(p.post_ID) AS NumberOfPosts FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_topics t
		ON p.fk_topic_ID=t.topic_ID
		INNER JOIN ".$this->prefix."_forums f
		ON t.fk_forum_ID=f.forum_ID
		WHERE p.fk_superuser_ID = ? 
		GROUP BY f.forum_ID
		ORDER BY NumberOfPosts DESC
		LIMIT 1"
		);
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function get_most_active_forum_from_char($id)
	{
		$query = $this->conn->prepare("SELECT f.title, f.forum_ID, COUNT(p.post_ID) AS NumberOfPosts FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_topics t
		ON p.fk_topic_ID=t.topic_ID
		INNER JOIN ".$this->prefix."_forums f
		ON t.fk_forum_ID=f.forum_ID
		WHERE p.ingame = 1 AND p.fk_character_ID = ?
		GROUP BY f.forum_ID
		ORDER BY NumberOfPosts DESC
		LIMIT 1"
		);
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function get_topposters_overall()
	{
		$query = $this->conn->prepare("SELECT s.color, s.superuser_ID, s.name, COUNT(p.post_ID) AS NumberOfPosts FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_superusers s
		ON p.fk_superuser_ID=s.superuser_ID
		WHERE p.ingame = 1 AND p.official = 1
		GROUP BY s.superuser_ID
		ORDER BY NumberOfPosts DESC
		LIMIT 5"
		);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function get_topposters_monthly($month, $year)
	{
		$query = $this->conn->prepare("SELECT s.color, s.superuser_ID, s.name, COUNT(p.post_ID) AS NumberOfPosts FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_superusers s
		ON p.fk_superuser_ID=s.superuser_ID
		WHERE p.ingame = 1 AND p.official = 1
		AND MONTH(datetime) = ? AND YEAR(datetime) = ?
		GROUP BY s.superuser_ID
		ORDER BY NumberOfPosts DESC
		LIMIT 5"
		);
		$query->bind_param('ii', $month, $year);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function update_superuser_profile($birthdate, $title, $reference, $geography, $website, $facebook, $skype, $color, $id) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_superusers SET birthday = ?, title = ?, reference = ?, geography = ?, website = ?, facebook = ?, 
		skype = ?, color = ? WHERE superuser_ID = ?");
		$query->bind_param('ssssssssi', $birthdate, $title, $reference, $geography, $website, $facebook, $skype, $color, $id);
		$query->execute();		
		return 1;
	}	
	
	public function update_superuser_avatar($avatar, $id) 
	{
		$this->conn->real_escape_string($avatar);
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_superusers SET avatar = ? WHERE superuser_ID = ?");
		$query->bind_param('si', $avatar, $id);
		$query->execute();		
		return 1;
	}	
	
	public function update_superuser_signature($sig, $id) 
	{
		$this->conn->real_escape_string($sig);
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_superusers SET signature = ? WHERE superuser_ID = ?");
		$query->bind_param('si', $sig, $id);
		$query->execute();		
		return 1;
	}	
	
	public function update_superuser_profiletext($text, $id) 
	{
		$this->conn->real_escape_string($text);
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_superusers SET profiletext = ? WHERE superuser_ID = ?");
		$query->bind_param('si', $text, $id);
		$query->execute();		
		return 1;
	}	
	
	public function update_superuser_password($hash, $id) 
	{
		$this->conn->real_escape_string($hash);
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_superusers SET password = ? WHERE superuser_ID = ?");
		$query->bind_param('si', $hash, $id);
		$query->execute();		
		return 1;
	}	
	
	public function update_superuser_email($email, $id) 
	{
		$this->conn->real_escape_string($email);
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_superusers SET email = ? WHERE superuser_ID = ?");
		$query->bind_param('si', $email, $id);
		$query->execute();		
		return 1;
	}	
	
	public function update_superuser_name($name, $id) 
	{
		$this->conn->real_escape_string($name);
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_superusers SET name = ? WHERE superuser_ID = ?");
		$query->bind_param('si', $name, $id);
		$query->execute();		
		return 1;
	}
	
	public function update_superuser_login($id) 
	{
		$now = date("Y-m-d H:i:s");
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_superusers SET last_active = ? WHERE superuser_ID = ?");
		$query->bind_param('si', $now, $id);
		$query->execute();		
		return 1;
	}	
	
	public function update_superuser_role($role, $id) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_superusers SET fk_role_ID = ? WHERE superuser_ID = ?");
		$query->bind_param('ii', $role, $id);
		$query->execute();		
		return 1;
	}		
	
	public function update_character_avatar($avatar, $id) 
	{
		$this->conn->real_escape_string($avatar);
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_usercharacters SET avatar = ? WHERE character_ID = ?");
		$query->bind_param('si', $avatar, $id);
		$query->execute();		
		return 1;
	}	
	
	public function update_character_signature($sig, $id) 
	{
		$this->conn->real_escape_string($sig);
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_usercharacters SET signature = ? WHERE character_ID = ?");
		$query->bind_param('si', $sig, $id);
		$query->execute();		
		return 1;
	}		
	
	public function update_character_forumname($name, $id) 
	{
		$this->conn->real_escape_string($name);
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_usercharacters SET name = ? WHERE character_ID = ?");
		$query->bind_param('si', $name, $id);
		$query->execute();		
		return 1;
	}	
	
	public function update_character_accepted_status($id, $status) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_usercharacters SET accepted = ? WHERE character_ID = ?");
		$query->bind_param('ii', $status, $id);
		$query->execute();		
		return 1;			
	}	
	
	public function update_character_active_status($id, $status) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_usercharacters SET active = ? WHERE character_ID = ?");
		$query->bind_param('ii', $status, $id);
		$query->execute();		
		return 1;			
	}	
	
	public function update_character_dead_status($id, $status) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_usercharacters SET dead = ? WHERE character_ID = ?");
		$query->bind_param('ii', $status, $id);
		$query->execute();		
		return 1;			
	}	
	
	public function update_character_color($color, $id) 
	{
		$this->conn->real_escape_string($color);
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_usercharacters SET color = ? WHERE character_ID = ?");
		$query->bind_param('si', $color, $id);
		$query->execute();		
		return 1;
	}	
	
	public function update_character_maxskill($maxskill, $id) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_usercharacters SET maxskill = ? WHERE character_ID = ?");
		$query->bind_param('ii', $maxskill, $id);
		$query->execute();		
		return 1;
	}	
	
	public function submit_reset_request($superuser, $uuid)
	{
		$now = date("Y-m-d H:i:s");
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_passwordreset (reset_ID, fk_superuser_ID, datetime)
		VALUES (?, ?, ?)");
		$query->bind_param('sis', $uuid, $superuser, $now);
		$query->execute();		
		return $this->conn->insert_id;	
	}
	
	public function try_get_reset_request($uuid)
	{
		$now = date("Y-m-d H:i:s");
		$query = $this->conn->prepare("SELECT COUNT(reset_ID) AS res FROM ".$this->prefix."_passwordreset WHERE reset_ID = ?");
		$query->bind_param('s', $uuid);
		$query->execute();		
		return $query->get_result();
	}
	
	public function get_reset_request($uuid)
	{
		$now = date("Y-m-d H:i:s");
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_passwordreset WHERE reset_ID = ?");
		$query->bind_param('s', $uuid);
		$query->execute();		
		return $query->get_result();
	}
	
	
	public function submit_new_character($name, $superuser)
	{
		$now = date("Y-m-d H:i:s");
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_usercharacters (name, date_created, accepted, active, dead, maxskill, fk_superuser_ID)
		VALUES (?, ?, 0, 1, 0, 50, ?)");
		$query->bind_param('ssi', $name, $now, $superuser);
		$query->execute();		
		return $this->conn->insert_id;	
	}
	
	public function create_character_profiledata($id)
	{
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_profiledata (fk_character_ID, fk_race_ID)
		VALUES (?, 11)");
		$query->bind_param('i', $id);
		$query->execute();				
		return 1;		
	}
	
	public function create_new_superuser($name, $hash, $email, $reference)
	{
		$now = date("Y-m-d H:i:s");
		$this->conn->real_escape_string($name);
		$this->conn->real_escape_string($hash);
		$this->conn->real_escape_string($email);
		$this->conn->real_escape_string($reference);
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_superusers (name, password, date_joined, last_active, email, reference, fk_role_ID)
		VALUES (?, ?, ?, ?, ?, ?, 1)");
		$query->bind_param('ssssss', $name, $hash, $now, $now, $email, $reference);
		$query->execute();		
		return $this->conn->insert_id;	
	}
	
	public function get_all_roles()
	{
		$query = $this->conn->prepare("SELECT role_ID, name FROM ".$this->prefix."_roles");
		$query->execute();		
		/* Get the result */
		return $query->get_result();			
	}	
	
	public function get_role($id)
	{
		$query = $this->conn->prepare("SELECT role_ID, name FROM ".$this->prefix."_roles WHERE role_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();			
	}	
	
	public function count_forums_where_user_is_forummod($id)
	{
		$query = $this->conn->prepare("SELECT COUNT(fk_forum_ID) AS res FROM ".$this->prefix."_forummods WHERE fk_superuser_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();			
	}	
	
	public function get_forums_where_user_is_forummod($id)
	{
		$query = $this->conn->prepare("SELECT fk_superuser_ID, fk_forum_ID FROM ".$this->prefix."_forummods WHERE fk_superuser_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();			
	}	
	
	public function delete_approvalrequests_from_char($id) 
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_approvalrequests WHERE fk_character_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		return 1;
	}	
	
	public function delete_groupmemberships_from_char($id) 
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_groupmembers WHERE fk_character_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		return 1;
	}
	
	public function delete_tags_from_char($id) 
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_tags WHERE fk_character_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		return 1;
	}
	
	public function delete_profiledata_from_char($id) 
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_profiledata WHERE fk_character_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		return 1;
	}
	
	public function delete_all_posts_from_char($id) 
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_posts WHERE fk_character_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		return 1;
	}
	
	public function delete_character($id) 
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_usercharacters WHERE character_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		return 1;
	}
	
	public function get_user_postrank($posts)
	{
		$query = $this->conn->prepare("SELECT title FROM ".$this->prefix."_postranks WHERE postlimit <= ? ORDER BY postlimit DESC LIMIT 1");
		$query->bind_param('i', $posts);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	/* FORUMS  */
	
	public function forum_exists($id) 
	{
		$query = $this->conn->prepare("SELECT COUNT(forum_ID) AS res FROM ".$this->prefix."_forums WHERE forum_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();			
	}	
	public function get_forum($id) 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_forums WHERE forum_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();			
	}	
	public function get_all_forums() 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_forums ORDER BY title");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();			
	}	
	
	public function get_all_writable_forums($ingame, $official) 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_forums WHERE writeable = 1 AND ingame = ? AND official = ? ORDER BY title");
		$query->bind_param('ii', $ingame, $official);
		$query->execute();		
		/* Get the result */
		return $query->get_result();			
	}	
		
	public function count_toplevel_forums()
	{
		$query = $this->conn->prepare("SELECT COUNT(forum_ID) AS res FROM ".$this->prefix."_forums WHERE parent_ID = 0");
		$query->execute();	
		/* Get the result */
		return $query->get_result();	
	}
	public function get_toplevel_forum($next) 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_forums WHERE parent_ID = 0 AND above_ID = ?");
		$query->bind_param('i', $next);
		$query->execute();	
		/* Get the result */
		return $query->get_result();				
	}	
	
	public function check_for_forum_below($forum) 
	{
		$query = $this->conn->prepare("SELECT COUNT(forum_ID) AS res FROM ".$this->prefix."_forums WHERE above_ID = ?");
		$query->bind_param('i', $forum);
		$query->execute();	
		/* Get the result */
		return $query->get_result();				
	}
	
	public function get_forum_below($forum) 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_forums WHERE above_ID = ?");
		$query->bind_param('i', $forum);
		$query->execute();	
		/* Get the result */
		return $query->get_result();				
	}
	
	public function count_subforums($parent)
	{
		$query = $this->conn->prepare("SELECT COUNT(forum_ID) AS res FROM ".$this->prefix."_forums WHERE parent_ID = ?");
		$query->bind_param('i', $parent);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	
	public function count_subforums_posts($parent)
	{
		$query = $this->conn->prepare("WITH forumsCTE AS
		( 
		
		SELECT forum_ID, title, parent_ID
		FROM ".$this->prefix."_forums
		WHERE parent_ID = ?
		UNION ALL
		
		SELECT f.forum_ID, f.title, f.parent_ID
		FROM ".$this->prefix."_forums f
		INNER JOIN forumsCTE s ON f.parent_ID = s.forum_ID 
		) 
		
		SELECT * FROM forumsCTE  ");	
		$query->bind_param('i', $parent);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	
	public function get_subforum($next, $parent) 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_forums WHERE parent_ID = ? AND above_ID = ?");
		$query->bind_param('ii', $parent, $next);
		$query->execute();
		/* Get the result */
		return $query->get_result();				
	}	
	
	public function get_all_subforums($parent) 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_forums WHERE parent_ID = ?");
		$query->bind_param('i', $parent);
		$query->execute();
		/* Get the result */
		return $query->get_result();				
	}	
	
	public function insert_new_forum($parent, $above, $title, $desc, $image, $ingame, $official, $category, $write, $read_access, $write_access) 
	{
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_forums (parent_ID, above_ID, title, description, picture, 
		ingame, official, category, writeable, read_access, write_access)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$query->bind_param('iisssiiiiii', $parent, $above, $title, $desc, $image, $ingame, $official, $category, $write, $read_access, $write_access);
		$query->execute();		
		/* Get the newly inserted id */
		return $this->conn->insert_id;
	}	
	
	public function edit_forum($parent, $title, $desc, $image, $ingame, $official, $category, $write, $read_access, $write_access, $forum) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_forums SET parent_ID = ?, title = ?, description = ?, picture = ?,
		ingame = ?, official = ?, category = ?, writeable = ?, read_access = ?, write_access = ? WHERE forum_ID = ?");
		$query->bind_param('isssiiiiiii', $parent, $title, $desc, $image, $ingame, $official, $category, $write, $read_access, $write_access, $forum);
		$query->execute();		
		/* Get the newly inserted id */
		return 1;
	}	
	
	
	public function update_forum_position($above, $forum) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_forums SET above_ID = ? WHERE forum_ID = ?");
		$query->bind_param('ii', $above, $forum);
		$query->execute();		
		
		return 1;
	}
	
	public function count_forum_posts($forum)
	{
		$query = $this->conn->prepare("SELECT COUNT(p.post_ID) AS res FROM ".$this->prefix."_posts p 
		INNER JOIN ".$this->prefix."_topics t ON
		p.fk_topic_ID = t.topic_ID
		WHERE t.fk_forum_ID = ?
		");
		$query->bind_param('i', $forum);
		$query->execute();	
		/* Get the result */
		return $query->get_result();		
	}
	
	public function count_all_forum_posts()
	{
		$query = $this->conn->prepare("SELECT COUNT(p.post_ID) AS numberOfPosts, t.fk_forum_ID FROM ".$this->prefix."_posts p 
		INNER JOIN ".$this->prefix."_topics t ON
		p.fk_topic_ID = t.topic_ID
		GROUP BY t.fk_forum_ID
		");
		$query->execute();	
		return $query->get_result();		
	}
	
	public function count_forum_topics($forum)
	{
		$query = $this->conn->prepare("SELECT COUNT(t.topic_ID) AS res FROM ".$this->prefix."_topics t 
		WHERE t.fk_forum_ID = ? ");
		$query->bind_param('i', $forum);
		$query->execute();	
		/* Get the result */
		return $query->get_result();		
	}
	
	public function count_all_forum_topics()
	{
		$query = $this->conn->prepare("SELECT COUNT(t.topic_ID) AS numberOfTopics, t.fk_forum_ID FROM ".$this->prefix."_topics t 
		GROUP BY t.fk_forum_ID");
		$query->execute();	
		return $query->get_result();		
	}
	
	public function delete_forum($forum) 
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_forums WHERE forum_ID = ?");
		$query->bind_param('i', $forum);
		$query->execute();	
		return 1;
	}	
	
		
	/* FORUM MODS */
	public function forummod_exists($forumid, $userid) 
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_forummods WHERE fk_forum_ID = ? AND fk_superuser_ID = ?");
		$query->bind_param('ii', $forumid, $userid);
		$query->execute();	
		/* Get the result */
		return $query->get_result();				
	}
	
	public function count_forummods($forumid) 
	{
		$query = $this->conn->prepare("SELECT COUNT(fk_superuser_ID) AS res FROM ".$this->prefix."_forummods WHERE fk_forum_ID = ?");
		$query->bind_param('i', $forumid);
		$query->execute();	
		/* Get the result */
		return $query->get_result();				
	}
	
	public function get_forummods($forumid) 
	{
		$query = $this->conn->prepare("SELECT fk_superuser_ID FROM ".$this->prefix."_forummods WHERE fk_forum_ID = ?");
		$query->bind_param('i', $forumid);
		$query->execute();	
		/* Get the result */
		return $query->get_result();				
	}
	
	public function insert_new_forummod($forumid, $userid) 
	{
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_forummods (fk_forum_ID, fk_superuser_ID)
		VALUES (?, ?)");
		$query->bind_param('ii', $forumid, $userid);
		$query->execute();		
		
		return 1;
	}
	
	public function delete_forummod($forumid, $userid) 
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_forummods WHERE fk_forum_ID = ? AND fk_superuser_ID = ?");
		$query->bind_param('ii', $forumid, $userid);
		$query->execute();	
		return 1;
	}	
	
	
	
	/* TOPICS AND POSTS */
	
	public function get_topics($id, $offset, $limit) 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_topics WHERE fk_forum_ID = ? ORDER BY pinned DESC, last_posted DESC LIMIT ? , ?");
		$query->bind_param('iii', $id, $offset, $limit);
		$query->execute();	
		/* Get the result */
		return $query->get_result();				
	}
	
	public function count_topics($id) 
	{
		$query = $this->conn->prepare("SELECT COUNT(topic_ID) FROM ".$this->prefix."_topics WHERE fk_forum_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		/* Get the result */
		return $query->get_result();				
	}
	
	public function count_all_topics()
	{
		$query = $this->conn->prepare("SELECT COUNT(topic_ID) AS res FROM ".$this->prefix."_topics");
		$query->execute();	
		return $query->get_result();	
	}	
	
	public function count_all_ingame_topics()
	{
		$query = $this->conn->prepare("SELECT COUNT(topic_ID) AS res FROM ".$this->prefix."_topics WHERE ingame = 1 AND official = 1");
		$query->execute();	
		return $query->get_result();			
	}	
	
	public function count_all_posts()
	{
		$query = $this->conn->prepare("SELECT COUNT(post_ID) AS res FROM ".$this->prefix."_posts");
		$query->execute();	
		return $query->get_result();		
	}	
	
	public function count_all_ingame_posts()
	{
		$query = $this->conn->prepare("SELECT COUNT(post_ID) AS res FROM ".$this->prefix."_posts WHERE ingame = 1 AND official = 1");
		$query->execute();	
		return $query->get_result();	
	}	
	
	public function count_topics_from_user($id)
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_superusers u ON p.fk_superuser_ID=u.superuser_ID WHERE u.superuser_ID = ? AND ingame = 1 AND official = 1 GROUP BY p.fk_topic_ID ORDER BY p.datetime DESC");	
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}		
	
	public function get_topics_from_user($id, $offset, $limit)
	{
		$query = $this->conn->prepare("SELECT p.fk_topic_id, p.fk_character_ID, p.datetime FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_superusers u ON p.fk_superuser_ID=u.superuser_ID WHERE u.superuser_ID = ? AND ingame = 1 AND official = 1 
		GROUP BY p.fk_topic_ID ORDER BY p.datetime DESC LIMIT ? , ?");	
		$query->bind_param('iii', $id, $offset, $limit);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}	
	
	public function count_offgame_topics_from_user($id)
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_superusers u ON p.fk_superuser_ID=u.superuser_ID WHERE u.superuser_ID = ? AND (ingame = 0 OR official = 0) GROUP BY p.fk_topic_ID ORDER BY p.datetime DESC");	
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}		
	
	public function get_offgame_topics_from_user($id)
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_superusers u ON p.fk_superuser_ID=u.superuser_ID WHERE u.superuser_ID = ? AND (ingame = 0 OR official = 0) GROUP BY p.fk_topic_ID ORDER BY p.datetime DESC");	
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}	
	
	public function get_topics_from_character($id)
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_topics WHERE fk_character_ID = ?");	
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
					
	public function topic_exists($id) 
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_topics WHERE topic_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		/* Get the result */
		return $query->get_result();				
	}		
	public function get_topic($id) 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_topics WHERE topic_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		/* Get the result */
		return $query->get_result();				
	}	
	
	public function get_numberof_posts($id)
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_posts WHERE fk_topic_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		/* Get the result */
		return $query->get_result();		
	}
	
	public function get_last_post($id)
	{
		$query = $this->conn->prepare("SELECT fk_character_ID, fk_superuser_ID, post_ID, datetime FROM ".$this->prefix."_posts WHERE fk_topic_ID = ? ORDER BY datetime DESC LIMIT 1");
		$query->bind_param('i', $id);
		$query->execute();
		/* Get the result */
		return $query->get_result();			
	}	
	
	public function get_first_post($id)
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_posts WHERE fk_topic_ID = ? ORDER BY datetime LIMIT 1");
		$query->bind_param('i', $id);
		$query->execute();
		/* Get the result */
		return $query->get_result();			
	}
	
	public function get_posts($topic, $offset, $limit) 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_posts WHERE fk_topic_ID = ? ORDER BY datetime LIMIT ? , ?");
		$query->bind_param('iii', $topic, $offset, $limit);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function get_five_latest_overall_posts($accessrank) 
	{
		$query = $this->conn->prepare("SELECT p.ingame, t.warning, t.topic_ID, f.forum_ID, f.title AS forumtitle, 
		t.title AS topictitle, p.post_ID, t.last_posted FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_topics t ON
		p.fk_topic_ID = t.topic_ID
		INNER JOIN ".$this->prefix."_forums f ON
		t.fk_forum_ID = f.forum_ID
		WHERE f.read_access <= ?
		GROUP BY t.topic_ID
		ORDER BY t.last_posted DESC LIMIT 5");
		$query->bind_param('i', $accessrank);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function get_fifty_latest_overall_posts($accessrank) 
	{
		$query = $this->conn->prepare("SELECT p.ingame, t.warning, t.views, t.datetime, t.topic_ID, f.forum_ID, f.title AS forumtitle, 
		t.title AS topictitle, p.fk_superuser_ID, p.fk_character_ID, p.post_ID, t.last_posted FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_topics t ON
		p.fk_topic_ID = t.topic_ID
		INNER JOIN ".$this->prefix."_forums f ON
		t.fk_forum_ID = f.forum_ID
		WHERE f.read_access <= ?
		GROUP BY t.topic_ID
		ORDER BY t.last_posted DESC LIMIT 50");
		$query->bind_param('i', $accessrank);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function get_last_topic_poster($topic)
	{
		$query = $this->conn->prepare("SELECT fk_character_ID, fk_superuser_ID FROM ".$this->prefix."_posts
		WHERE fk_topic_ID = ?
		ORDER BY datetime DESC LIMIT 1");
		$query->bind_param('i', $topic);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function get_posts_from_user($id, $offset, $limit, $accessrank) 
	{
		$query = $this->conn->prepare("SELECT p.fk_character_ID, p.ingame, p.fk_topic_ID, t.fk_forum_ID, p.text, p.post_ID, p.datetime FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_topics t ON
		p.fk_topic_ID = t.topic_ID
		INNER JOIN ".$this->prefix."_forums f ON
		t.fk_forum_ID = f.forum_ID
		WHERE p.fk_superuser_ID = ? AND f.read_access <= ?
		ORDER BY p.datetime DESC LIMIT ? , ?");
		$query->bind_param('iiii', $id, $accessrank, $offset, $limit);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function get_posts_from_character($id, $offset, $limit, $accessrank) 
	{
		$query = $this->conn->prepare("SELECT p.fk_character_ID, p.ingame, p.fk_topic_ID, t.fk_forum_ID, p.text, p.post_ID, p.datetime FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_topics t ON
		p.fk_topic_ID = t.topic_ID
		INNER JOIN ".$this->prefix."_forums f ON
		t.fk_forum_ID = f.forum_ID
		WHERE p.fk_character_ID = ? AND f.read_access <= ? AND p.ingame = 1
		ORDER BY p.datetime DESC LIMIT ? , ?");
		$query->bind_param('iiii', $id, $accessrank, $offset, $limit);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function get_number_of_posts_to_show_from_user($id, $accessrank) 
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_topics t ON
		p.fk_topic_ID = t.topic_ID
		INNER JOIN ".$this->prefix."_forums f ON
		t.fk_forum_ID = f.forum_ID
		WHERE p.fk_superuser_ID = ? AND f.read_access <= ?");
		$query->bind_param('ii', $id, $accessrank);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function get_number_of_posts_to_show_from_character($id, $accessrank) 
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_topics t ON
		p.fk_topic_ID = t.topic_ID
		INNER JOIN ".$this->prefix."_forums f ON
		t.fk_forum_ID = f.forum_ID
		WHERE p.fk_character_ID = ? AND f.read_access <= ? AND p.ingame = 1");
		$query->bind_param('ii', $id, $accessrank);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function get_topics_to_show_from_user($id, $offset, $limit, $accessrank) 
	{
		$query = $this->conn->prepare("
		SELECT t.topic_ID, t.title, t.warning, t.fk_superuser_ID, t.fk_character_ID, t.ingame, f.title AS forumtitle, f.forum_ID, t.datetime, 
		t.views, t.pinned, t.locked
		FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_topics t ON
		p.fk_topic_ID = t.topic_ID
		INNER JOIN ".$this->prefix."_forums f ON
		t.fk_forum_ID = f.forum_ID
		WHERE p.fk_superuser_ID = ? AND f.read_access <= ?
		GROUP BY t.topic_ID
		ORDER BY t.last_posted DESC
		LIMIT ? , ?");
		$query->bind_param('iiii', $id, $accessrank, $offset, $limit);
		$query->execute();		
		/* Get the result */ 
		return $query->get_result();		
	}
	
	public function get_topics_to_show_from_character($id, $offset, $limit, $accessrank) 
	{
		$query = $this->conn->prepare("
		SELECT t.topic_ID, t.title, t.warning, t.fk_superuser_ID, t.fk_character_ID, t.ingame, f.title AS forumtitle, f.forum_ID, t.datetime, 
		t.views, t.pinned, t.locked
		FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_topics t ON
		p.fk_topic_ID = t.topic_ID
		INNER JOIN ".$this->prefix."_forums f ON
		t.fk_forum_ID = f.forum_ID
		WHERE p.fk_character_ID = ? AND f.read_access <= ? AND p.ingame = 1
		GROUP BY t.topic_ID
		ORDER BY t.last_posted DESC
		LIMIT ? , ?");
		$query->bind_param('iiii', $id, $accessrank, $offset, $limit);
		$query->execute();		
		/* Get the result */ 
		return $query->get_result();		
	}
	
	public function get_ingame_topics_to_show_from_user($id, $offset, $limit, $accessrank) 
	{
		$query = $this->conn->prepare("
		SELECT t.topic_ID, t.title, t.warning, t.fk_superuser_ID, t.fk_character_ID, t.ingame, f.title AS forumtitle, f.forum_ID, t.datetime, 
		t.views, t.pinned, t.locked
		FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_topics t ON
		p.fk_topic_ID = t.topic_ID
		INNER JOIN ".$this->prefix."_forums f ON
		t.fk_forum_ID = f.forum_ID
		WHERE p.fk_superuser_ID = ? AND f.read_access <= ? AND p.ingame = 1 AND p.official = 1
		GROUP BY t.topic_ID
		ORDER BY t.last_posted DESC
		LIMIT ? , ?");
		$query->bind_param('iiii', $id, $accessrank, $offset, $limit);
		$query->execute();		
		/* Get the result */ 
		return $query->get_result();		
	}
	
	public function get_offgame_topics_to_show_from_user($id, $offset, $limit, $accessrank) 
	{
		$query = $this->conn->prepare("
		SELECT t.topic_ID, t.title, t.warning, t.fk_superuser_ID, t.fk_character_ID, t.ingame, f.title AS forumtitle, f.forum_ID, t.datetime, 
		t.views, t.pinned, t.locked
		FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_topics t ON
		p.fk_topic_ID = t.topic_ID
		INNER JOIN ".$this->prefix."_forums f ON
		t.fk_forum_ID = f.forum_ID
		WHERE p.fk_superuser_ID = ? AND f.read_access <= ? AND (p.ingame = 0 OR p.official = 0)
		GROUP BY t.topic_ID
		ORDER BY t.last_posted DESC
		LIMIT ? , ?");
		$query->bind_param('iiii', $id, $accessrank, $offset, $limit);
		$query->execute();		
		/* Get the result */ 
		return $query->get_result();		
	}
	
	public function get_number_of_topics_to_show_from_user($id, $accessrank) 
	{
		$query = $this->conn->prepare("SELECT COUNT(DISTINCT t.topic_ID) AS res 
		 FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_topics t ON
		p.fk_topic_ID = t.topic_ID
		INNER JOIN ".$this->prefix."_forums f ON
		t.fk_forum_ID = f.forum_ID
		WHERE p.fk_superuser_ID = ? AND f.read_access <= ?
		ORDER BY p.datetime");
		$query->bind_param('ii', $id, $accessrank);
		$query->execute();
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function get_number_of_topics_to_show_from_character($id, $accessrank) 
	{
		$query = $this->conn->prepare("SELECT COUNT(DISTINCT t.topic_ID) AS res 
		 FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_topics t ON
		p.fk_topic_ID = t.topic_ID
		INNER JOIN ".$this->prefix."_forums f ON
		t.fk_forum_ID = f.forum_ID
		WHERE p.fk_character_ID = ? AND f.read_access <= ? AND p.ingame = 1
		ORDER BY p.datetime");
		$query->bind_param('ii', $id, $accessrank);
		$query->execute();
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function get_number_of_ingame_topics_to_show_from_user($id, $accessrank) 
	{
		$query = $this->conn->prepare("SELECT COUNT(DISTINCT t.topic_ID) AS res 
		 FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_topics t ON
		p.fk_topic_ID = t.topic_ID
		INNER JOIN ".$this->prefix."_forums f ON
		t.fk_forum_ID = f.forum_ID
		WHERE p.fk_superuser_ID = ? AND f.read_access <= ? AND p.ingame = 1 AND p.official = 1
		ORDER BY p.datetime");
		$query->bind_param('ii', $id, $accessrank);
		$query->execute();
		/* Get the result */
		return $query->get_result();		
	}
	
	public function get_number_of_offgame_topics_to_show_from_user($id, $accessrank) 
	{
		$query = $this->conn->prepare("SELECT COUNT(DISTINCT t.topic_ID) AS res 
		 FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_topics t ON
		p.fk_topic_ID = t.topic_ID
		INNER JOIN ".$this->prefix."_forums f ON
		t.fk_forum_ID = f.forum_ID
		WHERE p.fk_superuser_ID = ? AND f.read_access <= ? AND (p.ingame = 0 OR p.official = 0)
		ORDER BY p.datetime");
		$query->bind_param('ii', $id, $accessrank);
		$query->execute();
		/* Get the result */
		return $query->get_result();		
	}
	
	public function get_post($id) 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_posts WHERE post_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function get_previous_post($datetime) 
	{
		$this->conn->real_escape_string($datetime);
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_posts WHERE datetime < ? ORDER BY datetime desc LIMIT 1");
		$query->bind_param('s', $datetime);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function post_exists($id) 
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_posts WHERE post_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function get_all_posts($topic) 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_posts WHERE fk_topic_ID = ? ORDER BY datetime");
		$query->bind_param('i', $topic);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}		
	
	public function get_ten_last_posts($topic) 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_posts WHERE fk_topic_ID = ? ORDER BY datetime DESC LIMIT 10");
		$query->bind_param('i', $topic);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function count_ingame_posts_from_superuser($id) 
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_posts WHERE fk_superuser_ID = ? AND ingame = 1 AND official = 1");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}			
	public function count_all_posts_from_superuser($id) 
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_posts WHERE fk_superuser_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function count_ingame_posts_from_character($id) 
	{
		$query = $this->conn->prepare("SELECT COUNT(post_ID) AS res FROM ".$this->prefix."_posts WHERE fk_character_ID = ? AND ingame = 1 AND official = 1");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function get_newest_post_from_character($id) 
	{
		$query = $this->conn->prepare("SELECT datetime FROM ".$this->prefix."_posts WHERE fk_character_ID = ? AND ingame = 1 ORDER BY datetime DESC LIMIT 1");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function update_topic_viewcount($value, $topic) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_topics SET views = ? WHERE topic_ID = ?");
		$query->bind_param('ii', $value, $topic);
		$query->execute();		
		return 1;
	}
	
	public function update_topic_lastpost($topic) 
	{
		$now = date("Y-m-d H:i:s");
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_topics SET last_posted = ? WHERE topic_ID = ?");
		$query->bind_param('si', $now, $topic);
		$query->execute();		
		return 1;
	}
		
	public function update_topic_lock($lock, $topic) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_topics SET locked = ? WHERE topic_ID = ?");
		$query->bind_param('ii', $lock, $topic);
		$query->execute();		
		return 1;
	}
	
	public function update_topic_forum($forum, $topic) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_topics SET fk_forum_ID = ? WHERE topic_ID = ?");
		$query->bind_param('ii', $forum, $topic);
		$query->execute();		
		return 1;
	}
	
	public function delete_topic($id)
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_topics WHERE topic_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		return 1;
	}
	
	public function delete_posts_from_topic($id)
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_posts WHERE fk_topic_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		return 1;
	}
	
	public function delete_post($id)
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_posts WHERE post_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		return 1;
	}
	
	
	public function update_ingame_topic_author($char, $user, $topic) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_topics SET fk_character_ID = ?, fk_superuser_ID = ? WHERE topic_ID = ?");
		$query->bind_param('iii', $char, $user, $topic);
		$query->execute();		
		return 1;
	}
	
	public function update_ingame_posts_author_to_guest($char) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_posts SET fk_character_ID = 0 WHERE fk_character_ID = ?");
		$query->bind_param('i', $char);
		$query->execute();		
		return 1;
	}
	public function update_ingame_topics_author_to_guest($char) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_topics SET fk_character_ID = 0 WHERE fk_character_ID = ?");
		$query->bind_param('i', $char);
		$query->execute();		
		return 1;
	}
	
	/* TAGS */
	
	public function try_get_tag($user, $topic) 
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_tags WHERE fk_character_ID = ? AND fk_topic_ID = ?");
		$query->bind_param('ii', $user, $topic);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function try_get_active_tag($user, $topic) 
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_tags WHERE fk_character_ID = ? AND fk_topic_ID = ? AND active = 1");
		$query->bind_param('ii', $user, $topic);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}		
	
	public function get_tag($user, $topic) 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_tags WHERE fk_character_ID = ? AND fk_topic_ID = ?");
		$query->bind_param('ii', $user, $topic);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
		
	public function update_tag($active, $tag) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_tags SET active = ? WHERE tag_ID = ?");
		$query->bind_param('ii', $active, $tag);
		$query->execute();		
		return 1;
	}
	
	public function delete_tag($tag) 
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_tags WHERE tag_ID = ?");
		$query->bind_param('i', $tag);
		$query->execute();		
		return 1;
	}
	
	public function count_topic_tags($topic) 
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_tags WHERE fk_topic_ID = ?");
		$query->bind_param('i', $topic);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}		
	
	public function get_topic_tags($topic) 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_tags WHERE fk_topic_ID = ?");
		$query->bind_param('i', $topic);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}			
	
	public function count_all_tags_from_superuser($id)
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_tags t 
		INNER JOIN ".$this->prefix."_usercharacters c ON t.fk_character_ID=c.character_ID WHERE c.fk_superuser_ID = ? AND t.active = 1");	
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	
	public function get_all_tags_from_superuser($id)
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_tags t 
		INNER JOIN ".$this->prefix."_usercharacters c ON t.fk_character_ID=c.character_ID WHERE c.fk_superuser_ID = ? AND t.active = 1 ORDER BY t.date DESC");	
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}	
	
	public function delete_tags_from_topic($id) 
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_tags WHERE fk_topic_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		return 1;
	}
	
	public function insert_new_tag($topic, $char, $tagged_by) 
	{
		$now = date("Y-m-d H:i:s");
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_tags (fk_topic_ID, fk_character_ID, tagged_by_ID, date, active)
		VALUES (?, ?, ?, ?, 1)");
		$query->bind_param('iiis', $topic, $char, $tagged_by, $now);
		$query->execute();		
		
		return 1;
	}	
	
	/* POSTING */
	
	public function insert_new_topic($forum, $title, $superuser, $char, $ingame, $official, $pinned, $warning) 
	{
		$now = date("Y-m-d H:i:s");
		$this->conn->real_escape_string($title);
		$this->conn->real_escape_string($warning);
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_topics (fk_forum_ID, title, datetime, last_posted, fk_superuser_ID, fk_character_ID, ingame, official, pinned, warning)
		VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
		$query->bind_param('isssiiiiis', $forum, $title, $now, $now, $superuser, $char, $ingame, $official, $pinned, $warning);
		$query->execute();		
		/* Get the newly inserted id */
		return $this->conn->insert_id;
	}	
	
	public function insert_new_post($topic, $text, $superuser, $char, $ingame, $official) 
	{
		$now = date("Y-m-d H:i:s");
		$this->conn->real_escape_string($text);
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_posts (fk_topic_ID, text, datetime, fk_superuser_ID, fk_character_ID, ingame, official)
		VALUES (?, ?, ?, ?, ?, ?, ?)");
		$query->bind_param('issiiii', $topic, $text, $now, $superuser, $char, $ingame, $official);
		$query->execute();		
		/* Get the newly inserted id */
		return $this->conn->insert_id;
	}	
	
	public function update_topic($title, $char, $pinned, $warning, $id)
	{
		$this->conn->real_escape_string($title);
		$this->conn->real_escape_string($warning);
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_topics SET title = ?, fk_character_ID = ?, pinned = ?, warning = ? WHERE topic_ID = ?");
		$query->bind_param('siisi', $title, $char, $pinned, $warning, $id);
		$query->execute();		
		return 1;
	}	
	
	public function update_post($text, $char, $id)
	{
		$this->conn->real_escape_string($text);
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_posts SET text = ?, fk_character_ID = ? WHERE post_ID = ?");
		$query->bind_param('sii', $text, $char, $id);
		$query->execute();		
		return 1;
	}	
	
	public function insert_new_poll($topic, $question, $choices) 
	{
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_polls (fk_topic_ID, question, multiple_options)
		VALUES (?, ?, ?)");
		$query->bind_param('isi', $topic, $question, $choices);
		$query->execute();		
		return $this->conn->insert_id;
	}	
	
	public function insert_polloption($poll, $option) 
	{
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_polloptions (fk_poll_ID, option)
		VALUES (?, ?)");
		$query->bind_param('is', $poll, $option);
		$query->execute();		
		
		return $this->conn->insert_id;
	}	
	
	public function check_if_topic_has_poll($topic) 
	{
		$query = $this->conn->prepare("SELECT COUNT(poll_ID) AS res FROM ".$this->prefix."_polls WHERE fk_topic_ID = ?");
		$query->bind_param('i', $topic);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
		
	
	public function get_poll_from_topic($topic) 
	{
		$query = $this->conn->prepare("SELECT poll_ID, question, multiple_options FROM ".$this->prefix."_polls WHERE fk_topic_ID = ?");
		$query->bind_param('i', $topic);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function get_poll_options($poll) 
	{
		$query = $this->conn->prepare("SELECT option, option_ID FROM ".$this->prefix."_polloptions WHERE fk_poll_ID = ?");
		$query->bind_param('i', $poll);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function get_highest_poll_vote($poll) 
	{
		$query = $this->conn->prepare("SELECT COUNT(v.fk_superuser_ID) AS numberOfVotes FROM ".$this->prefix."_pollvotes v
		INNER JOIN ".$this->prefix."_polloptions o ON
		v.fk_option_ID = o.option_ID
		WHERE o.fk_poll_ID = ?
		GROUP BY v.fk_option_ID
		ORDER BY numberOfVotes DESC LIMIT 1");
		$query->bind_param('i', $poll);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function get_polloptions_votes($option) 
	{
		$query = $this->conn->prepare("SELECT COUNT(fk_superuser_ID) AS numberOfVotes FROM ".$this->prefix."_pollvotes
		WHERE fk_option_ID = ?
		GROUP BY fk_option_ID");
		$query->bind_param('i', $option);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function count_all_poll_votes($poll) 
	{
		$query = $this->conn->prepare("SELECT COUNT(v.fk_superuser_ID) AS numberOfVotes FROM ".$this->prefix."_pollvotes v
		INNER JOIN ".$this->prefix."_polloptions o ON
		v.fk_option_ID = o.option_ID
		WHERE o.fk_poll_ID = ?");
		$query->bind_param('i', $poll);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function check_polloption_uservote($user, $option) 
	{
		$query = $this->conn->prepare("SELECT COUNT(fk_superuser_ID) AS res FROM ".$this->prefix."_pollvotes WHERE fk_option_ID = ? AND fk_superuser_ID = ?");
		$query->bind_param('ii', $option, $user);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function add_uservote($user, $option) 
	{
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_pollvotes (fk_option_ID, fk_superuser_ID)
		VALUES (?, ?)");
		$query->bind_param('ii', $option, $user);
		$query->execute();		
		return $this->conn->insert_id;	
	}
	
	public function delete_uservote($user, $option) 
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_pollvotes WHERE fk_option_ID = ? AND fk_superuser_ID = ?");
		$query->bind_param('ii', $option, $user);
		$query->execute();		
		/* Get the result */
		return 1;		
	}
	
	public function delete_votes_from_poll_option($option) 
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_pollvotes WHERE fk_option_ID = ?");
		$query->bind_param('i', $option);
		$query->execute();		
		/* Get the result */
		return 1;		
	}
	
	public function delete_options_from_poll($poll) 
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_polloptions WHERE fk_poll_ID = ?");
		$query->bind_param('i', $poll);
		$query->execute();		
		/* Get the result */
		return 1;		
	}
	
	public function delete_poll($poll) 
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_polls WHERE poll_ID = ?");
		$query->bind_param('i', $poll);
		$query->execute();		
		/* Get the result */
		return 1;		
	}
	
	/* Characterprofiles */
	
	public function get_all_races() 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_races ORDER BY name");
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function get_race($id) 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_races WHERE race_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function get_character_profiledata($id) 
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_profiledata WHERE fk_character_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function update_character_profiledata($shortname, $age, $faith, $alignment, $profession, $height, $weight, $looks, $magic1_skill, $magic2_skill, $story, $family, $habitat, $other, 
	$str, $weap, $flx, $end, $tact, $int, $crea, $men, $cha, $id)
	{
		$this->conn->real_escape_string($shortname);
		$this->conn->real_escape_string($faith);
		$this->conn->real_escape_string($alignment);
		$this->conn->real_escape_string($profession);
		$this->conn->real_escape_string($looks);
		$this->conn->real_escape_string($story);
		$this->conn->real_escape_string($family);
		$this->conn->real_escape_string($habitat);
		$this->conn->real_escape_string($other);
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_profiledata SET shortname = ?, age = ?, faith = ?, alignment = ?, profession = ?, height = ?, weight = ?, 
		looks = ?, magic1_skill = ?, magic2_skill = ?, story = ?, family = ?, habitat = ?, other = ?, skill_strength = ?, skill_weapons = ?, skill_flexiness = ?, skill_endurance = ?,
		skill_tactics = ?, skill_intelligence = ?, skill_creativity = ?, skill_mental = ?, 	skill_chakra = ? WHERE fk_character_ID = ?");
		$query->bind_param('sisssiisiissssiiiiiiiiii', $shortname, $age, $faith, $alignment, $profession, $height, $weight, $looks, $magic1_skill, $magic2_skill, $story, $family, $habitat, $other, 
		$str, $weap, $flx, $end, $tact, $int, $crea, $men, $cha, $id);
		$query->execute();		
		return 1;
		
	}
	
	public function update_character_profiledata_full($fullname, $shortname, $age, $gender, $birthday, $faith, $alignment, $profession, $race, $raceinfo, $height, $weight, $looks, 
	$magic1, $magic2, $magic1_skill, $magic2_skill, $personality, $story, $family, $habitat, $other, $str, $weap, $flx, $end, $tact, $int, $crea, $men, $cha, $id)
	{
		$this->conn->real_escape_string($fullname);
		$this->conn->real_escape_string($shortname);
		$this->conn->real_escape_string($gender);
		$this->conn->real_escape_string($birthday);
		$this->conn->real_escape_string($faith);
		$this->conn->real_escape_string($alignment);
		$this->conn->real_escape_string($profession);
		$this->conn->real_escape_string($raceinfo);
		$this->conn->real_escape_string($looks);
		$this->conn->real_escape_string($magic1);
		$this->conn->real_escape_string($magic2);
		$this->conn->real_escape_string($personality);
		$this->conn->real_escape_string($story);
		$this->conn->real_escape_string($family);
		$this->conn->real_escape_string($habitat);
		$this->conn->real_escape_string($other);
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_profiledata SET fullname = ?, shortname = ?, age = ?, gender = ?, birthday = ?, faith = ?, alignment = ?, profession = ?, 
		fk_race_ID = ?, raceinfo = ?, height = ?, weight = ?, looks = ?, magic1 = ?, magic2 = ?, magic1_skill = ?, magic2_skill = ?, personality = ?, story = ?, family = ?, habitat = ?, other = ?, 
		skill_strength = ?, skill_weapons = ?, skill_flexiness = ?, skill_endurance = ?, skill_tactics = ?, skill_intelligence = ?, skill_creativity = ?, skill_mental = ?, skill_chakra = ? 
		WHERE fk_character_ID = ?");
		$query->bind_param('ssisssssisiisssiisssssiiiiiiiiii', $fullname, $shortname, $age, $gender, $birthday, $faith, $alignment, $profession, $race, $raceinfo, $height, $weight, 
		$looks, $magic1, $magic2, $magic1_skill, $magic2_skill, $personality, $story, $family, $habitat, $other, $str, $weap, $flx, $end, $tact, $int, $crea, $men, $cha, $id);
		$query->execute();		
		return 1;
		
	}	
	
	public function submit_for_approval($char, $profile)
	{
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_approvalrequests (fk_character_ID, fk_profiledata_ID)
		VALUES (?, ?)");
		$query->bind_param('ii', $char, $profile);
		$query->execute();		
		
		return 1;		
	}
	
	public function delete_approval_request($id)
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_approvalrequests WHERE fk_character_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		return 1;
	}
	
	public function check_if_waiting_for_approval($char)
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_approvalrequests WHERE fk_character_ID = ?");
		$query->bind_param('i', $char);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	
	public function count_approval_waitlist()
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_approvalrequests");
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}	
	
	public function get_approval_waitlist()
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_approvalrequests");
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	
	
	/* Private messages */
	
	public function get_messages_sent_to_user($id)
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_messagereceivers r INNER JOIN ".$this->prefix."_messages m
		ON r.fk_message_ID = m.message_ID WHERE fk_receiver_ID = ? ORDER BY m.datetime DESC");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	
	public function get_messages_send_by_user($id)
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_messages WHERE fk_sender_ID = ? ORDER BY datetime DESC");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	
	public function get_message($id)
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_messages WHERE message_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	
	public function count_message_receivers($id)
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_messagereceivers WHERE fk_message_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	
	public function get_message_receivers($id)
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_messagereceivers WHERE fk_message_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	
	public function try_get_message_receivers($msg, $receiver)
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_messagereceivers WHERE fk_message_ID = ? AND fk_receiver_ID = ?");
		$query->bind_param('ii', $msg, $receiver);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	
	
	public function count_unread_messages($id)
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_messagereceivers WHERE fk_receiver_ID = ? AND readstatus = 0");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}	
	
	public function create_new_message($sender, $title, $message)
	{
		$now = date("Y-m-d H:i:s");
		$this->conn->real_escape_string($title);
		$this->conn->real_escape_string($message);
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_messages (fk_sender_ID, title, text, datetime)
		VALUES (?, ?, ?, ?)");
		$query->bind_param('isss', $sender, $title, $message, $now);
		$query->execute();		
		
		return $this->conn->insert_id;	
	}
	
	public function send_new_message($id, $receiver)
	{
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_messagereceivers (fk_receiver_ID, fk_message_ID)
		VALUES (?, ?)");
		$query->bind_param('ii', $receiver, $id);
		$query->execute();		
		
		return 1;
	}
	
	public function update_read_status($message, $receiver) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_messagereceivers SET readstatus = 1 WHERE fk_message_ID = ? AND fk_receiver_ID = ?");
		$query->bind_param('si', $message, $receiver);
		$query->execute();		
		return 1;
	}		
	
	/* GROUPS */
	
	public function get_all_groups()
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_groups ORDER BY title");
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	
	public function get_group($id)
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_groups WHERE group_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}	
	
	public function get_groupranks($id)
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_groupranks WHERE fk_group_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	
	public function get_grouprank($id)
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_groupranks WHERE grouprank_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}	
	
	public function count_groupmembers($id)
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_groupmembers WHERE fk_group_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}	
	
	public function get_groupmembers($id)
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_groupmembers WHERE fk_group_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}	
	
	public function get_groupmember($id)
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_groupmembers WHERE groupmember_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}
	
	public function try_find_groupmember($group, $id)
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_groupmembers WHERE fk_group_ID = ? AND fk_character_ID = ?");
		$query->bind_param('ii', $group, $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}		
	
	public function get_groupmembers_by_rank($id)
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_groupmembers WHERE fk_rank_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();	
	}	
	
	public function update_groupmember_rank($rank, $id) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_groupmembers SET fk_rank_ID = ? WHERE groupmember_ID = ?");
		$query->bind_param('ii', $rank, $id);
		$query->execute();		
		return 1;
	}		
	
	public function update_groupmember_defaultgroup($defualt, $id) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_groupmembers SET defaultgroup = ? WHERE groupmember_ID = ?");
		$query->bind_param('ii', $defualt, $id);
		$query->execute();		
		return 1;
	}		
	
	public function delete_groupmember($id) 
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_groupmembers WHERE groupmember_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		return 1;
	}	
	
	public function	add_new_groupmember($group, $char, $rank, $default)
	{
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_groupmembers (fk_group_ID, fk_character_ID, fk_rank_ID, defaultgroup)
		VALUES (?, ?, ?, ?)");
		$query->bind_param('iiii', $group, $char, $rank, $default);
		$query->execute();	
		return $this->conn->insert_id;		
	}
	
	public function create_new_group($title, $desc, $color, $autojoin)
	{
		$this->conn->real_escape_string($title);
		$this->conn->real_escape_string($desc);
		$this->conn->real_escape_string($color);
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_groups (title, description, color, autojoin)
		VALUES (?, ?, ?, ?)");
		$query->bind_param('sssi', $title, $desc, $color, $autojoin);
		$query->execute();		
		
		return $this->conn->insert_id;	
	}
		
	public function update_group($title, $desc, $color, $rank, $autojoin, $id)
	{
		$this->conn->real_escape_string($title);
		$this->conn->real_escape_string($desc);
		$this->conn->real_escape_string($color);
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_groups SET title = ?, description = ?, color = ?,  fk_default_rank = ?, autojoin = ? WHERE group_ID = ?");
		$query->bind_param('sssiii', $title, $desc, $color, $rank, $autojoin, $id);
		$query->execute();				
		return 1;
	}	
	
	public function update_default_grouprank($rank, $group) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_groups SET fk_default_rank = ? WHERE group_ID = ?");
		$query->bind_param('ii', $rank, $group);
		$query->execute();		
		return 1;
	}	
	
	public function update_grouprank_title($title, $id) 
	{
		$this->conn->real_escape_string($title);
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_groupranks SET title = ? WHERE grouprank_ID = ?");
		$query->bind_param('si', $title, $id) ;
		$query->execute();		
		return 1;
	}		
	
	public function create_new_grouprank($title, $group)
	{
		$this->conn->real_escape_string($title);
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_groupranks (title, fk_group_ID)
		VALUES (?, ?)");
		$query->bind_param('si', $title, $group);
		$query->execute();		
		
		return $this->conn->insert_id;	
	}
	
	public function delete_grouprank($id)
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_groupranks WHERE grouprank_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		return 1;
	}
	
	public function delete_all_groupmembers($id)
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_groupmembers WHERE fk_group_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		return 1;
	}
	
	public function delete_all_groupranks($id)
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_groupranks WHERE fk_group_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		return 1;
	}
	
	public function delete_group($id)
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_groups WHERE group_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		return 1;
	}
	
	public function check_users_default_group($id)
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_groupmembers WHERE fk_character_ID = ? AND defaultgroup = 1");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function get_users_default_group($id)
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_groupmembers WHERE fk_character_ID = ? AND defaultgroup = 1");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	/* Logging */
	
	public function insert_modlog($data, $id, $adminonly)
	{
		$now = date("Y-m-d H:i:s");
		$this->conn->real_escape_string($data);
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_modlog (logdata, fk_superuser_ID, datetime, adminonly)
		VALUES (?, ?, ?, ?)");
		$query->bind_param('sisi', $data, $id, $now, $adminonly);
		$query->execute();		
		
		return $this->conn->insert_id;	
	}
	
	public function get_modlog()
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_modlog ORDER BY datetime DESC LIMIT 20");
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function get_modlog_modonly()
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_modlog WHERE adminonly = 0 ORDER BY datetime DESC LIMIT 20");
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	/* STATISTICS */
	
	public function get_gender_statistics($gender) 
	{
		$query = $this->conn->prepare("SELECT COUNT(DISTINCT p.fk_character_ID) AS res FROM ".$this->prefix."_profiledata p 
		INNER JOIN ".$this->prefix."_usercharacters c 
		ON p.fk_character_ID = c.character_ID
		WHERE p.gender = ? AND c.accepted = 1");
		$query->bind_param('s', $gender);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function get_active_gender_statistics($gender) 
	{
		$query = $this->conn->prepare("SELECT COUNT(DISTINCT p.fk_character_ID) AS res FROM ".$this->prefix."_profiledata p 
		INNER JOIN ".$this->prefix."_usercharacters c 
		ON p.fk_character_ID = c.character_ID
		WHERE p.gender = ? AND c.accepted = 1 AND c.active = 1");
		$query->bind_param('s', $gender);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}	
	
	public function get_user_with_most_characters() 
	{
		$query = $this->conn->prepare("SELECT COUNT(c.character_ID) AS numberOfChars, s.superuser_ID, s.name 
		FROM ".$this->prefix."_usercharacters c 
		INNER JOIN ".$this->prefix."_superusers s 
		ON c.fk_superuser_ID = s.superuser_ID
		WHERE c.accepted = 1 AND c.dead = 0
		GROUP BY c.fk_superuser_ID
		ORDER BY numberOfChars DESC LIMIT 1");
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}		
	
	public function get_topposter_char()
	{
		$query = $this->conn->prepare("SELECT c.character_ID, c.name, c.color, COUNT(p.post_ID) AS NumberOfPosts FROM ".$this->prefix."_posts p
		INNER JOIN ".$this->prefix."_usercharacters c
		ON p.fk_character_ID=c.character_ID
		WHERE p.ingame = 1 AND p.official = 1
		GROUP BY c.character_ID
		ORDER BY NumberOfPosts DESC
		LIMIT 1"
		);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function get_most_played_race() 
	{
		$query = $this->conn->prepare("SELECT COUNT(DISTINCT p.fk_character_ID) AS numberOfChars, r.name FROM ".$this->prefix."_profiledata p 
		INNER JOIN ".$this->prefix."_usercharacters c
		ON p.fk_character_ID=c.character_ID
		INNER JOIN ".$this->prefix."_races r
		ON p.fk_race_ID = r.race_ID
		WHERE c.accepted = 1
		GROUP BY r.race_ID
		ORDER BY numberOfChars DESC LIMIT 1");
		$query->bind_param('s', $gender);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function get_most_played_alignment() 
	{
		$query = $this->conn->prepare("SELECT COUNT(DISTINCT p.fk_character_ID) AS numberOfChars, p.alignment FROM ".$this->prefix."_profiledata p 
		INNER JOIN ".$this->prefix."_usercharacters c
		ON p.fk_character_ID=c.character_ID
		WHERE c.accepted = 1
		GROUP BY p.alignment
		ORDER BY numberOfChars DESC LIMIT 1");
		$query->bind_param('s', $gender);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function get_longest_post() 
	{
		$query = $this->conn->prepare("SELECT LENGTH(p.text) AS postlength, t.title, c.fk_superuser_ID, t.topic_ID, p.post_ID FROM ".$this->prefix."_posts p 
		INNER JOIN ".$this->prefix."_usercharacters c 
		ON p.fk_character_ID = c.character_ID
		INNER JOIN ".$this->prefix."_topics t
		ON p.fk_topic_ID = t.topic_ID 
		WHERE p.ingame = 1 AND p.official = 1
		ORDER BY postlength DESC LIMIT 1");
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function count_posts_by_month($month, $year)
	{
		$query = $this->conn->prepare("SELECT COUNT(post_ID) AS NumberOfPosts FROM ".$this->prefix."_posts
		WHERE ingame = 1 AND official = 1
		AND MONTH(datetime) = ? AND YEAR(datetime) = ?"
		);
		$query->bind_param('ii', $month, $year);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function get_number_of_characters_by_race($race) 
	{
		$query = $this->conn->prepare("SELECT COUNT(DISTINCT p.fk_character_ID) AS numberOfChars, r.name FROM ".$this->prefix."_profiledata p 
		INNER JOIN ".$this->prefix."_usercharacters c
		ON p.fk_character_ID=c.character_ID
		INNER JOIN ".$this->prefix."_races r
		ON p.fk_race_ID = r.race_ID
		WHERE c.accepted = 1 AND r.race_ID = ?");
		$query->bind_param('i', $race);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function get_number_of_characters_by_alignment($alignment) 
	{
		$query = $this->conn->prepare("SELECT COUNT(DISTINCT p.fk_character_ID) AS numberOfChars FROM ".$this->prefix."_profiledata p 
		INNER JOIN ".$this->prefix."_usercharacters c
		ON p.fk_character_ID=c.character_ID
		WHERE c.accepted = 1 AND p.alignment = ?");
		$query->bind_param('s', $alignment);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	public function count_posts_by_day($month, $year, $day)
	{
		$query = $this->conn->prepare("SELECT COUNT(post_ID) AS NumberOfPosts FROM ".$this->prefix."_posts
		WHERE ingame = 1 AND official = 1
		AND MONTH(datetime) = ? AND YEAR(datetime) = ? AND DAY(datetime) = ?"
		);
		$query->bind_param('iii', $month, $year, $day);
		$query->execute();		
		/* Get the result */
		return $query->get_result();		
	}
	
	
	/* ACHIEVEMENTS */
	
	public function get_all_achievements()
	{
		$query = $this->conn->prepare("SELECT achievement_ID, title, type FROM ".$this->prefix."_achievements ORDER BY title");
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function get_achievement($id)
	{
		$query = $this->conn->prepare("SELECT achievement_ID, title, type, description, fk_forum_ID, fk_topic_ID, img 
		FROM ".$this->prefix."_achievements WHERE achievement_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		/* Get the result */
		return $query->get_result();
	}
	
	public function insert_new_achievement($title, $desc, $type, $forumid, $topicid, $img)
	{
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_achievements (title, description, type, fk_forum_ID, fk_topic_ID, img)
		VALUES (?, ?, ?, ?, ?, ?)");
		$query->bind_param('ssiiis', $title, $desc, $type, $forumid, $topicid, $img);
		$query->execute();		
		
		return $this->conn->insert_id;	
	}
	
	public function edit_achievement($id, $title, $desc, $type, $forumid, $topicid, $img)
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_achievements SET title = ?, description = ?, type = ?, fk_forum_ID = ?, fk_topic_ID = ?, img = ?
		where achievement_ID = ?");
		$query->bind_param('ssiiisi', $title, $desc, $type, $forumid, $topicid, $img, $id);
		$query->execute();		
		
		return 1;
	}
	
	public function add_achievement_to_user($user, $char, $achievement)
	{
		$now = date("Y-m-d H:i:s");
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_userachievements (fk_superuser_ID, fk_character_ID, fk_achievement_ID, datetime)
		VALUES (?, ?, ?, ?)");
		$query->bind_param('iiis', $user, $char, $achievement, $now);
		$query->execute();		
		
		return $this->conn->insert_id;	
	}
	
	public function check_if_user_has_achievement($user, $char, $achievement)
	{
		$query = $this->conn->prepare("SELECT COUNT(userachievement_ID) AS res FROM ".$this->prefix."_userachievements 
		WHERE fk_superuser_ID = ? AND fk_character_ID = ? AND fk_achievement_ID = ?");
		$query->bind_param('iii', $user, $char, $achievement);
		$query->execute();		
		
		return $query->get_result();	
	}
	
	public function count_userachievements_specific_type($user, $type)
	{
		$query = $this->conn->prepare("SELECT COUNT(u.userachievement_ID) AS res FROM ".$this->prefix."_userachievements u
		INNER JOIN  ".$this->prefix."_achievements a
		ON u.fk_achievement_ID = a.achievement_ID
		WHERE u.fk_superuser_ID = ? AND a.type = ?");
		$query->bind_param('ii', $user, $type);
		$query->execute();		
		
		return $query->get_result();	
	}
	
	public function get_userachievements_specific_type($user, $type)
	{
		$query = $this->conn->prepare("SELECT a.achievement_ID,  a.title, a.description, a.img FROM ".$this->prefix."_userachievements u
		INNER JOIN ".$this->prefix."_achievements a
		ON u.fk_achievement_ID = a.achievement_ID
		WHERE u.fk_superuser_ID = ? AND a.type = ?
		GROUP BY a.achievement_ID
		ORDER BY u.datetime DESC");
		$query->bind_param('ii', $user, $type);
		$query->execute();		
		
		return $query->get_result();	
	}
	
	public function count_userachievements_from_achievement($user, $achievement)
	{
		$query = $this->conn->prepare("SELECT COUNT(fk_character_ID) AS res FROM ".$this->prefix."_userachievements
		WHERE fk_superuser_ID = ? AND fk_achievement_ID = ?");
		$query->bind_param('ii', $user, $achievement);
		$query->execute();		
		
		return $query->get_result();	
	}
	
	public function get_userachievements_from_achievement($user, $achievement)
	{
		$query = $this->conn->prepare("SELECT c.character_ID, c.name FROM ".$this->prefix."_userachievements u
		INNER JOIN ".$this->prefix."_usercharacters c 
		ON u.fk_character_ID = c.character_ID
		WHERE u.fk_superuser_ID = ? AND u.fk_achievement_ID = ?
		ORDER BY datetime");
		$query->bind_param('ii', $user, $achievement);
		$query->execute();		
		
		return $query->get_result();	
	}
	
	public function count_all_userachievements_from_user($user)
	{
		$query = $this->conn->prepare("SELECT COUNT(DISTINCT fk_achievement_ID) AS res FROM ".$this->prefix."_userachievements
		WHERE fk_superuser_ID = ?");
		$query->bind_param('i', $user);
		$query->execute();		
		
		return $query->get_result();	
	}
	
	public function get_all_userachievements_from_user($user)
	{
		$query = $this->conn->prepare("SELECT u.userachievement_ID, a.achievement_ID, a.title, a.type, u.fk_character_ID FROM ".$this->prefix."_userachievements u
		INNER JOIN ".$this->prefix."_achievements a
		ON u.fk_achievement_ID = a.achievement_ID
		WHERE u.fk_superuser_ID = ?
		ORDER BY u.datetime");
		$query->bind_param('i', $user);
		$query->execute();		
		
		return $query->get_result();	
	}
	
	public function delete_userachievement($id)
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_userachievements
		WHERE userachievement_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		
		return 1;	
	}
	
	public function delete_userachievements_from_character($id)
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_userachievements
		WHERE fk_character_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		
		return 1;	
	}
	
	public function get_achievements_for_specific_forum($forum)
	{
		$query = $this->conn->prepare("SELECT achievement_ID FROM ".$this->prefix."_achievements WHERE fk_forum_ID = ?");
		$query->bind_param('i', $forum);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function get_achievements_for_specific_topic($topic)
	{
		$query = $this->conn->prepare("SELECT achievement_ID FROM ".$this->prefix."_achievements WHERE fk_topic_ID = ?");
		$query->bind_param('i', $topic);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	/* Wanted list */
	
	public function get_wantedlist_size()
	{
		$query = $this->conn->prepare("SELECT COUNT(wanted_ID) AS res FROM ".$this->prefix."_wantedlist WHERE accepted = 1");
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function count_nonaccepted_wantedposts()
	{
		$query = $this->conn->prepare("SELECT COUNT(wanted_ID) AS res FROM ".$this->prefix."_wantedlist WHERE accepted = 0");
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function get_nonaccepted_wantedposts()
	{
		$query = $this->conn->prepare("SELECT wanted_ID, fk_character_ID, crime, features, whereabouts, bounty FROM ".$this->prefix."_wantedlist WHERE accepted = 0");
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function get_wantedlist()
	{
		$query = $this->conn->prepare("SELECT wanted_ID, fk_character_ID, crime, features, whereabouts, bounty FROM ".$this->prefix."_wantedlist WHERE accepted = 1");
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function get_wantedpost($id)
	{
		$query = $this->conn->prepare("SELECT fk_character_ID, crime, features, whereabouts, bounty FROM ".$this->prefix."_wantedlist WHERE wanted_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function get_wantedpost_by_character($id)
	{
		$query = $this->conn->prepare("SELECT wanted_ID FROM ".$this->prefix."_wantedlist WHERE fk_character_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function check_bounty($id)
	{
		$query = $this->conn->prepare("SELECT COUNT(wanted_ID) AS res FROM ".$this->prefix."_wantedlist WHERE fk_character_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function add_new_bounty($id, $crime, $features, $whereabouts, $bounty)
	{
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_wantedlist (fk_character_ID, crime, features, whereabouts, bounty, accepted)
		VALUES (?, ?, ?, ?, ?, 0)");
		$query->bind_param('issss', $id, $crime, $features, $whereabouts, $bounty);
		$query->execute();		
		/* Get the result */
		return $this->conn->insert_id;	
	}
	
	public function edit_bounty($id, $crime, $features, $whereabouts, $bounty)
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_wantedlist SET crime = ?, features = ?, whereabouts = ?, bounty = ?, accepted = 1
		WHERE wanted_ID = ?");
		$query->bind_param('ssssi', $crime, $features, $whereabouts, $bounty, $id);
		$query->execute();		
		/* Get the result */
		return $this->conn->insert_id;	
	}
	
	public function remove_bounty($id) 
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_wantedlist WHERE wanted_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		return 1;
	}
	
	public function remove_bounty_from_character($id) 
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_wantedlist WHERE fk_character_ID = ?");
		$query->bind_param('i', $id);
		$query->execute();	
		return 1;
	}
	
	/* CHAT */
	
	public function count_chat_messages()
	{
		$query = $this->conn->prepare("SELECT COUNT(chat_ID) AS res FROM ".$this->prefix."_chat");
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function get_time_of_last_chat_message()
	{
		$query = $this->conn->prepare("SELECT datetime FROM ".$this->prefix."_chat ORDER BY datetime DESC LIMIT 1");
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function get_time_of_last_ic_chat_message()
	{
		$query = $this->conn->prepare("SELECT datetime FROM ".$this->prefix."_icchat ORDER BY datetime DESC LIMIT 1");
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function get_chat_messages()
	{
		$query = $this->conn->prepare("SELECT * FROM (SELECT * FROM ".$this->prefix."_chat ORDER BY datetime DESC LIMIT 20) AS chatdata ORDER BY datetime ASC");
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function get_ic_chat_messages()
	{
		$query = $this->conn->prepare("SELECT * FROM (SELECT * FROM ".$this->prefix."_icchat ORDER BY datetime DESC LIMIT 20) AS chatdata ORDER BY datetime ASC");
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function get_older_chat_messages($offset, $limit)
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_chat ORDER BY datetime DESC LIMIT ?, ?");
		$query->bind_param('ii', $offset, $limit);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function count_older_chat_messages($datetime)
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_chat WHERE datetime < ?");
		$query->bind_param('s', $datetime);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	
	public function get_older_ic_chat_messages($offset, $limit)
	{
		$query = $this->conn->prepare("SELECT * FROM ".$this->prefix."_icchat ORDER BY datetime DESC LIMIT ?, ?");
		$query->bind_param('ii', $offset, $limit);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function count_older_ic_chat_messages($datetime)
	{
		$query = $this->conn->prepare("SELECT COUNT(*) AS res FROM ".$this->prefix."_icchat WHERE datetime < ?");
		$query->bind_param('s', $datetime);
		$query->execute();		
		/* Get the result */
		return $query->get_result();
	}
	
	public function insert_chat_message($username, $title, $avatar, $message, $link, $color)
	{
		$now = date("Y-m-d H:i:s");
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_chat (username, title, avatar, message, datetime, link, color)
		VALUES (?, ?, ?, ?, ?, ?, ?)");
		$query->bind_param('sssssss', $username, $title, $avatar, $message, $now, $link, $color);
		$query->execute();		
		
		return $this->conn->insert_id;	
	}
	
	public function insert_ic_chat_message($userid, $message)
	{
		$now = date("Y-m-d H:i:s");
		$query = $this->conn->prepare("INSERT INTO ".$this->prefix."_icchat (fk_character_ID, message, datetime)
		VALUES (?, ?, ?)");
		$query->bind_param('iss', $userid, $message, $now);
		$query->execute();		
		
		return $this->conn->insert_id;	
	}
	
	public function delete_ic_chat_messages_from_char($char)
	{
		$query = $this->conn->prepare("DELETE FROM ".$this->prefix."_icchat WHERE fk_character_ID = ?");
		$query->bind_param('i', $char);
		$query->execute();		
		return 1;
	}
	
	public function update_superuser_chatimg($img, $id) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_superusers SET chatavatar = ? WHERE superuser_ID = ?");
		$query->bind_param('si', $img, $id);
		$query->execute();		
		return 1;
	}
	
	public function update_superuser_chattitle($title, $id) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_superusers SET chattitle = ? WHERE superuser_ID = ?");
		$query->bind_param('si', $title, $id);
		$query->execute();		
		return 1;
	}
	
	public function update_superuser_chatlink($link, $id) 
	{
		$query = $this->conn->prepare("UPDATE ".$this->prefix."_superusers SET chatlink = ? WHERE superuser_ID = ?");
		$query->bind_param('si', $link, $id);
		$query->execute();		
		return 1;
	}
	
}
?>