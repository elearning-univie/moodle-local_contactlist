define(['jquery', 'core/modal_factory', 'core/templates', 'core/modal_events', 'core/ajax', 'core/notification'],
    function ($, ModalFactory, Templates, ModalEvents, Ajax, Notification) {
    var trigger = $('#create-modal');
    ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        title: 'test title',
        body: Templates.render('local_contactlist/studentsettingsmodal', {})
    }, trigger)
        .done(function (modal) {
            modal.getRoot().on(ModalEvents.save, function (e) {
                e.preventDefault();
                var updateval = $('#localcontactlist-list').is(":checked") ? 1 : 0;
                Ajax.call([{
                    methodname: 'localcontactlist_update_settings',
                    args: {updateval: updateval},
                    done: function (result) {
                        window.console.log(result);
                    },
                    fail: Notification.exception
                }]);
            });
        });
});