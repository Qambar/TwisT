<?php
/* Class for Buddies 

	List of functions
	* addNewBuddy($sourceID, $destinationID)
	* AcceptFriendRequest($sourceID, $userId)
	* listPendingBuddiesRequest($userID)
	* removeBuddy($buddyID)
	* listBuddies($userID)
	* isOnline($userid)
	* listOnlineBuddies($userID)
	* getProfileData($BuddyId,$Field)
	* updateFrom($userID1,$userID2,$content,$timeStamp)
	* addNewUpdate($userId,$update,$timestamp)
*/

class Buddies 
{
	
	function addNewBuddy($sourceID, $destinationID)
	{
		//Code for adding new buddy to the system
		$timestamp = date("Y-m-d H:i:s", $_SERVER['REQUEST_TIME']);
		$query = "INSERT INTO buddies (sourceid, destinationid, datecreated) VALUES ('$sourceID', '$destinationID', '$timestamp')";
		$result = mysql_query($query) or die();
	}

		
	function AcceptFriendRequest($sourceID, $userId)
	{
		//Code for accepting a buddy request to the system
		$timestamp = date("Y-m-d H:i:s", $_SERVER['REQUEST_TIME']);
		$query = "SELECT * FROM users WHERE userid = '$userId'";
		$result = mysql_query($query) or die();
		while($row = mysql_fetch_array($result))
		{
			$userName1 = $row['firstname'].' '.$row['lastname'];
		}
		$query = "SELECT * FROM users WHERE userid = '$sourceID'";
		$result = mysql_query($query) or die();
		while($row = mysql_fetch_array($result))
		{
			$userName2 = $row['firstname'].' '.$row['lastname'];
		}
		$this->updateFrom($userId,$sourceID,'<a href=user.php?id='.$userId.' >'.$userName1.'</a> is now Friend with <a href="user.php?id='.$sourceID.'">'.$userName2.'</a>',$timestamp);
		$timestamp = date("Y-m-d H:i:s", $_SERVER['REQUEST_TIME']);
		$query = "UPDATE buddies SET accepted = '1',datecreated = '$timestamp' WHERE sourceid = $sourceID AND destinationid = '$userId'";
		mysql_query($query) or die();
		$query = "UPDATE suggestions SET connected = '1' WHERE ((sourceid = $sourceID AND destinationid = '$userId') OR (sourceid = $userId AND destinationid = '$sourceID'))";
		mysql_query($query) or die();

	}

	function listPendingBuddiesRequest($userID)
	{
		//Display the list of all Buddies that have send friend request to given userID.
		$query = "SELECT * from buddies WHERE destinationid = '$userID' AND accepted = '0'";
		$result = mysql_query($query);
		$i=0;
		while($row = mysql_fetch_array($result))
		{   
			echo "<br/>";			
			$b[$i] = $row['sourceid'];
			$i++;
		}
		return $b;
	}

	function removeBuddy($sourceID, $destinationID)
	{
		//Code for removing an existing buddy from the system
		//Buddy must EXIST
		$query = "DELETE FROM buddies WHERE (sourceid='$sourceID' AND destinationid='$destinationID') OR (sourceid='$destinationID' AND destinationid='$sourceID') AND accepted=1";
		mysql_query($query) or die();
	}
	
	function listBuddies($userID)
	{
		//Display the list of all Buddies for given userID.
		$query = "SELECT * from buddies WHERE (sourceid = '$userID' OR destinationid = '$userID') AND accepted = 1";
		$result = mysql_query($query);
		$i=0;
		while($row = mysql_fetch_array($result))
		{
			if($row['sourceid'] == $userID)
			{
				$myBuddies[$i] = $row['destinationid'];
				$i = $i + 1;
			}
			else
			{
				$myBuddies[$i] = $row['sourceid'];
				$i = $i + 1;
			}
		}
		return $myBuddies; 
	}

	//Function to check if user is buddy or not, returns true if buddy
	function isBuddy($srcid,$destid) 
	{
		
		$query = "SELECT * from buddies WHERE ((sourceid = '$srcid' AND destinationid = '$destid') OR (sourceid = '$destid' AND destinationid = '$srcid'))";
		$result = mysql_query($query);
		
		if(mysql_num_rows($result))
			return true;	
		else
			return false;
	}
	
	function isBuddyAccepted($srcid,$destid) 
	{
		
		$query = "SELECT * from buddies WHERE ((sourceid = '$srcid' AND destinationid = '$destid') OR (sourceid = '$destid' AND destinationid = '$srcid')) AND accepted = 1";
		$result = mysql_query($query);
		
		if(mysql_num_rows($result))
			return true;	
		else
			return false;
	}

	//Function to check if user is online, returns true if online
	function isOnline($userid) 
	{
		$query = "SELECT * from  users WHERE userid='$userid' AND online='1' ";
		$result = mysql_query($query);
		
		if(mysql_num_rows($result))
			return true;	
		else
			return false;
	}
	
	//Display the list of all Buddies for an User.
	function listOnlineBuddies($userID)
	{
		$query = "SELECT * FROM buddies WHERE ((sourceid = '$userID' OR destinationid = '$userID') AND accepted = '1')";
		$result = mysql_query($query);
		$i=0;
		while($row = mysql_fetch_array($result))
		{
			if($row['sourceid'] == $userID)
			{
				if($this->isOnline($row['destinationid']))
				{
				$OnlineBuddies[$i] = $row['destinationid'];
				$i = $i + 1;
				}
			}
			else
			{
				if($this->isOnline($row['sourceid']))
				{
				$OnlineBuddies[$i] = $row['sourceid'];
				$i = $i + 1;
				}
			}
		}
		return $OnlineBuddies;
	}

	function getProfileData($BuddyId,$Field)
	{
		$query = "SELECT '$Field' FROM profile WHERE userid = '$BuddyId'";
		$result = mysql_query($query);
		while($row = mysql_fetch_array($result))
			{
				$r = $row['$Field'];
			}
			return $r;
	}

	//Function to display updates to all buddies of user ID 1 and UserID 2.

	function updateFrom($userID1,$userID2,$content,$timeStamp)
	{
		$query1 = "SELECT * from buddies WHERE (sourceid = '$userID1' OR destinationid = '$userID1') AND accepted = 1";
		$query2 = "SELECT * from buddies WHERE (sourceid = '$userID2' OR destinationid = '$userID2') AND accepted = 1";
		$result1 = mysql_query($query1);
		$result2 = mysql_query($query2);
		$i=0;
		$count = 0;

		//fetches Buddies for userID1
		while($row1 = mysql_fetch_array($result1))
		{
			if($row1['sourceid'] == $userID1)
			{
				$buddies[$i] = $row1['destinationid'];      
				$i = $i + 1;
			}
			else
			{
				$buddies[$i] = $row1['sourceid'];
				$i = $i + 1;
			}
		}
		
		//fetches Buddies for userID2
		while($row2 = mysql_fetch_array($result2))
		{
			
			if($row2['sourceid'] == $userID2)
			{
				$buddies[$i] = $row1['destinationid'];      
				$i = $i + 1;
			}
			else
			{
				$buddies[$i] = $row1['sourceid'];
				$i = $i + 1;
			}
			}
		}
	
		for($i=0;$i<count($buddies);$i++)				//Displays the updates from database to the buddies
		{
			$this->addNewUpdate($buddies[$i],$content,$timeStamp);
		}
	}

	//Function to Display Updates
	function addNewUpdate($userId,$update,$timestamp)
	{
		// shifts updates
		$query = "SELECT * FROM  updates WHERE userid = $userId";
		$result = mysql_query($query) or die();
		while($row = mysql_fetch_array($result))
		{
				$j[1]=$row['update1'];
				$j[2]=$row['update2'];
				$j[3]=$row['update3'];
				$j[4]=$row['update4'];
				$j[5]=$row['update5'];
				$j[6]=$row['update6'];
				$j[7]=$row['update7'];
				$j[8]=$row['update8'];
				$j[9]=$row['update9'];
				$j[10]=$row['update10'];
				$j[11]=$row['update11'];
				$j[12]=$row['update12'];
				$k[1]=$row['time1'];
				$k[2]=$row['time2'];
				$k[3]=$row['time3'];
				$k[4]=$row['time4'];
				$k[5]=$row['time5'];
				$k[6]=$row['time6'];
				$k[7]=$row['time7'];
				$k[8]=$row['time8'];
				$k[9]=$row['time9'];
				$k[10]=$row['time10'];
				$k[11]=$row['time11'];
				$k[12]=$row['time12'];
		}
		$query = "UPDATE updates SET update1 = '$update', time1 = '$timestamp', update2 = '$j[1]', time2 = '$k[1]', update3 = '$j[2]', time3 = '$k[2]', update4 = '$j[3]', time4 = '$k[3]', update5 = '$j[4]', time5 = '$k[4]', update6 = '$j[5]', time6 = '$k[5]', update7 = '$j[6]', time7 = '$k[6]', update8 = '$j[7]', time8 = '$k[7]', update9 = '$j[8]', time9 = '$k[8]', update10 = '$j[9]', time10 = '$k[9]', update11 = '$j[10]', time11 = '$k[10]', update12 = '$j[11]', time12 = '$k[11]' WHERE userid = '$userId'";
		mysql_query($query) or die(); 
		
	}
}
?>