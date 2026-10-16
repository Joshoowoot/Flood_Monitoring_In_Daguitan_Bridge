(function (window) {
	'use strict';

	window.DaguitanMapLayers = {
		add: function (map, picker) {
			var street = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				attribution: '&copy; OpenStreetMap contributors',
				maxZoom: 19
			});
			var satellite = L.layerGroup([
				L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
					attribution: 'Imagery &copy; Esri, Maxar, Earthstar Geographics, and the GIS User Community',
					maxZoom: 19
				}),
				L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
					attribution: 'Labels &copy; Esri',
					maxZoom: 19
				})
			]);
			var terrain = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}', {
				attribution: 'Tiles &copy; Esri, Garmin, USGS, NOAA',
				maxZoom: 19
			});
			var options = [
				{ label: 'Street', layer: street },
				{ label: 'Satellite', layer: satellite },
				{ label: 'Terrain', layer: terrain }
			];
			var buttons = [];
			if (!picker) {
				throw new Error('Map style picker element is required.');
			}
			picker.setAttribute('role', 'group');
			picker.setAttribute('aria-label', 'Map style');
			options.forEach(function (option, index) {
				var button = document.createElement('button');
				button.type = 'button';
				button.className = 'map-style-switcher__option';
				button.textContent = option.label;
				button.setAttribute('aria-pressed', index === 0 ? 'true' : 'false');
				button.addEventListener('click', function () {
					options.forEach(function (item) {
						if (map.hasLayer(item.layer)) map.removeLayer(item.layer);
					});
					option.layer.addTo(map);
					buttons.forEach(function (item) {
						item.setAttribute('aria-pressed', item === button ? 'true' : 'false');
					});
				});
				buttons.push(button);
				picker.appendChild(button);
			});
			street.addTo(map);
			return options.map(function (option) { return option.layer; });
		}
	};
})(window);
