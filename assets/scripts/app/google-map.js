
function initialize() {

    document.getElementById("google-drapewell");


    
    var latLngDrapewellMarker = new google.maps.LatLng(51.550882,0.678105),
        latLngDrapewell = new google.maps.LatLng(51.551939,0.678062),
        mapDrapewell = new google.maps.Map(document.getElementById("google-drapewell"), { zoom:16, center:latLngDrapewell, mapTypeIds: [google.maps.MapTypeId.ROADMAP], disableDefaultUI: true, scrollwheel: true });

    var marker = new google.maps.Marker({
        position: latLngDrapewellMarker, 
        map: mapDrapewell, 
        title:"Drapewell",
        icon: window.site_path + 'assets/images/google-marker.png'});
        
    var styles = [
        {
          featureType: "all",
          elementType: "all",
        }
    ];

    var mapType = new google.maps.StyledMapType(styles, { name:"Grayscale" });
        mapDrapewell.mapTypes.set('grey', mapType);
        mapDrapewell.setMapTypeId('grey');

}

function loadScript() {
    var script = document.createElement("script");
        script.type = "text/javascript";
        script.src = "http://maps.googleapis.com/maps/api/js?key=AIzaSyA2eYkWKSYnyzyjDvZV_gqVRiM4nbVGhD0&sensor=false&callback=initialize";
        document.body.appendChild(script);
}

loadScript();