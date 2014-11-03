/*global $,L*/
$(document).ready(function() {
        var map, users, mapquest, firstLoad,
            linkMapper = {
                url_website: {
                    title: 'Website',
                    fa: 'fa-external-link'
                },
                twitter_username: {
                    title: 'Twitter',
                    fa: 'fa-twitter',
                    urlPrefix: 'https://twitter.com/'
                },
                github_username: {
                    title: 'Github',
                    fa: 'fa-github',
                    urlPrefix: 'https://github.com/'
                },
                stackoverflow_url: {
                    title: 'Stack Exchange',
                    fa: 'fa-stack-exchange'
                },
                linkedin_url: {
                    title: 'Linkedin',
                    fa: 'fa-linkedin'
                },
                certification_board_url: {
                    title: 'Magento Certification Advisory Board',
                    fa: 'fa-certificate'
                },
                certified_developer_url: {
                    title: 'Magento Certified Developer',
                    fa: 'fa-certificate'
                },
                certified_developer_plus_url: {
                    title: 'Magento Certified Developer Plus',
                    fa: 'fa-plus'
                },
                certified_solution_specialist_url: {
                    title: 'Magento Certified Solution Specialist',
                    fa: 'fa-certificate'
                },
                certified_frontend_developer_url: {
                    title: 'Magento Certified Frontend Developer',
                    fa: 'fa-certificate'
                }
            };
        firstLoad = true;

        function getLinkHtml(key, url) {
            if (linkMapper[ key ].urlPrefix) {
                url = linkMapper[ key ].urlPrefix + url;
            }
            return "<a href='" + url + "' target='_blank'><i class='fa " + linkMapper[ key ].fa + "' title='" + linkMapper[ key ].title + "'></i></a>";
        }

        //users = new L.FeatureGroup();
        users = new L.MarkerClusterGroup({ spiderfyOnMaxZoom: true, showCoverageOnHover: false, zoomToBoundsOnClick: true });

        mapquest = new L.TileLayer("http://{s}.mqcdn.com/tiles/1.0.0/osm/{z}/{x}/{y}.png", {
            maxZoom: 18,
            subdomains: [ "otile1", "otile2", "otile3", "otile4" ],
            attribution: 'Basemap tiles courtesy of <a href="http://www.mapquest.com/" target="_blank">MapQuest</a> <img src="http://developer.mapquest.com/content/osm/mq_logo.png">. Map data (c) <a href="http://www.openstreetmap.org/" target="_blank">OpenStreetMap</a> contributors, CC-BY-SA.'
        });

        map = new L.Map('map', {
            center: new L.LatLng(39.90973623453719, - 93.69140625),
            zoom: 3,
            layers: [ mapquest, users ]
        });

        var geolocControl = new L.control({
            position: 'topright'
        });
        geolocControl.onAdd = function(map) {
            window.getMyMapLocation = function() {
                // http://leafletjs.com/reference.html#map-locate
                // maybe show info for user that getLocation is processing it may take some time for the browser
                // to find your location. watch then the events locationfound or locationerror.
                map.locate({ setView: true, maxZoom: 14 });
            };
            var div = L.DomUtil.create('div', 'leaflet-control-zoom leaflet-control');
            div.innerHTML = '<a class="leaflet-control-geoloc" href="#" onclick="getMyMapLocation()" title="My location">&nbsp;</a>';
            return div;
        };

        map.addControl(geolocControl);
        map.addControl(new L.Control.Scale());

        function getUsers() {

            $.getJSON("/map/users", function(data) {
                    var i = 0,
                        row = {},
                        popupData = [],
                        location = {},
                        name = '',
                        marker = {};

                    for (i = 0; i < data.length; i = i + 1) {
                        row = data[ i ];
                        popupData = [];
                        location = new L.LatLng(row.latitude || 0, row.longitude || 0);
                        name = row.name || '';

                        if (row.image && row.image.length > 1) {
                            popupData.push('<img src="' + row.image + '" alt="avatar" height="80">');
                        }
                        popupData[ 1 ] = "<div class='header'>" + name + "</div>";
                        if (row.username && row.username.length > 1) {
                            popupData[ 1 ] = "<div class='header'><a href='/" + row.username + "' target='_blank'>" + name + "</a></div>";
                        }

                        if (row.city && row.city.length > 1) {
                            popupData.push('<div>' + row.city + '</div>');
                        }

                        popupData.push("<div class='social'>");
                        $.each(row.links, function(key, url) {
                            if (url && url.length > 1) {
                                popupData.push(getLinkHtml(key, url));
                            }
                        });
                        popupData.push('</div>');

                        marker = new L.Marker(location, {
                            title: name
                        });
                        marker.bindPopup("<div class='hero'>" + popupData.join('') + '</div>', { maxWidth: '400' });
                        users.addLayer(marker);
                    }
                }
            ).complete(function() {
                    if (firstLoad === true) {
                        map.fitBounds(users.getBounds());
                        firstLoad = false;
                    }
                });
        }

        $(document).ready(function() {
            $.ajaxSetup({ cache: false });
            $('#map').css('height', ($(window).height() - 130));
            getUsers();
        });

        $(window).resize(function() {
            $('#map').css('height', ($(window).height() - 130));
        }).resize();
    }
);



