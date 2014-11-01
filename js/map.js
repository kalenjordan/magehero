/*global $,L*/
$(document).ready(function () {
    var map, users, mapquest, firstLoad;

    firstLoad = true;

    //users = new L.FeatureGroup();
    users = new L.MarkerClusterGroup({spiderfyOnMaxZoom: true, showCoverageOnHover: false, zoomToBoundsOnClick: true});

    mapquest = new L.TileLayer("http://{s}.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png", {
      maxZoom: 18,
      subdomains: ["otile1", "otile2", "otile3", "otile4"],
      attribution: 'Basemap tiles courtesy of <a href="http://www.mapquest.com/" target="_blank">MapQuest</a> <img src="http://developer.mapquest.com/content/osm/mq_logo.png">. Map data (c) <a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> contributors, CC-BY-SA.'
    });

    map = new L.Map('map', {
      center: new L.LatLng(39.90973623453719, -93.69140625),
      zoom: 3,
      layers: [mapquest, users]
    });

    // hmm bug does not work when clicking in the top right corner
    $('a.leaflet-control-geoloc').on('click', function (e) {
      e.preventDefault();
      map.locate({setView: true, maxZoom: 17});
    });

    var geolocControl = new L.control({
      position: 'topright'
    });
    geolocControl.onAdd = function (map) {
      var div = L.DomUtil.create('div', 'leaflet-control-zoom leaflet-control');
      div.innerHTML = '<a class="leaflet-control-geoloc" href="#" title="My location">My Location</a>';
      return div;
    };

    map.addControl(geolocControl);
    map.addControl(new L.Control.Scale());

    function getUsers() {

      var certMap = {
        d: 'Magento Certified Developer',
        p: 'Magento Certified Developer Plus',
        f: 'Magento Certified Frontend Developer',
        s: 'Magento Certified Solution Specialist',
        b: 'Magento Certification Advisory Board'
      };

      $.getJSON("/map/users", function (data) {

          for (var i = 0; i < data.length; i++) {
            var row = data[i];
            var popupData = [];
            var location = new L.LatLng(row.lat || 0, row.lng || 0);
            var name = row.name || '';

            if (row.img && row.img.length > 1) {
              popupData.push('<img src="' + row.img + '" alt="avatar" height="80">');
            }
            popupData[1] = "<div class='header'>" + name + "</div>";
            if (row.username && row.username.length > 1) {
              popupData[1] = "<div class='header'><a href='/" + row.username + "' target='_blank'>" + name + "</a></div>";
            }

            if (row.company && row.company.length > 1) {
              popupData.push('<div>' + row.company + '</div>');
            }
            if (row.city && row.city.length > 1) {
              popupData.push('<div>' + row.city + '</div>');
            }

            popupData.push("<div class='social'>");
            $.each(row.certs, function (k, certUrl) {
              if (certUrl && certUrl.length > 7 && certMap[k]) {
                popupData.push("<a href='" + certUrl + "' target='_blank'><i class='fa fa-certificate' title='" + certMap[k] + "'></i></a>");
              }
            });

            if (row.website && row.website.length > 7) {
              popupData.push("<a href='" + row.website + "' target='_blank'><i class='fa fa-external-link' title='Website'></i></a>");
            }
            if (row.tw && row.tw.length > 1) {
              popupData.push("<a href='https://twitter.com/" + row.tw + "' target='_blank'><i class='fa fa-twitter' title='Twitter'></i></a>");
            }
            if (row.gh && row.gh.length > 1) {
              popupData.push("<a href='https://github.com/" + row.gh + "' target='_blank'><i class='fa fa-github' title='GitHub'></i></a>");
            }
            if (row.so && row.so.length > 1) {
              popupData.push("<a href='" + row.so + "' target='_blank'><i class='fa fa-stack-exchange' title='Stack Exchange'></i></a>");
            }
            if (row.li && row.li.length > 1) {
              popupData.push("<a href='" + row.li + "' target='_blank'><i class='fa fa-linkedin' title='Linkedin'></i></a>");
            }

            popupData.push('</div>');

            var marker = new L.Marker(location, {
              title: name
            });
            marker.bindPopup("<div class='hero'>" + popupData.join('') + '</div>', {maxWidth: '400'});
            users.addLayer(marker);
          }
        }
      ).complete(function () {
          if (firstLoad == true) {
            map.fitBounds(users.getBounds());
            firstLoad = false;
          }
        });
    }

    $(document).ready(function () {
      $.ajaxSetup({cache: false});
      $('#map').css('height', ($(window).height() - 51));
      getUsers();
    });

    $(window).resize(function () {
      $('#map').css('height', ($(window).height() - 51));
    }).resize();
  }
);



