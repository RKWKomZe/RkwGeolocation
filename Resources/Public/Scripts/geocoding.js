jQuery(document).ready(function() {

    var options = {
        enableHighAccuracy: true,
        timeout: 1000,
        maximumAge: 0
    }

    function getLongitude(position) {
        return position.coords.longitude;
    }

    function getLatitude(position) {
        return position.coords.latitude;
    }



    //example
    jQuery(".location").click(function() {

        navigator.geolocation.getCurrentPosition(getCoordinates, error, options);

    })


    function getCoordinates(position) {
        console.log(position.coords.latitude);
        console.log(position.coords.longitude);

    /*    function error (msg) {
            console.log(typeof msg == "string" ? msg: "error");
        }

        output.innerHTML = '<p>Latitude is ' + latitude + '<br>Longitude is ' + longitude + '</p>';

        var img = new Image();
        img.src = "https://maps.googleapis.com/maps/api/staticmap?center=" + latitude + "," + longitude + "&zoom=13&size=300x300&sensor=false";

        output.appendChild(img);
    */
    }


});
