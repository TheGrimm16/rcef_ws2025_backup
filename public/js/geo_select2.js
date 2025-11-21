/**
 * geo_select2.js – One-Tap Fixed Version (Nov 2025)
 * Cascading Select2: Region → Province → Municipality
 * Auto-focus search & proper placeholder reset
 */

function enableSelect2AutoFocus($el) {
    $el.off('select2:open.autofocus');
    $el.on('select2:open.autofocus', function () {
        requestAnimationFrame(function () {
            setTimeout(function () {
                const searchInput = document.querySelector('.select2-container--open .select2-search__field');
                if (searchInput) {
                    searchInput.focus();
                    if (searchInput.value) {
                        searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
                    }
                }
            }, 0);
        });
    });
}

// Update Select2 options while keeping its own placeholder
function updateSelect2Options($select, items, placeholderText) {
    $select.empty();

    if (Array.isArray(items) && items.length > 0) {
        items.forEach(item => {
            $select.append(new Option(item.text, item.id, false, false));
        });
    }

    // Reset value so placeholder shows
    $select.val(null).trigger('change');

    // Keep custom placeholder
    if (placeholderText) {
        $select.data('placeholder', placeholderText);
        $select.select2({ placeholder: placeholderText });
    }
}

window.initGeoSelect2 = function (config) {
    const $region       = $(config.region || null);
    const $province     = $(config.province);
    const $municipality = $(config.municipality);

    // Custom placeholders
    const regionPlaceholder = config.regionPlaceholder || 'Select Region';
    const provincePlaceholder = config.provincePlaceholder || 'Select Province';
    const municipalityPlaceholder = config.municipalityPlaceholder || 'Select Municipality';

    // Initialize Select2 once
    [
        { $el: $region, placeholder: regionPlaceholder },
        { $el: $province, placeholder: provincePlaceholder },
        { $el: $municipality, placeholder: municipalityPlaceholder }
    ].forEach(item => {
        if (item.$el.length && !item.$el.hasClass("select2-hidden-accessible")) {
            item.$el.select2({
                placeholder: item.placeholder,
                allowClear: true,
                width: '100%'
            });
            enableSelect2AutoFocus(item.$el);
        }
    });

    // Load Regions
    if ($region.length) {
        $.getJSON(window.geoRoutes.regions, function (data) {
            updateSelect2Options($region, data.map(r => ({ id: r.id, text: r.text })), regionPlaceholder);
        });
    }

    // REGION → Province + Municipality
    $region.on('change', function () {
        const regionCode = $region.val();

        // Reset dependent selects with proper placeholder
        updateSelect2Options($province, [], provincePlaceholder);
        updateSelect2Options($municipality, [], municipalityPlaceholder);

        if (!regionCode) return;

        $.getJSON(window.geoRoutes.provinces.replace("__REGION__", regionCode), function (provinces) {
            updateSelect2Options($province, provinces.map(p => ({ id: p.id, text: p.text })), provincePlaceholder);
        });
    });

    // PROVINCE → Municipality
    $province.on('change', function () {
        const provinceCode = $province.val();

        // Reset municipality with its own placeholder
        updateSelect2Options($municipality, [], municipalityPlaceholder);

        if (!provinceCode) return;

        $.getJSON(window.geoRoutes.municipalities.replace("__PROVINCE__", provinceCode), function (munis) {
            updateSelect2Options($municipality, munis.map(m => ({ id: m.id, text: m.text })), municipalityPlaceholder);
        });
    });
};

// window.initGeoSelect2 = function(config) {
//     var regionSelect = $(config.region);
//     var provinceSelect = $(config.province);
//     var municipalitySelect = $(config.municipality);

//     function populateSelect(selectEl, data, placeholder, selectedId) {
//         selectEl.empty().append('<option></option>'); // placeholder
//         data.forEach(function(item) {
//             selectEl.append(new Option(item.text, item.id, false, false));
//         });
//         if (selectedId) {
//             selectEl.val(selectedId).trigger('change.select2'); // set existing selection if valid
//         } else {
//             selectEl.val(null).trigger('change.select2'); // reset to placeholder
//         }
//     }

//     // Load regions
//     $.getJSON(window.geoRoutes.regions, function(data) {
//         regionSelect.data('all', data);
//         regionSelect.select2({
//             placeholder: 'Select Region',
//             width: '100%',
//             data: data
//         });
//     });

//     // Load provinces
//     $.getJSON(window.geoRoutes.provinces.replace('__REGION__',''), function(data) {
//         provinceSelect.data('all', data);
//         provinceSelect.select2({
//             placeholder: 'Select Province',
//             width: '100%'
//         });
//         populateSelect(provinceSelect, data, 'Select Province');
//     });

//     // Load municipalities
//     $.getJSON(window.geoRoutes.municipalities.replace('__PROVINCE__',''), function(data) {
//         municipalitySelect.data('all', data);
//         municipalitySelect.select2({
//             placeholder: 'Select Municipality',
//             width: '100%'
//         });
//         populateSelect(municipalitySelect, data, 'Select Municipality');
//     });

//     // Region change -> filter provinces
//     regionSelect.on('change', function() {
//         var regionCode = regionSelect.val();
//         var allProvinces = provinceSelect.data('all') || [];
//         var filtered = allProvinces.filter(p => !regionCode || p.regCode == regionCode);

//         populateSelect(provinceSelect, filtered, 'Select Province');
//         populateSelect(municipalitySelect, [], 'Select Municipality'); // reset municipalities
//     });

//     // Province change -> filter municipalities and set higher tier
//     provinceSelect.on('change', function() {
//         var provinceCode = provinceSelect.val();
//         var allMunis = municipalitySelect.data('all') || [];
//         var filtered = allMunis.filter(m => !provinceCode || m.provCode == provinceCode);

//         populateSelect(municipalitySelect, filtered, 'Select Municipality');

//         // Auto-set region to match province
//         var allProvinces = provinceSelect.data('all') || [];
//         var parentProvince = allProvinces.find(p => p.id == provinceCode);
//         if (parentProvince && regionSelect.val() !== parentProvince.regCode) {
//             regionSelect.val(parentProvince.regCode).trigger('change.select2');
//         }
//     });

//     // Municipality change -> auto-set province & region
//     municipalitySelect.on('change', function() {
//         var muniId = municipalitySelect.val();
//         var allMunis = municipalitySelect.data('all') || [];
//         var selectedMuni = allMunis.find(m => m.id == muniId);

//         if (selectedMuni) {
//             // Set province
//             if (provinceSelect.val() !== selectedMuni.provCode) {
//                 provinceSelect.val(selectedMuni.provCode).trigger('change.select2');
//             }

//             // Set region
//             var allProvinces = provinceSelect.data('all') || [];
//             var parentProvince = allProvinces.find(p => p.id == selectedMuni.provCode);
//             if (parentProvince && regionSelect.val() !== parentProvince.regCode) {
//                 regionSelect.val(parentProvince.regCode).trigger('change.select2');
//             }
//         }
//     });
// };
