(function () {
  const calendaroffcanvas = new bootstrap.Offcanvas('#calendar-add_edit_event');
  const calendarmodal = new bootstrap.Modal('#calendar-modal');
  var calendevent = '';

  var date = new Date();
  var d = date.getDate();
  var m = date.getMonth();
  var y = date.getFullYear();

  var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
    locale: 'es',
    displayEventTime: true,  // <-- aquí
    displayEventEnd: false,
    eventTimeFormat: {
      hour: '2-digit',
      minute: '2-digit',
      meridiem: true // si quieres AM/PM pon: true
    },
    headerToolbar: {
      left: 'prev,next today',
      center: 'title',
      right: 'dayGridMonth,listMonth'
    },
    buttonText: {
      today: 'Hoy',
      month: 'Mes',
      week: 'Semana',
      day: 'Día',
      list: 'Lista'
    },
    themeSystem: 'bootstrap',
    initialDate: new Date(y, m, 16),
    slotDuration: '00:10:00',
    navLinks: true,
    height: 'auto',
    droppable: true,
    selectable: true,
    selectMirror: true,
    editable: true,
    dayMaxEvents: true,
    handleWindowResize: true,
    select: function (info) {

      document.getElementById('agenda_id').value = 0;
      document.getElementById('pc-e-date').value = info.startStr;
      document.getElementById('pc-e-title').value = "";
      document.getElementById('pc-e-description').value = "";
      document.getElementById('pc-e-notify-time').value = "0";
      document.getElementById('pc-e-btn-text').innerHTML = '<i class="align-text-bottom me-1 ti ti-calendar-plus"></i> Agregar';
      document.querySelector('#pc_event_add').setAttribute('data-pc-action', 'add');

      document.getElementById('calendar-add_event-title').innerHTML = 'Agregar Actividad';

      calendaroffcanvas.show();
      calendar.unselect();
    },
    eventClick: function (info) {
      console.log(info.event);

      calendevent = info.event;
      var clickedevent = info.event;
      var e_title = clickedevent.title === undefined ? '' : clickedevent.title;
      var e_desc = clickedevent.extendedProps.description === undefined ? '' : clickedevent.extendedProps.description;
      var e_date_start = clickedevent.start === null ? '' : dateformat(clickedevent.start);
      var e_date_end = clickedevent.end === null ? '' : " <i class='text-sm'>to</i> " + dateformat(clickedevent.end);
      e_date_end = clickedevent.end === null ? '' : e_date_end;
      var dias_notificar = clickedevent.extendedProps.dias_notificar === undefined ? '' : clickedevent.extendedProps.dias_notificar;
      var horas_notificar = clickedevent.extendedProps.horas_notificar === undefined ? '' : clickedevent.extendedProps.horas_notificar;

      if (dias_notificar > 0) {
        document.querySelector('.pc-event-notificar').innerHTML = dias_notificar + " días";
      } else {
        document.querySelector('.pc-event-notificar').innerHTML = horas_notificar + " horas";
      }

      document.querySelector('.calendar-modal-title').innerHTML = e_title;
      document.querySelector('.pc-event-title').innerHTML = e_title;
      document.querySelector('.pc-event-description').innerHTML = e_desc;
      document.querySelector('.pc-event-date').innerHTML = e_date_start + e_date_end;

      calendarmodal.show();
    },
    events: function (info, successCallback, failureCallback) {
      fetch('agenda/getAgenda') // URL de tu endpoint
        .then(response => response.json())
        .then(data => {

          successCallback(data);
        })
        .catch(error => {
          console.error('Error al cargar eventos:', error);
          failureCallback(error);
        });
    }
  });

  calendar.render();
  document.addEventListener('DOMContentLoaded', function () {
    var calbtn = document.querySelectorAll('.fc-toolbar-chunk');
    for (var t = 0; t < calbtn.length; t++) {
      var c = calbtn[t];
      c.children[0].classList.remove('btn-group');
      c.children[0].classList.add('d-inline-flex');
    }
  });

  var pc_event_remove = document.querySelector('#pc_event_remove');
  if (pc_event_remove) {
    pc_event_remove.addEventListener('click', function () {
      const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
          confirmButton: 'btn btn-light-success',
          cancelButton: 'btn btn-light-danger'
        },
        buttonsStyling: false
      });
      swalWithBootstrapButtons
        .fire({
          title: 'Are you sure?',
          text: 'you want to delete this event?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, delete it!',
          cancelButtonText: 'No, cancel!',
          reverseButtons: true
        })
        .then((result) => {
          if (result.isConfirmed) {
            calendevent.remove();
            calendarmodal.hide();
            swalWithBootstrapButtons.fire('Deleted!', 'Your Event has been deleted.', 'success');
          } else if (result.dismiss === Swal.DismissReason.cancel) {
            swalWithBootstrapButtons.fire('Cancelled', 'Your Event data is safe.', 'error');
          }
        });
    });
  }

  //agregar una actividad
  const formAdd = document.querySelector('#pc-form-event');
  if (formAdd) {
    formAdd.addEventListener('submit', function (e) {
      e.preventDefault();
      const formData = new FormData(formAdd);

      const pc_event_add = document.getElementById('pc_event_add');
      pc_event_add.disabled = true;

      fetch('agenda/save', {
        method: 'POST',
        body: formData
      })
        .then(response => response.json())
        .then(data => {
          pc_event_add.disabled = false;
          if (data.status == 'success') {
            calendar.refetchEvents();
            calendaroffcanvas.hide();
            Swal.fire({
              customClass: {
                confirmButton: 'btn btn-light-primary'
              },
              buttonsStyling: false,
              icon: 'success',
              title: 'Success',
              text: data.message
            });
          } else {
            Swal.fire({
              customClass: {
                confirmButton: 'btn btn-light-primary'
              },
              buttonsStyling: false,
              icon: 'error',
              title: 'Error',
              text: data.message
            });
          }
        })
        .catch(error => {
          console.error('Error al agregar actividad:', error);
        });
    });
  }

  var pc_event_edit = document.querySelector('#pc_event_edit');
  if (pc_event_edit) {
    pc_event_edit.addEventListener('click', function (event) {
      event.preventDefault();

      var e_title = calendevent.title === undefined ? '' : calendevent.title;
      var e_desc = calendevent.extendedProps.description === undefined ? '' : calendevent.extendedProps.description;

      document.getElementById('calendar-add_event-title').innerHTML = 'Editar Actividad';

      document.getElementById('agenda_id').value = calendevent.id;
      document.getElementById('pc-e-title').value = e_title;
      document.getElementById('pc-e-description').value = e_desc;
      var sdt = calendevent.start;

      const fecha = sdt.getFullYear() + '-' + getRound(sdt.getMonth() + 1) + '-' + getRound(sdt.getDate());

      const hora = getRound(sdt.getHours()) + ':' + getRound(sdt.getMinutes());

      document.getElementById('pc-e-date').value = fecha;
      document.getElementById('pc-e-time').value = hora;

      const dias_notificar = calendevent.extendedProps.dias_notificar;
      let notify_time;

      if (dias_notificar > 0) {
        document.getElementById('por_dia').checked = true;
        document.getElementById('por_hora').checked = false;
        notify_time = dias_notificar;
      } else {
        document.getElementById('por_hora').checked = true;
        document.getElementById('por_dia').checked = false;
        notify_time = calendevent.extendedProps.horas_notificar;
      }

      document.getElementById('pc-e-notify-time').value = notify_time;


      document.getElementById('pc-e-btn-text').innerHTML = '<i class="align-text-bottom me-1 ti ti-calendar-stats"></i> Editar';
      document.querySelector('#pc_event_add').setAttribute('data-pc-action', 'edit');
      calendarmodal.hide();
      calendaroffcanvas.show();
    });
  }
  //  get round value
  function getRound(vale) {
    var tmp = '';
    if (vale < 10) {
      tmp = '0' + vale;
    } else {
      tmp = vale;
    }
    return tmp;
  }

  //  get time
  function getTime(timeValue) {
    timeValue = new Date(timeValue);
    if (timeValue.getHours() != null) {
      var hour = timeValue.getHours();
      var minute = timeValue.getMinutes() ? timeValue.getMinutes() : 0;
      return hour + ':' + minute;
    }
  }

  //  get date
  function dateformat(dt) {
    var d = new Date(dt);

    var day = d.getDate().toString().padStart(2, '0');
    var month = (d.getMonth() + 1).toString().padStart(2, '0');
    var year = d.getFullYear();

    var hours = d.getHours();
    var minutes = d.getMinutes().toString().padStart(2, '0');
    var ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;

    return `${day}/${month}/${year} ${hours}:${minutes} ${ampm}`;
  }

  //  get full date
  function timeformat(time) {
    var timeFormat = time.split(':');
    var hours = timeFormat[0];
    var minutes = timeFormat[1];
    var newformat = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12;
    hours = hours ? hours : 12;
    minutes = minutes < 10 ? '0' + minutes : minutes;
    return hours + ':' + minutes + ' ' + newformat;
  }
})();
