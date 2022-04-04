$(function() {
    $( "#client_status" ).change(function() {
        var clientStatus = $("#client_status" ).val();
        $.ajax({
            type: "GET",
            url: "filters/" + clientStatus,
            success: function(resultData){
                $('#client_name').find('option').remove();
                $('#client_name').append($('<option>', {
                    value: 0,
                    text : 'Select an option'
                }));
                $.each(resultData, function (i, item) {
                    $('#client_name').append($('<option>', {
                        value: item.id,
                        text : item.client_name
                    }));
                });
            }
        });
    });
});
