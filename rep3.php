<?php

require_once("conf.php");
require_once("menu.php");


?>
<br><br><br>
<form name="form" action="<?php echo $PHP_SELF;?>" method="post" enctype="multipart/form-data">
<fieldset>
<legend>Report</legend>

    <br> <b> From date</b>: <input type="text" name="from" value= "<?php echo date("Y-m")."-01"; ?>" size=10 maxlength=10 style="background: #FFFFCC;"> &nbsp;&nbsp;&nbsp;
    <b> To date</b>:  <input type="text"     name="to"      value= "<?php echo date("Y-m-d"); ?>"size=10 maxlength=10  style="background: #FFFFCC;" >
		      <input type="submit"   name="send"    value="Generate" autofocus >
<br><br>
</fieldset>
</form>
<?php




	#ov
	#type_: L - razhod; A - prihod
	$result = mysql_query ("select  id, name, type, liquidity, orderby from items ORDER by orderby ");
	$counter=1;
	while ($row0 = mysql_fetch_array($result) ) {
	    $data[$counter][1]= $row0['name'];
	    $data[$counter][4]= $row0['type'];
	    $data[$counter][7]= $row0['liquidity'];
	    $data[$counter][8]= $row0['orderby'];
	    $data[$counter][11]= $row0['id'];
	    $counter++;
	}


	$result = mysql_query ("
	    SELECT items.name AS name, sum(ledger.ammount) AS amnt
	    FROM items
	    LEFT JOIN  ledger ON ledger.item_dt=items.id and ledger.date<=\"" . $_POST['to'] . "\" 
		      and ledger.date>=\"" . $_POST['from'] . "\"  
	    GROUP BY items.id
	    ORDER BY orderby ");


	$counter=1;
	while ($row1 = mysql_fetch_array($result) ) {
	    $data[$counter][2]= $row1['amnt'];
	    $counter++;
	}

	$result = mysql_query ("
	    SELECT items.name AS name, sum(ledger.ammount) AS amnt
	    FROM items
	    LEFT JOIN  ledger ON ledger.item_ct=items.id and ledger.date<=\"" . $_POST['to'] . "\" 
		      and ledger.date>=\"" . $_POST['from'] . "\" 
	    GROUP BY items.id
	    ORDER BY orderby ");

	$counter=1;
	while ($row2 = mysql_fetch_array($result) ) {
	    $data[$counter][3]= $row2['amnt'];
	    $counter++;
	}



	# $data[][1] - item name
	#	 [2] - total turnover DT 
	#	 [3] - total turnover CT
	#	 [4] - item type Asset / Liability
	#	 [5] - yearly turnover DT
	#	 [6] - yearly turnover CT
	#	 [7] - liquidity +/-
	#	 [8] - orderby
	#	[11] - account id

#	echo "<table  class=\"ref\"  bgcolor=\"#DDD4FF\">";
	echo "<caption> from: ". $_POST['from'] . "&nbsp;&nbsp; to: ". $_POST['to'] . "<p>";
    echo "</caption> ";
  
	$prihod=0;
	$razhod=0;
	for ($i=1;$i<$counter;$i++) {
		if ($data[$i][4] == "L" ) { 
		    $razhod += ($data[$i][3] - $data[$i][2]);
		}

		if ($data[$i][4] == "A" ) { 
		    $prihod += ($data[$i][3] - $data[$i][2]);
		}
	}


echo '
   <script type="text/javascript" src="js/loader.js"></script>
    <script type="text/javascript">
      google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          [\'\', \' \'],
';




	$liq=0;

	for ($i=1;$i<$counter;$i++) {
            if ($data[$i][4] == "L" and $data[$i][2] - $data[$i][3] > 0){
                echo "[' ". $data[$i][1] . "', ";
                echo  $data[$i][2] - $data[$i][3] . "],";
            }
        }

?>

        ]);

        var options = {
          title: 'Expenses',
          pieHole: 0.4,
        };

        var chart = new google.visualization.PieChart(document.getElementById('donutchart1'));
        chart.draw(data, options);
      }

</script>


<?php
echo '
   <script type="text/javascript" src="js/loader.js"></script>
    <script type="text/javascript">
      google.charts.load("current", {packages:["corechart"]});
      google.charts.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          [\'\', \'\'],
';

	$liq=0;

	for ($i=1;$i<$counter;$i++) {
           if ($data[$i][4] == "A" and $data[$i][3] - $data[$i][2]>0 ) {
                echo "[' ". $data[$i][1] . "', ";
                echo  $data[$i][3] - $data[$i][2] . "],";
            }
        }

?>

        ]);

        var options = {
          title: 'Revenue',
          pieHole: 0.4,
        };

        var chart = new google.visualization.PieChart(document.getElementById('donutchart2'));
        chart.draw(data, options);
      }

</script>


<div id="donutchart1" style="width: 900px; height: 500px;"></div>

Expenses: <b><?php  echo number_format((-1) * $razhod,2); ?> </b><br>
<p>
&nbspRevenue: <b><?php  echo number_format($prihod,2); ?> </b><br>
<div id="donutchart2" style="width: 900px; height: 500px;"></div>


