export class Dataloader {

    static DATASOURCE = "https://davossail.ch/uploads/weather_2022.csv";

    constructor(raw_data) {
        this.raw_data = raw_data;
    }

    getCurrentWaterTemp() {
        var data = this.CSVToArray(this.raw_data);
        data.pop();
        return Math.round(data.pop()[0], 1);
    }

    getWaterValues() {
        var data = this.CSVToArray(this.raw_data).slice(2);
        var datapoints = Array(0)
        var lastTemp = parseFloat(data[0][0])
        for (let i = 0; i < data.length; ++i) {
            var temp = parseFloat(data[i][0])
            if (isNaN(temp)) {
                temp = null;
            } else {
                if (isNaN(lastTemp)) {
                    lastTemp = temp; 
                } else {
                    if (temp == 0 && Math.abs(temp - lastTemp) > 2) {
                        temp = null
                    }
                    else {
                        lastTemp = temp
                    }
                }
            }
            datapoints.push({ x: new Date(data[i][2] * 1000), y: temp});
        }
        return datapoints;
    }

    getCurrentAirTemp() {
        var data = this.CSVToArray(this.raw_data);
        data.pop();
        return Math.round(data.pop()[1], 1);
    }

    getAirValues() {
        var data = this.CSVToArray(this.raw_data).slice(2);
        var datapoints = Array(0)
        var lastTemp = parseFloat(data[0][1])
        for (let i = 0; i < data.length; ++i) {
            var temp = parseFloat(data[i][1])
            if (isNaN(temp)) {
                temp = null
            } else {
                if (isNaN(lastTemp)) {
                    lastTemp = temp;
                } else {
                    if (temp == 0 && Math.abs(temp - lastTemp) > 2) {
                        temp = null
                    }
                    else {
                        lastTemp = temp
                    }
                }
            }
            datapoints.push({ x: new Date(data[i][2] * 1000), y: temp});
        }
        return datapoints;
    }

    getWaterAverages(numDays) {
        var data = this.getWaterValues().filter(point => point["y"] != null);
        var datapoints = Array(0) 

        var date = data[0]["x"];
        var temp = data[0]["y"];
        var amount = 1
        for (let i = 0; i < data.length; ++i) {
            if (date.getDate() == data[i]["x"].getDate()) {
                temp += data[i]["y"];
                amount++;
                if (i == data.length - 1) {
                    datapoints.push({x: date, y: (temp / amount)})
                }
            }
            else {
                datapoints.push({x: date, y: (temp / amount)})

                //Add a null value to all dates between last and next date (to fill gaps)
                const datesAreOnSameDay = (first, second) =>
                    first.getFullYear() === second.getFullYear() &&
                    first.getMonth() === second.getMonth() &&
                    first.getDate() === second.getDate();

                var gapPoint = new Date(date.getTime());
                gapPoint.setDate(gapPoint.getDate() + 1);

                while (!datesAreOnSameDay(data[i]["x"], gapPoint)) {
                    datapoints.push({x: gapPoint, y: null});
                    gapPoint.setDate(gapPoint.getDate() + 1);
                }

                date = data[i]["x"];
                temp = data[i]["y"];
                amount = 1
            }
        }
        var sliceLength = 0;
        var nonNullDays = 0;

        for (let i = datapoints.length - 1; i >= 0; --i) {
            if (datapoints[i]["y"] != null) {
                nonNullDays++;
            }
            sliceLength++;

            if(nonNullDays >= numDays) {
                return datapoints.slice(datapoints.length - sliceLength);
            }
        }

        return datapoints;
        
    }

    getAirAverages(numDays) {
        var data = this.getAirValues().filter(point => point["y"] != null);
        var datapoints = Array(0) 

        var date = data[0]["x"];
        var temp = data[0]["y"];
        var amount = 1
        if (temp == null) { amount = 0 }
        for (let i = 0; i < data.length; ++i) {
            if (date.getDate() == data[i]["x"].getDate()) {
                temp += data[i]["y"];
                amount++;
                if (i == data.length - 1) {
                    datapoints.push({x: date, y: (temp / amount)})
                }
            }
            else {
                datapoints.push({x: date, y: (temp / amount)})

                //Add a null value to all dates between last and next date (to fill gaps)
                const datesAreOnSameDay = (first, second) =>
                    first.getFullYear() === second.getFullYear() &&
                    first.getMonth() === second.getMonth() &&
                    first.getDate() === second.getDate();

                var gapPoint = new Date(date.getTime());
                gapPoint.setDate(gapPoint.getDate() + 1);

                while (!datesAreOnSameDay(data[i]["x"], gapPoint)) {
                    datapoints.push({x: gapPoint, y: null});
                    gapPoint.setDate(gapPoint.getDate() + 1);
                }

                date = data[i]["x"];
                temp = data[i]["y"];
                amount = 1
            }
        }

        var sliceLength = 0;
        var nonNullDays = 0;

        for (let i = datapoints.length - 1; i >= 0; --i) {
            if (datapoints[i]["y"] != null) {
                nonNullDays++;
            }
            sliceLength++;

            if(nonNullDays >= numDays) {
                return datapoints.slice(datapoints.length - sliceLength);
            }
        }

        return datapoints;
    }

    downloadWaterData() {

        console.log("lol");

        const file = new File(['foo'], 'new-note.txt', {
            type: 'text/plain',
        });
          
        const link = document.createElement('a');
        const url = URL.createObjectURL(file);
          
        link.href = url;
        link.download = file.name;
        document.body.appendChild(link);
        link.click();
          
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);

    }

    // ref: http://stackoverflow.com/a/1293163/2343
    // This will parse a delimited string into an array of
    // arrays. The default delimiter is the comma, but this
    // can be overriden in the second argument.
    CSVToArray( strData, strDelimiter ){
        // Check to see if the delimiter is defined. If not,
        // then default to comma.
        strDelimiter = (strDelimiter || ",");

        // Create a regular expression to parse the CSV values.
        var objPattern = new RegExp(
            (
                // Delimiters.
                "(\\" + strDelimiter + "|\\r?\\n|\\r|^)" +

                // Quoted fields.
                "(?:\"([^\"]*(?:\"\"[^\"]*)*)\"|" +

                // Standard fields.
                "([^\"\\" + strDelimiter + "\\r\\n]*))"
            ),
            "gi"
            );


        // Create an array to hold our data. Give the array
        // a default empty first row.
        var arrData = [[]];

        // Create an array to hold our individual pattern
        // matching groups.
        var arrMatches = null;


        // Keep looping over the regular expression matches
        // until we can no longer find a match.
        while (arrMatches = objPattern.exec( strData )){

            // Get the delimiter that was found.
            var strMatchedDelimiter = arrMatches[ 1 ];

            // Check to see if the given delimiter has a length
            // (is not the start of string) and if it matches
            // field delimiter. If id does not, then we know
            // that this delimiter is a row delimiter.
            if (
                strMatchedDelimiter.length &&
                strMatchedDelimiter !== strDelimiter
                ){

                // Since we have reached a new row of data,
                // add an empty row to our data array.
                arrData.push( [] );

            }

            var strMatchedValue;

            // Now that we have our delimiter out of the way,
            // let's check to see which kind of value we
            // captured (quoted or unquoted).
            if (arrMatches[ 2 ]){

                // We found a quoted value. When we capture
                // this value, unescape any double quotes.
                strMatchedValue = arrMatches[ 2 ].replace(
                    new RegExp( "\"\"", "g" ),
                    "\""
                    );

            } else {

                // We found a non-quoted value.
                strMatchedValue = arrMatches[ 3 ];

            }


            // Now that we have our value string, let's add
            // it to the data array.
            arrData[ arrData.length - 1 ].push( strMatchedValue );
        }

        // Return the parsed data.
        return( arrData );
    }
}