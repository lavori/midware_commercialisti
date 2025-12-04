"use strict";

!function (NioApp, $) {
    "use strict";

    // Variabili globali del tema
    var $win = $(window),
        $body = $('body'),
        breaks = NioApp.Break;

    // >>>>> VERSIONE SAFE <<<<<
    NioApp.Calendar = function () {
        // Trova un container valido del calendario
        var calendarEl =
            document.getElementById('calendar') ||
            document.querySelector('[data-calendar]') ||
            document.querySelector('.js-calendar');

        // Se il container NON esiste, esci senza inizializzare
        if (!calendarEl) {
            // console.debug('NioApp.Calendar: nessun container, skip init');
            return;
        }

        // Se FullCalendar non è caricato, esci
        if (typeof window.FullCalendar === 'undefined' || !FullCalendar.Calendar) {
            // console.warn('NioApp.Calendar: FullCalendar non presente');
            return;
        }

        // --- Date helper (come prima) ---
        var today = new Date();
        var dd = String(today.getDate()).padStart(2, '0');
        var mm = String(today.getMonth() + 1).padStart(2, '0');
        var yyyy = today.getFullYear();
        var tomorrow = new Date(today);     tomorrow.setDate(today.getDate() + 1);
        var t_dd = String(tomorrow.getDate()).padStart(2, '0');
        var t_mm = String(tomorrow.getMonth() + 1).padStart(2, '0');
        var t_yyyy = tomorrow.getFullYear();
        var yesterday = new Date(today);    yesterday.setDate(today.getDate() - 1);
        var y_dd = String(yesterday.getDate()).padStart(2, '0');
        var y_mm = String(yesterday.getMonth() + 1).padStart(2, '0');
        var y_yyyy = yesterday.getFullYear();
        var YM = yyyy + '-' + mm;
        var YESTERDAY = y_yyyy + '-' + y_mm + '-' + y_dd;
        var TODAY = yyyy + '-' + mm + '-' + dd;
        var TOMORROW = t_yyyy + '-' + t_mm + '-' + t_dd;
        var month = ["Gennaio","Febbraio","Marzo","Aprile","Maggio","Giugno","Luglio","Agosto","Settembre","Ottobre","Novembre","Dicembre"];

        // Altri riferimenti del template (possono non esistere; jQuery gestisce set vuoti)
        var eventsEl = document.getElementById('externalEvents');
        var removeEvent = document.getElementById('removeEvent');
        var addEventBtn = $('#addEvent');
        var addEventForm = $('#addEventForm');
        var addEventPopup = $('#addEventPopup');
        var updateEventBtn = $('#updateEvent');
        var editEventForm = $('#editEventForm');
        var editEventPopup = $('#editEventPopup');
        var previewEventPopup = $('#previewEventPopup');
        var deleteEventBtn = $('#deleteEvent');

        // Dati eventi passati da PHP
        var phpEvents = Array.isArray(window.calendarEventsFromPHP) ? window.calendarEventsFromPHP : [];

        // --- Inizializzazione FullCalendar (come prima) ---
        var calendar = new FullCalendar.Calendar(calendarEl, {
            locale: 'it',
            timeZone: 'UTC',
            initialView: 'listWeek',
            themeSystem: 'bootstrap5',
            headerToolbar: {
                left: 'title prev,next',
                center: null,
                right: 'today listWeek,dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                today: 'Oggi',
                dayGridMonth: 'Mensile',
                timeGridWeek: 'Settimanale',
                timeGridDay: 'Giornaliero',
                listWeek: 'Lista'
            },
            noEventsText: typeof window.calendarNoEventsText !== 'undefined'
                ? window.calendarNoEventsText
                : 'Nessun evento da visualizzare',
            height: 800,
            contentHeight: 780,
            aspectRatio: 3,
            editable: false,
            droppable: true,
            views: {
                dayGridMonth: { dayMaxEventRows: 2 },
                listWeek: { allDayText: '' }
            },
            direction: NioApp.State.isRTL ? "rtl" : "ltr",
            nowIndicator: true,
            now: TODAY + 'T09:25:00',

            eventMouseEnter: function (info) {
                if (info.view.type.startsWith('list')) return;
                var elm = info.el,
                    title = info.event._def.title,
                    content = info.event._def.extendedProps.description;
                if (content) {
                    var fcPopover = new bootstrap.Popover(elm, {
                        template: '<div class="popover event-popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
                        title: title,
                        content: content || '',
                        placement: 'top'
                    });
                    fcPopover.show();
                }
            },
            eventMouseLeave: function () { removePopover(); },
            eventDragStart: function () { removePopover(); },

            // Badge con importo nella vista lista
            eventContent: function(arg) {
                if (arg.view.type.startsWith('list')) {
                    var props = arg.event.extendedProps;
                    if (props.importoRata) {
                        var importo = parseFloat(props.importoRata)
                            .toLocaleString('it-IT', { style: 'currency', currency: 'EUR' });
                        var html =
                            '<div class="d-flex justify-content-between w-100 align-items-center">'+
                            '<span>'+ arg.event.title +'</span>'+
                            '<span class="badge rounded-pill bg-light text-dark fw-normal">'+ importo +'</span>'+
                            '</div>';
                        return { html: html };
                    }
                }
            },

            eventClick: function (info) {
                var title = info.event._def.title;
                var description = info.event._def.extendedProps.description;
                var start = info.event._instance.range.start;
                var startDate = start.getFullYear() + '-' + String(start.getMonth()+1).padStart(2,'0') + '-' + String(start.getDate()).padStart(2,'0');
                var startTime = start.toUTCString().split(' '); startTime = startTime[startTime.length - 2]; startTime = startTime == '00:00:00' ? '' : startTime;
                var end = info.event._instance.range.end;
                var endDate = end.getFullYear() + '-' + String(end.getMonth()+1).padStart(2,'0') + '-' + String(end.getDate()).padStart(2,'0');
                var endTime = end.toUTCString().split(' '); endTime = endTime[endTime.length - 2]; endTime = endTime == '00:00:00' ? '' : endTime;
                var className = info.event._def.ui.classNames[0].slice(3);
                var eventId = info.event._def.publicId;

                $('#edit-event-title').val(title);
                $('#edit-event-start-date').val(startDate).datepicker('update');
                $('#edit-event-end-date').val(endDate).datepicker('update');
                $('#edit-event-start-time').val(startTime);
                $('#edit-event-end-time').val(endTime);
                $('#edit-event-description').val(description);
                $('#edit-event-theme').val(className).trigger('change.select2');
                editEventForm.attr('data-id', eventId);

                var meseInizio = month[start.getMonth()];
                var meseFine   = month[end.getMonth()];
                var previewStart = String(start.getDate()).padStart(2,'0') + ' ' + (meseInizio.charAt(0).toUpperCase()+meseInizio.slice(1)) + ' ' + start.getFullYear() + (startTime ? ' - ' + to12(startTime) : '');
                var previewEnd   = String(end.getDate()).padStart(2,'0')   + ' ' + (meseFine.charAt(0).toUpperCase()+meseFine.slice(1))   + ' ' + end.getFullYear()   + (endTime ? ' - ' + to12(endTime)   : '');
                $('#preview-event-title').text(title);
                $('#preview-event-header').addClass('fc-' + className);
                $('#preview-event-start').text(previewStart);
                $('#preview-event-end').text(previewEnd);
                $('#preview-event-description').text(description);
                if (!description) $('#preview-event-description-check').css('display','none');
                removePopover();
                document.querySelectorAll('.fc-more-popover').forEach(function (elm) { elm.remove(); });
                $('#previewEventPopup').modal('show');
            },

            // Eventi dal PHP
            events: phpEvents
        });

        calendar.render();

        // --- Azioni popup (funzionano anche se i bottoni non esistono: jQuery opera su set vuoti) ---
        addEventBtn.on("click", function (e) {
            e.preventDefault();
            var eventTitle = $('#event-title').val();
            var eventStartDate = $('#event-start-date').val();
            var eventEndDate = $('#event-end-date').val();
            var eventStartTime = $('#event-start-time').val();
            var eventEndTime = $('#event-end-time').val();
            var eventDescription = $('#event-description').val();
            var eventTheme = $('#event-theme').val();
            var eventStartTimeCheck = eventStartTime ? 'T' + eventStartTime + 'Z' : '';
            var eventEndTimeCheck   = eventEndTime   ? 'T' + eventEndTime   + 'Z' : '';
            calendar.addEvent({
                id: 'added-event-id-' + Math.floor(Math.random() * 9999999),
                title: eventTitle,
                start: eventStartDate + eventStartTimeCheck,
                end:   eventEndDate   + eventEndTimeCheck,
                className: "fc-" + eventTheme,
                description: eventDescription
            });
            addEventPopup.modal('hide');
        });

        updateEventBtn.on("click", function (e) {
            e.preventDefault();
            var eventTitle = $('#edit-event-title').val();
            var eventStartDate = $('#edit-event-start-date').val();
            var eventEndDate = $('#edit-event-end-date').val();
            var eventStartTime = $('#edit-event-start-time').val();
            var eventEndTime = $('#edit-event-end-time').val();
            var eventDescription = $('#edit-event-description').val();
            var eventTheme = $('#edit-event-theme').val();
            var eventStartTimeCheck = eventStartTime ? 'T' + eventStartTime + 'Z' : '';
            var eventEndTimeCheck   = eventEndTime   ? 'T' + eventEndTime   + 'Z' : '';
            var selectEvent = calendar.getEventById(editEventForm[0] ? editEventForm[0].dataset.id : '');
            if (selectEvent) selectEvent.remove();
            calendar.addEvent({
                id: editEventForm[0] ? editEventForm[0].dataset.id : ('edited-event-id-' + Math.floor(Math.random()*9999999)),
                title: eventTitle,
                start: eventStartDate + eventStartTimeCheck,
                end:   eventEndDate   + eventEndTimeCheck,
                className: "fc-" + eventTheme,
                description: eventDescription
            });
            editEventPopup.modal('hide');
        });

        deleteEventBtn.on("click", function (e) {
            e.preventDefault();
            var selectEvent = calendar.getEventById(editEventForm[0] ? editEventForm[0].dataset.id : '');
            if (selectEvent) selectEvent.remove();
        });

        function removePopover() {
            document.querySelectorAll('.event-popover').forEach(function (elm) { elm.remove(); });
        }
        function to12(time) {
            time = time.toString().match(/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];
            if (time.length > 1) {
                time = time.slice(1); time.pop();
                time[5] = +time[0] < 12 ? ' AM' : ' PM';
                time[0] = +time[0] % 12 || 12;
            }
            return time.join('');
        }

        function customCalSelect(cat) {
            if (!cat.id) return cat.text;
            var $cat = $('<span class="fc-' + cat.element.value + '"> <span class="dot"></span>' + cat.text + '</span>');
            return $cat;
        }
        NioApp.Select2('.select-calendar-theme', { templateResult: customCalSelect });

        addEventPopup.on('hidden.bs.modal', function () {
            setTimeout(function () {
                $('#addEventForm input,#addEventForm textarea').val('');
                $('#event-theme').val('event-primary').trigger('change.select2');
            }, 1000);
        });
        previewEventPopup.on('hidden.bs.modal', function () {
            $('#preview-event-header').removeClass().addClass('modal-header');
        });
    };

    // Esegue solo a docReady; la nostra guardia iniziale evita errori quando il container manca
    NioApp.coms.docReady.push(NioApp.Calendar);

}(NioApp, jQuery);

/*"use strict";

!function (NioApp, $) {
  "use strict";

  // Variable
  var $win = $(window),
    $body = $('body'),
    breaks = NioApp.Break;
  NioApp.Calendar = function () {
    var today = new Date();
    var dd = String(today.getDate()).padStart(2, '0');
    var mm = String(today.getMonth() + 1).padStart(2, '0');
    var yyyy = today.getFullYear();
    var tomorrow = new Date(today);
    tomorrow.setDate(today.getDate() + 1);
    var t_dd = String(tomorrow.getDate()).padStart(2, '0');
    var t_mm = String(tomorrow.getMonth() + 1).padStart(2, '0');
    var t_yyyy = tomorrow.getFullYear();
    var yesterday = new Date(today);
    yesterday.setDate(today.getDate() - 1);
    var y_dd = String(yesterday.getDate()).padStart(2, '0');
    var y_mm = String(yesterday.getMonth() + 1).padStart(2, '0');
    var y_yyyy = yesterday.getFullYear();
    var YM = yyyy + '-' + mm;
    var YESTERDAY = y_yyyy + '-' + y_mm + '-' + y_dd;
    var TODAY = yyyy + '-' + mm + '-' + dd;
    var TOMORROW = t_yyyy + '-' + t_mm + '-' + t_dd;
    var month = ["Gennaio", "Febbraio", "Marzo", "Aprile", "Maggio", "Giugno", "Luglio", "Agosto", "Settembre", "Ottobre", "Novembre", "Dicembre"];
    
    var calendarEl = document.getElementById('calendar');
    var eventsEl = document.getElementById('externalEvents');
    var removeEvent = document.getElementById('removeEvent');
    var addEventBtn = $('#addEvent');
    var addEventForm = $('#addEventForm');
    var addEventPopup = $('#addEventPopup');
    var updateEventBtn = $('#updateEvent');
    var editEventForm = $('#editEventForm');
    var editEventPopup = $('#editEventPopup');
    var previewEventPopup = $('#previewEventPopup');
    var deleteEventBtn = $('#deleteEvent');
    //var mobileView = NioApp.Win.width < NioApp.Break.md ? true : false;
    var calendar = new FullCalendar.Calendar(calendarEl, {
      locale: 'it',
      timeZone: 'UTC',
      initialView: 'listWeek',
      themeSystem: 'bootstrap5',
      headerToolbar: {
        left: 'title prev,next',
        center: null,
        right: 'today listWeek,dayGridMonth,timeGridWeek,timeGridDay'
      },
      buttonText: {
        today: 'Oggi',
        dayGridMonth: 'Mensile',
        timeGridWeek: 'Settimanale',
        timeGridDay: 'Giornaliero',
        listWeek: 'Lista'
      },
      noEventsText: typeof window.calendarNoEventsText !== 'undefined' ? window.calendarNoEventsText : 'Nessun evento da visualizzare',
      height: 800,
      contentHeight: 780,
      aspectRatio: 3,
      editable: false,
      droppable: true,
      views: {
        dayGridMonth: {
          dayMaxEventRows: 2,
        },
        listWeek: {
            allDayText: ''
        }
      },
      direction: NioApp.State.isRTL ? "rtl" : "ltr",
      nowIndicator: true,
      now: TODAY + 'T09:25:00',
      eventMouseEnter: function eventMouseEnter(info) {
        if (info.view.type.startsWith('list')) {
            return; 
        }
        var elm = info.el,
          title = info.event._def.title,
          content = info.event._def.extendedProps.description;
        if (content) {
          var fcPopover = new bootstrap.Popover(elm, {
            template: '<div class="popover event-popover"><div class="popover-arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>',
            title: title,
            content: content ? content : '',
            placement: 'top'
          });
          fcPopover.show();
        }
      },
      eventMouseLeave: function eventMouseLeave() {
        removePopover();
      },
      eventDragStart: function eventDragStart() {
        removePopover();
      },
      eventContent: function(arg) {
        // Solo per la vista a lista, personalizza il contenuto
        if (arg.view.type.startsWith('list')) {
            let props = arg.event.extendedProps;
            
            // Controlla se è un evento di tipo "rata" (verificando la presenza di importoRata)
            if (props.importoRata) {
                // Formatta l'importo come valuta
                let importo = parseFloat(props.importoRata).toLocaleString('it-IT', { style: 'currency', currency: 'EUR' });
                
                let html = `
                    <div class="d-flex justify-content-between w-100 align-items-center">
                        <span>${arg.event.title}</span>
                        <span class="badge rounded-pill bg-light text-dark fw-normal">${importo}</span>
                    </div>
                `;
                return { html: html };
            }
        }
        // Per tutte le altre viste, usa il rendering di default (non ritornare nulla)
      },
      eventClick: function eventClick(info) {
        // Get data
        var title = info.event._def.title;
        var description = info.event._def.extendedProps.description;
        var start = info.event._instance.range.start;
        var startDate = start.getFullYear() + '-' + String(start.getMonth() + 1).padStart(2, '0') + '-' + String(start.getDate()).padStart(2, '0');
        var startTime = start.toUTCString().split(' ');
        startTime = startTime[startTime.length - 2];
        startTime = startTime == '00:00:00' ? '' : startTime;
        var end = info.event._instance.range.end;
        var endDate = end.getFullYear() + '-' + String(end.getMonth() + 1).padStart(2, '0') + '-' + String(end.getDate()).padStart(2, '0');
        var endTime = end.toUTCString().split(' ');
        endTime = endTime[endTime.length - 2];
        endTime = endTime == '00:00:00' ? '' : endTime;
        var className = info.event._def.ui.classNames[0].slice(3);
        var eventId = info.event._def.publicId;

        //Set data in eidt form
        $('#edit-event-title').val(title);
        $('#edit-event-start-date').val(startDate).datepicker('update');
        $('#edit-event-end-date').val(endDate).datepicker('update');
        $('#edit-event-start-time').val(startTime);
        $('#edit-event-end-time').val(endTime);
        $('#edit-event-description').val(description);
        $('#edit-event-theme').val(className);
        $('#edit-event-theme').trigger('change.select2');
        editEventForm.attr('data-id', eventId);

        // Set data in preview
        var meseInizio = month[start.getMonth()];
        var meseFine = month[end.getMonth()];
        var previewStart = String(start.getDate()).padStart(2, '0') + ' ' + (meseInizio.charAt(0).toUpperCase() + meseInizio.slice(1)) + ' ' + start.getFullYear() + (startTime ? ' - ' + to12(startTime) : '');
        var previewEnd = String(end.getDate()).padStart(2, '0') + ' ' + (meseFine.charAt(0).toUpperCase() + meseFine.slice(1)) + ' ' + end.getFullYear() + (endTime ? ' - ' + to12(endTime) : '');
        $('#preview-event-title').text(title);
        $('#preview-event-header').addClass('fc-' + className);
        $('#preview-event-start').text(previewStart);
        $('#preview-event-end').text(previewEnd);
        $('#preview-event-description').text(description);
        !description ? $('#preview-event-description-check').css('display', 'none') : null;
        removePopover();
        var fcMorePopover = document.querySelectorAll('.fc-more-popover');
        fcMorePopover && fcMorePopover.forEach(function (elm) {
          elm.remove();
        });
        previewEventPopup.modal('show');
      },
      /*
      events: [{
        id: 'default-event-id-' + Math.floor(Math.random() * 9999999),
        title: 'Reader will be distracted',
        start: YM + '-03T13:30:00',
        className: "fc-event-danger",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden."
      }, {
        id: 'default-event-id-' + Math.floor(Math.random() * 9999999),
        title: 'Rabfov va hezow.',
        start: YM + '-14T13:30:00',
        end: YM + '-14',
        className: "fc-event-success",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden."
      }, {
        id: 'default-event-id-' + Math.floor(Math.random() * 9999999),
        title: 'The leap into electronic',
        start: YM + '-05',
        end: YM + '-06',
        className: "fc-event-primary",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden."
      }, {
        id: 'default-event-id-' + Math.floor(Math.random() * 9999999),
        title: 'Lorem Ipsum passage - Product Release',
        start: YM + '-02',
        end: YM + '-04',
        className: "fc-event-primary",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden."
      }, {
        title: 'Gibmuza viib hepobe.',
        start: YM + '-12',
        end: YM + '-10',
        className: "fc-event-pink-dim",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden."
      }, {
        id: 'default-event-id-' + Math.floor(Math.random() * 9999999),
        title: 'Jidehse gegoj fupelone.',
        start: YM + '-07T16:00:00',
        className: "fc-event-danger-dim",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden."
      }, {
        id: 'default-event-id-' + Math.floor(Math.random() * 9999999),
        title: 'Ke uzipiz zip.',
        start: YM + '-16T16:00:00',
        end: YM + '-14',
        className: "fc-event-info-dim",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden."
      }, {
        id: 'default-event-id-' + Math.floor(Math.random() * 9999999),
        title: 'Piece of classical Latin literature',
        start: TODAY,
        end: TODAY + '-01',
        className: "fc-event-primary",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden."
      }, {
        id: 'default-event-id-' + Math.floor(Math.random() * 9999999),
        title: 'Nogok kewwib ezidbi.',
        start: TODAY + 'T10:00:00',
        className: "fc-event-info",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden."
      }, {
        id: 'default-event-id-' + Math.floor(Math.random() * 9999999),
        title: 'Mifebi ik cumean.',
        start: TODAY + 'T14:30:00',
        className: "fc-event-warning-dim",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden."
      }, {
        id: 'default-event-id-' + Math.floor(Math.random() * 9999999),
        title: 'Play Time',
        start: TODAY + 'T17:30:00',
        className: "fc-event-info",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden."
      }, {
        id: 'default-event-id-' + Math.floor(Math.random() * 9999999),
        title: 'Rujfogve kabwih haznojuf.',
        start: YESTERDAY + 'T05:00:00',
        className: "fc-event-danger",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden."
      }, {
        id: 'default-event-id-' + Math.floor(Math.random() * 9999999),
        title: 'simply dummy text of the printing',
        start: YESTERDAY + 'T07:00:00',
        className: "fc-event-primary-dim",
        description: "Use a passage of Lorem Ipsum, you need to be sure there isn't anything embarrassing hidden."
      }]*/
/*
     // Utilizza gli eventi passati da PHP, o un array vuoto come fallback
      events: typeof window.calendarEventsFromPHP !== 'undefined' ? window.calendarEventsFromPHP : []
    });
    calendar.render();

    //Add event

    addEventBtn.on("click", function (e) {
      e.preventDefault();
      var eventTitle = $('#event-title').val();
      var eventStartDate = $('#event-start-date').val();
      var eventEndDate = $('#event-end-date').val();
      var eventStartTime = $('#event-start-time').val();
      var eventEndTime = $('#event-end-time').val();
      var eventDescription = $('#event-description').val();
      var eventTheme = $('#event-theme').val();
      var eventStartTimeCheck = eventStartTime ? 'T' + eventStartTime + 'Z' : '';
      var eventEndTimeCheck = eventEndTime ? 'T' + eventEndTime + 'Z' : '';
      calendar.addEvent({
        id: 'added-event-id-' + Math.floor(Math.random() * 9999999),
        title: eventTitle,
        start: eventStartDate + eventStartTimeCheck,
        end: eventEndDate + eventEndTimeCheck,
        className: "fc-" + eventTheme,
        description: eventDescription
      });
      addEventPopup.modal('hide');
    });
    updateEventBtn.on("click", function (e) {
      e.preventDefault();
      var eventTitle = $('#edit-event-title').val();
      var eventStartDate = $('#edit-event-start-date').val();
      var eventEndDate = $('#edit-event-end-date').val();
      var eventStartTime = $('#edit-event-start-time').val();
      var eventEndTime = $('#edit-event-end-time').val();
      var eventDescription = $('#edit-event-description').val();
      var eventTheme = $('#edit-event-theme').val();
      var eventStartTimeCheck = eventStartTime ? 'T' + eventStartTime + 'Z' : '';
      var eventEndTimeCheck = eventEndTime ? 'T' + eventEndTime + 'Z' : '';
      var selectEvent = calendar.getEventById(editEventForm[0].dataset.id);
      selectEvent.remove();
      calendar.addEvent({
        id: editEventForm[0].dataset.id,
        title: eventTitle,
        start: eventStartDate + eventStartTimeCheck,
        end: eventEndDate + eventEndTimeCheck,
        className: "fc-" + eventTheme,
        description: eventDescription
      });
      editEventPopup.modal('hide');
    });
    deleteEventBtn.on("click", function (e) {
      e.preventDefault();
      var selectEvent = calendar.getEventById(editEventForm[0].dataset.id);
      selectEvent.remove();
    });
    function removePopover() {
      var fcPopover = document.querySelectorAll('.event-popover');
      fcPopover.forEach(function (elm) {
        elm.remove();
      });
    }
    function to12(time) {
      time = time.toString().match(/^([01]\d|2[0-3])(:)([0-5]\d)(:[0-5]\d)?$/) || [time];
      if (time.length > 1) {
        time = time.slice(1);
        time.pop();
        time[5] = +time[0] < 12 ? ' AM' : ' PM'; // Set AM/PM
        time[0] = +time[0] % 12 || 12;
      }
      time = time.join('');
      return time;
    }

    function customCalSelect(cat) {
      if (!cat.id) {
        return cat.text;
      }
      var $cat = $('<span class="fc-' + cat.element.value + '"> <span class="dot"></span>' + cat.text + '</span>');
      return $cat;
    }
    ;

    NioApp.Select2('.select-calendar-theme', {
      templateResult: customCalSelect
    });
    addEventPopup.on('hidden.bs.modal', function (e) {
      setTimeout(function () {
        $('#addEventForm input,#addEventForm textarea').val('');
        $('#event-theme').val('event-primary');
        $('#event-theme').trigger('change.select2');
      }, 1000);
    });
    previewEventPopup.on('hidden.bs.modal', function (e) {
      $('#preview-event-header').removeClass().addClass('modal-header');
    });
  };
  NioApp.coms.docReady.push(NioApp.Calendar);
}(NioApp, jQuery);*/