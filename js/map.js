/*global $,L*/
$(document).ready(function() {
        var map, users, mapquest, firstLoad,
            linkMapper = {
                website: {
                    title: 'Website',
                    fa: 'fa-external-link'
                },
                twitter: {
                    title: 'Twitter',
                    fa: 'fa-twitter',
                    urlPrefix: 'https://twitter.com/'
                },
                github: {
                    title: 'Github',
                    fa: 'fa-github',
                    urlPrefix: 'https://github.com/'
                },
                stackOverflow: {
                    title: 'Stack Exchange',
                    fa: 'fa-stack-exchange'
                },
                linkedIn: {
                    title: 'Linkedin',
                    fa: 'fa-linkedin'
                },
                developer: {
                    title: 'Magento Certified Developer',
                    fa: 'fa-certificate'
                },
                developerPlus: {
                    title: 'Magento Certified Developer Plus',
                    fa: 'fa-plus'
                },
                developerFrontend: {
                    title: 'Magento Certified Frontend Developer',
                    fa: 'fa-certificate'
                },
                solutionSpecialist: {
                    title: 'Magento Certified Solution Specialist',
                    fa: 'fa-certificate'
                },
                certificationBoard: {
                    title: 'Magento Certification Advisory Board',
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

        // hmm bug does not work when clicking in the top right corner
        $('a.leaflet-control-geoloc').on('click', function(e) {
            e.preventDefault();
            map.locate({ setView: true, maxZoom: 17 });
        });

        var geolocControl = new L.control({
            position: 'topright'
        });
        geolocControl.onAdd = function(map) {
            var div = L.DomUtil.create('div', 'leaflet-control-zoom leaflet-control');
            div.innerHTML = '<a class="leaflet-control-geoloc" href="#" title="My location">My Location</a>';
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
            $('#map').css('height', ($(window).height() - 51));
            getUsers();
        });

        $(window).resize(function() {
            $('#map').css('height', ($(window).height() - 51));
        }).resize();
    }
);



