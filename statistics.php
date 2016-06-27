<?php
include('header.php');
?>

<script src="functions/charts/Chart.js"></script>

<?php
echo "<div id='forumcontainer'>";
echo "<div id='forumlist'>";

echo "<div class='category'><a href=''>Statistik</a></div>";	

echo "<div class='statisticcontainer'>";
echo "<div class='statistic'>";
echo "<h5>Kønsfordeling (karakterer)</h5>";
echo "<p>Mænd: "; $numberofmales = $forum->get_gender_statistics("Mand")->fetch_assoc(); echo $numberofmales['res']." ";
$numberofactivemales = $forum->get_active_gender_statistics("Mand")->fetch_assoc(); echo "(".$numberofactivemales['res']." aktive)</p>";
echo "<p>Kvinder: "; $numberoffemales = $forum->get_gender_statistics("Kvinde")->fetch_assoc(); echo $numberoffemales['res']." ";
$numberofactivefemales = $forum->get_active_gender_statistics("Kvinde")->fetch_assoc(); echo "(".$numberofactivefemales['res']." aktive)</p>";
echo "<p>Intetkøn: "; $numberofnogenders = $forum->get_gender_statistics("Intetkøn")->fetch_assoc(); echo $numberofnogenders['res']." ";
$numberofactivenogenders = $forum->get_active_gender_statistics("Intetkøn")->fetch_assoc(); echo "(".$numberofactivenogenders['res']." aktive)</p>";
echo "</div>";

echo "<div class='statistic'>";
echo "<h5>Karakterer</h5>";
echo "<p>Bruger med flest karakterer: "; $userwithmostchars = $forum->get_user_with_most_characters()->fetch_assoc(); 
echo "<a class='username' href='memberprofile.php?id=".$userwithmostchars['superuser_ID']."'>".$userwithmostchars['name']."</a> (".$userwithmostchars['numberOfChars'].")</p>";
echo "<p>Mest spillede karakter: "; $mostplayedchar = $forum->get_topposter_char()->fetch_assoc();
echo "<a class='username' style='color:".$mostplayedchar['color'].";' href='characterprofile.php?id=".$mostplayedchar['character_ID']."'>".$mostplayedchar['name']."</a> 
(".$mostplayedchar['NumberOfPosts']." posts)</p>";
echo "<p>Mest spillede race: "; $mostplayedrace = $forum->get_most_played_race()->fetch_assoc(); echo $mostplayedrace['name']." (".$mostplayedrace['numberOfChars'].")</p>";
echo "<p>Almindeligeste tilhørsforhold: "; $mostplayedalignment = $forum->get_most_played_alignment()->fetch_assoc(); 
echo $mostplayedalignment['alignment']." (".$mostplayedalignment['numberOfChars'].")</p>";
echo "</div>";

echo "<div class='statistic'>";
echo "<h5>Posting</h5>";
echo "<p>Længste post: "; $longestpost = $forum->get_longest_post()->fetch_assoc(); 

$numberofprevposts = $forum->count_prev_posts($longestpost['topic_ID'], $longestpost['datetime'])->fetch_assoc();
$numberofprevposts = $numberofprevposts['res'] + 1;
$topicpagenumber = ceil($numberofprevposts / $postsperpage);

$superuser = $forum->get_superuser($longestpost['fk_superuser_ID'])->fetch_assoc();
echo "af <a class='username' href='memberprofile.php?id=".$superuser['superuser_ID']."'>".$superuser['name']."</a> ";
echo "i <a href='viewtopic.php?t=".$longestpost['topic_ID']."&currentpage=".$topicpagenumber."#".$longestpost['post_ID']."'>".$longestpost['title']."</a> 
(".$longestpost['postlength']." tegn)</p>";
echo "<p>Ingame posts (Denne måned): "; $poststhismonth = $forum->count_posts_by_month(date('m'),date('Y'))->fetch_assoc(); echo $poststhismonth['NumberOfPosts']."</p>"; 
echo "<p>Ingame posts (I alt): "; $ingameposts = $forum->count_all_ingame_posts()->fetch_assoc(); echo $ingameposts['res']."</p>";
echo "</div>";


echo "<div class='statistic statchart'>";
echo "<h5>Racefordeling</h5>";
echo "<div class='chartholder'>";
echo "<canvas id='racechart' width='400' height='400'/>";
echo "</div>";
echo "</div>";
?>
<script>

var pieData1 = [
				{
					<?php $racenumber1 = $forum->get_number_of_characters_by_race(1)->fetch_assoc(); ?>
					value: <?php echo $racenumber1['numberOfChars'] ?>,
					color:"#b57b99",
					highlight: "#cc92b0",
					label: "<?php echo $racenumber1['name'] ?>"
				},
				{
					<?php $racenumber6 = $forum->get_number_of_characters_by_race(6)->fetch_assoc(); ?>
					value: <?php echo $racenumber6['numberOfChars'] ?>,
					color: "#70a7c7",
					highlight: "#90c1dd",
					label: "<?php echo $racenumber6['name'] ?>"
				},
				{
					<?php $racenumber24 = $forum->get_number_of_characters_by_race(24)->fetch_assoc(); ?>
					value: <?php echo $racenumber24['numberOfChars'] ?>,
					color: "#8a7b68",
					highlight: "#a79784",
					label: "<?php echo $racenumber24['name'] ?>"
				},
				{
					<?php $racenumber2 = $forum->get_number_of_characters_by_race(2)->fetch_assoc(); ?>
					value: <?php echo $racenumber2['numberOfChars'] ?>,
					color: "#4d6677",
					highlight: "#668092",
					label: "<?php echo $racenumber2['name'] ?>"
				},
				{
					<?php $racenumber3 = $forum->get_number_of_characters_by_race(3)->fetch_assoc(); ?>
					value: <?php echo $racenumber3['numberOfChars'] ?>,
					color: "#6a2525",
					highlight: "#7c3333",
					label: "<?php echo $racenumber3['name'] ?>"
				},
				{
					<?php $racenumber7 = $forum->get_number_of_characters_by_race(7)->fetch_assoc(); ?>
					value: <?php echo $racenumber7['numberOfChars'] ?>,
					color: "#f3f2e9",
					highlight: "#ffffff",
					label: "<?php echo $racenumber7['name'] ?>"
				},
				{
					<?php $racenumber21 = $forum->get_number_of_characters_by_race(21)->fetch_assoc(); ?>
					value: <?php echo $racenumber21['numberOfChars'] ?>,
					color: "#748b9b",
					highlight: "#85a0b2",
					label: "<?php echo $racenumber21['name'] ?>"
				},
				{
					<?php $racenumber8 = $forum->get_number_of_characters_by_race(8)->fetch_assoc(); ?>
					value: <?php echo $racenumber8['numberOfChars'] ?>,
					color: "#528771",
					highlight: "#689e88",
					label: "<?php echo $racenumber8['name'] ?>"
				},
				{
					<?php $racenumber23 = $forum->get_number_of_characters_by_race(23)->fetch_assoc(); ?>
					value:  <?php echo $racenumber23['numberOfChars'] ?>,
					color: "#724848",
					highlight: "#825b5b",
					label:  "<?php echo $racenumber23['name'] ?>"
				},
				{
					<?php $racenumber20 = $forum->get_number_of_characters_by_race(20)->fetch_assoc(); ?>
					value: <?php echo $racenumber20['numberOfChars'] ?>,
					color: "#94bd7e",
					highlight: "#aacf96",
					label:  "<?php echo $racenumber20['name'] ?>"
				},
				{
					<?php $racenumber22 = $forum->get_number_of_characters_by_race(22)->fetch_assoc(); ?>
					value:  <?php echo $racenumber22['numberOfChars'] ?>,
					color: "#575d3d",
					highlight: "#696e55",
					label:  "<?php echo $racenumber22['name'] ?>"
				},
				{
					<?php $racenumber9 = $forum->get_number_of_characters_by_race(9)->fetch_assoc(); ?>
					value: <?php echo $racenumber9['numberOfChars'] ?>,
					color: "#3860aa",
					highlight: "#5577b6",
					label: "<?php echo $racenumber9['name'] ?>"
				},
				{
					<?php $racenumber10 = $forum->get_number_of_characters_by_race(10)->fetch_assoc(); ?>
					value: <?php echo $racenumber10['numberOfChars'] ?>,
					color: "#1e7d4d",
					highlight: "#359162",
					label: "<?php echo $racenumber10['name'] ?>"
				},
				{
					<?php $racenumber11 = $forum->get_number_of_characters_by_race(11)->fetch_assoc(); ?>
					value: <?php echo $racenumber11['numberOfChars'] ?>,
					color: "#d1ba97",
					highlight: "#e5d5bd",
					label: "<?php echo $racenumber11['name'] ?>"
				},
				{
					<?php $racenumber13 = $forum->get_number_of_characters_by_race(13)->fetch_assoc(); ?>
					value: <?php echo $racenumber13['numberOfChars'] ?>,
					color: "#7f601e",
					highlight: "#8d6e2c",
					label: "<?php echo $racenumber13['name'] ?>"
				},
				{
					<?php $racenumber5 = $forum->get_number_of_characters_by_race(5)->fetch_assoc(); ?>
					value: <?php echo $racenumber5['numberOfChars'] ?>,
					color: "#47316b",
					highlight: "#5a457d",
					label: "<?php echo $racenumber5['name'] ?>"
				},
				{
					<?php $racenumber12 = $forum->get_number_of_characters_by_race(12)->fetch_assoc(); ?>
					value: <?php echo $racenumber12['numberOfChars'] ?>,
					color: "#1c5175",
					highlight: "#376b8f",
					label: "<?php echo $racenumber12['name'] ?>"
				},
				{
					<?php $racenumber14 = $forum->get_number_of_characters_by_race(14)->fetch_assoc(); ?>
					value: <?php echo $racenumber14['numberOfChars'] ?>,
					color: "#5b792c",
					highlight: "#728f43",
					label: "<?php echo $racenumber14['name'] ?>"
				},
				{
					<?php $racenumber17 = $forum->get_number_of_characters_by_race(17)->fetch_assoc(); ?>
					value: <?php echo $racenumber17['numberOfChars'] ?>,
					color: "#65a990",
					highlight: "#80bea7",
					label: "<?php echo $racenumber17['name'] ?>"
				},
				{
					<?php $racenumber4 = $forum->get_number_of_characters_by_race(4)->fetch_assoc(); ?>
					value: <?php echo $racenumber4['numberOfChars'] ?>,
					color: "#71a256",
					highlight: "#82b467",
					label: "<?php echo $racenumber4['name'] ?>"
				},
				{
					<?php $racenumber25 = $forum->get_number_of_characters_by_race(25)->fetch_assoc(); ?>
					value: <?php echo $racenumber25['numberOfChars'] ?>,
					color: "#876fa1",
					highlight: "#9c8ab0",
					label: "<?php echo $racenumber25['name'] ?>"
				},
				{
					<?php $racenumber15 = $forum->get_number_of_characters_by_race(15)->fetch_assoc(); ?>
					value: <?php echo $racenumber15['numberOfChars'] ?>,
					color: "#be6636",
					highlight: "#c7774c",
					label: "<?php echo $racenumber15['name'] ?>"
				},
				{
					<?php $racenumber16 = $forum->get_number_of_characters_by_race(16)->fetch_assoc(); ?>
					value: <?php echo $racenumber16['numberOfChars'] ?>,
					color: "#7d683e",
					highlight: "#927b50",
					label: "<?php echo $racenumber16['name'] ?>"
				},
				{
					<?php $racenumber18 = $forum->get_number_of_characters_by_race(18)->fetch_assoc(); ?>
					value: <?php echo $racenumber18['numberOfChars'] ?>,
					color: "#961a1a",
					highlight: "#a72f2f",
					label: "<?php echo $racenumber18['name'] ?>"
				},
				{
					<?php $racenumber19 = $forum->get_number_of_characters_by_race(19)->fetch_assoc(); ?>
					value: <?php echo $racenumber19['numberOfChars'] ?>,
					color: "#67422c",
					highlight: "#75503a",
					label: "<?php echo $racenumber19['name'] ?>"
				},
			];

	</script>


<?php
echo "<div class='statistic statchart'>";
echo "<h5>Fordeling af tilhørsforhold</h5>";
echo "<div class='chartholder'>";
echo "<canvas id='alignmentchart' width='350' height='350'/>";
echo "</div>";
echo "</div>";
?>
<script>

var pieData2 = [
				{
					<?php $alignment1 = $forum->get_number_of_characters_by_alignment('Retmæssig God')->fetch_assoc(); ?>
					value: <?php echo $alignment1['numberOfChars'] ?>,
					color:"#658b68",
					highlight: "#779d7a",
					label: "Retmæssig God"
				},
				{
					<?php $alignment2 = $forum->get_number_of_characters_by_alignment('Neutral God')->fetch_assoc(); ?>
					value: <?php echo $alignment2['numberOfChars'] ?>,
					color: "#85a987",
					highlight: "#97bc9a",
					label: "Neutral God"
				},
				{
					<?php $alignment3 = $forum->get_number_of_characters_by_alignment('Kaotisk God')->fetch_assoc(); ?>
					value: <?php echo $alignment3['numberOfChars'] ?>,
					color: "#b3d2b5",
					highlight: "#cce3cd",
					label: "Kaotisk God"
				},
				{
					<?php $alignment4 = $forum->get_number_of_characters_by_alignment('Retmæssig Neutral')->fetch_assoc(); ?>
					value: <?php echo $alignment4['numberOfChars'] ?>,
					color: "#898989",
					highlight: "#a1a1a1",
					label: "Retmæssig Neutral"
				},
				{
					<?php $alignment5 = $forum->get_number_of_characters_by_alignment('Sand Neutral')->fetch_assoc(); ?>
					value: <?php echo $alignment5['numberOfChars'] ?>,
					color: "#b5b5b5",
					highlight: "#c2c2c2",
					label: "Sand Neutral"
				},
				{
					<?php $alignment6 = $forum->get_number_of_characters_by_alignment('Kaotisk Neutral')->fetch_assoc(); ?>
					value: <?php echo $alignment6['numberOfChars'] ?>,
					color: "#cdcdcd",
					highlight: "#e0e0e0",
					label: "Kaotisk Neutral"
				},
				{

					<?php $alignment7 = $forum->get_number_of_characters_by_alignment('Retmæssig Ond')->fetch_assoc(); ?>
					value: <?php echo $alignment7['numberOfChars'] ?>,
					color: "#671b1b",
					highlight: "#7d2727",
					label: "Retmæssig Ond"
				},
				{
					<?php $alignment8 = $forum->get_number_of_characters_by_alignment('Neutral Ond')->fetch_assoc(); ?>
					value: <?php echo $alignment8['numberOfChars'] ?>,
					color: "#872121",
					highlight: "#9c2b2b",
					label: "Neutral Ond"
				},
				{
					<?php $alignment9 = $forum->get_number_of_characters_by_alignment('Kaotisk Ond')->fetch_assoc(); ?>
					value: <?php echo $alignment9['numberOfChars'] ?>,
					color: "#b02020",
					highlight: "#c13939",
					label: "Kaotisk Ond"
				},
			];

	</script>

<?php
echo "<div class='statistic statchart'>";
echo "<h5>Ingame post-statistik (seneste måned)</h5>";
echo "<div class='chartholder'>";
echo "<canvas id='postchart' width='100%' height='50px'/>";
echo "</div>";
echo "</div>";

$postsperday = array();
$dailydata = $forum->count_daily_posts_in_a_month();

while($dailyposts = $dailydata->fetch_assoc())
{
	$postsperday[$dailyposts['DayName']] = $dailyposts['NumberOfPosts'];
}

$count = 30;
while($count >= 0)
{
	$date = date('Y-m-d');
	$subdate = strtotime($date.'-'.$count.' days');	
	$datekey = (string) date('Y-m-d', $subdate);
	if(!array_key_exists($datekey, $postsperday)) { $postsperday[$datekey] = 0; }	
	$count--;		
}
ksort($postsperday);
?>


<script>
	var barData = {
		<?php
		echo "labels: [";
		
		foreach($postsperday as $k => $v)
		{
			echo '"'.date("d.m", strtotime($k)).'",';		
		}
		
		echo "],";		
		?>
		datasets : [
			{
				fillColor : "rgba(151,187,205,0.5)",
				strokeColor : "rgba(151,187,205,0.8)",
				highlightFill : "rgba(151,187,205,0.75)",
				highlightStroke : "rgba(151,187,205,1)",
				<?php
				echo "data : [";
				
				foreach($postsperday as $k => $v)
				{
					echo $v.",";
				}

				echo "]";
				?>
			}
		]

	}

	</script>

<?php
echo "<div class='statistic statchart'>";
echo "<h5>Ingame post-statistik (seneste år)</h5>";
echo "<div class='chartholder'>";
echo "<canvas id='postchartyearly' width='100%' height='50px'/>";
echo "</div>";
echo "</div>";

$postspermonth = array();
$monthlydata = $forum->count_monthly_posts_in_a_year();

while($monthlyposts = $monthlydata->fetch_assoc())
{
	$num_padded = sprintf("%02d", $monthlyposts['MonthName']);
	$key = $monthlyposts['YearName']."-".$num_padded;
	$postspermonth[$key] = $monthlyposts['NumberOfPosts'];
}

$count = 12;
while($count >= 0)
{
	$date = date('Y-m-d');
	$subdate = strtotime($date.'-'.$count.' months');	
	$datekey = (string) date('Y-m', $subdate);
	if(!array_key_exists($datekey, $postspermonth)) { $postspermonth[$datekey] = 0; }	
	$count--;		
}
ksort($postspermonth);
array_shift($postspermonth);
?>

<script>
	//var randomScalingFactor = function(){ return Math.round(Math.random()*100)};
	var barData2 = {
		<?php
		echo "labels: [";
		$count = 11;
		while($count >= 0)
		{
			$date = date('Y-m-d');
			$time = strtotime($date.'-'.$count.' months');
			echo '"'.date("M", $time).'",';	
			$count--;
		}
		echo "],";		
		?>
		//labels : ["January","February","March","April","May","June","July"],
		datasets : [
			{
				fillColor : "rgba(151,187,205,0.5)",
				strokeColor : "rgba(151,187,205,0.8)",
				highlightFill : "rgba(151,187,205,0.75)",
				highlightStroke : "rgba(151,187,205,1)",
				
				<?php
				echo "data : [";
				foreach($postspermonth as $k => $v)
				{
					echo $v.",";
				}
				echo "]";
				?>
				
				//data : [randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor(),randomScalingFactor()]
			}
		]

	}

	</script>    
    
    <script>

	window.onload = function(){
	var ctx2 = document.getElementById("alignmentchart").getContext("2d");
	window.alignmentPie = new Chart(ctx2).Pie(pieData2);
	var ctx1 = document.getElementById("racechart").getContext("2d");
	window.racePie = new Chart(ctx1).Pie(pieData1);
	var ctx3 = document.getElementById("postchart").getContext("2d");
		window.postBar = new Chart(ctx3).Bar(barData, {
			responsive : true
		});
	var ctx4 = document.getElementById("postchartyearly").getContext("2d");
		window.postBar2 = new Chart(ctx4).Bar(barData2, {
			responsive : true
		});	
		
	}



	</script>
<?php
echo "</div>";
echo "</div>";
include('sidebar.php');
echo "</div>";
?>

<?php
include('footer.php');
?>