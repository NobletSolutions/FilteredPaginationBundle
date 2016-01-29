$(document).ready(function()
{
    var limit = GetParameterValues('limit');
    if (limit) {
        $('#limit_select_limit').val(limit);
    }

    $('#limit_select_limit').on('change', function () {
        var limit = GetParameterValues('limit');
        if (limit) {
            var currentUrl = window.location.href;
            currentUrl = currentUrl.replace(/(limit=)[^\&]+/, '$1' + this.value);
            window.location.href = currentUrl;
        } else {
            var query = window.location.search;
            var url = window.location.href;
            if (query) {
                var redirect_url = url + '&limit=' + this.value;
            } else {
                var redirect_url = url + '?limit=' + this.value;
            }
            window.location.href = redirect_url;
        }
    });

    function GetParameterValues(param) {
        var url = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for (var i = 0; i < url.length; i++) {
            var urlparam = url[i].split('=');
            if (urlparam[0] == param) {
                return urlparam[1];
            }
        }
    }
});
