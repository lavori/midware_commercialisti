<style>
    .nk-calendar#calendar {
        height: 42em !important;
    }
</style>
<!-- content @s -->
<h2 class="nk-block-title">Calendario Scadenze</h2>
<div class="nk-content p-0">
    <div class="card">
    </div>
    <div class="card mt-0">
        <div class="card-inner">
            <div id="calendar" class="nk-calendar"></div>
        </div>
    </div>
</div>
<?php  ?>
<!-- content @e -->
<div class="modal fade" id="addEventPopup">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Events</h5>
                <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <em class="icon ni ni-cross"></em>
                </a>
            </div>
            <div class="modal-body">
                <form action="#" id="addEventForm" class="form-validate is-alter">
                    <div class="row gx-4 gy-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label class="form-label" for="event-description">Event Description</label>
                                <div class="form-control-wrap">
                                    <textarea class="form-control" id="event-description"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <ul class="d-flex justify-content-between gx-4 mt-1">
                                <li>
                                    <button id="addEvent" type="submit" class="btn btn-primary">Add Event</button>
                                </li>
                                <li>
                                    <button id="resetEvent" data-bs-dismiss="modal"
                                        class="btn btn-danger btn-dim">Discard</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="previewEventPopup">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div id="preview-event-header" class="modal-header">
                <h5 id="preview-event-title" class="modal-title"></h5>
                <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <em class="icon ni ni-cross"></em>
                </a>
            </div>
            <div class="modal-body">
                <div class="row gy-3 py-1">
                    <div class="col-sm-10" id="preview-event-description-check">
                        <h6 class="overline-title">Description</h6>
                        <p id="preview-event-description"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Script per passare gli eventi PHP a JavaScript -->
<script>
    window.calendarEventsFromPHP = <?php echo isset($events) && is_array($events) ? json_encode($events) : '[]'; ?>;
    window.calendarNoEventsText = 'Non ci sono rate in scadenza da visualizzare';
</script>