define(['jquery'],
    function ($) {
        return {
            init: function () {
                  alert($('#local-contactlist-toggle'));
                 $('#local-contactlist-toggle').onclick = function(){
                     var checkbox = $('#localcontactlist-visible');
                     alert("test2");
                     if (checkbox.checked) {
                         checkbox.checked = false;
                     } else {
                         checkbox.checked = true;
                     }
                 };
            }
        };
    });