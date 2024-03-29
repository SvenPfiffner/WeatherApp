<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Wetterstation</title>
    <meta http-equiv="refresh" content="300">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="Free HTML Templates" name="keywords">
    <meta content="Free HTML Templates" name="description">

    <?php include "air_graph_avg.php"?>

    <!-- Favicon -->
    <link type="image/png" href="img/favicon_genni_dark.png" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet"> 

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/style.css" rel="stylesheet">

</head>

<body data-spy="scroll" data-target=".navbar" data-offset="51">
    <!-- Navbar Start -->
    <nav class="navbar fixed-top shadow-sm navbar-expand-lg bg-light navbar-light py-3 py-lg-0 px-lg-5">
        <div>
            <a href="https://davossail.ch" class="navbar-brand ml-lg-3">
                <h1 class="m-0 display-5"><span class="text-primary">DAVOS</span><span style="color:#ffd400">SAIL</span></h1>
            </a>
            <img src=img/logo_mast_dark.png height="37" width="auto" alt="Davossail Logo">
        </div>
        <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse px-lg-3" id="navbarCollapse">
            <div class="navbar-nav m-auto py-0">
                <a href="#home" class="nav-item nav-link active">Home</a>
                <a href="#water" class="nav-item nav-link">Wassertemperatur</a>
                <a href="#air" class="nav-item nav-link">Lufttemperatur</a>
                <a href="#combined" class="nav-item nav-link">Alle Daten</a>
                <a href="#downlad" class="nav-item nav-link">Download</a>
            </div>
        </div>
    </nav>
    <!-- Navbar End -->


    <!-- Header Start -->
    <div class="container-fluid bg-primary d-flex align-items-center mb-5 py-5" id="home" style="min-height: 100vh;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-5 px-5 pl-lg-0 pb-5 pb-lg-0">
                    <img class="img-fluid w-100 rounded-circle shadow-sm" src="img/profile.jpg" alt="">
                </div>
                <div class="col-lg-7 text-center text-lg-left">
                    <h3 class="text-white font-weight-normal mb-3">Segelschule Davosersee</h3>
                    <h1 class="display-3 text-uppercase text-primary mb-2" style="-webkit-text-stroke: 2px #ffffff;">Wetterstation</h1>
                    <h1 class="display-5 text-uppercase text-primary mb-2" style="-webkit-text-stroke: 1px #ffffff;">46°48'58"N 9°51'08"E</h1>
                    <h1 class="typed-text-output d-inline font-weight-lighter text-white"></h1>
                    <div class="typed-text d-none">Wassertemperatur, Lufttemperatur, Rohdaten</div>
                    <div class="d-flex align-items-center justify-content-center mb-4">
                        <div class="d-flex align-items-center" style="background-color: #0e324c; border-radius: 25px; padding-top: 10px; padding-right: 10px; padding-bottom: 10px;">
                            <i class="fa fa-2x fa-water service-icon bg-primary text-white mr-3"></i>
                            <h4 id="current_water_temp_comb2" class="font-weight-bold m-0 text-white"></h4>
                        </div>
                        <div style="width: 10px;"></div>
                        <div class="d-flex align-items-center" style="background-color: #0e324c; border-radius: 25px; padding-top: 10px; padding-right: 10px; padding-bottom: 10px;">
                            <i class="fa fa-2x fa-sun service-icon bg-primary text-white mr-3"></i>
                            <h4 id="current_air_temp_comb2" class="font-weight-bold m-0 text-white"></h4>
                        </div>
                    </div>
                    <!--
                    <div class="d-flex align-items-center justify-content-center mb-4">
                        <div class="d-flex align-items-center" style="background-color: #0e324c; border-radius: 25px; padding-top: 10px; padding-right: 10px; padding-bottom: 10px;">
                            <i class="fa fa-2x fa-sun service-icon bg-primary text-white mr-3"></i>
                            <h4 class="text-white font-weight-bold m-3">46°48'58.2"N 9°51'08.3"E</h4>
                        </div>
                    </div>
                    -->
                </div>

                
                <script type="module">
                    import {Dataloader} from './js/dataloader.js';

                    <?php
                        include 'datapointer.php';
                        $csv_data = file_get_contents($CSV_URL);
                    ?>

                    var raw_data = `<?php echo $csv_data?>`;
                    const dataloader = new Dataloader(raw_data);
                    document.getElementById("current_water_temp_comb2").innerHTML = "Aktuell: " + dataloader.getCurrentWaterTemp() + "°C";
                    document.getElementById("current_air_temp_comb2").innerHTML = "Aktuell: " + dataloader.getCurrentAirTemp() + "°C";
                    var current_timedate = dataloader.getCurrentMeasurementTime()
                    document.getElementById("current_measurement_time_comb2").innerHTML = "Aktuelle Messung um " + current_timedate.getHours() + ":" + current_timedate.getMinutes();
                </script>
            </div>
        </div>
    </div>
    
    <!-- Header End -->


    <!-- Watertemp Start -->
    <div class="container-fluid py-5" id="water">
        <div class="container">
            <div class="position-relative d-flex align-items-center justify-content-center">
                <h1 class="display-1 text-uppercase text-white" style="-webkit-text-stroke: 1px #dee2e6;">Wasser</h1>
                <h1 class="position-absolute text-uppercase text-primary">Wassertemperatur</h1>
            </div>
            <div class="d-flex align-items-center justify-content-center mb-4">
                    <div class="d-flex align-items-center" style="background-color: #0e324c; border-radius: 25px; padding-top: 10px; padding-right: 10px; padding-bottom: 10px;">
                        <i class="fa fa-2x fa-water service-icon bg-primary text-white mr-3"></i>
                        <h4 id="current_water_temp" class="font-weight-bold m-0 text-white"></h4>
                    </div>
            </div>
            <script type="module">
                    import {Dataloader} from './js/dataloader.js';

                    <?php
                        include 'datapointer.php';
                        $csv_data = file_get_contents($CSV_URL);
                    ?>

                    var raw_data = `<?php echo $csv_data?>`;
                    const dataloader = new Dataloader(raw_data);
                    document.getElementById("current_water_temp").innerHTML = "Aktuell: " + dataloader.getCurrentWaterTemp() + "°C";
                </script>
            <div class="row align-items-center">
                <div id="WaterAvgContainer" style="height: 300px; width: 100%;"></div>
                <p>Zum Zoomen einen Bereich markieren</p>
                <div id="WaterAllContainer" style="height: 300px; width: 100%; margin-top:3cm;"></div>
                <p>Zum Zoomen einen Bereich markieren</p>
            </div>
        </div>
    </div>
    <!-- Watertemp End -->


    <!-- Air Start -->
    <div class="container-fluid py-5" id="air">
        <div class="container">
            <div class="position-relative d-flex align-items-center justify-content-center">
                <h1 class="display-1 text-uppercase text-white" style="-webkit-text-stroke: 1px #dee2e6;">Luft</h1>
                <h1 class="position-absolute text-uppercase text-primary">Lufttemperatur</h1>
            </div>
            <div class="d-flex align-items-center justify-content-center mb-4">
                    <div class="d-flex align-items-center" style="background-color: #0e324c; border-radius: 25px; padding-top: 10px; padding-right: 10px; padding-bottom: 10px;">
                        <i class="fa fa-2x fa-sun service-icon bg-primary text-white mr-3"></i>
                        <h4 id="current_air_temp" class="font-weight-bold m-0 text-white"></h4>
                    </div>
            </div>
            <script type="module">
                    import {Dataloader} from './js/dataloader.js';

                    <?php
                        include 'datapointer.php';
                        $csv_data = file_get_contents($CSV_URL);
                    ?>

                    var raw_data = `<?php echo $csv_data?>`;
                    const dataloader = new Dataloader(raw_data);
                    document.getElementById("current_air_temp").innerHTML = "Aktuell: " + dataloader.getCurrentAirTemp() + "°C";
                </script>
            <div class="row align-items-center">
                <div id="AirAvgContainer" style="height: 300px; width: 100%;"></div>
                <p>Zum Zoomen einen Bereich markieren</p>
                <div id="AirAllContainer" style="height: 300px; width: 100%; margin-top:3cm;"></div>
                <p>Zum Zoomen einen Bereich markieren</p>
            </div>
        </div>
    </div>
    <!-- Air End -->


    <!-- combined Start -->
    <div class="container-fluid py-5" id="combined">
        <div class="container">
            <div class="position-relative d-flex align-items-center justify-content-center">
                <h1 class="display-1 text-uppercase text-white" style="-webkit-text-stroke: 1px #dee2e6;">Daten</h1>
                <h1 class="position-absolute text-uppercase text-primary">Alle Daten</h1>
            </div>
            <div class="d-flex align-items-center justify-content-center mb-4">
                    <div class="d-flex align-items-center" style="background-color: #0e324c; border-radius: 25px; padding-top: 10px; padding-right: 10px; padding-bottom: 10px;">
                        <i class="fa fa-2x fa-water service-icon bg-primary text-white mr-3"></i>
                        <h4 id="current_water_temp_comb" class="font-weight-bold m-0 text-white"></h4>
                    </div>
                    <div style="width: 10px;"></div>
                    <div class="d-flex align-items-center" style="background-color: #0e324c; border-radius: 25px; padding-top: 10px; padding-right: 10px; padding-bottom: 10px;">
                        <i class="fa fa-2x fa-sun service-icon bg-primary text-white mr-3"></i>
                        <h4 id="current_air_temp_comb" class="font-weight-bold m-0 text-white"></h4>
                    </div>
            </div>
            <script type="module">
                    import {Dataloader} from './js/dataloader.js';

                    <?php
                        include 'datapointer.php';
                        $csv_data = file_get_contents($CSV_URL);
                    ?>

                    var raw_data = `<?php echo $csv_data?>`;
                    const dataloader = new Dataloader(raw_data);
                    document.getElementById("current_water_temp_comb").innerHTML = "Aktuell: " + dataloader.getCurrentWaterTemp() + "°C";
                    document.getElementById("current_air_temp_comb").innerHTML = "Aktuell: " + dataloader.getCurrentAirTemp() + "°C";
                </script>
            <div class="row align-items-center">
                <div id="CombinedAvgContainer" style="height: 300px; width: 100%;"></div>
                <p>Zum Zoomen einen Bereich markieren</p>
                <div id="CombinedAllContainer" style="height: 300px; width: 100%; margin-top:3cm;"></div>
                <p style="margin-bottom:3cm">Zum Zoomen einen Bereich markieren</p>
            </div>
        </div>
    </div>
    <!-- combined End -->


    <!-- Download Start -->
    <div class="container-fluid py-5" id="downlad">
        <div class="container">
            <div class="position-relative d-flex align-items-center justify-content-center">
                <h1 class="display-1 text-uppercase text-white" style="-webkit-text-stroke: 1px #dee2e6;">Downlad</h1>
                <h1 class="position-absolute text-uppercase text-primary">Download</h1>
            </div>
            <div class="d-flex align-items-center justify-content-center mb-4">
                <!--
                <div class="d-flex align-items-center" style="background-color: #0e324c; border-radius: 25px; padding-top: 10px; padding-right: 10px; padding-bottom: 10px;">
                    <i class="fa fa-2x fa-water service-icon bg-primary text-white mr-3"></i>
                    <h4 class="font-weight-bold m-0 text-white">Wassertemeratur</h4>
                </div>
                <div style="width: 10px;"></div>
                <div class="d-flex align-items-center" style="background-color: #0e324c; border-radius: 25px; padding-top: 10px; padding-right: 10px; padding-bottom: 10px;">
                    <i class="fa fa-2x fa-sun service-icon bg-primary text-white mr-3"></i>
                    <h4 class="font-weight-bold m-0 text-white">Lufttemperatur</h4>
                </div>
                <div style="width: 10px;"></div>
                -->

                <a href="https://davossail.ch/uploads/weather_2022.csv" download="weather_davosersee_2022.csv" style="text-decoration: none">
                    <div class="d-flex align-items-center" style="background-color: #0e324c; border-radius: 25px; padding-top: 10px; padding-right: 10px; padding-bottom: 10px;">
                        <i class="fa fa-2x fa-download service-icon bg-primary text-white mr-3"></i>
                        <h4 class="font-weight-bold m-0 text-white">Alle Daten</h4>
                    </div>
                </a>
            </div>
        </div>

    </div>
    <!-- Downlad End -->

    <!-- Footer Start -->
    <div class="container-fluid bg-primary text-white mt-5 py-5 px-sm-3 px-md-5">
        <div class="container text-center py-5">
            <p class="m-0">&copy; <a class="text-white font-weight-bold" href="https://www.davossail.ch">davossail</a>. All Rights Reserved. Weatherstation Front & Backend by <b>Luca Dalbosco</b> | <a class="text-white font-weight-bold" href="https://github.com/SvenPfiffner">Sven Pfiffner</a>
            </p>
        </div>
    </div>
    <!-- Footer End -->

    <!-- Scroll to Bottom -->
    <i class="fa fa-2x fa-angle-down text-white scroll-to-bottom"></i>

    <!-- Back to Top -->
    <a href="#" class="btn btn-outline-dark px-0 back-to-top"><i class="fa fa-angle-double-up"></i></a>


    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
    <script src="lib/typed/typed.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/isotope/isotope.pkgd.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
</body>

</html>