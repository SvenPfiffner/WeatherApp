<!DOCTYPE HTML>
<html>
<head>
<script type="module">
import {Dataloader} from './js/dataloader.js';

<?php
include 'datapointer.php';
$csv_data = file_get_contents($CSV_URL);
?>

var raw_data = `<?php echo $csv_data?>`;
const dataloader = new Dataloader(raw_data);

window.onload = function () {

var chartWaterAvg = new CanvasJS.Chart("WaterAvgContainer", {
	animationEnabled: true,
	title:{
		text: "Wassertemperatur im Tagesdurchschnitt"
	},
	axisX: {
		valueFormatString: "DD MMM,YY"
	},
	axisY: {
		title: "Temperatur (in °C)",
		suffix: " °C"
	},
	legend:{
		cursor: "pointer",
		fontSize: 16,
		itemclick: toggleDataSeries
	},
	toolTip:{
		shared: true
	},
	data: [{
		name: "Wassertemperatur",
		type: "spline",
		yValueFormatString: "#0.## °C",
		showInLegend: false,
		dataPoints: dataloader.getWaterAverages(10)
	}]
});
chartWaterAvg.render();

function toggleDataSeries(e){
	if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
		e.dataSeries.visible = false;
	}
	else{
		e.dataSeries.visible = true;
	}
	chartWaterAvg.render();
}

}
</script>
</head>
<body>
<div id="WaterAvgContainer" style="height: 300px; width: 100%;"></div>
</body>
</html>
