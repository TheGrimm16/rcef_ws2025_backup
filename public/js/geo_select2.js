window.initGeoSelect2 = function(config) {
    var regionSelect = $(config.region);
    var provinceSelect = $(config.province);
    var municipalitySelect = $(config.municipality);

    function populateSelect(selectEl, data, placeholder, selectedId) {
        selectEl.empty().append('<option></option>'); // placeholder
        data.forEach(function(item) {
            selectEl.append(new Option(item.text, item.id, false, false));
        });
        if (selectedId) {
            selectEl.val(selectedId).trigger('change.select2'); // set existing selection if valid
        } else {
            selectEl.val(null).trigger('change.select2'); // reset to placeholder
        }
    }

    // Load regions
    $.getJSON(window.geoRoutes.regions, function(data) {
        regionSelect.data('all', data);
        regionSelect.select2({
            placeholder: 'Select Region',
            width: '100%',
            data: data
        });
    });

    // Load provinces
    $.getJSON(window.geoRoutes.provinces.replace('__REGION__',''), function(data) {
        provinceSelect.data('all', data);
        provinceSelect.select2({
            placeholder: 'Select Province',
            width: '100%'
        });
        populateSelect(provinceSelect, data, 'Select Province');
    });

    // Load municipalities
    $.getJSON(window.geoRoutes.municipalities.replace('__PROVINCE__',''), function(data) {
        municipalitySelect.data('all', data);
        municipalitySelect.select2({
            placeholder: 'Select Municipality',
            width: '100%'
        });
        populateSelect(municipalitySelect, data, 'Select Municipality');
    });

    // Region change -> filter provinces
    regionSelect.on('change', function() {
        var regionCode = regionSelect.val();
        var allProvinces = provinceSelect.data('all') || [];
        var filtered = allProvinces.filter(p => !regionCode || p.regCode == regionCode);

        populateSelect(provinceSelect, filtered, 'Select Province');
        populateSelect(municipalitySelect, [], 'Select Municipality'); // reset municipalities
    });

    // Province change -> filter municipalities and set higher tier
    provinceSelect.on('change', function() {
        var provinceCode = provinceSelect.val();
        var allMunis = municipalitySelect.data('all') || [];
        var filtered = allMunis.filter(m => !provinceCode || m.provCode == provinceCode);

        populateSelect(municipalitySelect, filtered, 'Select Municipality');

        // Auto-set region to match province
        var allProvinces = provinceSelect.data('all') || [];
        var parentProvince = allProvinces.find(p => p.id == provinceCode);
        if (parentProvince && regionSelect.val() !== parentProvince.regCode) {
            regionSelect.val(parentProvince.regCode).trigger('change.select2');
        }
    });

    // Municipality change -> auto-set province & region
    municipalitySelect.on('change', function() {
        var muniId = municipalitySelect.val();
        var allMunis = municipalitySelect.data('all') || [];
        var selectedMuni = allMunis.find(m => m.id == muniId);

        if (selectedMuni) {
            // Set province
            if (provinceSelect.val() !== selectedMuni.provCode) {
                provinceSelect.val(selectedMuni.provCode).trigger('change.select2');
            }

            // Set region
            var allProvinces = provinceSelect.data('all') || [];
            var parentProvince = allProvinces.find(p => p.id == selectedMuni.provCode);
            if (parentProvince && regionSelect.val() !== parentProvince.regCode) {
                regionSelect.val(parentProvince.regCode).trigger('change.select2');
            }
        }
    });
};
