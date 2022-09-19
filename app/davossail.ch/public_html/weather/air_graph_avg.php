<script type="module">
import {Dataloader} from './js/dataloader.js';

<?php
include 'datapointer.php';
$csv_data = file_get_contents($CSV_URL);
?>

var raw_data = `<?php echo $csv_data?>`;
const dataloader = new Dataloader(raw_data);

var chartAirAvg = new CanvasJS.Chart("AirAvgContainer", {
	animationEnabled: true,
	title:{
		text: "Tagesdurchschnitt der letzten 10 Tage"
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
		fontSize: 16
	},
	toolTip:{
		shared: true
	},
	data: [{
		name: "Lufttemperatur",
		type: "spline",
		color: "#ffd400",
		connectNullData: true,
		yValueFormatString: "#0.## °C",
		showInLegend: false,
		dataPoints: dataloader.getAirAverages(10)
	}]
});

var chartAirAll = new CanvasJS.Chart("AirAllContainer", {
	theme: "light1", // "light1", "light2", "dark1", "dark2"

	animationEnabled: true,
	zoomEnabled: true,
	title: {
		text: "Alle Messwerte der Saison"
	},
	axisY: {
		title: "Temperatur (in °C)",
		suffix: " °C"
	},
	data: [{
		type: "spline",
		color: "#ffd400",
		connectNullData: true,
		dataPoints: dataloader.getAirValues()
	}]
});

var chartWaterAvg = new CanvasJS.Chart("WaterAvgContainer", {
	animationEnabled: true,
	title:{
		text: "Tagesdurchschnitt der letzten 10 Tage"
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
		fontSize: 16
	},
	toolTip:{
		shared: true
	},
	data: [{
		name: "Wassertemperatur",
		type: "spline",
		color: "#0e324c",
		connectNullData: true,
		yValueFormatString: "#0.## °C",
		showInLegend: false,
		dataPoints: dataloader.getWaterAverages(10)
	}]
});

var chartWaterAll = new CanvasJS.Chart("WaterAllContainer", {
	theme: "light1", // "light1", "light2", "dark1", "dark2"
	animationEnabled: true,
	zoomEnabled: true,
	title: {
		text: "Alle Messwerte der Saison"
	},
	axisY: {
		title: "Temperatur (in °C)",
		suffix: " °C"
	},
	data: [{
		type: "spline",
		color: "#0e324c",
		connectNullData: true,
		dataPoints: dataloader.getWaterValues()
	}]
});

var chartCombinedAvg = new CanvasJS.Chart("CombinedAvgContainer", {
	animationEnabled: true,
	title:{
		text: "Tagesdurchschnitt der letzten 10 Tage"
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
		name: "Lufttemperatur",
		type: "spline",
		color: "#ffd400",
		connectNullData: true,
		yValueFormatString: "#0.## °C",
		showInLegend: true,
		dataPoints: dataloader.getAirAverages(10)
	},
	{
		name: "Wassertemperatur",
		type: "spline",
		color: "#0e324c",
		connectNullData: true,
		yValueFormatString: "#0.## °C",
		showInLegend: true,
		dataPoints: dataloader.getWaterAverages(10)
	}]
});

var chartCombinedAll = new CanvasJS.Chart("CombinedAllContainer", {
	animationEnabled: true,
	zoomEnabled: true,
	title:{
		text: "Alle Messwerte der Saison"
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
		itemclick: toggleDataSeriesAll
	},
	toolTip:{
		shared: true
	},
	data: [{
		name: "Lufttemperatur",
		type: "spline",
		color: "#ffd400",
		connectNullData: true,
		yValueFormatString: "#0.## °C",
		showInLegend: true,
		dataPoints: dataloader.getAirValues()
	},
	{
		name: "Wassertemperatur",
		type: "spline",
		color: "#0e324c",
		connectNullData: true,
		yValueFormatString: "#0.## °C",
		showInLegend: true,
		dataPoints: dataloader.getWaterValues()
	}]
});

chartAirAvg.render();
chartAirAll.render();
chartWaterAvg.render();
chartWaterAll.render();
chartCombinedAvg.render();
chartCombinedAll.render();

function toggleDataSeries(e){
	if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
		e.dataSeries.visible = false;
	}
	else{
		e.dataSeries.visible = true;
	}
	chartCombinedAvg.render();
}

function toggleDataSeriesAll(e){
	if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
		e.dataSeries.visible = false;
	}
	else{
		e.dataSeries.visible = true;
	}
	chartCombinedAll.render();
}
</script>
