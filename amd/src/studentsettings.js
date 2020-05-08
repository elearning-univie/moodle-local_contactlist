define(['jquery', 'core/modal_factory', 'core/templates', 'core/modal_events', 'core/ajax', 'core/notification'],
    function ($, ModalFactory, Templates, ModalEvents, Ajax, Notification) {
        return {
            init: function (courseid) {
                //$.local_contactlist_create_modal = function (courseid) {
                    ModalFactory.create({
                        type: ModalFactory.types.SAVE_CANCEL,
                        body: Templates.render('local_contactlist/studentsettingsmodal', {})
                    }).then(function (modal) {
                        modal.getRoot().on(ModalEvents.save, function (e) {
                            e.preventDefault();
                            var updateval = $('#localcontactlist-visible').is(":checked") ? 1 : 0;
                            Ajax.call([{
                                methodname: 'localcontactlist_update_settings',
                                args: {courseid: courseid, updateval: updateval},
                                done: function (result) {
                                    window.console.log(result);
                                    modal.hide();
                                },
                                fail: Notification.exception
                            }]);
                        });
                        modal.show();
                    });
                //};
            }
        };
    });