define(['jquery', 'core/modal_factory', 'core/templates', 'core/modal_events', 'core/ajax', 'core/notification', 'core/str'],
    function ($, ModalFactory, Templates, ModalEvents, Ajax, Notification, Str) {
        return {
            init: function (courseid) {
                ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: Str.get_string('localvisibility_help', 'local_contactlist'),
                    body: Templates.render('local_contactlist/studentsettingsmodal', {})
                }).then(function (modal) {
                    modal.getRoot().on(ModalEvents.save, function (e) {
                        e.preventDefault();
                        var updateval = $('#localcontactlist-visible').is(":checked") ? 1 : 0;
                        Ajax.call([{
                            methodname: 'localcontactlist_update_settings',
                            args: {courseid: courseid, updateval: updateval},
                            done: function () {
                                modal.hide();
                                location.reload();
                            },
                            fail: Notification.exception
                        }]);
                    });
                    modal.show();
              $('#local-contactlist-toggle')[0].addEventListener("click", function(){
                  var checkbox = $('#localcontactlist-visible')[0];
                  if (checkbox.checked) {
                      checkbox.checked = false;
                  } else {
                      checkbox.checked = true;
                  }
              });
                });
            }
        };
    });