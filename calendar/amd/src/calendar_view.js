// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This module is responsible for handle calendar day and upcoming view.
 *
 * @module     core_calendar/calendar
 * @package    core_calendar
 * @copyright  2017 Simey Lameze <simey@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([
        'jquery',
        'core/str',
        'core/notification',
        'core_calendar/selectors',
        'core_calendar/events',
        'core_calendar/view_manager',
        'core_calendar/repository',
        'core/modal_factory',
        'core_calendar/modal_event_form',
        'core/modal_events',
        'core_calendar/modal_delete'
    ],
    function(
        $,
        Str,
        Notification,
        CalendarSelectors,
        CalendarEvents,
        CalendarViewManager,
        CalendarRepository,
        ModalFactory,
        ModalEventForm,
        ModalEvents,
        ModalDelete
    ) {


        /**
         * Prepares the action for the summary modal's delete action.
         *
         * @param {} summaryModal The summary modal instance.
         * @param {Number} summaryModal The summary modal instance.
         * @param {ModalEventSummary} summaryModal The summary modal instance.
         * @method showDeleteModal
         */
        function showDeleteModal(eventId, eventTitle, eventCount) {

            deleteModalPromise.then(function(modal) {

                var deleteStrings = [
                    {
                        key: 'deleteevent',
                        component: 'calendar'
                    },
                    {
                        key: 'confirmeventdelete',
                        component: 'calendar',
                        param: eventTitle
                    }
                ];

                Str.get_strings(deleteStrings).then(function (strings) {
                    modal.setTitle(strings[0]);
                    modal.setBody(strings[1]);
                    modal.setSaveButtonText(strings[0]);
                    modal.show();
                });

                modal.getRoot().on(ModalEvents.save, function() {
                    CalendarRepository.deleteEvent(eventId).then(function() {
                        $('body').trigger(CalendarEvents.deleted, [eventId, false]);

                        return;
                    }).catch(Notification.exception);
                });

                return;
            });
        }

        /**
         * Create the event form modal for creating new events and
         * editing existing events.
         *
         * @method registerEventFormModal
         * @param {object} root The calendar root element
         * @param {object} newEventButton The new event button element
         * @return {object} The create modal promise
         */
        var registerEventFormModal = function(root, newEventButton) {
            var contextId = newEventButton.data('context-id');
            return ModalFactory.create(
                {
                    type: ModalEventForm.TYPE,
                    large: true,
                    templateContext: {
                        contextid: contextId
                    }
                }, [root, CalendarSelectors.newEventButton]
            );
        };

        /**
         * Create the event form modal for creating new events and
         * editing existing events.
         *
         * @method registerEventFormModal
         * @param {object} deleteLink The delete link element
         * @return {object} The create modal promise
         */
        var registerDeleteEventModal = function(deleteLink) {
            return [
             ModalFactory.create(
                {
                    type: ModalFactory.types.SAVE_CANCEL
                },
                deleteLink
            ),
                ModalFactory.create(
                    {
                        type: ModalDelete.TYPE
                    },
                    deleteLink
                )
            ]
        };
        /**
         * Prepares the action for the summary modal's delete action.
         *
         * @param {ModalEventSummary} summaryModal The summary modal instance.
         * @param {string} eventTitle The event title.
         */
        function prepareDeleteAction(eventId, eventTitle, eventCount) {
            console.log(eventCount);
            var deleteStrings = [
                {
                    key: 'deleteevent',
                    component: 'calendar'
                },
            ];

            //var eventCount = parseInt(summaryModal.getEventCount(), 10);
            var deletePromise;
            var isRepeatedEvent = eventCount > 1;
            if (isRepeatedEvent) {
                deleteStrings.push({
                    key: 'confirmeventseriesdelete',
                    component: 'calendar',
                    param: {
                        name: eventTitle,
                        count: eventCount,
                    },
                });

                deletePromise = ModalFactory.create(
                    {
                        type: ModalDelete.TYPE
                    },
                    $('[data-action="delete"]')
                );
            } else {
                deleteStrings.push({
                    key: 'confirmeventdelete',
                    component: 'calendar',
                    param: eventTitle
                });

                deletePromise = ModalFactory.create(
                    {
                        type: ModalFactory.types.SAVE_CANCEL
                    },
                    $('[data-action="delete"]')
                );
            }

            //var eventId = summaryModal.getEventId();
            var stringsPromise = Str.get_strings(deleteStrings);

            $.when(stringsPromise, deletePromise)
                .then(function(strings, deleteModal) {
                    deleteModal.setTitle(strings[0]);
                    deleteModal.setBody(strings[1]);
                    if (!isRepeatedEvent) {
                        deleteModal.setSaveButtonText(strings[0]);
                    }

                    deleteModal.getRoot().on(ModalEvents.save, function() {
                        CalendarRepository.deleteEvent(eventId, false)
                            .then(function() {
                                $('body').trigger(CalendarEvents.deleted, [eventId, false]);
                                return;
                            })
                            .catch(Notification.exception);
                    });

                    deleteModal.getRoot().on(CalendarEvents.deleteAll, function() {
                        CalendarRepository.deleteEvent(eventId, true)
                            .then(function() {
                                $('body').trigger(CalendarEvents.deleted, [eventId, true]);
                                return;
                            })
                            .catch(Notification.exception);
                    });

                    return deleteModal;
                })
                .fail(Notification.exception);
        }

        var registerEventListeners = function(root) {
            var deleteLink = $(CalendarSelectors.deleteLink),
                newEventButton = $(CalendarSelectors.newEventButton),
                body = $('body');
            var courseId = 1;

            var eventFormPromise = registerEventFormModal(root, newEventButton);
            //var deleteModalPromise = registerDeleteEventModal(deleteLink);

            root.on('click', CalendarSelectors.editLink, function(e) {
                e.preventDefault();
                var target = $(e.currentTarget);

                eventFormPromise.then(function(modal) {
                    // When something within the calendar tells us the user wants
                    // to edit an event then show the event form modal.
                    var eventId = target.data('event-id');
                    if (eventId) {
                        modal.setEventId(eventId);
                    }
                    modal.show();
                    return;
                }).fail(Notification.exception);
            });

            root.on('click', CalendarSelectors.deleteLink, function(e) {
                e.preventDefault();

                var target = $(e.currentTarget),
                    eventId = target.data('event-id'),
                    eventTitle = target.data('title'),
                    eventCount = target.data('event-event-count');

                prepareDeleteAction(eventId, eventTitle, eventCount);

            });

            root.on('change', CalendarSelectors.courseSelector, function() {
                var selectElement = $(this);
                var courseId = selectElement.val();
                CalendarViewManager.reloadCurrentUpcoming(root, courseId)
                    .then(function() {
                        // We need to get the selector again because the content has changed.
                        return root.find(CalendarSelectors.courseSelector).val(courseId);
                    })
                    .fail(Notification.exception);
            });

            body.on(CalendarEvents.created, function() {
                CalendarViewManager.reloadCurrentUpcoming(root, courseId);
            });

            body.on(CalendarEvents.updated, function() {
                CalendarViewManager.reloadCurrentUpcoming(root, courseId);
            });

            body.on(CalendarEvents.deleted, function() {
                CalendarViewManager.reloadCurrentUpcoming(root, courseId);
            });

            body.on(CalendarEvents.filterChanged, function(e, data) {
                var daysWithEvent = root.find(CalendarSelectors.eventType[data.type]);
                if (data.hidden == true) {
                    daysWithEvent.addClass('hidden');
                } else {
                    daysWithEvent.removeClass('hidden');
                }
            });
        };

        return {
            init: function(root) {
                root = $(root);

                CalendarViewManager.init(root);
                registerEventListeners(root);
            }
        };
    });
