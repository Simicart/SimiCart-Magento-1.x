/**
 * Created by frank on 1/10/18.
 */

$j = jQuery.noConflict();




$j(document).ready(function () {

    $j('select[name=country]').change(function () {
        console.log('You selected country' + $j('#deviceGrid_filter_country').val());
        change_country($j('#deviceGrid_filter_country').val());
    });

    function change_country(code) {
        var url = $j('#span_hidden_simi').text();

        $j.ajax(
            {
                url: url,
                method: 'GET',
                data: {country_code: code, is_state: 1},
                success: function ($result) {
                    $j('select[name=city]').children().remove();
                    $j('select[name=city]').append($result);
                }

            }
        );
    }

    $j('select[name=city]').change(function () {
        change_city($j('select[name=city]').val());
    });

    function change_city(cityCode) {
        var url = $j('#span_hidden_simi').text();
        var countryCode = $j('#deviceGrid_filter_country').val();
        $j.ajax(
            {
                url: url,
                data: {is_state: 0, country_code: countryCode, city_code: cityCode},
                success: function (result) {
                    $j('select[name=state]').children().remove();
                    $j('select[name=state]').append(result);
                }
            }
        );
    }

});
